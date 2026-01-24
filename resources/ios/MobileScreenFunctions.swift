import Foundation
import UIKit

/// MobileScreen bridge functions for iOS.
///
/// Provides screen wake lock and brightness control functionality:
/// - KeepAwake: Prevent device from sleeping
/// - IsAwake: Check wake lock status
/// - SetBrightness: Set screen brightness (0.0-1.0)
/// - GetBrightness: Get current brightness level
/// - ResetBrightness: Reset to original brightness
/// - StartBrightnessListener: Listen for brightness changes (user-initiated only)
/// - StopBrightnessListener: Stop listening for changes
enum MobileScreenFunctions {

    /// Internal state for tracking wake lock and brightness
    private class ScreenState {
        static let shared = ScreenState()
        var originalBrightness: CGFloat = -1
        var brightnessObserver: NSObjectProtocol?
        var isListening = false
    }

    /// Enable or disable screen wake lock to prevent device from sleeping
    class KeepAwake: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let enabled = parameters["enabled"] as? Bool ?? true

            DispatchQueue.main.sync {
                UIApplication.shared.isIdleTimerDisabled = enabled
            }

            return BridgeResponse.success(data: ["enabled": enabled])
        }
    }

    /// Check if screen wake lock is currently active
    class IsAwake: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            var awake = false

            DispatchQueue.main.sync {
                awake = UIApplication.shared.isIdleTimerDisabled
            }

            return BridgeResponse.success(data: ["awake": awake])
        }
    }

    /// Set screen brightness level (0.0-1.0)
    class SetBrightness: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            guard let level = parameters["level"] as? Double else {
                return BridgeResponse.error(code: "MISSING_PARAMETER", message: "Missing level parameter")
            }

            let clampedLevel = min(max(CGFloat(level), 0.0), 1.0)

            // Store original brightness if not already stored
            if ScreenState.shared.originalBrightness < 0 {
                DispatchQueue.main.sync {
                    ScreenState.shared.originalBrightness = UIScreen.main.brightness
                }
            }

            // Use sync to ensure brightness is set before returning
            DispatchQueue.main.sync {
                UIScreen.main.brightness = clampedLevel
            }

            // Read back the actual brightness
            var actualLevel: CGFloat = clampedLevel
            DispatchQueue.main.sync {
                actualLevel = UIScreen.main.brightness
            }

            return BridgeResponse.success(data: [
                "success": true,
                "level": Double(actualLevel)
            ])
        }
    }

    /// Get current screen brightness level (0.0-1.0)
    class GetBrightness: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            var brightness: CGFloat = 0

            DispatchQueue.main.sync {
                brightness = UIScreen.main.brightness
            }

            return BridgeResponse.success(data: ["level": Double(brightness)])
        }
    }

    /// Reset screen brightness to original value before app modified it
    class ResetBrightness: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let originalBrightness = ScreenState.shared.originalBrightness

            var newLevel: CGFloat = -1

            if originalBrightness >= 0 {
                DispatchQueue.main.sync {
                    UIScreen.main.brightness = originalBrightness
                    newLevel = UIScreen.main.brightness
                }
                // Reset stored original brightness
                ScreenState.shared.originalBrightness = -1
            } else {
                // No original stored, just return current
                DispatchQueue.main.sync {
                    newLevel = UIScreen.main.brightness
                }
            }

            return BridgeResponse.success(data: [
                "success": true,
                "level": Double(newLevel)
            ])
        }
    }

    /// Start listening for brightness changes (Note: only detects user-initiated changes via Control Center, not programmatic changes)
    class StartBrightnessListener: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            if ScreenState.shared.isListening {
                return BridgeResponse.success(data: [
                    "success": true,
                    "message": "Already listening"
                ])
            }

            ScreenState.shared.brightnessObserver = NotificationCenter.default.addObserver(
                forName: UIScreen.brightnessDidChangeNotification,
                object: nil,
                queue: .main
            ) { _ in
                let brightness = UIScreen.main.brightness
                let timestamp = Int(Date().timeIntervalSince1970)

                LaravelBridge.shared.send?(
                    "SRWieZ\\MobileScreen\\Events\\BrightnessChanged",
                    [
                        "level": Double(brightness),
                        "timestamp": timestamp
                    ]
                )
            }

            ScreenState.shared.isListening = true

            return BridgeResponse.success(data: [
                "success": true,
                "message": "Brightness listener started"
            ])
        }
    }

    /// Stop listening for brightness changes
    class StopBrightnessListener: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            if let observer = ScreenState.shared.brightnessObserver {
                NotificationCenter.default.removeObserver(observer)
                ScreenState.shared.brightnessObserver = nil
            }

            ScreenState.shared.isListening = false

            return BridgeResponse.success(data: [
                "success": true,
                "message": "Brightness listener stopped"
            ])
        }
    }
}
