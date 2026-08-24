<?php

namespace SRWieZ\NativePHP\Mobile\Screen;

use Illuminate\Support\ServiceProvider;
use Native\Mobile\Testing\FakeBridge;

class ScreenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Screen::class, function () {
            return new Screen;
        });
    }

    public function boot(): void
    {
        if (class_exists(FakeBridge::class)) {
            $this->registerTestingMacros();
        }
    }

    /**
     * Teach the NativePHP v4 FakeBridge this plugin's test vocabulary, so app
     * tests can assert in domain terms instead of raw bridge method strings.
     */
    protected function registerTestingMacros(): void
    {
        FakeBridge::macro('assertKeptAwake', function (): FakeBridge {
            /** @var FakeBridge $this */
            return $this->assertCalled('MobileScreen.KeepAwake', fn (array $params): bool => ($params['enabled'] ?? true) === true);
        });

        FakeBridge::macro('assertSleepAllowed', function (): FakeBridge {
            /** @var FakeBridge $this */
            return $this->assertCalled('MobileScreen.KeepAwake', fn (array $params): bool => ($params['enabled'] ?? true) === false);
        });

        FakeBridge::macro('assertBrightnessSet', function (?float $level = null): FakeBridge {
            /** @var FakeBridge $this */
            return $this->assertCalled('MobileScreen.SetBrightness', fn (array $params): bool => $level === null || abs(($params['level'] ?? -1.0) - $level) < 0.0001);
        });

        FakeBridge::macro('withBrightness', function (float $level): FakeBridge {
            /** @var FakeBridge $this */
            return $this->respondTo('MobileScreen.GetBrightness', ['level' => $level]);
        });
    }
}
