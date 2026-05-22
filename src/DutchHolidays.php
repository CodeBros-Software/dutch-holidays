<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use CodeBros\DutchHolidays\Calculators\EasterCalculator;
use CodeBros\DutchHolidays\Calculators\KingsdayCalculator;
use CodeBros\DutchHolidays\Contracts\HolidayProvider;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;
use CodeBros\DutchHolidays\Enums\Sector;
use CodeBros\DutchHolidays\Exceptions\UnknownSectorException;
use CodeBros\DutchHolidays\Profiles\DefaultSectorProfiles;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Hoofdingang van het Dutch Holidays-pakket.
 *
 * Drie use-cases:
 *
 *   1. Snel iets ophalen voor de geconfigureerde sector:
 *        DutchHolidays::forYear(2026)
 *        DutchHolidays::officialHolidays(2026)
 *        DutchHolidays::isWorkday('2026-05-05')
 *
 *   2. Tijdelijk omschakelen naar een andere sector:
 *        DutchHolidays::sector('construction')->forYear(2026)
 *        DutchHolidays::sector(Sector::Government)->daysOff(2026)
 *
 *   3. Ad-hoc policies overrulen zonder een sector aan te maken:
 *        DutchHolidays::withPolicies(['good_friday' => 'always'])->forYear(2026)
 *
 * Instances zijn immutable: elke `with*()` en `sector()` retourneert een
 * nieuwe instance. Veilig om in multi-tenant requests te gebruiken.
 */
class DutchHolidays
{
    /** @var array<string, SectorProfile> */
    protected array $sectors = [];

    /** @var array<int, HolidayCollection> */
    protected array $yearCache = [];

    protected SectorProfile $activeSector;

    /** @var array<int, HolidayProvider> */
    protected array $providers = [];

    /**
     * @param  array{
     *     default_sector?: string,
     *     sectors?: array<string, SectorProfile>,
     *     cache?: bool
     * }  $config
     */
    public function __construct(protected array $config = [])
    {
        $this->sectors = DefaultSectorProfiles::all();

        // Verrijk/overschrijf met user-defined sectoren uit config
        foreach (($config['sectors'] ?? []) as $key => $profile) {
            if ($profile instanceof SectorProfile) {
                $this->sectors[$key] = $profile;
            }
        }

        $defaultKey = $config['default_sector'] ?? Sector::Default->value;
        $this->activeSector = $this->resolveSector($defaultKey);
    }

    // -------------------------------------------------------------------
    // Sector- en policy-handling (fluent, immutable)
    // -------------------------------------------------------------------

    /** Schakel naar een sectorprofiel. Geeft een nieuwe instance terug. */
    public function sector(Sector|string|SectorProfile $sector): self
    {
        $profile = match (true) {
            $sector instanceof SectorProfile => $sector,
            $sector instanceof Sector => $this->resolveSector($sector->value),
            default => $this->resolveSector($sector),
        };

        return $this->cloneWith(activeSector: $profile);
    }

    /**
     * Override specifieke policies bovenop de huidige sector.
     *
     * @param  array<HolidayKey|value-of<HolidayKey>, HolidayPolicy|string>  $policies
     */
    public function withPolicies(array $policies): self
    {
        return $this->cloneWith(
            activeSector: $this->activeSector->withPolicies($policies)
        );
    }

    /** Voeg extra (niet-wettelijke) feestdagen toe aan de huidige context. */
    public function withExtraHolidays(HolidayKey ...$keys): self
    {
        return $this->cloneWith(
            activeSector: $this->activeSector->withExtraHolidays(...$keys)
        );
    }

    /** Haak een eigen HolidayProvider in (cao-dagen, bedrijfssluitingen, etc.). */
    public function withProvider(HolidayProvider $provider): self
    {
        $clone = $this->cloneWith();
        $clone->providers[] = $provider;

        return $clone;
    }

    /** Registreer een sector globaal op deze instance. */
    public function registerSector(string $key, SectorProfile $profile): self
    {
        $this->sectors[$key] = $profile;
        $this->yearCache = []; // policies kunnen veranderen — cache leeg

        return $this;
    }

    /** Het op dit moment actieve sectorprofiel. */
    public function activeSector(): SectorProfile
    {
        return $this->activeSector;
    }

    /** @return array<string, SectorProfile> */
    public function availableSectors(): array
    {
        return $this->sectors;
    }

    // -------------------------------------------------------------------
    // Ophalen van feestdagen
    // -------------------------------------------------------------------

