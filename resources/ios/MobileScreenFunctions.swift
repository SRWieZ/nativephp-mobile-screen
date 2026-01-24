import Foundation
import UIKit

enum MobileScreenFunctions {

    private class ScreenState {
        static let shared = ScreenState()
        var originalBrightness: CGFloat = -1
        var brightnessObserver: NSObjectProtocol?
        var isListening = false
    }

    class KeepAwake: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let enabled = parameters["enabled"] as? Bool ?? true

            DispatchQueue.main.sync {
                UIApplication.shared.isIdleTimerDisabled = enabled
            }

            return BridgeResponse.success(data: ["enabled": enabled])
        }
    }

    class IsAwake: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            var awake = false

            DispatchQueue.main.sync {
                awake = UIApplication.shared.isIdleTimerDisabled
            }

            return BridgeResponse.success(data: ["awake": awake])
        }
    }

    class SetBrightness: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            guard let level = parameters["level"] as? Double else {
                return BridgeResponse.success(data: [
                    "success": false,
                    "error": "Missing level parameter"
                ])
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

    class GetBrightness: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            var brightness: CGFloat = 0

            DispatchQueue.main.sync {
                brightness = UIScreen.main.brightness
            }

            return BridgeResponse.success(data: ["level": Double(brightness)])
        }
    }

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
