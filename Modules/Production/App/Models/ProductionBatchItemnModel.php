<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class ProductionBatchItemnModel extends Model
{
    use HasFactory;

    protected $table = 'pro_batch_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'batch_id',
        'production_item_id',
        'receive_quantity',
        'issue_quantity',
        'damage_quantity'
    ];


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }


}
