# Dutch Holidays voor Laravel

Nederlandse feestdagen en werkdagberekeningen voor Laravel, met **sector- en cao-profielen** zodat je per klant of branche kunt configureren welke dagen vrij zijn.

Geen externe dependencies voor de holiday-berekeningen — alles is intern via het Gauss-algoritme.

---

## Features

- ✅ Alle nationale Nederlandse feestdagen met automatische berekening (Pasen, Hemelvaart, Pinksteren, Koningsdag-uitzondering)
- ✅ **10 voorgedefinieerde sectorprofielen** (overheid, bouw, zorg, onderwijs, ICT, grafimedia, ...)
- ✅ **Eigen sectoren/cao's** registreren met custom policies
- ✅ Per-feestdag policy: `Official`, `Always`, `Never`
- ✅ Extra niet-wettelijke dagen (carnaval, Sinterklaas, Oudejaarsdag, ...)
- ✅ Werkdag-helpers: `isWorkday()`, `nextWorkday()`, `addWorkdays()`, `workdaysBetween()`
- ✅ Carbon-macro's: `Carbon::today()->isDutchHoliday()`
- ✅ Artisan-commands voor inspectie
- ✅ Immutable, multi-tenant safe
- ✅ Volledig PHP 8.2+ met `readonly`, enums, named args

---

## Installatie

```bash
composer require codebros/dutch-holidays
```

Publiceer optioneel de config:

```bash
php artisan vendor:publish --tag=dutch-holidays-config
```

---

## Snelstart

```php
use CodeBros\DutchHolidays\Facades\DutchHolidays;

// Alle feestdagen voor 2026
DutchHolidays::forYear(2026);

// Alleen wettelijke vrije dagen
DutchHolidays::officialHolidays(2026);

// Check een datum
DutchHolidays::isHoliday('2026-12-25');         // true
DutchHolidays::isWorkday('2026-05-05');         // true (default sector, geen lustrumjaar)

// Werkdag-rekenen
DutchHolidays::nextWorkday('2026-12-24');       // 2026-12-28
DutchHolidays::workdaysBetween('2026-01-01', '2026-12-31');  // ~252
DutchHolidays::addWorkdays('2026-12-24', 5);    // datum + 5 werkdagen
```

---

## Sectoren (cao's)

Het grootste verschil tussen cao's is welke dagen wel of niet vrij zijn. Daarom werkt het pakket met **sectorprofielen**.

### Beschikbare sectoren

| Key             | Sector              | Goede Vrijdag | Bevrijdingsdag |
| --------------- | ------------------- | ------------- | -------------- |
| `default`       | Wettelijk           | nee           | lustrumjaren   |
| `government`    | Overheid            | ja            | ja             |
| `construction`  | Bouw                | ja            | ja             |
| `metal`         | Metaal & Techniek   | ja            | lustrumjaren   |
| `healthcare`    | Zorg                | ja            | lustrumjaren   |
| `education`     | Onderwijs           | ja            | ja             |
| `hospitality`   | Horeca              | nee           | lustrumjaren   |
| `ict`           | ICT / Software      | nee           | ja             |
| `grafimedia`    | Grafimedia          | ja            | ja             |
| `retail`        | Retail              | nee           | lustrumjaren   |

### Sector kiezen

```php
use CodeBros\DutchHolidays\Enums\Sector;

// Via enum (aanbevolen)
DutchHolidays::sector(Sector::Construction)->daysOff(2026);

// Of als string
DutchHolidays::sector('government')->isWorkday('2026-04-03'); // Goede Vrijdag → false
```

### Default sector instellen

In `.env`:

```env
DUTCH_HOLIDAYS_SECTOR=construction
```

Of in `config/dutch-holidays.php`:

```php
'default_sector' => Sector::Construction->value,
```

---

## Eigen sectoren / cao's

### Methode 1: ad-hoc policies

Voor incidenteel gebruik zonder een sector aan te maken:

```php
use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;

DutchHolidays::withPolicies([
    HolidayKey::GoodFriday->value => HolidayPolicy::Always,
    HolidayKey::LiberationDay->value => HolidayPolicy::Never,
])->forYear(2026);
```

### Methode 2: SectorProfile registreren

Voor cao's die je vaker nodig hebt — registreer ze in een `ServiceProvider`:

```php
use CodeBros\DutchHolidays\Facades\DutchHolidays;
use CodeBros\DutchHolidays\SectorProfile;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use CodeBros\DutchHolidays\Enums\HolidayPolicy;

public function boot(): void
{
    DutchHolidays::registerSector('werkpunt_klant_a', new SectorProfile(
        name: 'Klant A — eigen cao',
        policies: [
            HolidayKey::GoodFriday->value => HolidayPolicy::Always,
            HolidayKey::LiberationDay->value => HolidayPolicy::Always,
        ],
        extraHolidays: [HolidayKey::NewYearsEve],
        description: 'Klant heeft 5 mei en Oudejaarsdag als extra vrije dag.',
    ));
}
```

Daarna:

```php
DutchHolidays::sector('werkpunt_klant_a')->daysOff(2026);
```

### Methode 3: dynamisch per klant in een service

Bij multi-tenant apps waar policies in de database staan:

