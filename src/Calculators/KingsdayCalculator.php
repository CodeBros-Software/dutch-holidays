<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Calculators;

use Carbon\CarbonImmutable;

/**
 * Koningsdag (vanaf 2014): 27 april, tenzij dat een zondag is — dan 26 april.
 * Voor jaren vóór 2014: Koninginnedag, 30 april met dezelfde zondag-uitzondering.
 *
 * Naamgeving volgt de toenmalige situatie; voor jaren ≥ 2014 heet de dag Koningsdag.
 */
final class KingsdayCalculator
{
    public static function calculate(int $year): CarbonImmutable
    {
        if ($year >= 2014) {
            $date = CarbonImmutable::create($year, 4, 27)->startOfDay();

            return $date->isSunday() ? $date->subDay() : $date;
        }

        $date = CarbonImmutable::create($year, 4, 30)->startOfDay();

        return $date->isSunday() ? $date->subDay() : $date;
    }

    public static function nameFor(int $year): string
    {
        return $year >= 2014 ? 'Koningsdag' : 'Koninginnedag';
    }
}
