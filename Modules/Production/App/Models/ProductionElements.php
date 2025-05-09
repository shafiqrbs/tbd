<?php

namespace Modules\Production\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemModel;


class ProductionElements extends Model
{
    use HasFactory;

    protected $table = 'pro_element';
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

    public function invStock()
    {
        return $this->belongsTo(StockItemModel::class, 'material_id','id');
    }


    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):500;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where([
//                    ['pro_element.status', '=', 1],
                    ['pro_element.config_id', '=', $domain['pro_config']],
                ])
            ->join('inv_stock','inv_stock.id','=','pro_element.material_id')
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->select([
                'pro_element.id',
                'pro_element.material_id',
                'inv_stock.display_name as product_name',
                'inv_particular.name as unit_name',
                'pro_element.quantity',
                'pro_element.material_quantity',
                'pro_element.price',
                'pro_element.sub_total',
                'pro_element.wastage_quantity',
                'pro_element.wastage_percent',
                'pro_element.wastage_amount',
                'pro_element.status',
            ]);

        if (isset($request['pro_item_id']) && $request['pro_item_id']!='') {
            $entity = $entity->where('pro_element.production_item_id', $request['pro_item_id']);
        }

        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('pro_element.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities' => $entities);
        return $data;
    }

    public static function getProItemsWiseRecipeItems($proItemIds,$proConfig,$batchItem){
        $items = self::where('pro_element.config_id', $proConfig)
            ->whereIn('pro_element.production_item_id', $proItemIds)
            ->join('pro_item', 'pro_item.id', '=', 'pro_element.production_item_id')
            ->join('inv_stock as pro_item_stock', 'pro_item_stock.id', '=', 'pro_item.item_id')
            ->join('inv_stock as recipe', 'recipe.id', '=', 'pro_element.material_id')
            ->select([
                'pro_element.material_id as product_id',
                'recipe.sales_price',
                'recipe.purchase_price',
                'recipe.quantity as stock_quantity',
                'recipe.display_name as display_name',
                'recipe.uom as unit_name',
                DB::raw('GROUP_CONCAT(DISTINCT pro_element.production_item_id SEPARATOR ", ") as production_item_id'),
                DB::raw('GROUP_CONCAT(DISTINCT pro_item_stock.display_name SEPARATOR ", ") as pro_item_names'),
                DB::raw('SUM(pro_element.quantity) as total_quantity'),
            ])
            ->groupBy('pro_element.material_id')
            ->get()
            ->map(function ($item, $index) {
                $item->id = $index + 1;
                $item->type = 'batch_issue';
                return $item;
            })
            ->toArray();
        return $items;
    }


}
