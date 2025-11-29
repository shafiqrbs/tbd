<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;


class ProductionBatchItemModel extends Model
{
    protected $table = 'pro_batch_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'batch_id',
        'production_item_id',
        'receive_quantity',
        'issue_quantity',
        'damage_quantity',
        'price',
        'warehouse_id'
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

    public function productionItems()
    {
        return $this->hasMany(ProductionElements::class, 'production_item_id','production_item_id');
    }
    public function productionItem()
    {
        return $this->belongsTo(ProductionItems::class, 'production_item_id', 'id');
    }

    public function productionExpenses()
    {
        return $this->hasMany(ProductionExpense::class, 'production_batch_item_id','id');
    }

    public function productionIssue()
    {
        return $this->belongsTo(ProductionIssueModel::class, 'production_item_id', 'id');
    }



}
