<?php

namespace SRWieZ\MobileScreen;

use Illuminate\Support\ServiceProvider;

class MobileScreenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MobileScreen::class, function () {
            return new MobileScreen;
        });
    }

    public function boot(): void
    {
        //
    }
}
