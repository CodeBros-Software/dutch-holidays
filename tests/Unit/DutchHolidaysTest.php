<?php

declare(strict_types=1);

use CodeBros\DutchHolidays\DutchHolidays;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;
use CodeBros\DutchHolidays\Enums\Sector;

beforeEach(function () {
    $this->holidays = new DutchHolidays();
});

describe('basic holiday retrieval', function () {
    it('returns all 11 holidays for a given year', function () {
        $list = $this->holidays->forYear(2026);

        // 11 wettelijke + Goede Vrijdag (niet-officieel, maar wel berekend)
        expect($list->count())->toBe(11);
    });

    it('finds a holiday by date', function () {
        $christmas = $this->holidays->getHoliday('2026-12-25');

        expect($christmas)->not->toBeNull();
        expect($christmas->name)->toBe('Eerste Kerstdag');
        expect($christmas->key)->toBe(HolidayKey::Christmas);
    });

    it('returns null for non-holiday dates', function () {
        expect($this->holidays->getHoliday('2026-06-15'))->toBeNull();
    });

    it('places Easter Monday one day after Easter', function () {
        $easter = $this->holidays->forYear(2026)->byKey(HolidayKey::Easter);
        $easterMonday = $this->holidays->forYear(2026)->byKey(HolidayKey::EasterMonday);

        expect($easterMonday->date->diffInDays($easter->date))->toBe(1);
    });

    it('places Ascension 39 days after Easter and Pentecost 49 days after', function () {
        $year = $this->holidays->forYear(2026);
        $easter = $year->byKey(HolidayKey::Easter);

        expect($year->byKey(HolidayKey::Ascension)->date->diffInDays($easter->date))->toBe(39);
        expect($year->byKey(HolidayKey::Pentecost)->date->diffInDays($easter->date))->toBe(49);
    });
});

describe('liberation day policy', function () {
    it('marks Bevrijdingsdag as official only in lustrum years by default', function () {
        expect($this->holidays->getHoliday('2025-05-05')->official)->toBeTrue();
        expect($this->holidays->getHoliday('2026-05-05')->official)->toBeFalse();
        expect($this->holidays->getHoliday('2030-05-05')->official)->toBeTrue();
    });

    it('marks Bevrijdingsdag as always official with construction sector', function () {
        $bouw = $this->holidays->sector(Sector::Construction);

        expect($bouw->getHoliday('2025-05-05')->official)->toBeTrue();
        expect($bouw->getHoliday('2026-05-05')->official)->toBeTrue();
        expect($bouw->getHoliday('2027-05-05')->official)->toBeTrue();
    });

    it('does not mutate the original instance when switching sector', function () {
        $bouw = $this->holidays->sector(Sector::Construction);

        expect($this->holidays->getHoliday('2026-05-05')->official)->toBeFalse();
        expect($bouw->getHoliday('2026-05-05')->official)->toBeTrue();
    });
});

describe('good friday policy', function () {
    it('is unofficial in default sector', function () {
        $goodFriday = $this->holidays->forYear(2026)->byKey(HolidayKey::GoodFriday);

        expect($goodFriday->official)->toBeFalse();
    });

    it('is official in government sector', function () {
        $goodFriday = $this->holidays
            ->sector(Sector::Government)
            ->forYear(2026)
            ->byKey(HolidayKey::GoodFriday);

        expect($goodFriday->official)->toBeTrue();
    });
});

describe('custom policies', function () {
    it('allows overriding policies ad-hoc', function () {
        $custom = $this->holidays->withPolicies([
            HolidayKey::GoodFriday->value => HolidayPolicy::Always,
            HolidayKey::LiberationDay->value => HolidayPolicy::Always,
        ]);

        expect($custom->getHoliday('2026-04-03')->official)->toBeTrue(); // Goede Vrijdag 2026
        expect($custom->getHoliday('2026-05-05')->official)->toBeTrue();
    });

    it('accepts string policies', function () {
        $custom = $this->holidays->withPolicies([
            'good_friday' => 'always',
        ]);

        expect($custom->getHoliday('2026-04-03')->official)->toBeTrue();
    });

    it('combines sector + ad-hoc policies', function () {
        // Government heeft Goede Vrijdag al op Always
        // Daarbovenop schakelen we Kerstmis uit (edge case)
        $custom = $this->holidays
            ->sector(Sector::Government)
            ->withPolicies(['christmas' => 'never']);

        expect($custom->getHoliday('2026-04-03')->official)->toBeTrue();
        expect($custom->getHoliday('2026-12-25')->official)->toBeFalse();
    });
});

describe('extra holidays', function () {
    it('can add carnival days as extras', function () {
        $instance = $this->holidays->withExtraHolidays(
            HolidayKey::CarnivalMonday,
            HolidayKey::CarnivalTuesday,
        );

        $list = $instance->forYear(2026);

        // Pasen 2026 = 5 april, dus carnaval 16-17 februari
        expect($list->byKey(HolidayKey::CarnivalMonday)?->date->toDateString())->toBe('2026-02-16');
        expect($list->byKey(HolidayKey::CarnivalTuesday)?->date->toDateString())->toBe('2026-02-17');
    });

    it('adds Sinterklaas on December 5', function () {
        $instance = $this->holidays->withExtraHolidays(HolidayKey::Sinterklaas);

        expect($instance->getHoliday('2026-12-05')->name)->toBe('Sinterklaas');
    });
});

