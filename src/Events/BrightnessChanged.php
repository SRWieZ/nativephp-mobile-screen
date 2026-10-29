<?php

namespace SRWieZ\NativePHP\Mobile\Screen\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Native\Mobile\Events\Contracts\DispatchableFromNative;

class BrightnessChanged implements DispatchableFromNative
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public float $level,
        public ?int $timestamp = null
    ) {}
}
