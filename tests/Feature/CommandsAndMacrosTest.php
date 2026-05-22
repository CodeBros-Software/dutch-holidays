<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonImmutable;

it('runs the holidays:list command for the default sector', function () {
    $this->artisan('holidays:list', ['year' => 2026])
        ->assertSuccessful()
        ->expectsOutputToContain('Feestdagen 2026')
        ->expectsOutputToContain('Standaard');
});

it('runs holidays:list with a specific sector', function () {
    $this->artisan('holidays:list', ['year' => 2026, '--sector' => 'construction'])
        ->assertSuccessful()
        ->expectsOutputToContain('Bouw');
});

it('runs holidays:sectors and lists built-in sectors', function () {
    $this->artisan('holidays:sectors')
        ->assertSuccessful()
        ->expectsOutputToContain('construction')
        ->expectsOutputToContain('government');
});

it('outputs JSON when --json is passed', function () {
    $this->artisan('holidays:list', ['year' => 2026, '--json' => true])
        ->assertSuccessful();
});

it('registers Carbon macros on Carbon', function () {
    expect(Carbon::hasMacro('isDutchHoliday'))->toBeTrue();
    expect(Carbon::hasMacro('isDutchWorkday'))->toBeTrue();
});

it('registers Carbon macros on CarbonImmutable', function () {
    expect(CarbonImmutable::hasMacro('isDutchHoliday'))->toBeTrue();
});

it('macros work on Carbon instances', function () {
    expect(Carbon::parse('2026-12-25')->isDutchHoliday())->toBeTrue();
    expect(Carbon::parse('2026-06-15')->isDutchWorkday())->toBeTrue();
});

it('addDutchWorkdays macro skips holidays', function () {
    expect(Carbon::parse('2026-12-24')->addDutchWorkdays(1)->toDateString())->toBe('2026-12-28');
});