```php
class HolidayService
{
    public function __construct(private DutchHolidays $holidays) {}

    public function daysOffFor(Klant $klant, int $year): HolidayCollection
    {
        $policies = [];
        if ($klant->goede_vrijdag_vrij) {
            $policies[HolidayKey::GoodFriday->value] = HolidayPolicy::Always;
        }
        if ($klant->bevrijdingsdag_vrij) {
            $policies[HolidayKey::LiberationDay->value] = HolidayPolicy::Always;
        }

        return $this->holidays
            ->sector($klant->basis_sector ?? 'default')
            ->withPolicies($policies)
            ->daysOff($year);
    }
}
```

---

## Extra (niet-wettelijke) dagen

Voor regionale of bedrijfsspecifieke dagen:

```php
DutchHolidays::withExtraHolidays(
    HolidayKey::CarnivalMonday,
    HolidayKey::CarnivalTuesday,
)->forYear(2026);
```

Beschikbare extra-keys:
- `HolidayKey::CarnivalMonday` / `CarnivalTuesday`
- `HolidayKey::Sinterklaas`
- `HolidayKey::ChristmasEve`
- `HolidayKey::NewYearsEve`

---

## Werkdag-helpers

```php
// Is dit een werkdag in de huidige sector?
DutchHolidays::isWorkday('2026-05-05');

// Volgende / vorige werkdag
DutchHolidays::nextWorkday(now());
DutchHolidays::previousWorkday('2026-12-27');

// Werkdagen tellen tussen twee datums (inclusief)
DutchHolidays::workdaysBetween('2026-01-01', '2026-12-31');

// N werkdagen optellen (slaat weekenden + feestdagen over)
DutchHolidays::addWorkdays('2026-12-24', 5);
DutchHolidays::addWorkdays('2026-12-28', -2);
```

---

## Carbon-macro's

Zijn standaard geregistreerd (uit te zetten via config):

```php
Carbon::today()->isDutchHoliday();          // bool
Carbon::today()->isDutchWorkday();          // bool
Carbon::today()->nextDutchWorkday();        // CarbonImmutable
Carbon::today()->previousDutchWorkday();    // CarbonImmutable
Carbon::today()->addDutchWorkdays(5);       // CarbonImmutable
Carbon::today()->dutchHoliday();            // ?Holiday
```

---

## Datumbereik en meerdere jaren

```php
// Tussen twee datums (jaargrenzen overspannen mag)
DutchHolidays::between('2025-12-01', '2026-03-31');

// Alleen wettelijke
DutchHolidays::between('2025-12-01', '2026-03-31', officialOnly: true);

// Meerdere jaren tegelijk
$multi = DutchHolidays::forYears(2025, 2026, 2027);
$multi[2026]->official(); // HolidayCollection voor 2026
```

---

## Collections

Resultaten zijn `HolidayCollection`-instances (extends `\Illuminate\Support\Collection`) met handige scoping:

```php
$holidays = DutchHolidays::forYear(2026);

$holidays->official();                  // alleen vrije dagen
$holidays->unofficial();                // alleen niet-vrije (Goede Vrijdag in default)
$holidays->from('2026-06-01');          // vanaf datum
$holidays->until('2026-06-30');         // tot en met datum
$holidays->byKey(HolidayKey::Christmas);
$holidays->asMap();                     // ['2026-12-25' => 'Eerste Kerstdag', ...]
$holidays->dates();                     // alleen Carbon-datums
```

Plus alle standaard Collection-methods (`filter`, `map`, `groupBy`, ...).

---

## Artisan-commands

```bash
# Toon feestdagen voor huidig jaar (default sector)
php artisan holidays:list

# Voor een specifiek jaar
php artisan holidays:list 2026

# Voor een specifieke sector
php artisan holidays:list 2026 --sector=construction

# Inclusief niet-officiële dagen
php artisan holidays:list 2026 --all

# Output als JSON
php artisan holidays:list 2026 --json

# Toon alle beschikbare sectoren
php artisan holidays:sectors
```

---

## Custom HolidayProvider (geavanceerd)

Voor compleet eigen feestdagenbronnen (bijv. uit een database, een externe API of voor schoolvakanties) implementeer je `HolidayProvider`:

```php
use CodeBros\DutchHolidays\Contracts\HolidayProvider;
use CodeBros\DutchHolidays\Holiday;
use CodeBros\DutchHolidays\Enums\HolidayKey;
use Carbon\CarbonImmutable;

class CompanyClosureProvider implements HolidayProvider
{
    public function holidaysFor(int $year): array
    {
        return [
            new Holiday(
                key: HolidayKey::ChristmasEve,
                name: 'Bedrijfssluiting',
                date: CarbonImmutable::create($year, 12, 27),
                official: true,
            ),
        ];
    }

    public function name(): string
    {
        return 'company-closure';
    }
}

// Gebruik
DutchHolidays::withProvider(new CompanyClosureProvider())->daysOff(2026);
```

Providers worden ná de nationale set toegevoegd en kunnen bestaande dagen overrulen via dezelfde `HolidayKey`.

---

## Disclaimer over cao-data

De voorgedefinieerde sectorprofielen weerspiegelen de meest gangbare invulling, maar **cao-afspraken verschillen per werkgever en veranderen jaarlijks**. Controleer altijd de actuele cao van je klant en pas indien nodig aan via `->withPolicies()` of een eigen geregistreerde sector.

---

## Testing

```bash
composer test
composer test-coverage
```

---

## License

MIT — see [LICENSE.md](LICENSE.md).
