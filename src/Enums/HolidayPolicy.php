<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Enums;

/**
 * Per-holiday policy: should this day be treated as a (paid) day off?
 *
 * Sectors and individual configurations apply one of these per holiday key.
 */
enum HolidayPolicy: string
{
    /** Volg de wettelijke standaard (bijv. Bevrijdingsdag in lustrumjaren). */
    case Official = 'official';

    /** Altijd vrij, ongeacht het jaar. */
    case Always = 'always';

    /** Nooit vrij. */
    case Never = 'never';

    /**
     * Past de policy toe op een specifiek jaar, gegeven de wettelijke default.
     *
     * @param bool $officialDefault  De wettelijke uitkomst voor dit jaar
     */
    public function resolve(bool $officialDefault): bool
    {
        return match ($this) {
            self::Official => $officialDefault,
            self::Always => true,
            self::Never => false,
        };
    }
}
