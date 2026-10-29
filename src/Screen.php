<?php

namespace SRWieZ\NativePHP\Mobile\Screen;

use Illuminate\Support\Facades\Log;
use stdClass;

class Screen
{
    /**
     * Keep the screen awake (prevent the device from sleeping)
     *
     * @return bool True when the requested wake lock state was applied
     */
    public function keepAwake(bool $enabled = true): bool
    {
        $result = $this->call('MobileScreen.KeepAwake', ['enabled' => $enabled]);

        return ($result['enabled'] ?? null) === $enabled;
    }

    /**
     * Allow the screen to sleep (disable wake lock)
     *
     * @return bool True when the wake lock was released
     */
    public function allowSleep(): bool
    {
        return $this->keepAwake(false);
    }

    /**
     * Check if the screen wake lock is currently active
     */
    public function isAwake(): bool
    {
        $result = $this->call('MobileScreen.IsAwake');

        return $result['awake'] ?? false;
    }

    /**
     * Set the screen brightness level
     *
     * @param  float  $level  Brightness level from 0.0 (minimum) to 1.0 (maximum)
     * @return bool|float Returns the actual brightness level set, or false on failure
     */
    public function setBrightness(float $level): bool|float
    {
        $level = max(0.0, min(1.0, $level));

        $result = $this->call('MobileScreen.SetBrightness', ['level' => $level]);

        if (! ($result['success'] ?? false)) {
            return false;
        }

        return $result['level'] ?? $level;
    }

    /**
     * Get the current screen brightness level
     *
     * @return float|null Brightness level from 0.0 to 1.0, or null if unavailable
     */
    public function getBrightness(): ?float
    {
        $level = $this->call('MobileScreen.GetBrightness')['level'] ?? null;

        return is_numeric($level) && $level >= 0 ? (float) $level : null;
    }

    /**
     * Reset the screen brightness to the system default
     *
     * @return bool|float Returns the new brightness level, or false on failure
     */
    public function resetBrightness(): bool|float
    {
        $result = $this->call('MobileScreen.ResetBrightness');

        if (! ($result['success'] ?? false)) {
            return false;
        }

        return $result['level'] ?? true;
    }

    /**
     * Start listening for brightness changes
     *
     * On iOS this reports user-initiated changes (e.g. Control Center);
     * on Android it reports system brightness changes.
     */
    public function startBrightnessListener(): bool
    {
        return $this->call('MobileScreen.StartBrightnessListener')['success'] ?? false;
    }

    /**
     * Stop listening for brightness changes
     */
    public function stopBrightnessListener(): bool
    {
        return $this->call('MobileScreen.StopBrightnessListener')['success'] ?? false;
    }

    /**
     * Whether this plugin's native side is installed in the running app
     *
     * Advisory and PHP-only: it reports what the native bridge registered,
     * so it is false outside a NativePHP app (tests, web preview).
     */
    public function isAvailable(): bool
    {
        return $this->can('MobileScreen.IsAwake');
    }

    /**
     * Whether the running app can control the screen brightness
     *
     * Advisory and PHP-only; see isAvailable().
     */
    public function canControlBrightness(): bool
    {
        return $this->can('MobileScreen.SetBrightness');
    }

    /**
     * Call a native bridge function and decode its response.
     *
     * Returns null when the bridge is unavailable, answers nothing or invalid
     * JSON, or reports an error ({"status": "error", ...}). Each public method
     * maps null to its documented failure value.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    protected function call(string $method, array $params = []): ?array
    {
        if (! function_exists('nativephp_call')) {
            return null;
        }

        $result = nativephp_call($method, json_encode($params === [] ? new stdClass : $params));

        if (! $result) {
            return null;
        }

        $decoded = json_decode($result, true);

        if (! is_array($decoded)) {
            return null;
        }

        if (($decoded['status'] ?? null) === 'error') {
            Log::debug('Native bridge call failed', [
                'method' => $method,
                'code' => $decoded['code'] ?? null,
                'message' => $decoded['message'] ?? null,
            ]);

            return null;
        }

        return $decoded;
    }

    /**
     * Whether the native bridge registered a function. False when there is
     * no bridge at all.
     */
    protected function can(string $method): bool
    {
        return function_exists('nativephp_can') && nativephp_can($method);
    }
}
