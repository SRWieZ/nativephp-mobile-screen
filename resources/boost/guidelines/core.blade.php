## srwiez/nativephp-mobile-screen

A NativePHP plugin for screen wake lock and brightness control. Perfect for ticketing apps displaying barcodes or scoring apps showing live progress.

### Installation

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

### PHP Usage

Use the `Screen` facade. Methods return plain values, never arrays:

@verbatim
<code-snippet name="Keep Screen Awake" lang="php">
use SRWieZ\NativePHP\Mobile\Screen\Facades\Screen;

// Keep screen awake (e.g., when displaying a barcode)
Screen::keepAwake(); // true on success

// Allow screen to sleep again
Screen::allowSleep(); // true on success

// Check if wake lock is active
$isAwake = Screen::isAwake(); // bool
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Control Screen Brightness" lang="php">
use SRWieZ\NativePHP\Mobile\Screen\Facades\Screen;

// Set brightness to maximum (1.0); returns the applied level, or false
$level = Screen::setBrightness(1.0);

// Get current brightness level (0.0-1.0), or null when unavailable
$level = Screen::getBrightness();

// Reset to system default; returns the new level, or false
Screen::resetBrightness();
</code-snippet>
@endverbatim

### Available Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `Screen::keepAwake(bool $enabled = true)` | `bool` | Enable/disable wake lock |
| `Screen::allowSleep()` | `bool` | Alias for `keepAwake(false)` |
| `Screen::isAwake()` | `bool` | Check if wake lock is active |
| `Screen::setBrightness(float $level)` | `bool\|float` | Set brightness (0.0-1.0), returns the applied level or `false` |
| `Screen::getBrightness()` | `?float` | Current brightness, or `null` |
| `Screen::resetBrightness()` | `bool\|float` | Reset to system default, returns the level or `false` |
| `Screen::startBrightnessListener()` | `bool` | Start dispatching `BrightnessChanged` |
| `Screen::stopBrightnessListener()` | `bool` | Stop the brightness listener |
| `Screen::isAvailable()` | `bool` | Whether the plugin's native side is installed (PHP only, no bridge call) |
| `Screen::canControlBrightness()` | `bool` | Whether brightness control is available (PHP only, no bridge call) |

### Events

`SRWieZ\NativePHP\Mobile\Screen\Events\BrightnessChanged` carries the payload keys `level` (float, 0.0-1.0) and `timestamp` (Unix seconds). Handler parameters are bound by payload key name.

- NativePHP v4 native screen (`Native\Mobile\Edge\NativeComponent`): `#[Native\Mobile\Attributes\On(BrightnessChanged::class)]`. The attribute adds the `native:` prefix itself.
- Web view screen with Livewire: `#[Livewire\Attributes\On('native:'.BrightnessChanged::class)]`.
- Web view apps, anywhere in Laravel: `Event::listen(BrightnessChanged::class, ...)` receives the event object. Requires plugin 2.1.0+ on NativePHP 4.6.1+ and 3.3.11+. On v4 native screens plugin events only reach the screen's `#[On]` handlers.
- `#[Native\Mobile\Attributes\OnNative(BrightnessChanged::class)]` is the pre-v4 form. It needs Livewire installed; a v4 app without Livewire never reads it.

@verbatim
<code-snippet name="Listening for Brightness Changes" lang="php">
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\NativeComponent;
use SRWieZ\NativePHP\Mobile\Screen\Events\BrightnessChanged;
use SRWieZ\NativePHP\Mobile\Screen\Facades\Screen;

class TicketScreen extends NativeComponent
{
    public function mount(): void
    {
        Screen::startBrightnessListener();
    }

    #[On(BrightnessChanged::class)]
    public function brightnessChanged(float $level, ?int $timestamp = null): void
    {
        // ...
    }
}
</code-snippet>

<code-snippet name="Listening in a Livewire Component" lang="php">
use Livewire\Attributes\On;
use SRWieZ\NativePHP\Mobile\Screen\Events\BrightnessChanged;

#[On('native:'.BrightnessChanged::class)]
public function brightnessChanged(float $level, ?int $timestamp = null): void
{
    // ...
}
</code-snippet>
@endverbatim

### JavaScript Usage (Vue/React/Inertia)

The JS module ships inside the Composer package; import it from the vendor path.

@verbatim
<code-snippet name="Using the Screen module in JavaScript" lang="javascript">
import { mobileScreen } from '../../vendor/srwiez/nativephp-mobile-screen/resources/js/mobileScreen.js';

// Keep screen awake
await mobileScreen.keepAwake();

// Set maximum brightness for barcode display
await mobileScreen.setBrightness(1.0);

// When done, reset everything
await mobileScreen.resetBrightness();
await mobileScreen.allowSleep();
</code-snippet>
@endverbatim

### Common Use Cases

@verbatim
<code-snippet name="Barcode Display Component" lang="php">
use Livewire\Component;
use SRWieZ\NativePHP\Mobile\Screen\Facades\Screen;

class BarcodeDisplay extends Component
{
    public function mount(): void
    {
        // Keep screen awake and maximize brightness for scanning
        Screen::keepAwake();
        Screen::setBrightness(1.0);
    }

    public function done(): void
    {
        Screen::resetBrightness();
        Screen::allowSleep();
    }
}
</code-snippet>
@endverbatim

### Platform Notes

- **iOS**: Brightness resets automatically when device locks
- **Android**: Uses window-level brightness (no system permissions required); the level floors at 0.01 so the screen stays visible
- Both platforms require no special permissions
