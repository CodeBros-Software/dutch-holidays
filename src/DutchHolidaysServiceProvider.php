<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays;

use CodeBros\DutchHolidays\Console\ListHolidaysCommand;
use CodeBros\DutchHolidays\Console\ListSectorsCommand;
use CodeBros\DutchHolidays\Support\CarbonMacros;
use Illuminate\Support\ServiceProvider;

class DutchHolidaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dutch-holidays.php',
            'dutch-holidays'
        );

        $this->app->singleton(DutchHolidays::class, function ($app) {
            return new DutchHolidays(
                config('dutch-holidays', [])
            );
        });

        $this->app->alias(DutchHolidays::class, 'dutch-holidays');
    }

    public function boot(): void
    {
        if (config('dutch-holidays.register_carbon_macros', true)) {
            CarbonMacros::register();
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dutch-holidays.php' => config_path('dutch-holidays.php'),
            ], 'dutch-holidays-config');

            $this->commands([
                ListHolidaysCommand::class,
                ListSectorsCommand::class,
            ]);
        }
    }

    /** @return array<int, string> */
    public function provides(): array
    {
        return [
            DutchHolidays::class,
            'dutch-holidays',
        ];
    }
}
