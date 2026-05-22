<?php

declare(strict_types=1);

use CodeBros\DutchHolidays\DutchHolidays;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;
use CodeBros\DutchHolidays\Enums\Sector;
use CodeBros\DutchHolidays\Exceptions\UnknownSectorException;
use CodeBros\DutchHolidays\Profiles\DefaultSectorProfiles;
use CodeBros\DutchHolidays\SectorProfile;

it('exposes all built-in sectors', function () {
    $profiles = DefaultSectorProfiles::all();

    foreach (Sector::cases() as $case) {
        expect($profiles)->toHaveKey($case->value);
    }
});

it('throws an exception when switching to an unknown sector', function () {
    (new DutchHolidays())->sector('vermeende_cao');
})->throws(UnknownSectorException::class);

it('can register a custom sector at runtime', function () {
    $holidays = new DutchHolidays();

    $custom = new SectorProfile(
        name: 'My CAO',
        policies: [
            HolidayKey::GoodFriday->value => HolidayPolicy::Always,
        ],
        extraHolidays: [HolidayKey::NewYearsEve],
    );

    $holidays->registerSector('my_cao', $custom);

    $result = $holidays->sector('my_cao');

    expect($result->getHoliday('2026-04-03')->official)->toBeTrue(); // Goede Vrijdag
    expect($result->getHoliday('2026-12-31')->name)->toBe('Oudejaarsdag');
});

it('can be loaded via config sectors array', function () {
    $custom = new SectorProfile(
        name: 'Config CAO',
        policies: [HolidayKey::LiberationDay->value => HolidayPolicy::Always],
    );

    $holidays = new DutchHolidays([
        'sectors' => ['config_cao' => $custom],
    ]);

    expect($holidays->sector('config_cao')->getHoliday('2026-05-05')->official)->toBeTrue();
});

it('uses default_sector from config', function () {
    $holidays = new DutchHolidays([
        'default_sector' => Sector::Construction->value,
    ]);

    expect($holidays->activeSector()->name)->toBe(Sector::Construction->label());
    // Default is nu bouw → 5 mei 2026 is vrij
    expect($holidays->isWorkday('2026-05-05'))->toBeFalse();
});

it('builds new immutable profile with withPolicies', function () {
    $base = new SectorProfile(name: 'base');
    $modified = $base->withPolicies([
        HolidayKey::GoodFriday->value => HolidayPolicy::Always,
    ]);

    expect($base->policies)->toBeEmpty();
    expect($modified->policies)->toHaveKey('good_friday');
});

it('builds new profile with extra holidays', function () {
    $base = new SectorProfile(name: 'base');
    $modified = $base->withExtraHolidays(HolidayKey::Sinterklaas, HolidayKey::NewYearsEve);

    expect($base->extraHolidays)->toBeEmpty();
    expect($modified->extraHolidays)->toHaveCount(2);
});

it('serializes profile to array', function () {
    $profile = DefaultSectorProfiles::construction();
    $array = $profile->toArray();

    expect($array)->toHaveKeys(['name', 'description', 'policies', 'extra_holidays']);
    expect($array['policies'])->toMatchArray([
        'good_friday' => 'always',
        'liberation_day' => 'always',
    ]);
});
