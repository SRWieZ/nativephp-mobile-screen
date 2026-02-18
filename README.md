# MobileScreen Plugin for NativePHP Mobile

A NativePHP plugin for screen wake lock and brightness control.

Perfect for:
- Ticketing apps displaying barcodes
- Scoring apps showing live progress
- Any app that needs to keep the screen visible

## Features

- **Keep Screen Awake** - Prevent the device from sleeping
- **Brightness Control** - Set screen brightness programmatically

## Installation

```bash
# Install the package
composer require srwiez/nativephp-mobile-screen

# Publish the plugins provider (first time only)
php artisan vendor:publish --tag=nativephp-plugins-provider

# Register the plugin
php artisan native:plugin:register srwiez/nativephp-mobile-screen

# Verify registration
php artisan native:plugin:list
```

This adds `\SRWieZ\NativePHP\Mobile\Screen\MobileScreenServiceProvider::class` to your `plugins()` array.

## Usage

### PHP (Livewire/Blade)

```php
use SRWieZ\NativePHP\Mobile\Screen\Facades\MobileScreen;

// Keep screen awake
MobileScreen::keepAwake(); // true if wake lock enabled

// Allow screen to sleep
MobileScreen::allowSleep(); // true if wake lock disabled

// Check wake lock status
$isAwake = MobileScreen::isAwake(); // bool

// Set brightness (0.0 to 1.0)
$level = MobileScreen::setBrightness(1.0); // returns actual level, or false on failure

// Get current brightness
$level = MobileScreen::getBrightness(); // float or null

// Reset to system default
MobileScreen::resetBrightness(); // returns level or false on failure
```

### JavaScript (Vue/React/Inertia)

```javascript
import { mobileScreen } from '@srwiez/nativephp-mobile-screen';

// Keep screen awake
await mobileScreen.keepAwake();

// Set maximum brightness
await mobileScreen.setBrightness(1.0);

// Reset when done
await mobileScreen.resetBrightness();
await mobileScreen.allowSleep();
```

## API Reference

| Method | Returns | Description |
|--------|---------|-------------|
| `keepAwake(bool $enabled = true)` | `bool` | Enable/disable screen wake lock |
| `allowSleep()` | `bool` | Alias for `keepAwake(false)` |
| `isAwake()` | `bool` | Check if wake lock is active |
| `setBrightness(float $level)` | `bool\|float` | Set brightness (0.0-1.0). Returns actual level or `false` on failure |
| `getBrightness()` | `?float` | Get current brightness level |
| `resetBrightness()` | `bool\|float` | Reset to system default. Returns level or `false` on failure |

## Support

For questions or issues, email [nativephp@eserdeniz.fr](mailto:nativephp@eserdeniz.fr).
