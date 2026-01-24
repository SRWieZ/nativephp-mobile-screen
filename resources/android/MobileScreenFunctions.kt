package com.srwiez.plugins.mobilescreen

import android.database.ContentObserver
import android.os.Handler
import android.os.Looper
import android.provider.Settings
import android.view.WindowManager
import androidx.fragment.app.FragmentActivity
import com.nativephp.mobile.NativeActionCoordinator
import com.nativephp.mobile.bridge.BridgeError
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse
import org.json.JSONObject
import java.util.concurrent.CountDownLatch
import java.util.concurrent.TimeUnit

/**
 * MobileScreen bridge functions for Android.
 *
 * Provides screen wake lock and brightness control functionality:
 * - KeepAwake: Prevent device from sleeping
 * - IsAwake: Check wake lock status
 * - SetBrightness: Set screen brightness (0.0-1.0)
 * - GetBrightness: Get current brightness level
 * - ResetBrightness: Reset to system default
 * - StartBrightnessListener: Listen for system brightness changes
 * - StopBrightnessListener: Stop listening for changes
 */
object MobileScreenFunctions {

    /** Internal state for tracking wake lock and brightness */
    private object ScreenState {
        var isAwake = false
        var originalBrightness: Float = -1f
        var brightnessObserver: ContentObserver? = null
        var isListening = false
    }

    /** Enable or disable screen wake lock to prevent device from sleeping */
    class KeepAwake(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val enabled = parameters["enabled"] as? Boolean ?: true
            val latch = CountDownLatch(1)

            activity.runOnUiThread {
                if (enabled) {
                    activity.window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
                } else {
                    activity.window.clearFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
                }
                ScreenState.isAwake = enabled
                latch.countDown()
            }

            latch.await(1, TimeUnit.SECONDS)

            return BridgeResponse.success(mapOf("enabled" to enabled))
        }
    }

    /** Check if screen wake lock is currently active */
    class IsAwake(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            var awake = false
            val latch = CountDownLatch(1)

            activity.runOnUiThread {
                val flags = activity.window.attributes.flags
                awake = (flags and WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON) != 0
                latch.countDown()
            }

            latch.await(1, TimeUnit.SECONDS)

            return BridgeResponse.success(mapOf("awake" to awake))
        }
    }

    /** Set screen brightness level (0.0-1.0) */
    class SetBrightness(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val level = (parameters["level"] as? Number)?.toFloat()
                ?: return BridgeResponse.error(BridgeError("MISSING_PARAMETER", "Missing level parameter"))

            // Clamp brightness between 0.01 (to avoid device issues) and 1.0
            val clampedLevel = level.coerceIn(0.01f, 1.0f)
            var actualLevel = clampedLevel
            val latch = CountDownLatch(1)

            // Store original brightness if not already stored
            if (ScreenState.originalBrightness < 0) {
                val currentBrightness = activity.window.attributes.screenBrightness
                if (currentBrightness >= 0) {
                    ScreenState.originalBrightness = currentBrightness
                }
            }

            activity.runOnUiThread {
                val layoutParams = activity.window.attributes
                layoutParams.screenBrightness = clampedLevel
                activity.window.attributes = layoutParams
                // Read back actual value
                actualLevel = activity.window.attributes.screenBrightness
                latch.countDown()
            }

            latch.await(1, TimeUnit.SECONDS)

            return BridgeResponse.success(mapOf(
                "success" to true,
                "level" to actualLevel
            ))
        }
    }

    /** Get current screen brightness level (0.0-1.0) */
    class GetBrightness(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            var brightness = -1f
            val latch = CountDownLatch(1)

            activity.runOnUiThread {
                brightness = activity.window.attributes.screenBrightness
                latch.countDown()
            }

            latch.await(1, TimeUnit.SECONDS)

            // If brightness is negative, it means using system default
            val level = if (brightness < 0) {
                // Try to get system brightness (0-255 range, convert to 0-1)
                try {
                    val systemBrightness = Settings.System.getInt(
                        activity.contentResolver,
                        Settings.System.SCREEN_BRIGHTNESS
                    )
                    systemBrightness / 255f
                } catch (e: Exception) {
                    -1f
                }
            } else {
                brightness
            }

            return BridgeResponse.success(mapOf("level" to level))
        }
    }

    /** Reset screen brightness to system default */
    class ResetBrightness(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            var newLevel = -1f
            val latch = CountDownLatch(1)

            activity.runOnUiThread {
                val layoutParams = activity.window.attributes
                // BRIGHTNESS_OVERRIDE_NONE (-1) tells the system to use the default brightness
                layoutParams.screenBrightness = WindowManager.LayoutParams.BRIGHTNESS_OVERRIDE_NONE
                activity.window.attributes = layoutParams

                // Get system brightness as the new level
                try {
                    val systemBrightness = Settings.System.getInt(
                        activity.contentResolver,
                        Settings.System.SCREEN_BRIGHTNESS
                    )
                    newLevel = systemBrightness / 255f
                } catch (e: Exception) {
                    newLevel = 0.5f // Default fallback
                }
                latch.countDown()
            }

            latch.await(1, TimeUnit.SECONDS)

            // Reset stored original brightness
            ScreenState.originalBrightness = -1f

            return BridgeResponse.success(mapOf(
                "success" to true,
                "level" to newLevel
            ))
        }
    }

    /** Start listening for system brightness changes (Note: only detects system-level changes, not app-level) */
    class StartBrightnessListener(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            if (ScreenState.isListening) {
                return BridgeResponse.success(mapOf(
                    "success" to true,
                    "message" to "Already listening"
                ))
            }

            val handler = Handler(Looper.getMainLooper())
            ScreenState.brightnessObserver = object : ContentObserver(handler) {
                override fun onChange(selfChange: Boolean) {
                    super.onChange(selfChange)
                    if (selfChange) return

                    try {
                        val brightness = Settings.System.getInt(
                            activity.contentResolver,
                            Settings.System.SCREEN_BRIGHTNESS
                        )
                        val level = brightness / 255f
                        val timestamp = System.currentTimeMillis() / 1000

                        val payload = JSONObject().apply {
                            put("level", level)
                            put("timestamp", timestamp)
                        }

                        NativeActionCoordinator.dispatchEvent(
                            activity,
                            "SRWieZ\\MobileScreen\\Events\\BrightnessChanged",
                            payload.toString()
                        )
                    } catch (e: Exception) {
                        // Ignore errors reading brightness
                    }
                }
            }

            activity.contentResolver.registerContentObserver(
                Settings.System.getUriFor(Settings.System.SCREEN_BRIGHTNESS),
                true,
                ScreenState.brightnessObserver!!
            )

            ScreenState.isListening = true

            return BridgeResponse.success(mapOf(
                "success" to true,
                "message" to "Brightness listener started (system brightness only)"
            ))
        }
    }

    /** Stop listening for brightness changes */
    class StopBrightnessListener(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            ScreenState.brightnessObserver?.let { observer ->
                activity.contentResolver.unregisterContentObserver(observer)
                ScreenState.brightnessObserver = null
            }

            ScreenState.isListening = false

            return BridgeResponse.success(mapOf(
                "success" to true,
                "message" to "Brightness listener stopped"
            ))
        }
    }
}
