<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItemModel extends Model
{

    protected $table = 'inv_sales_return_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }
}
