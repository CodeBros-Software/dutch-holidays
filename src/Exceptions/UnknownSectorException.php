<?php

declare(strict_types=1);

namespace CodeBros\DutchHolidays\Exceptions;

class UnknownSectorException extends DutchHolidaysException
{
    public static function make(string $sector): self
    {
        return new self(sprintf(
            'Onbekende sector "%s". Registreer eerst via DutchHolidays::registerSector() of gebruik een Sector enum-case.',
            $sector
        ));
    }
}