describe('workday helpers', function () {
    it('detects weekends correctly', function () {
        // 16 mei 2026 is een zaterdag
        expect($this->holidays->isWorkday('2026-05-16'))->toBeFalse();
        expect($this->holidays->isWorkday('2026-05-17'))->toBeFalse();
        expect($this->holidays->isWorkday('2026-05-18'))->toBeTrue();
    });

    it('treats Christmas as a non-workday', function () {
        expect($this->holidays->isWorkday('2026-12-25'))->toBeFalse();
    });

    it('treats Liberation Day as a workday in non-lustrum years (default sector)', function () {
        // 2026 is geen lustrumjaar — 5 mei is dinsdag → werkdag
        expect($this->holidays->isWorkday('2026-05-05'))->toBeTrue();
    });

    it('treats Liberation Day as non-workday in construction sector', function () {
        expect($this->holidays->sector(Sector::Construction)->isWorkday('2026-05-05'))->toBeFalse();
    });

    it('finds the next workday correctly across holidays', function () {
        // 24 december 2026 (donderdag) → volgende werkdag is 28 december (maandag),
        // want 25, 26 zijn feestdagen en 27 is zondag
        $next = $this->holidays->nextWorkday('2026-12-24');
        expect($next->toDateString())->toBe('2026-12-28');
    });

    it('finds the previous workday correctly', function () {
        // 27 december 2026 (zondag) → vorige werkdag is 24 december (donderdag),
        // want 25, 26 zijn kerst
        $prev = $this->holidays->previousWorkday('2026-12-27');
        expect($prev->toDateString())->toBe('2026-12-24');
    });

    it('counts workdays between two dates inclusive', function () {
        // Maandag 4 mei t/m vrijdag 8 mei 2026: 5 werkdagen (5 mei is werkdag in default sector)
        expect($this->holidays->workdaysBetween('2026-05-04', '2026-05-08'))->toBe(5);

        // In bouw-sector: 4 werkdagen want 5 mei is dan vrij
        expect($this->holidays->sector(Sector::Construction)->workdaysBetween('2026-05-04', '2026-05-08'))->toBe(4);
    });

    it('handles reversed date range in workdaysBetween', function () {
        expect($this->holidays->workdaysBetween('2026-05-08', '2026-05-04'))->toBe(5);
    });

    it('adds N workdays correctly', function () {
        // Vrijdag 27 maart 2026 + 1 werkdag = maandag 30 maart
        expect($this->holidays->addWorkdays('2026-03-27', 1)->toDateString())->toBe('2026-03-30');
    });

    it('skips holidays when adding workdays', function () {
        // Donderdag 24 december 2026 + 1 werkdag = maandag 28 december
        // (25, 26 zijn kerstdagen, 27 is zondag)
        expect($this->holidays->addWorkdays('2026-12-24', 1)->toDateString())->toBe('2026-12-28');
    });

    it('supports negative workday addition', function () {
        // Maandag 28 december 2026 - 1 werkdag = donderdag 24 december
        expect($this->holidays->addWorkdays('2026-12-28', -1)->toDateString())->toBe('2026-12-24');
    });
});

describe('multi-year retrieval', function () {
    it('returns holidays for multiple years', function () {
        $multi = $this->holidays->forYears(2025, 2026, 2027);

        expect($multi)->toHaveCount(3);
        expect($multi[2026]->count())->toBe(11);
    });

    it('returns holidays between two dates spanning years', function () {
        $between = $this->holidays->between('2025-12-01', '2026-02-28');

        // Sinterklaas in normale sector is geen extra holiday, dus:
        // dec 25, dec 26, jan 1 → 3 feestdagen
        expect($between->count())->toBe(3);
        expect($between->first()->name)->toBe('Eerste Kerstdag');
    });

    it('respects officialOnly flag in between()', function () {
        // 27 maart - 10 april 2026 bevat Goede Vrijdag (3 april), Pasen (5), Tweede Paasdag (6)
        $all = $this->holidays->between('2026-03-27', '2026-04-10', officialOnly: false);
        $official = $this->holidays->between('2026-03-27', '2026-04-10', officialOnly: true);

        expect($all->count())->toBe(3); // GF, Pasen, Tweede Paasdag
        expect($official->count())->toBe(2); // Pasen, Tweede Paasdag
    });
});

describe('serialization', function () {
    it('serializes holidays to JSON correctly', function () {
        $holiday = $this->holidays->getHoliday('2026-12-25');
        $json = json_encode($holiday);
        $decoded = json_decode($json, true);

        expect($decoded['key'])->toBe('christmas');
        expect($decoded['name'])->toBe('Eerste Kerstdag');
        expect($decoded['date'])->toBe('2026-12-25');
        expect($decoded['official'])->toBeTrue();
        expect($decoded['christian'])->toBeTrue();
    });

    it('exposes asMap for frontend consumption', function () {
        $map = $this->holidays->officialHolidays(2026)->asMap();

        expect($map)->toHaveKey('2026-12-25');
        expect($map['2026-12-25'])->toBe('Eerste Kerstdag');
    });
});
