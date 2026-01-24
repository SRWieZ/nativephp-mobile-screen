<?php

namespace SRWieZ\MobileScreen;

class MobileScreen
{
    /**
     * Keep the screen awake (prevent the device from sleeping)
     */
    public function keepAwake(bool $enabled = true): bool
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.KeepAwake', json_encode(['enabled' => $enabled]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->enabled ?? false;
            }
        }

        return false;
    }

    /**
     * Allow the screen to sleep (disable wake lock)
     */
    public function allowSleep(): bool
    {
        return ! $this->keepAwake(false);
    }

    /**
     * Check if the screen wake lock is currently active
     */
    public function isAwake(): bool
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.IsAwake', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->awake ?? false;
            }
        }

        return false;
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

        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.SetBrightness', json_encode(['level' => $level]));

            if ($result) {
                $decoded = json_decode($result);

                if ($decoded->success ?? false) {
                    return $decoded->level ?? $level;
                }
            }
        }

        return false;
    }

    /**
     * Get the current screen brightness level
     *
     * @return float|null Brightness level from 0.0 to 1.0, or null if unavailable
     */
    public function getBrightness(): ?float
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.GetBrightness', '{}');

            if ($result) {
                $decoded = json_decode($result);

                $level = $decoded->level ?? null;

                return $level !== null ? (float) $level : null;
            }
        }

        return null;
    }

    /**
     * Reset the screen brightness to the system default
     *
     * @return bool|float Returns the new brightness level, or false on failure
     */
    public function resetBrightness(): bool|float
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.ResetBrightness', '{}');

            if ($result) {
                $decoded = json_decode($result);

                if ($decoded->success ?? false) {
                    return $decoded->level ?? true;
                }
            }
        }

        return false;
    }

    /**
     * Start listening for brightness changes (iOS only)
     */
    public function startBrightnessListener(): bool
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.StartBrightnessListener', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->success ?? false;
            }
        }

        return false;
    }

    /**
     * Stop listening for brightness changes
     */
    public function stopBrightnessListener(): bool
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileScreen.StopBrightnessListener', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->success ?? false;
            }
        }

        return false;
    }
}
