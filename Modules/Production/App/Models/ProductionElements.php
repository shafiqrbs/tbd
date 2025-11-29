<?php

namespace Modules\Production\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionItem;


class ProductionElements extends Model
{
    protected $table = 'pro_element';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'production_item_id',
        'material_id',
        'config_id',
        'quantity',
        'purchase_price',
        'price',
        'sub_total',
        'wastage_percent',
        'wastage_quantity',
        'wastage_amount',
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

    public function invStock()
    {
        return $this->belongsTo(StockItemModel::class, 'material_id','id');
    }

    public function material()
    {
        return $this->belongsTo(StockItemModel::class, 'material_id', 'id');
    }

    public function productionItem()
    {
        return $this->belongsTo(ProductionItem::class);
    }


    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):500;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where([
                    ['pro_element.status', '=', 1],
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
    public static function getProductionExpenseElement($productionItemId, $domain)
    {
        return self::from('pro_element as pe')
            ->join('inv_stock as s', 's.id', '=', 'pe.material_id')
            ->join('inv_product as p','p.id','=','s.product_id')
            ->leftJoin('inv_particular as u', 'u.id', '=', 'p.unit_id')
            ->where([
                ['pe.status', '=', 1],
                ['pe.config_id', '=', $domain['pro_config']],
                ['pe.production_item_id', '=', $productionItemId],
            ])
            ->select([
                'pe.id',
                's.display_name as product_name',
                'u.name as unit_name',
                'pe.quantity',
                'pe.material_quantity',
                'pe.price',
                'pe.sub_total',
                'pe.wastage_quantity',
                'pe.wastage_percent',
                'pe.wastage_amount',
                'pe.status',
            ])
            ->get();
    }


}
