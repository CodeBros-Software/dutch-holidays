<?php

use CodeBros\DutchHolidays\Enums\Sector;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Sector
    |--------------------------------------------------------------------------
    |
    | De sector waaronder feestdagen worden berekend als je geen expliciete
    | sector kiest. Geldige waarden zijn de cases van de Sector-enum
    | (default, government, construction, metal, healthcare, education,
    | hospitality, ict, grafimedia, retail) of een eigen geregistreerde key.
    |
    */
    'default_sector' => env('DUTCH_HOLIDAYS_SECTOR', Sector::Default->value),

    /*
    |--------------------------------------------------------------------------
    | Custom Sectors
    |--------------------------------------------------------------------------
    |
    | Hier kun je eigen SectorProfile-instances registreren. Voorbeeld:
    |
    | use CodeBros\DutchHolidays\SectorProfile;
    | use CodeBros\DutchHolidays\Enums\HolidayKey;
    | use CodeBros\DutchHolidays\Enums\HolidayPolicy;
    |
    | 'sectors' => [
    |     'mijn_cao' => new SectorProfile(
    |         name: 'Mijn eigen cao',
    |         policies: [
    |             HolidayKey::GoodFriday->value => HolidayPolicy::Always,
    |         ],
    |         extraHolidays: [HolidayKey::NewYearsEve],
    |     ),
    | ],
    |
    | In runtime kun je ze ook toevoegen via:
    | DutchHolidays::registerSector('mijn_cao', $profile);
    |
    */
    'sectors' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Carbon Macros
    |--------------------------------------------------------------------------
    |
    | Registreer Carbon-macro's zoals ->isDutchHoliday() en ->isDutchWorkday().
    | Zet uit als je naamconflicten hebt met andere packages.
    |
    */
    'register_carbon_macros' => env('DUTCH_HOLIDAYS_CARBON_MACROS', true),

];
