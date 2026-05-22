<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Tests;

use CodeBros\DutchHolidays\DutchHolidaysServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DutchHolidaysServiceProvider::class,
        ];
    }
}
