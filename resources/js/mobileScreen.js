/**
 * MobileScreen Plugin for NativePHP Mobile
 *
 * Screen wake lock and brightness control for mobile apps.
 *
 * @example
 * import { mobileScreen } from '@srwiez/nativephp-mobile-screen';
 *
 * // Keep screen awake (e.g., for barcode display)
 * await mobileScreen.keepAwake();
 *
 * // Set brightness to maximum
 * await mobileScreen.setBrightness(1.0);
 *
 * // Reset when done
 * await mobileScreen.resetBrightness();
 * await mobileScreen.allowSleep();
 */

const baseUrl = '/_native/api/call';

async function bridgeCall(method, params = {}) {
    const response = await fetch(baseUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ method, params })
    });

    const result = await response.json();

    if (result.status === 'error') {
        throw new Error(result.message || 'Native call failed');
    }

    const nativeResponse = result.data;
    if (nativeResponse && nativeResponse.data !== undefined) {
        return nativeResponse.data;
    }

    return nativeResponse;
}

/**
 * Keep the screen awake (prevent device from sleeping)
 * @param {boolean} enabled - Whether to enable wake lock (default: true)
 * @returns {Promise<{enabled: boolean}>}
 */
export async function keepAwake(enabled = true) {
    return bridgeCall('MobileScreen.KeepAwake', { enabled });
}

/**
 * Allow the screen to sleep (disable wake lock)
 * @returns {Promise<{enabled: boolean}>}
 */
export async function allowSleep() {
    return bridgeCall('MobileScreen.KeepAwake', { enabled: false });
}

/**
 * Check if the screen wake lock is currently active
 * @returns {Promise<{awake: boolean}>}
 */
export async function isAwake() {
    return bridgeCall('MobileScreen.IsAwake');
}

/**
 * Set the screen brightness level
 * @param {number} level - Brightness level from 0.0 (minimum) to 1.0 (maximum)
 * @returns {Promise<{success: boolean, level: number}>}
 */
export async function setBrightness(level) {
    return bridgeCall('MobileScreen.SetBrightness', { level });
}

/**
 * Get the current screen brightness level
 * @returns {Promise<{level: number}>}
 */
export async function getBrightness() {
    return bridgeCall('MobileScreen.GetBrightness');
}

/**
 * Reset the screen brightness to the system default
 * @returns {Promise<{success: boolean}>}
 */
export async function resetBrightness() {
    return bridgeCall('MobileScreen.ResetBrightness');
}

/**
 * Start listening for brightness changes
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function startBrightnessListener() {
    return bridgeCall('MobileScreen.StartBrightnessListener');
}

/**
 * Stop listening for brightness changes
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function stopBrightnessListener() {
    return bridgeCall('MobileScreen.StopBrightnessListener');
}

export const mobileScreen = {
    keepAwake,
    allowSleep,
    isAwake,
    setBrightness,
    getBrightness,
    resetBrightness,
    startBrightnessListener,
    stopBrightnessListener
};

export default mobileScreen;
