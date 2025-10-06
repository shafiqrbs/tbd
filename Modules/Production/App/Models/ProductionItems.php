<?php

namespace Modules\Production\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Models\ProductModel;


class ProductionItems extends Model
{
    use Sluggable;

    protected $table = 'pro_item';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'item_id',
        'created_by_id',
        'checked_by_id',
        'approved_by_id',
        'opening_date',
        'name',
        'mode',
        'quantity',
        'price',
        'sub_total',
        'license_date',
        'initiate_date',
        'uom',
        'remark',
        'issue_by',
        'designation',
        'waste_percent',
        'waste_amount',
        'material_amount',
        'material_quantity',
        'waste_material_quantity',
        'value_added_amount',
        'issue_quantity',
        'return_quantity',
        'damage_quantity',
        'reminig_quantity',
        'status',
        'is_delete',
        'is_revised',
        'warehouse_id',
        'process'
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


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

    public function setting_type()
    {
        return $this->belongsTo(SettingTypeModel::class);
    }


    public function itemAmendment()
    {
        return $this->hasMany(ProductionItemAmendmentModel::class, 'production_item_id');
    }


    public static function insertUpdate($configId,$itemId,$warehouseId){

        self::updateOrCreate(
            ['item_id' => $itemId], // condition
            [
                'warehouse_id' => $warehouseId,
                'config_id'    => $configId,
                'status'       => 1,
                'is_delete'     => 0
            ]
        );
    }


    public static function getFinishGoodsDownload($domain)
    {
        $data = self::where([
            ['pro_item.status', '=', 1],
            ['pro_item.is_delete', '=', 0],
            ['pro_config.domain_id', '=', $domain['global_id']],
        ])
            ->where('inv_setting.is_production', '=', 1)
            ->join('inv_stock','inv_stock.id','=','pro_item.item_id')
            ->join('pro_config','pro_config.id','=','pro_item.config_id')
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->join('inv_category','inv_category.id','=','inv_product.category_id')
            ->join('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'pro_item.id',
                'inv_stock.name as product_name',
                'inv_stock.uom as unit_name',
                'pro_item.waste_amount',
                DB::raw('DATE_FORMAT(pro_item.license_date, "%d-%M-%Y") as license_date'),
                DB::raw('DATE_FORMAT(pro_item.initiate_date, "%d-%M-%Y") as initiate_date'),
                'pro_item.quantity',
                'pro_item.waste_percent',
                'pro_item.waste_amount',
                'pro_item.material_amount',
                'pro_item.material_quantity',
                'pro_item.value_added_amount',
                'pro_item.sub_total',
                'pro_item.reminig_quantity',
                'inv_setting.slug as product_type_slug',
                'inv_category.name as category_name',
                'pro_item.status',
                'pro_item.is_revised',
                'pro_item.process',
            ])
            ->orderBy('pro_item.id','DESC')
            ->get()->toArray();

        return $data;
    }

    public static function getRecords($request,$domain){

        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page*$perPage:0;

        $entity = self::where([
                    ['pro_item.status', '=', 1],
                    ['pro_item.is_delete', '=', 0],
                    ['pro_config.domain_id', '=', $domain['global_id']],
                ])
            ->where('inv_setting.is_production', '=', 1)
            ->join('inv_stock','inv_stock.id','=','pro_item.item_id')
            ->join('pro_config','pro_config.id','=','pro_item.config_id')
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->join('inv_category','inv_category.id','=','inv_product.category_id')
            ->join('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->join('cor_warehouses','cor_warehouses.id','=','pro_item.warehouse_id')
            ->select([
                'pro_item.id',
                'inv_stock.name as product_name',
                /*'inv_stock.uom as unit_name',*/
                'inv_particular.name as unit_name',
                'pro_item.waste_amount',
                DB::raw('DATE_FORMAT(pro_item.license_date, "%d-%M-%Y") as license_date'),
                DB::raw('DATE_FORMAT(pro_item.initiate_date, "%d-%M-%Y") as initiate_date'),
                'pro_item.quantity',
                'pro_item.waste_percent',
                'pro_item.waste_amount',
                'pro_item.material_amount',
                'pro_item.material_quantity',
                'pro_item.value_added_amount',
                'pro_item.sub_total',
                'pro_item.reminig_quantity',
                'inv_setting.slug as product_type_slug',
                'inv_category.name as category_name',
                'cor_warehouses.name as warehouse_name',
                'cor_warehouses.id as warehouse_id',
                'pro_item.status',
                'pro_item.is_revised',
                'pro_item.process',
            ])->with(['itemAmendment:id,production_item_id,created_at']);

        if (isset($request['term']) && !empty($request['term'])){
            $entity = $entity->whereAny(['inv_stock.name','inv_setting.slug'],'LIKE','%'.trim($request['term']).'%');
        }

        if (isset($request['product_name']) && !empty($request['product_name'])){
            $entity = $entity->where('inv_stock.name','LIKE','%'.trim($request['product_name']));
        }
        $total  = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('pro_item.id','DESC')
            ->get();

        $data = array('count'=> $total,'entities' => $entities);
        return $data;
    }


    public static function dropdown($domain){
        $entity = self::where([
            ['pro_item.status', '=', 1],
            ['pro_item.is_delete', '=', 0],
            ['pro_config.domain_id', '=', $domain['global_id']],
        ])
            ->where('inv_setting.is_production', '=', 1)
            ->join('inv_stock','inv_stock.id','=','pro_item.item_id')
            ->join('pro_config','pro_config.id','=','pro_item.config_id')
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->join('inv_category','inv_category.id','=','inv_product.category_id')
            ->join('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'pro_item.id',
                'inv_stock.name as product_name',
                'inv_stock.uom as unit_name',
                'inv_setting.slug as product_type_slug',
                'inv_category.name as category_name',
            ])->get();


        return $entity;
    }

}
