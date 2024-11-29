<?php

namespace Modules\Domain\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubdomainCategory extends Model
{
    use HasFactory;

    protected $table = 'inv_config_subdomain_product';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'created_by_id',
        'category_group_id',
        'category_id',
        'notes',
        'process',
        'status',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->status = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });

    }

}
