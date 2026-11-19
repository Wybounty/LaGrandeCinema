<?php

namespace App;

use Carbon\CarbonInterface;

enum StatisticsPeriod: string
{
    case Today = 'today';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public function range(): array
    {
        $now = now();

        return match ($this) {
            self::Today => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            self::Week => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            self::Month => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            self::Year => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Today => "Aujourd'hui",
            self::Week => 'Cette semaine',
            self::Month => 'Ce mois-ci',
            self::Year => 'Cette année',
        };
    }
}
