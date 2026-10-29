<?php

use Native\Mobile\Events\Contracts\DispatchableFromNative;
use SRWieZ\NativePHP\Mobile\Screen\Events as ScreenEvents;
use SRWieZ\NativePHP\Mobile\Screen\Screen;

beforeEach(function () {
    $this->pluginPath = dirname(__DIR__);
    $this->manifestPath = $this->pluginPath.'/nativephp.json';
});

describe('Plugin Manifest', function () {
    it('has a valid nativephp.json file', function () {
        expect(file_exists($this->manifestPath))->toBeTrue();

        $content = file_get_contents($this->manifestPath);
        $manifest = json_decode($content, true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    });

    it('has required fields', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest)->toHaveKeys(['namespace', 'bridge_functions']);
    });

    it('has valid bridge functions', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest['bridge_functions'])->toBeArray();

        foreach ($manifest['bridge_functions'] as $function) {
            expect($function)->toHaveKeys(['name']);
            expect(isset($function['android']) || isset($function['ios']))->toBeTrue();
        }
    });

    it('has all required bridge functions', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        $functionNames = array_column($manifest['bridge_functions'], 'name');

        expect($functionNames)->toContain('MobileScreen.KeepAwake');
        expect($functionNames)->toContain('MobileScreen.IsAwake');
        expect($functionNames)->toContain('MobileScreen.SetBrightness');
        expect($functionNames)->toContain('MobileScreen.GetBrightness');
        expect($functionNames)->toContain('MobileScreen.ResetBrightness');
        expect($functionNames)->toContain('MobileScreen.StartBrightnessListener');
        expect($functionNames)->toContain('MobileScreen.StopBrightnessListener');
    });

    it('has events registered', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest)->toHaveKey('events');
        expect($manifest['events'])->toContain('SRWieZ\\NativePHP\\Mobile\\Screen\\Events\\BrightnessChanged');
    });
});

describe('Native Code', function () {
    it('has Android Kotlin file', function () {
        $kotlinFile = $this->pluginPath.'/resources/android/MobileScreenFunctions.kt';
        expect(file_exists($kotlinFile))->toBeTrue();
    });

    it('has iOS Swift file', function () {
        $swiftFile = $this->pluginPath.'/resources/ios/MobileScreenFunctions.swift';
        expect(file_exists($swiftFile))->toBeTrue();
    });

    it('has Android implementation for all bridge functions', function () {
        $kotlinFile = $this->pluginPath.'/resources/android/MobileScreenFunctions.kt';
        $content = file_get_contents($kotlinFile);

        expect($content)->toContain('class KeepAwake');
        expect($content)->toContain('class IsAwake');
        expect($content)->toContain('class SetBrightness');
        expect($content)->toContain('class GetBrightness');
        expect($content)->toContain('class ResetBrightness');
        expect($content)->toContain('class StartBrightnessListener');
        expect($content)->toContain('class StopBrightnessListener');
    });

    it('has iOS implementation for all bridge functions', function () {
        $swiftFile = $this->pluginPath.'/resources/ios/MobileScreenFunctions.swift';
        $content = file_get_contents($swiftFile);

        expect($content)->toContain('class KeepAwake');
        expect($content)->toContain('class IsAwake');
        expect($content)->toContain('class SetBrightness');
        expect($content)->toContain('class GetBrightness');
        expect($content)->toContain('class ResetBrightness');
        expect($content)->toContain('class StartBrightnessListener');
        expect($content)->toContain('class StopBrightnessListener');
    });
});

describe('PHP Classes', function () {
    it('has service provider', function () {
        $file = $this->pluginPath.'/src/ScreenServiceProvider.php';
        expect(file_exists($file))->toBeTrue();
    });

    it('has facade', function () {
        $file = $this->pluginPath.'/src/Facades/Screen.php';
        expect(file_exists($file))->toBeTrue();
    });

    it('has main implementation class', function () {
        $file = $this->pluginPath.'/src/Screen.php';
        expect(file_exists($file))->toBeTrue();
    });

    it('has all required methods in main class', function () {
        $file = $this->pluginPath.'/src/Screen.php';
        $content = file_get_contents($file);

        expect($content)->toContain('function keepAwake');
        expect($content)->toContain('function allowSleep');
        expect($content)->toContain('function isAwake');
        expect($content)->toContain('function setBrightness');
        expect($content)->toContain('function getBrightness');
        expect($content)->toContain('function resetBrightness');
        expect($content)->toContain('function startBrightnessListener');
        expect($content)->toContain('function stopBrightnessListener');
    });

    it('has BrightnessChanged event class', function () {
        $file = $this->pluginPath.'/src/Events/BrightnessChanged.php';
        expect(file_exists($file))->toBeTrue();

        $content = file_get_contents($file);
        expect($content)->toContain('class BrightnessChanged');
        expect($content)->toContain('public float $level');
    });
});

