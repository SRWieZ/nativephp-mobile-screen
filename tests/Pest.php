<?php

use Native\Mobile\Testing\FakeBridge;
use SRWieZ\NativePHP\Mobile\Screen\Tests\TestCase;

/*
 * On nativephp/mobile v4 the bridge polyfills consult FakeBridge before
 * falling through to the Jump TCP relay, which makes the PHP wrapper fully
 * testable in-process. On v3 there is no FakeBridge, so the polyfills are
 * not loaded and the Feature suite skips itself.
 */
if (class_exists(FakeBridge::class)) {
    require_once __DIR__.'/../vendor/nativephp/mobile/src/jump_bridge_functions.php';
}

uses(TestCase::class)->in('Feature');
