<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Facades;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\Sector;
use CodeBros\DutchHolidays\Holiday;
use CodeBros\DutchHolidays\HolidayCollection;
use CodeBros\DutchHolidays\SectorProfile;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CodeBros\DutchHolidays\DutchHolidays sector(Sector|string|SectorProfile $sector)
 * @method static \CodeBros\DutchHolidays\DutchHolidays withPolicies(array $policies)
 * @method static \CodeBros\DutchHolidays\DutchHolidays withExtraHolidays(HolidayKey ...$keys)
 * @method static \CodeBros\DutchHolidays\DutchHolidays withProvider(\CodeBros\DutchHolidays\Contracts\HolidayProvider $provider)
 * @method static \CodeBros\DutchHolidays\DutchHolidays registerSector(string $key, SectorProfile $profile)
 * @method static SectorProfile activeSector()
 * @method static array availableSectors()
 * @method static HolidayCollection forYear(int $year)
 * @method static HolidayCollection officialHolidays(int $year)
 * @method static HolidayCollection daysOff(int $year)
 * @method static Collection forYears(int ...$years)
 * @method static HolidayCollection between(CarbonInterface|DateTimeInterface|string $from, CarbonInterface|DateTimeInterface|string $to, bool $officialOnly = false)
 * @method static bool isHoliday(CarbonInterface|DateTimeInterface|string $date, bool $officialOnly = true)
 * @method static ?Holiday getHoliday(CarbonInterface|DateTimeInterface|string $date)
 * @method static bool isWorkday(CarbonInterface|DateTimeInterface|string $date)
 * @method static CarbonImmutable nextWorkday(CarbonInterface|DateTimeInterface|string $date)
 * @method static CarbonImmutable previousWorkday(CarbonInterface|DateTimeInterface|string $date)
 * @method static int workdaysBetween(CarbonInterface|DateTimeInterface|string $from, CarbonInterface|DateTimeInterface|string $to)
 * @method static CarbonImmutable addWorkdays(CarbonInterface|DateTimeInterface|string $date, int $days)
 *
 * @see \CodeBros\DutchHolidays\DutchHolidays
 */
class DutchHolidays extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dutch-holidays';
    }
}
