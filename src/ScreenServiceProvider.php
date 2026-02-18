<?php

namespace SRWieZ\NativePHP\Mobile\Screen;

use Illuminate\Support\ServiceProvider;

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
        //
    }
}
