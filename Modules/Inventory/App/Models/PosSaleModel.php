<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PosSaleProcess;

class PosSaleModel extends Model
{
    protected $table = 'inv_pos_sales';

    protected $fillable = [
        'config_id',
        'created_by_id',
        'content',
        'process',
        'status',
        'device_id',
        'sync_batch_id'
    ];

    protected $casts = [
        'process' => PosSaleProcess::class,
        'content' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->status  = true;
            $model->process = PosSaleProcess::PENDING;
        });
    }
}
