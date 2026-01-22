<?php

declare(strict_types=1);

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

final class PosSaleFailureModel extends Model
{
    protected $table = 'inv_pos_sales_failures';

    protected $fillable = [
        'sync_batch_id',
        'device_id',
        'sale_data',
        'error_message',
    ];

    protected $casts = [
        'sale_data' => 'array',
    ];
}
