<?php

use Native\Mobile\Testing\FakeBridge;
use SRWieZ\NativePHP\Mobile\Screen\Facades\Screen;

beforeEach(function () {
    if (! class_exists(FakeBridge::class)) {
        $this->markTestSkipped('Behavioral tests require nativephp/mobile v4 (FakeBridge).');
    }

    $this->bridge = FakeBridge::enable();
});

describe('keepAwake', function () {
    it('enables the wake lock', function () {
        $this->bridge->respondTo('MobileScreen.KeepAwake', fn (array $params) => ['enabled' => $params['enabled']]);

        expect(Screen::keepAwake())->toBeTrue();

        $this->bridge->assertKeptAwake();
    });

    it('disables the wake lock', function () {
        $this->bridge->respondTo('MobileScreen.KeepAwake', fn (array $params) => ['enabled' => $params['enabled']]);

        expect(Screen::keepAwake(false))->toBeTrue();

        $this->bridge->assertSleepAllowed();
    });

    it('reports failure when the bridge does not answer', function () {
        expect(Screen::keepAwake())->toBeFalse();
    });

    it('reports failure when the native side returns an error', function () {
        $this->bridge->respondTo('MobileScreen.KeepAwake', ['status' => 'error', 'message' => 'nope']);

        expect(Screen::keepAwake())->toBeFalse();
    });
});

describe('allowSleep', function () {
    it('releases the wake lock', function () {
        $this->bridge->respondTo('MobileScreen.KeepAwake', fn (array $params) => ['enabled' => $params['enabled']]);

        expect(Screen::allowSleep())->toBeTrue();

        $this->bridge->assertSleepAllowed();
    });

    it('reports failure when the bridge does not answer', function () {
        expect(Screen::allowSleep())->toBeFalse();
    });
});

describe('isAwake', function () {
    it('returns the wake lock state', function () {
        $this->bridge->respondTo('MobileScreen.IsAwake', ['awake' => true]);

        expect(Screen::isAwake())->toBeTrue();
    });

    it('returns false when the bridge does not answer', function () {
        expect(Screen::isAwake())->toBeFalse();
    });
});

describe('setBrightness', function () {
    it('sets the brightness and returns the applied level', function () {
        $this->bridge->respondTo('MobileScreen.SetBrightness', fn (array $params) => ['success' => true, 'level' => $params['level']]);

        expect(Screen::setBrightness(0.8))->toBe(0.8);

        $this->bridge->assertBrightnessSet(0.8);
    });

    it('clamps the level to the 0.0-1.0 range before calling native', function () {
        $this->bridge->respondTo('MobileScreen.SetBrightness', fn (array $params) => ['success' => true, 'level' => $params['level']]);

        expect(Screen::setBrightness(1.5))->toBe(1.0)
            ->and(Screen::setBrightness(-0.5))->toBe(0.0);

        $this->bridge->assertBrightnessSet(1.0);
        $this->bridge->assertBrightnessSet(0.0);
    });

    it('returns false when the native side reports failure', function () {
        $this->bridge->respondTo('MobileScreen.SetBrightness', ['success' => false, 'error' => 'Missing level parameter']);

        expect(Screen::setBrightness(0.5))->toBeFalse();
    });

    it('returns false when the bridge does not answer', function () {
        expect(Screen::setBrightness(0.5))->toBeFalse();
    });
});

describe('getBrightness', function () {
    it('returns the current level', function () {
        $this->bridge->withBrightness(0.42);

        expect(Screen::getBrightness())->toBe(0.42);
    });

    it('returns null when native reports a negative sentinel', function () {
        $this->bridge->respondTo('MobileScreen.GetBrightness', ['level' => -1]);

        expect(Screen::getBrightness())->toBeNull();
    });

    it('returns null when the bridge does not answer', function () {
        expect(Screen::getBrightness())->toBeNull();
    });
});

describe('resetBrightness', function () {
    it('resets to the system default and returns the new level', function () {
        $this->bridge->respondTo('MobileScreen.ResetBrightness', ['success' => true, 'level' => 0.5]);

        expect(Screen::resetBrightness())->toBe(0.5);
    });

    it('returns false when the bridge does not answer', function () {
        expect(Screen::resetBrightness())->toBeFalse();
    });
});

describe('brightness listener', function () {
    it('starts and stops the listener', function () {
        $this->bridge->respondTo('MobileScreen.StartBrightnessListener', ['success' => true]);
        $this->bridge->respondTo('MobileScreen.StopBrightnessListener', ['success' => true]);

        expect(Screen::startBrightnessListener())->toBeTrue()
            ->and(Screen::stopBrightnessListener())->toBeTrue();

        $this->bridge->assertCallOrder([
            'MobileScreen.StartBrightnessListener',
            'MobileScreen.StopBrightnessListener',
        ]);
    });
});
