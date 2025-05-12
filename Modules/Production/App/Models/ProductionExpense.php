<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProductionExpense extends Model
{
    protected $table = 'pro_expense';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at','issue_date'];
    protected $fillable = [
        'config_id',
        'production_inventory_id',
        'production_item_id',
        'production_batch_item_id',
        'issue_item_id',
        'production_element_id',
        'return_receive_batch_item_id',
        'quantity',
        'return_quantity',
        'issue_date',
        'purchase_price',
        'sales_price'
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


    public static function getProItemsWiseRecipeItems($proItemIds,$proConfig,$batchItem){
        $items = self::where('pro_expense.config_id', $proConfig)
            ->whereIn('pro_expense.production_item_id', $proItemIds)
            ->join('pro_element', 'pro_element.id', '=', 'pro_expense.production_element_id')
            ->join('pro_item', 'pro_item.id', '=', 'pro_element.production_item_id')
            ->join('inv_stock as pro_item_stock', 'pro_item_stock.id', '=', 'pro_item.item_id')
            ->join('inv_stock as recipe', 'recipe.id', '=', 'pro_element.material_id')
            ->select([
                'pro_expense.id',
                'pro_expense.production_batch_item_id',
                'pro_element.material_id as stock_item_id',
                'recipe.sales_price',
                'recipe.purchase_price',
                'recipe.quantity as stock_quantity',
                'recipe.display_name as display_name',
                'recipe.uom as unit_name',
                DB::raw('GROUP_CONCAT(DISTINCT pro_expense.production_item_id SEPARATOR ", ") as production_item_id'),
//                DB::raw('GROUP_CONCAT(DISTINCT pro_item_stock.display_name SEPARATOR ", ") as pro_item_names'),
                DB::raw('SUM(pro_expense.quantity) as batch_quantity'),
            ])
            ->groupBy('pro_element.material_id')
            ->get()
            ->map(function ($item, $index) {
                $item->id = $index + 1;
//                $item->type = 'batch_issue';
                return $item;
            })
            ->toArray();
        return $items;
    }


}
