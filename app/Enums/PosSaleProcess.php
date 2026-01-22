<?php

namespace App\Enums;

enum PosSaleProcess: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED  = 'completed';
    case FAILED     = 'failed';
    case COMPLETE_PARTIALLY     = 'completed_partially';

    /**
     * Optional: for validation rules
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
