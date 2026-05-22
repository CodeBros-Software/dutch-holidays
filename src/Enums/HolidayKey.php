<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Enums;

/**
 * Strongly-typed identifiers for known Dutch holidays.
 *
 * Used as keys in sector profiles and override maps so policies
 * can't be set on typo'd or non-existent holidays.
 */
enum HolidayKey: string
{
    case NewYear = 'new_year';
    case GoodFriday = 'good_friday';
    case Easter = 'easter';
    case EasterMonday = 'easter_monday';
    case Kingsday = 'kingsday';
    case LiberationDay = 'liberation_day';
    case Ascension = 'ascension';
    case Pentecost = 'pentecost';
    case PentecostMonday = 'pentecost_monday';
    case Christmas = 'christmas';
    case BoxingDay = 'boxing_day';

    /** Non-wettelijke, maar in veel cao's relevante dagen. */
    case CarnivalMonday = 'carnival_monday';
    case CarnivalTuesday = 'carnival_tuesday';
    case Sinterklaas = 'sinterklaas';
    case NewYearsEve = 'new_years_eve';
    case ChristmasEve = 'christmas_eve';

    public function label(): string
    {
        return match ($this) {
            self::NewYear => 'Nieuwjaarsdag',
            self::GoodFriday => 'Goede Vrijdag',
            self::Easter => 'Eerste Paasdag',
            self::EasterMonday => 'Tweede Paasdag',
            self::Kingsday => 'Koningsdag',
            self::LiberationDay => 'Bevrijdingsdag',
            self::Ascension => 'Hemelvaartsdag',
            self::Pentecost => 'Eerste Pinksterdag',
            self::PentecostMonday => 'Tweede Pinksterdag',
            self::Christmas => 'Eerste Kerstdag',
            self::BoxingDay => 'Tweede Kerstdag',
            self::CarnivalMonday => 'Carnavalsmaandag',
            self::CarnivalTuesday => 'Carnavalsdinsdag',
            self::Sinterklaas => 'Sinterklaas',
            self::NewYearsEve => 'Oudejaarsdag',
            self::ChristmasEve => 'Kerstavond',
        };
    }

    public function isChristian(): bool
    {
        return match ($this) {
            self::GoodFriday,
            self::Easter,
            self::EasterMonday,
            self::Ascension,
            self::Pentecost,
            self::PentecostMonday,
            self::Christmas,
            self::BoxingDay,
            self::CarnivalMonday,
            self::CarnivalTuesday,
            self::Sinterklaas => true,
            default => false,
        };
    }
}
