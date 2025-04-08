<?php

namespace Modules\Domain\App\Models;
use Illuminate\Database\Eloquent\Model;

class SubDomainModel extends Model
{
    protected $table = 'dom_sub_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'domain_id',
        'sub_domain_id',
        'status'
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
