<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Calculators;

use Carbon\CarbonImmutable;

/**
 * Berekent Eerste Paasdag voor een gegeven jaar volgens het Gauss-algoritme
 * (anonieme variant). Werkt voor alle jaren in het Gregoriaanse tijdperk (1583+).
 *
 * Alle paas-afhankelijke feestdagen (Goede Vrijdag, Hemelvaart, Pinksteren)
 * worden vanuit deze datum afgeleid.
 */
final class EasterCalculator
{
    public static function calculate(int $year): CarbonImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);

        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::create($year, $month, $day)->startOfDay();
    }
}