    /** Alle feestdagen voor één jaar (zowel official als unofficial). */
    public function forYear(int $year): HolidayCollection
    {
        return $this->yearCache[$year] ??= $this->calculate($year);
    }

    /** Alleen de dagen die voor de huidige sector als vrije dag tellen. */
    public function officialHolidays(int $year): HolidayCollection
    {
        return $this->forYear($year)->official();
    }

    /** Alias voor `officialHolidays()` — leest natuurlijk in planningscode. */
    public function daysOff(int $year): HolidayCollection
    {
        return $this->officialHolidays($year);
    }

    /**
     * Feestdagen voor meerdere jaren, gekeyed op jaartal.
     *
     * @return Collection<int, HolidayCollection>
     */
    public function forYears(int ...$years): Collection
    {
        return collect($years)->mapWithKeys(
            fn (int $year) => [$year => $this->forYear($year)]
        );
    }

    /**
     * Feestdagen binnen een datumbereik (inclusief eindpunt).
     * Kan jaargrenzen overspannen.
     */
    public function between(
        CarbonInterface|DateTimeInterface|string $from,
        CarbonInterface|DateTimeInterface|string $to,
        bool $officialOnly = false,
    ): HolidayCollection {
        $from = $this->normalize($from);
        $to = $this->normalize($to);

        $items = new HolidayCollection();

        for ($year = $from->year; $year <= $to->year; $year++) {
            $items = $items->concat(
                $this->forYear($year)->filter(
                    fn (Holiday $h) => $h->date->betweenIncluded($from, $to)
                )
            );
        }

        $sorted = $items->sortBy(fn (Holiday $h) => $h->date->getTimestamp())->values();

        /** @var HolidayCollection $sorted */
        $sorted = $officialOnly ? $sorted->official() : $sorted;

        return $sorted;
    }

    // -------------------------------------------------------------------
    // Werkdag-helpers
    // -------------------------------------------------------------------

    /** Is dit een feestdag (volgens huidige sector + overrides)? */
    public function isHoliday(CarbonInterface|DateTimeInterface|string $date, bool $officialOnly = true): bool
    {
        $holiday = $this->getHoliday($date);

        if ($holiday === null) {
            return false;
        }

        return $officialOnly ? $holiday->official : true;
    }

    /** Haal de Holiday voor een datum op, of null. */
    public function getHoliday(CarbonInterface|DateTimeInterface|string $date): ?Holiday
    {
        $date = $this->normalize($date);

        return $this->forYear($date->year)
            ->first(fn (Holiday $h) => $h->date->isSameDay($date));
    }

    /** Werkdag = geen weekend én geen officiële feestdag voor de huidige sector. */
    public function isWorkday(CarbonInterface|DateTimeInterface|string $date): bool
    {
        $date = $this->normalize($date);

        if ($date->isWeekend()) {
            return false;
        }

        return ! $this->isHoliday($date);
    }

    /** Eerstvolgende werkdag na de gegeven datum. */
    public function nextWorkday(CarbonInterface|DateTimeInterface|string $date): CarbonImmutable
    {
        $cursor = $this->normalize($date)->addDay();

        while (! $this->isWorkday($cursor)) {
            $cursor = $cursor->addDay();
        }

        return $cursor;
    }

    /** Vorige werkdag vóór de gegeven datum. */
    public function previousWorkday(CarbonInterface|DateTimeInterface|string $date): CarbonImmutable
    {
        $cursor = $this->normalize($date)->subDay();

        while (! $this->isWorkday($cursor)) {
            $cursor = $cursor->subDay();
        }

        return $cursor;
    }

    /** Aantal werkdagen tussen twee datums (beide inclusief). */
    public function workdaysBetween(
        CarbonInterface|DateTimeInterface|string $from,
        CarbonInterface|DateTimeInterface|string $to,
    ): int {
        $from = $this->normalize($from);
        $to = $this->normalize($to);

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $count = 0;
        $cursor = $from;

        while ($cursor->lessThanOrEqualTo($to)) {
            if ($this->isWorkday($cursor)) {
                $count++;
            }
            $cursor = $cursor->addDay();
        }

        return $count;
    }

    /** Voeg N werkdagen toe aan een datum (slaat weekenden en feestdagen over). */
    public function addWorkdays(CarbonInterface|DateTimeInterface|string $date, int $days): CarbonImmutable
    {
        $cursor = $this->normalize($date);

        if ($days === 0) {
            return $cursor;
        }

        $step = $days > 0 ? 1 : -1;
        $remaining = abs($days);

        while ($remaining > 0) {
            $cursor = $cursor->addDays($step);
            if ($this->isWorkday($cursor)) {
                $remaining--;
            }
        }

        return $cursor;
    }

