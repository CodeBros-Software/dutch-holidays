<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Contracts;

/**
 * Extensiehaak voor extra feestdagen-bronnen (cao-specifieke dagen,
 * bedrijfssluitingen, schoolvakanties).
 *
 * Een provider retourneert feestdagen voor een gegeven jaar. Ze worden
 * gemerged met de nationale set en kunnen die ook overschrijven via
 * dezelfde `HolidayKey`.
 */
interface HolidayProvider
{
    /**
     * Lever de feestdagen voor een specifiek jaar.
     *
     * @return array<int, \CodeBros\DutchHolidays\Holiday>
     */
    public function holidaysFor(int $year): array;

    /**
     * Korte naam voor debug/inspect doeleinden.
     */
    public function name(): string;
}
