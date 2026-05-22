<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Enums;

/**
 * Voorgedefinieerde sector-identifiers met gangbare cao-conventies.
 *
 * Deze profielen geven een redelijke startwaarde; afwijkingen per cao
 * kun je altijd nog overrulen met `->withPolicies([...])`.
 *
 * Eigen sectoren registreer je via DutchHolidays::registerSector().
 */
enum Sector: string
{
    /** Alleen de wettelijke nationale feestdagen (Bevrijdingsdag in lustrumjaren). */
    case Default = 'default';

    /** Overheid: Goede Vrijdag en Bevrijdingsdag elk jaar vrij. */
    case Government = 'government';

    /** Bouw-cao: Goede Vrijdag en Bevrijdingsdag elk jaar vrij. */
    case Construction = 'construction';

    /** Metaal & Techniek: Goede Vrijdag vrij. */
    case Metal = 'metal';

    /** Zorg: meestal Goede Vrijdag vrij. */
    case Healthcare = 'healthcare';

    /** Onderwijs: Goede Vrijdag, Bevrijdingsdag en vaak meer (afhankelijk van schoolniveau). */
    case Education = 'education';

    /** Horeca: voornamelijk wettelijke dagen. */
    case Hospitality = 'hospitality';

    /** ICT/Software: vaak Bevrijdingsdag altijd vrij; Goede Vrijdag varieert. */
    case Ict = 'ict';

    /** Grafimedia-cao: Goede Vrijdag en Bevrijdingsdag elk jaar vrij. */
    case Grafimedia = 'grafimedia';

    /** Retail/detailhandel: voornamelijk wettelijke dagen. */
    case Retail = 'retail';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Standaard (wettelijk)',
            self::Government => 'Overheid',
            self::Construction => 'Bouw',
            self::Metal => 'Metaal & Techniek',
            self::Healthcare => 'Zorg',
            self::Education => 'Onderwijs',
            self::Hospitality => 'Horeca',
            self::Ict => 'ICT / Software',
            self::Grafimedia => 'Grafimedia',
            self::Retail => 'Retail',
        };
    }
}
