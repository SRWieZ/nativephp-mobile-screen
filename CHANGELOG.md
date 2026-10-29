# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.0] - 2026-10-29

### Added

- `Screen::isAvailable()` and `Screen::canControlBrightness()`: PHP-only
  checks that tell you whether the plugin's native side is installed and
  whether brightness control is available. Test the fallback path with
  `FakeBridge::withoutCapability()`.
- This changelog, backfilled from the earlier releases.

### Changed

- Documented how to listen for events on NativePHP v4 native screens, with
  the payload keys of every event.
- The Boost guidelines describe the current API. They still showed the 1.x
  `MobileScreen` facade, its namespace and array results.
- The README states the real NativePHP v4 minimums (Android API 26, iOS
  18.2), not API 29 and iOS 18.
- `LICENSE` is now `LICENSE.md`, like the other plugins.

### Fixed

- PHP listeners (`Event::listen`) now receive this plugin's events on
  nativephp/mobile 4.6.1+ and 3.3.11+. Those versions only deliver events
  that opt in, and the plugin's events did not, so listeners stayed silent
  while Livewire still reacted. The minimum core versions move to 4.6.1
  and 3.3.11 accordingly.
- Native-side errors are now reported as failures instead of being read as
  data. The error code and message are logged at debug level.

## [2.0.0] - 2026-08-24

Modernized for NativePHP Mobile v4 (v3 keeps working).

### Added

- Testing macros on NativePHP v4's `FakeBridge` (`assertKeptAwake`,
  `assertSleepAllowed`, `assertBrightnessSet`, `withBrightness`) and a
  behavioral test suite.
- Pest and Pint run on GitHub Actions.

### Changed

- **Breaking:** requires PHP 8.3+.
- **Breaking:** `keepAwake()` returns whether the requested state was
  applied, so `keepAwake(false)` now returns `true` on success (it used to
  return the new wake lock state).
- Native implementations tidied; README synced with the actual API.

### Fixed

- `allowSleep()` reported success when the bridge was unavailable.
- `getBrightness()` returned Android's `-1` failure value instead of `null`.

## [1.1.0] - 2026-07-17

### Added

- NativePHP Mobile v4 support (`nativephp/mobile: ^3.0|^4.0`).

## [1.0.2] - 2026-05-26

### Added

- Laravel 13 support.

## [1.0.1] - 2026-05-19

### Added

- License file and lint scripts for PHP and Swift.

### Changed

- Minimum Android and iOS versions declared in the manifest and README.
- README rewritten with screenshots; the PHP examples use the `Screen`
  facade; support requests go to GitHub issues.

### Fixed

- Event namespaces.

## [1.0.0] - 2026-02-18

### Added

- Initial release: keep the screen awake, set, read and reset the
  brightness, and listen for brightness changes on iOS and Android.

[Unreleased]: https://github.com/SRWieZ/nativephp-mobile-screen/compare/v2.1.0...HEAD
[2.1.0]: https://github.com/SRWieZ/nativephp-mobile-screen/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/SRWieZ/nativephp-mobile-screen/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/SRWieZ/nativephp-mobile-screen/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/SRWieZ/nativephp-mobile-screen/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/SRWieZ/nativephp-mobile-screen/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/SRWieZ/nativephp-mobile-screen/releases/tag/v1.0.0
