<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays;

use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;

/**
 * Beschrijft de holiday-policies voor één sector of cao.
 *
 * Bestaat uit twee delen:
 *   1. `policies`: per HolidayKey overschrijven hoe de standaard wettelijke
 *      uitkomst behandeld wordt (bv. Bevrijdingsdag altijd vrij i.p.v.
 *      alleen lustrumjaren).
 *   2. `extraHolidays`: keys van niet-nationale feestdagen die binnen deze
 *      sector wel meetellen (carnaval, oudejaarsdag, etc.).
 *
 * Immutable. Bouw nieuwe profielen met `with*()`-methoden.
 */
final readonly class SectorProfile
{
    /**
     * @param  array<value-of<HolidayKey>, HolidayPolicy>  $policies
     * @param  array<int, HolidayKey>  $extraHolidays
     */
    public function __construct(
        public string $name,
        public array $policies = [],
        public array $extraHolidays = [],
        public ?string $description = null,
    ) {
    }

    /**
     * Resolveer de policy voor een specifieke holiday-key.
     * Default valt terug op Official.
     */
    public function policyFor(HolidayKey $key): HolidayPolicy
    {
        return $this->policies[$key->value] ?? HolidayPolicy::Official;
    }

    /** Tellen de extra niet-wettelijke dagen voor deze sector mee als vrij? */
    public function includesExtra(HolidayKey $key): bool
    {
        return in_array($key, $this->extraHolidays, strict: true);
    }

    /**
     * Maak een nieuwe profile-instance met aanvullende of overschreven policies.
     *
     * @param  array<value-of<HolidayKey>|HolidayKey, HolidayPolicy|string>  $policies
     */
    public function withPolicies(array $policies): self
    {
        $normalized = [];
        foreach ($policies as $key => $value) {
            $keyStr = $key instanceof HolidayKey ? $key->value : $key;
            $policy = $value instanceof HolidayPolicy ? $value : HolidayPolicy::from($value);
            $normalized[$keyStr] = $policy;
        }

        return new self(
            name: $this->name,
            policies: [...$this->policies, ...$normalized],
            extraHolidays: $this->extraHolidays,
            description: $this->description,
        );
    }

    /**
     * Voeg extra (niet-wettelijke) feestdagen toe aan deze sector.
     */
    public function withExtraHolidays(HolidayKey ...$keys): self
    {
        $merged = $this->extraHolidays;
        foreach ($keys as $key) {
            if (! in_array($key, $merged, strict: true)) {
                $merged[] = $key;
            }
        }

        return new self(
            name: $this->name,
            policies: $this->policies,
            extraHolidays: $merged,
            description: $this->description,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'policies' => array_map(fn (HolidayPolicy $p) => $p->value, $this->policies),
            'extra_holidays' => array_map(fn (HolidayKey $k) => $k->value, $this->extraHolidays),
        ];
    }
}
