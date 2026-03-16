<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;

final class NumberHelper
{
    public static function formatAmount($amount, int $precision = 2): string
    {
        return rtrim(rtrim(number_format((float)$amount, $precision), '0'), '.');
    }
}
