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

### PHP Usage (Livewire/Blade)

Use the `MobileScreen` facade. All methods return arrays with the result data:

@verbatim
<code-snippet name="Keep Screen Awake" lang="php">
use SRWieZ\MobileScreen\Facades\MobileScreen;

// Keep screen awake (e.g., when displaying a barcode)
$result = MobileScreen::keepAwake();
// $result = ['enabled' => true]

// Allow screen to sleep again
$result = MobileScreen::allowSleep();
// $result = ['enabled' => false]

// Check if wake lock is active
$result = MobileScreen::isAwake();
$isAwake = $result['awake']; // bool
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Control Screen Brightness" lang="php">
use SRWieZ\MobileScreen\Facades\MobileScreen;

// Set brightness to maximum (1.0)
$result = MobileScreen::setBrightness(1.0);
// $result = ['success' => true, 'level' => 1.0]

// Set brightness to 50%
$result = MobileScreen::setBrightness(0.5);

// Get current brightness level
$result = MobileScreen::getBrightness();
$level = $result['level']; // float (0.0-1.0) or null

// Reset to system default
$result = MobileScreen::resetBrightness();
// $result = ['success' => true]
</code-snippet>
@endverbatim

### Available Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `MobileScreen::keepAwake(bool $enabled = true)` | `array{enabled: bool}` | Enable/disable wake lock |
| `MobileScreen::allowSleep()` | `array{enabled: bool}` | Alias for `keepAwake(false)` |
| `MobileScreen::isAwake()` | `array{awake: bool}` | Check if wake lock is active |
| `MobileScreen::setBrightness(float $level)` | `array{success: bool, level: float}` | Set brightness (0.0-1.0) |
| `MobileScreen::getBrightness()` | `array{level: float\|null}` | Get current brightness |
| `MobileScreen::resetBrightness()` | `array{success: bool}` | Reset to system default |

### JavaScript Usage (Vue/React/Inertia)

@verbatim
<code-snippet name="Using MobileScreen in JavaScript" lang="javascript">
import { mobileScreen } from '@srwiez/nativephp-mobile-screen';

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
use SRWieZ\MobileScreen\Facades\MobileScreen;

class BarcodeDisplay extends Component
{
    public function mount()
    {
        // Keep screen awake and maximize brightness for scanning
        MobileScreen::keepAwake();
        MobileScreen::setBrightness(1.0);
    }

    public function dehydrate()
    {
        // Reset when component is destroyed
        MobileScreen::resetBrightness();
        MobileScreen::allowSleep();
    }
}
</code-snippet>
@endverbatim

### Platform Notes

- **iOS**: Brightness resets automatically when device locks
- **Android**: Uses window-level brightness (no system permissions required)
- Both platforms require no special permissions
