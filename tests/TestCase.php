<?php

namespace SRWieZ\NativePHP\Mobile\Screen\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SRWieZ\NativePHP\Mobile\Screen\ScreenServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ScreenServiceProvider::class];
    }
}
