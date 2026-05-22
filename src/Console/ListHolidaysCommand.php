<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Console;

use CodeBros\DutchHolidays\DutchHolidays;
use CodeBros\DutchHolidays\Holiday;
use Illuminate\Console\Command;

class ListHolidaysCommand extends Command
{
    protected $signature = 'holidays:list
                            {year? : Het jaar (default: huidig jaar)}
                            {--sector= : Welke sector (default uit config)}
                            {--all : Toon ook niet-officiële dagen}
                            {--json : Output als JSON}';

    protected $description = 'Toon Nederlandse feestdagen voor een jaar';

    public function handle(DutchHolidays $holidays): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);
        $sector = $this->option('sector');
        $showAll = (bool) $this->option('all');
        $asJson = (bool) $this->option('json');

        $instance = $sector ? $holidays->sector($sector) : $holidays;
        $list = $showAll ? $instance->forYear($year) : $instance->officialHolidays($year);

        if ($asJson) {
            $this->line($list->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info(sprintf('Feestdagen %d', $year));
        $this->line(sprintf(
            'Sector: %s%s',
            $instance->activeSector()->name,
            $showAll ? ' (incl. niet-officiële)' : ''
        ));

        if ($description = $instance->activeSector()->description) {
            $this->line("<fg=gray>{$description}</>");
        }

        $this->newLine();

        $this->table(
            ['Datum', 'Dag', 'Naam', 'Wettelijk'],
            $list->map(fn (Holiday $h) => [
                $h->date->format('d-m-Y'),
                $h->date->locale('nl')->isoFormat('dddd'),
                $h->name,
                $h->official ? '✓' : '—',
            ])->all()
        );

        $this->line(sprintf('<fg=green>Totaal: %d %s</>', $list->count(), $showAll ? 'dagen' : 'vrije dagen'));

        return self::SUCCESS;
    }
}
