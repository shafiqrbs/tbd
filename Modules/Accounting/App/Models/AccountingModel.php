<?php

namespace Modules\Accounting\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingModel extends Model
{
    use HasFactory;

    protected $table = 'acc_config';
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