describe('Composer Configuration', function () {
    it('has valid composer.json', function () {
        $composerPath = $this->pluginPath.'/composer.json';
        expect(file_exists($composerPath))->toBeTrue();

        $content = file_get_contents($composerPath);
        $composer = json_decode($content, true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE);
        expect($composer['type'])->toBe('nativephp-plugin');
    });
});

describe('JavaScript Module', function () {
    it('has JavaScript module file', function () {
        $jsFile = $this->pluginPath.'/resources/js/mobileScreen.js';
        expect(file_exists($jsFile))->toBeTrue();
    });

    it('exports all required functions', function () {
        $jsFile = $this->pluginPath.'/resources/js/mobileScreen.js';
        $content = file_get_contents($jsFile);

        expect($content)->toContain('export async function keepAwake');
        expect($content)->toContain('export async function allowSleep');
        expect($content)->toContain('export async function isAwake');
        expect($content)->toContain('export async function setBrightness');
        expect($content)->toContain('export async function getBrightness');
        expect($content)->toContain('export async function resetBrightness');
        expect($content)->toContain('export async function startBrightnessListener');
        expect($content)->toContain('export async function stopBrightnessListener');
    });
});

describe('Events', function () {
    beforeEach(function () {
        $this->events = json_decode(file_get_contents($this->manifestPath), true)['events'] ?? [];
    });

    it('lists only event classes that exist', function () {
        expect($this->events)->not->toBeEmpty();

        foreach ($this->events as $event) {
            expect(class_exists($event))->toBeTrue("{$event} is listed in nativephp.json but does not exist");
        }
    });

    it('marks every event as dispatchable from native code', function () {
        foreach ($this->events as $event) {
            expect(is_subclass_of($event, DispatchableFromNative::class))
                ->toBeTrue("{$event} must implement DispatchableFromNative, or NativePHP 4.6.1+ and 3.3.11+ refuse to dispatch it");
        }
    });

    it('lists every event class in the manifest', function () {
        $classes = array_map(
            fn (string $file): string => 'SRWieZ\\NativePHP\\Mobile\\Screen\\Events\\'.basename($file, '.php'),
            glob($this->pluginPath.'/src/Events/*.php'),
        );

        expect($this->events)->toEqualCanonicalizing($classes);
    });

    it('can be built from a partial native payload', function () {
        foreach ($this->events as $event) {
            $parameters = (new ReflectionClass($event))->getConstructor()?->getParameters() ?? [];

            foreach (array_slice($parameters, 1) as $parameter) {
                expect($parameter->isOptional())
                    ->toBeTrue("{$event}::\${$parameter->getName()} needs a default so `new \$event(...\$payload)` survives a missing key");
            }
        }
    });

    it('accepts every key the native side sends', function (string $event, array $payload) {
        // NativePHP 3.x hydrates events with `new $event(...$payload)`, which
        // throws on any payload key the constructor does not declare.
        expect(new $event(...$payload))->toBeInstanceOf($event);
    })->with([
        'BrightnessChanged (Android and iOS)' => [ScreenEvents\BrightnessChanged::class, ['level' => 0.47, 'timestamp' => 1793283006]],
    ]);
});

describe('Capability checks without a bridge', function () {
    it('reports nothing as available', function () {
        $screen = new Screen;

        expect($screen->isAvailable())->toBeFalse()
            ->and($screen->canControlBrightness())->toBeFalse();
    })->skip(fn () => function_exists('nativephp_can'), 'A nativephp_can() polyfill is loaded (NativePHP v4 test bridge).');
});
