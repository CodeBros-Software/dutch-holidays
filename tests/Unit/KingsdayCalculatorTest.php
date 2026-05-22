<?php

declare(strict_types=1);

use CodeBros\DutchHolidays\Calculators\KingsdayCalculator;

it('returns April 27 by default for Kingsday years', function () {
    expect(KingsdayCalculator::calculate(2024)->toDateString())->toBe('2024-04-27');
    expect(KingsdayCalculator::calculate(2026)->toDateString())->toBe('2026-04-27');
});

it('shifts Kingsday to April 26 when April 27 is a Sunday', function () {
    // 27 april 2014 was de eerste keer dat het op een zondag viel (na de troonswisseling)
    expect(KingsdayCalculator::calculate(2014)->toDateString())->toBe('2014-04-26');
    // 27 april 2025 valt op een zondag
    expect(KingsdayCalculator::calculate(2025)->toDateString())->toBe('2025-04-26');
});

it('uses Queens Day rules for years before 2014', function () {
    // 30 april 2006 was een zondag → verschuift naar 29 april
    expect(KingsdayCalculator::calculate(2006)->toDateString())->toBe('2006-04-29');
    // 30 april 2013 (laatste Koninginnedag) was een dinsdag → blijft 30 april
    expect(KingsdayCalculator::calculate(2013)->toDateString())->toBe('2013-04-30');
});

it('returns correct holiday name based on year', function () {
    expect(KingsdayCalculator::nameFor(2013))->toBe('Koninginnedag');
    expect(KingsdayCalculator::nameFor(2014))->toBe('Koningsdag');
    expect(KingsdayCalculator::nameFor(2026))->toBe('Koningsdag');
});
