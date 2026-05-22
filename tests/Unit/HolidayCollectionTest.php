<?php

declare(strict_types=1);

use CodeBros\DutchHolidays\DutchHolidays;
use CodeBros\DutchHolidays\Enums\HolidayKey;

beforeEach(function () {
    $this->holidays = new DutchHolidays();
});

it('filters official holidays only', function () {
    $all = $this->holidays->forYear(2026);
    $official = $all->official();

    expect($all->count())->toBe(11);
    expect($official->count())->toBe(9); // Goede Vrijdag + Bevrijdingsdag (niet-lustrumjaar) eraf
});

it('filters unofficial holidays only', function () {
    $unofficial = $this->holidays->forYear(2026)->unofficial();

    expect($unofficial->count())->toBe(2); // Goede Vrijdag + Bevrijdingsdag in niet-lustrumjaar
    expect($unofficial->first()->key)->toBe(HolidayKey::GoodFriday);
});

it('scopes from a date', function () {
    $afterJune = $this->holidays->forYear(2026)->from('2026-06-01');

    // Alleen Kerstmis + Tweede Kerstdag in tweede helft
    expect($afterJune->count())->toBe(2);
});

it('scopes until a date', function () {
    $beforeJune = $this->holidays->forYear(2026)->until('2026-05-31');

    // Nieuwjaar, GF, Pasen, 2e Pasen, Koningsdag, Bevrijdingsdag, Hemelvaart, 1e en 2e Pinksteren = 9
    expect($beforeJune->count())->toBe(9);
});

it('finds by holiday key', function () {
    $kerstmis = $this->holidays->forYear(2026)->byKey(HolidayKey::Christmas);

    expect($kerstmis)->not->toBeNull();
    expect($kerstmis->date->toDateString())->toBe('2026-12-25');
});

it('returns null for missing key', function () {
    expect($this->holidays->forYear(2026)->byKey(HolidayKey::CarnivalMonday))->toBeNull();
});

it('maps to date => name array', function () {
    $map = $this->holidays->officialHolidays(2026)->asMap();

    expect($map)->toBeArray();
    expect($map)->toHaveKey('2026-01-01');
    expect($map['2026-01-01'])->toBe('Nieuwjaarsdag');
});

it('extracts dates as Collection', function () {
    $dates = $this->holidays->officialHolidays(2026)->dates();

    expect($dates)->toHaveCount(9);
    expect($dates->first())->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});
