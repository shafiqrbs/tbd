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
        'issue_quantity',
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
                'pro_expense.production_batch_item_id',
                'pro_element.material_id as stock_item_id',
                'recipe.sales_price',
                'recipe.purchase_price',
                'recipe.quantity as stock_quantity',
                'recipe.display_name as display_name',
                'recipe.uom as unit_name',
//                DB::raw('GROUP_CONCAT(DISTINCT pro_expense.production_item_id SEPARATOR ", ") as production_item_id'),
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

    public static function handleProductionExpenseBatchItemIdWise(object $productionBatchItem, int $configId)
    {
        // Fetch recipe items and related stock data
        $recipeItems = ProductionElements::join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id')
            ->select('pro_element.*', 'inv_stock.name', 'inv_stock.purchase_price as item_purchase_price', 'inv_stock.sales_price as item_sale_price')
            ->where('pro_element.production_item_id', $productionBatchItem->production_item_id)
            ->get();

        // If recipe items exist, calculate and store expenses
        if ($recipeItems->isNotEmpty()) {
            foreach ($recipeItems as $item) {
                $filter = [
                    'config_id' => $configId,
                    'production_item_id' => $productionBatchItem->production_item_id,
                    'production_batch_item_id' => $productionBatchItem->id,
                    'production_element_id' => $item->id,
                ];

                $data = [
                    'purchase_price' => $item->item_purchase_price,
                    'sales_price' => $item->item_sale_price,
                    'quantity' => $productionBatchItem->issue_quantity * $item->quantity,
                    'issue_quantity' => $productionBatchItem->issue_quantity * $item->quantity,
                ];

                ProductionExpense::updateOrCreate($filter, $data);
            }
        }
    }

}
