<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;

final class DateHelper
{
    public static function getFirstAndLastDate(int $year, int $month): array
    {
        $firstDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $lastDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

        return [
            'first_date' => $firstDate->toDateString(),
            'last_date'  => $lastDate->toDateString(),
        ];
    }
}
