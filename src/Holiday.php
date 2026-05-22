<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays;

use Carbon\CarbonImmutable;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use JsonSerializable;

/**
 * Immutable representatie van één feestdag op een specifieke datum.
 *
 * `official` betekent: telt deze dag als (betaalde) vrije dag voor de
 * geconfigureerde context (sector + overrides), niet of het in de wet staat.
 * Voor de "puur wettelijk" check kun je `key->isWettelijk()` overwegen,
 * maar in de praktijk is `official` precies wat een planning-app nodig heeft.
 */
final readonly class Holiday implements JsonSerializable
{
    public function __construct(
        public HolidayKey $key,
        public string $name,
        public CarbonImmutable $date,
        public bool $official,
    ) {
    }

    public function isChristian(): bool
    {
        return $this->key->isChristian();
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key->value,
            'name' => $this->name,
            'date' => $this->date->toDateString(),
            'official' => $this->official,
            'christian' => $this->isChristian(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->date->toDateString());
    }
}
