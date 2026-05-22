<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Console;

use CodeBros\DutchHolidays\DutchHolidays;
use CodeBros\DutchHolidays\SectorProfile;
use Illuminate\Console\Command;

class ListSectorsCommand extends Command
{
    protected $signature = 'holidays:sectors';

    protected $description = 'Toon alle beschikbare sector-profielen en hun policies';

    public function handle(DutchHolidays $holidays): int
    {
        $sectors = $holidays->availableSectors();

        $this->info(sprintf('Beschikbare sectoren (%d):', count($sectors)));
        $this->newLine();

        foreach ($sectors as $key => $profile) {
            $this->renderSector($key, $profile);
            $this->newLine();
        }

        return self::SUCCESS;
    }

    protected function renderSector(string $key, SectorProfile $profile): void
    {
        $this->line("<fg=cyan;options=bold>{$key}</> <fg=gray>—</> {$profile->name}");

        if ($profile->description) {
            $this->line("  <fg=gray>{$profile->description}</>");
        }

        if (! empty($profile->policies)) {
            $this->line('  <options=bold>Policies:</>');
            foreach ($profile->policies as $holidayKey => $policy) {
                $this->line("    • {$holidayKey} → <fg=yellow>{$policy->value}</>");
            }
        }

        if (! empty($profile->extraHolidays)) {
            $extras = collect($profile->extraHolidays)
                ->map(fn ($k) => $k->value)
                ->implode(', ');
            $this->line("  <options=bold>Extra feestdagen:</> {$extras}");
        }
    }
}
