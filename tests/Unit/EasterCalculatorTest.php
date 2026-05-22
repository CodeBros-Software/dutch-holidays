<?php

declare(strict_types=1);

use CodeBros\DutchHolidays\Calculators\EasterCalculator;

it('calculates Easter Sunday correctly for known years', function (int $year, string $expected) {
    expect(EasterCalculator::calculate($year)->toDateString())->toBe($expected);
})->with([
    'Easter 2020' => [2020, '2020-04-12'],
    'Easter 2021' => [2021, '2021-04-04'],
    'Easter 2022' => [2022, '2022-04-17'],
    'Easter 2023' => [2023, '2023-04-09'],
    'Easter 2024' => [2024, '2024-03-31'],
    'Easter 2025' => [2025, '2025-04-20'],
    'Easter 2026' => [2026, '2026-04-05'],
    'Easter 2027' => [2027, '2027-03-28'],
    'Easter 2028' => [2028, '2028-04-16'],
    'Easter 2030' => [2030, '2030-04-21'],
    'Easter 2035' => [2035, '2035-03-25'],
    'Easter 2040' => [2040, '2040-04-01'],
]);

it('returns a CarbonImmutable at start of day', function () {
    $date = EasterCalculator::calculate(2026);

    expect($date)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    expect($date->format('H:i:s'))->toBe('00:00:00');
});
