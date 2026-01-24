<?php

namespace SRWieZ\MobileScreen\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool keepAwake(bool $enabled = true)
 * @method static bool allowSleep()
 * @method static bool isAwake()
 * @method static bool|float setBrightness(float $level)
 * @method static float|null getBrightness()
 * @method static bool|float resetBrightness()
 * @method static bool startBrightnessListener()
 * @method static bool stopBrightnessListener()
 *
 * @see \SRWieZ\MobileScreen\MobileScreen
 */
class MobileScreen extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SRWieZ\MobileScreen\MobileScreen::class;
    }
}
