<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays;

use Carbon\CarbonImmutable;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use Illuminate\Support\Collection;

/**
 * Typed Collection van Holiday-objecten met handige scoping-methods.
 *
 * @extends Collection<int, Holiday>
 */
class HolidayCollection extends Collection
{
    /** Alleen feestdagen die voor de huidige context vrij zijn. */
    public function official(): self
    {
        return $this->filter(fn (Holiday $h) => $h->official)->values();
    }

    /** Alleen feestdagen die NIET als vrije dag tellen (bv. Goede Vrijdag in default-sector). */
    public function unofficial(): self
    {
        return $this->reject(fn (Holiday $h) => $h->official)->values();
    }

    /** Alleen feestdagen op of na een gegeven datum. */
    public function from(CarbonImmutable|string $date): self
    {
        $date = $date instanceof CarbonImmutable ? $date : CarbonImmutable::parse($date);

        return $this->filter(fn (Holiday $h) => $h->date->greaterThanOrEqualTo($date))->values();
    }

    /** Alleen feestdagen op of voor een gegeven datum. */
    public function until(CarbonImmutable|string $date): self
    {
        $date = $date instanceof CarbonImmutable ? $date : CarbonImmutable::parse($date);

        return $this->filter(fn (Holiday $h) => $h->date->lessThanOrEqualTo($date))->values();
    }

    /** Zoek een specifieke feestdag op zijn key. */
    public function byKey(HolidayKey $key): ?Holiday
    {
        return $this->first(fn (Holiday $h) => $h->key === $key);
    }

    /** Map naar [datum => naam] voor JSON/frontend gebruik. */
    public function asMap(): array
    {
        return $this->mapWithKeys(fn (Holiday $h) => [
            $h->date->toDateString() => $h->name,
        ])->all();
    }

    /** Alleen de datums als losse Collection. */
    public function dates(): Collection
    {
        return $this->map(fn (Holiday $h) => $h->date)->values();
    }
}
