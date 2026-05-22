<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeBros\DutchHolidays\DutchHolidays;

/**
 * Registreert Carbon-macro's voor natuurlijk feestdag-gebruik:
 *
 *   Carbon::today()->isDutchHoliday();
 *   Carbon::parse('2026-05-05')->isDutchWorkday();
 *   Carbon::today()->nextDutchWorkday();
 *
 * Werkt zowel op Carbon als op CarbonImmutable.
 */
final class CarbonMacros
{
    public static function register(): void
    {
        $resolve = static fn (): DutchHolidays => app('dutch-holidays');

        $macros = [
            'isDutchHoliday' => function (bool $officialOnly = true) use ($resolve) {
                /** @var \Carbon\CarbonInterface $this */
                return $resolve()->isHoliday($this, $officialOnly);
            },
            'isDutchWorkday' => function () use ($resolve) {
                /** @var \Carbon\CarbonInterface $this */
                return $resolve()->isWorkday($this);
            },
            'nextDutchWorkday' => function () use ($resolve) {
                /** @var \Carbon\CarbonInterface $this */
                return $resolve()->nextWorkday($this);
            },
            'previousDutchWorkday' => function () use ($resolve) {
                /** @var \Carbon\CarbonInterface $this */
                return $resolve()->previousWorkday($this);
            },
            'addDutchWorkdays' => function (int $days) use ($resolve) {
                /** @var \Carbon\CarbonInterface $this */
                return $resolve()->addWorkdays($this, $days);
            },
            'dutchHoliday' => function () use ($resolve) {
                /** @var \Carbon\CarbonInterface $this */
                return $resolve()->getHoliday($this);
            },
        ];

        foreach ($macros as $name => $callback) {
            Carbon::macro($name, $callback);
            CarbonImmutable::macro($name, $callback);
        }
    }
}
