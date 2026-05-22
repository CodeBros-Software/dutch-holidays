<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Profiles;

use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;
use CodeBros\DutchHolidays\Enums\Sector;
use CodeBros\DutchHolidays\SectorProfile;

/**
 * Voorgedefinieerde profielen voor de gangbare Nederlandse sectoren/cao's.
 *
 * Disclaimer: cao-afspraken veranderen en verschillen per werkgever. Deze
 * profielen reflecteren de meest voorkomende invulling — controleer altijd
 * de actuele cao van je klant of werkgever en pas indien nodig aan via
 * `->withPolicies([...])` of een eigen geregistreerde sector.
 */
final class DefaultSectorProfiles
{
    /**
     * @return array<value-of<Sector>, SectorProfile>
     */
    public static function all(): array
    {
        return [
            Sector::Default->value => self::default(),
            Sector::Government->value => self::government(),
            Sector::Construction->value => self::construction(),
            Sector::Metal->value => self::metal(),
            Sector::Healthcare->value => self::healthcare(),
            Sector::Education->value => self::education(),
            Sector::Hospitality->value => self::hospitality(),
            Sector::Ict->value => self::ict(),
            Sector::Grafimedia->value => self::grafimedia(),
            Sector::Retail->value => self::retail(),
        ];
    }

    public static function default(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Default->label(),
            description: 'Alleen de wettelijke nationale feestdagen. Bevrijdingsdag alleen in lustrumjaren.',
        );
    }

    public static function government(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Government->label(),
            policies: [
                HolidayKey::GoodFriday->value => HolidayPolicy::Always,
                HolidayKey::LiberationDay->value => HolidayPolicy::Always,
            ],
            description: 'Rijksoverheid: Goede Vrijdag en Bevrijdingsdag elk jaar vrij.',
        );
    }

    public static function construction(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Construction->label(),
            policies: [
                HolidayKey::GoodFriday->value => HolidayPolicy::Always,
                HolidayKey::LiberationDay->value => HolidayPolicy::Always,
            ],
            description: 'Bouw-cao: Goede Vrijdag en Bevrijdingsdag elk jaar vrij.',
        );
    }

    public static function metal(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Metal->label(),
            policies: [
                HolidayKey::GoodFriday->value => HolidayPolicy::Always,
            ],
            description: 'Metaal & Techniek: Goede Vrijdag vrij; Bevrijdingsdag conform wet (lustrumjaren).',
        );
    }

    public static function healthcare(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Healthcare->label(),
            policies: [
                HolidayKey::GoodFriday->value => HolidayPolicy::Always,
            ],
            description: 'Zorg: Goede Vrijdag doorgaans vrij. Let op: 24/7-diensten kunnen afwijken.',
        );
    }

    public static function education(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Education->label(),
            policies: [
                HolidayKey::GoodFriday->value => HolidayPolicy::Always,
                HolidayKey::LiberationDay->value => HolidayPolicy::Always,
            ],
            description: 'Onderwijs: Goede Vrijdag en Bevrijdingsdag vrij. Schoolvakanties zijn separaat.',
        );
    }

    public static function hospitality(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Hospitality->label(),
            description: 'Horeca: hoofdzakelijk wettelijke dagen; bedrijven zijn op feestdagen vaak juist open.',
        );
    }

    public static function ict(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Ict->label(),
            policies: [
                HolidayKey::LiberationDay->value => HolidayPolicy::Always,
            ],
            description: 'ICT/Software: Bevrijdingsdag vaak altijd vrij; Goede Vrijdag wisselt per werkgever.',
        );
    }

    public static function grafimedia(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Grafimedia->label(),
            policies: [
                HolidayKey::GoodFriday->value => HolidayPolicy::Always,
                HolidayKey::LiberationDay->value => HolidayPolicy::Always,
            ],
            description: 'Grafimedia-cao: Goede Vrijdag en Bevrijdingsdag elk jaar vrij.',
        );
    }

    public static function retail(): SectorProfile
    {
        return new SectorProfile(
            name: Sector::Retail->label(),
            description: 'Retail/detailhandel: voornamelijk wettelijke dagen; winkels zijn vaak open.',
        );
    }
}