    // -------------------------------------------------------------------
    // Intern
    // -------------------------------------------------------------------

    protected function resolveSector(string $key): SectorProfile
    {
        if (! isset($this->sectors[$key])) {
            throw UnknownSectorException::make($key);
        }

        return $this->sectors[$key];
    }

    protected function calculate(int $year): HolidayCollection
    {
        $easter = EasterCalculator::calculate($year);
        $kingsday = KingsdayCalculator::calculate($year);
        $sector = $this->activeSector;

        $candidates = [
            HolidayKey::NewYear => [CarbonImmutable::create($year, 1, 1), true],
            HolidayKey::GoodFriday => [$easter->subDays(2), false],
            HolidayKey::Easter => [$easter, true],
            HolidayKey::EasterMonday => [$easter->addDay(), true],
            HolidayKey::Kingsday => [$kingsday, true],
            HolidayKey::LiberationDay => [CarbonImmutable::create($year, 5, 5), $year % 5 === 0],
            HolidayKey::Ascension => [$easter->addDays(39), true],
            HolidayKey::Pentecost => [$easter->addDays(49), true],
            HolidayKey::PentecostMonday => [$easter->addDays(50), true],
            HolidayKey::Christmas => [CarbonImmutable::create($year, 12, 25), true],
            HolidayKey::BoxingDay => [CarbonImmutable::create($year, 12, 26), true],
        ];

        $holidays = new HolidayCollection();

        foreach ($candidates as $key => [$date, $officialDefault]) {
            $official = $sector->policyFor($key)->resolve($officialDefault);

            $name = $key === HolidayKey::Kingsday
                ? KingsdayCalculator::nameFor($year)
                : $key->label();

            $holidays->push(new Holiday($key, $name, $date, $official));
        }

        // Extra (niet-wettelijke) feestdagen die in deze sector mee tellen
        foreach ($sector->extraHolidays as $extraKey) {
            $extraDate = $this->extraHolidayDate($extraKey, $year, $easter);
            if ($extraDate === null) {
                continue;
            }

            // Override-policy kan ook hier nog "never" zeggen
            $official = $sector->policyFor($extraKey)->resolve(true);

            $holidays->push(new Holiday($extraKey, $extraKey->label(), $extraDate, $official));
        }

        // Externe providers (custom HolidayProvider implementaties)
        foreach ($this->providers as $provider) {
            foreach ($provider->holidaysFor($year) as $holiday) {
                // Vervang als dezelfde key al bestaat, anders toevoegen
                $existingIndex = $holidays->search(
                    fn (Holiday $h) => $h->key === $holiday->key
                );

                if ($existingIndex === false) {
                    $holidays->push($holiday);
                } else {
                    $holidays->put($existingIndex, $holiday);
                }
            }
        }

        return $holidays
            ->sortBy(fn (Holiday $h) => $h->date->getTimestamp())
            ->values();
    }

    protected function extraHolidayDate(HolidayKey $key, int $year, CarbonImmutable $easter): ?CarbonImmutable
    {
        return match ($key) {
            HolidayKey::CarnivalMonday => $easter->subDays(48),
            HolidayKey::CarnivalTuesday => $easter->subDays(47),
            HolidayKey::Sinterklaas => CarbonImmutable::create($year, 12, 5),
            HolidayKey::NewYearsEve => CarbonImmutable::create($year, 12, 31),
            HolidayKey::ChristmasEve => CarbonImmutable::create($year, 12, 24),
            default => null,
        };
    }

    protected function normalize(CarbonInterface|DateTimeInterface|string $date): CarbonImmutable
    {
        if ($date instanceof CarbonImmutable) {
            return $date->startOfDay();
        }

        if ($date instanceof CarbonInterface) {
            return CarbonImmutable::instance($date)->startOfDay();
        }

        if ($date instanceof DateTimeInterface) {
            return CarbonImmutable::instance($date)->startOfDay();
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }

    /**
     * Clone deze instance met optionele overrides.
     * De yearCache wordt geleegd omdat een policy-wijziging de uitkomst verandert.
     */
    protected function cloneWith(?SectorProfile $activeSector = null): self
    {
        $clone = clone $this;
        $clone->yearCache = [];

        if ($activeSector !== null) {
            $clone->activeSector = $activeSector;
        }

        return $clone;
    }
}
