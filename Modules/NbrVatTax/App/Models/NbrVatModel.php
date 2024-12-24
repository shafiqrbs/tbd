<?php

namespace Modules\NbrVatTax\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NbrVatModel extends Model
{
    use HasFactory;

    protected $table = 'nbr_config';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
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
