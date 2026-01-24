# MobileScreen Plugin for NativePHP Mobile

A NativePHP plugin for screen wake lock and brightness control. Perfect for:
- Ticketing apps displaying barcodes
- Scoring apps showing live progress
- Any app that needs to keep the screen visible

## Features

- **Keep Screen Awake** - Prevent the device from sleeping
- **Brightness Control** - Set screen brightness programmatically
- **No Permissions Required** - Works at the window/app level

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

This adds `\SRWieZ\MobileScreen\MobileScreenServiceProvider::class` to your `plugins()` array.

## Usage

### PHP (Livewire/Blade)

```php
use SRWieZ\MobileScreen\Facades\MobileScreen;

// Keep screen awake
$result = MobileScreen::keepAwake();
if ($result['enabled']) {
    // Wake lock enabled
}

// Allow screen to sleep
$result = MobileScreen::allowSleep();

// Check wake lock status
$result = MobileScreen::isAwake();
$isAwake = $result['awake']; // bool

// Set brightness (0.0 to 1.0)
$result = MobileScreen::setBrightness(1.0);  // Maximum brightness
if ($result['success']) {
    $actualLevel = $result['level'];
}

// Get current brightness
$result = MobileScreen::getBrightness();
$level = $result['level']; // float or null

// Reset to system default
$result = MobileScreen::resetBrightness();
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
| `keepAwake(bool $enabled = true)` | `array{enabled: bool}` | Enable/disable screen wake lock |
| `allowSleep()` | `array{enabled: bool}` | Alias for `keepAwake(false)` |
| `isAwake()` | `array{awake: bool}` | Check if wake lock is active |
| `setBrightness(float $level)` | `array{success: bool, level: float}` | Set brightness (0.0-1.0) |
| `getBrightness()` | `array{level: float\|null}` | Get current brightness level |
| `resetBrightness()` | `array{success: bool}` | Reset to system default |
