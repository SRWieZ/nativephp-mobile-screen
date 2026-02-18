<?php

namespace SRWieZ\NativePHP\Mobile\Screen\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrightnessChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public float $level,
        public ?int $timestamp = null
    ) {}
}
