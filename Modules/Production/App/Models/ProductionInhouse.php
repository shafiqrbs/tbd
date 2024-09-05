<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionInhouse extends Model
{
    use HasFactory;

    protected $table = 'pro_batch';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'vendor_id',
        'created_by_id',
        'checked_by_id',
        'approved_by_id',
        'code',
        'mode',
        'invoice',
        'remark',
        'requisition_no',
        'process',
        'status',
        'address',
        'issue_person',
        'issue_designation',
        'issue_time',
        'issue_date',
        'receive_date',
        'created_at',
        'updated_at',
        'path'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = self::quickRandom();
            $date =  new \DateTime("now");
            $model->receive_date = $date;
            $model->issue_date = $date;
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function quickRandom($length = 12)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


}
