<?php

namespace Modules\Production\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProductionConfig extends Model
{
    use HasFactory;

    protected $table = 'pro_config';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $fillable = [
        'domain_id',
        'production_procedure_id',
        'consumption_method_id',
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
