<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Utility\App\Models\ProductUnitModel;
use Ramsey\Collection\Collection;

class InvoiceBatchItemModel extends Model
{
    use HasFactory;

    protected $table = 'inv_batch_item';
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


    public function sales()
    {
        return $this->belongsTo(SalesModel::class);
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnitModel::class,'unit_id');
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $products = self::where([['inv_product.config_id',$domain['config_id']]])
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('uti_product_unit','uti_product_unit.id','=','inv_product.unit_id')
            ->leftjoin('inv_brand','inv_brand.id','=','inv_product.brand_id')
            ->leftjoin('uti_settings','uti_settings.id','=','inv_product.product_type_id')
            ->select([
                'inv_product.id',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'uti_product_unit.name as unit_name',
                'inv_brand.name as brand_name',
                'inv_product.opening_quantity',
                'inv_product.min_quantity',
                'inv_product.reorder_quantity',
                'inv_product.purchase_price',
                'inv_product.sales_price',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'uti_settings.name as product_type',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $products = $products->whereAny(['inv_product.name','inv_product.slug','inv_category.name','uti_product_unit.name','inv_brand.name','inv_product.sales_price','uti_settings.name'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])){
            $products = $products->where('inv_product.name',$request['name']);
        }

        if (isset($request['alternative_name']) && !empty($request['alternative_name'])){
            $products = $products->where('inv_product.alternative_name',$request['alternative_name']);
        }
        if (isset($request['sku']) && !empty($request['sku'])){
            $products = $products->where('inv_product.sku',$request['sku']);
        }
        if (isset($request['sales_price']) && !empty($request['sales_price'])){
            $products = $products->where('inv_product.sales_price',$request['sales_price']);
        }

        $total  = $products->count();
        $entities = $products->skip($skip)
            ->take($perPage)
            ->orderBy('inv_product.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getProductDetails($id,$domain)
    {
        $product = self::where([['inv_product.config_id',$domain['config_id']],['inv_product.id',$id]])
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('uti_product_unit','uti_product_unit.id','=','inv_product.unit_id')
            ->leftjoin('inv_brand','inv_brand.id','=','inv_product.brand_id')
            ->leftjoin('uti_settings','uti_settings.id','=','inv_product.product_type_id')
            ->select([
                'inv_product.id',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_product.category_id',
                'inv_category.name as category_name',
                'inv_product.unit_id',
                'uti_product_unit.name as unit_name',
                'inv_product.brand_id',
                'inv_brand.name as brand_name',
                'inv_product.opening_quantity',
                'inv_product.min_quantity',
                'inv_product.reorder_quantity',
                'inv_product.purchase_price',
                'inv_product.sales_price',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'inv_product.product_type_id',
                'uti_settings.name as product_type',
                'inv_product.sku',
                'inv_product.status'
            ])->first();

        return $product;
    }


    public static function getStockItem($domain)
    {
        $products = self::
            leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('uti_product_unit','uti_product_unit.id','=','inv_product.unit_id')
            ->leftjoin('inv_brand','inv_brand.id','=','inv_product.brand_id')
            ->leftjoin('uti_settings','uti_settings.id','=','inv_product.product_type_id')
            ->select([
                'inv_product.id',
                'inv_product.name as display_name',
                \DB::raw("CONCAT(inv_product.name, ' [',inv_product.remaining_quantity,'] ', uti_product_unit.name) AS product_name"),
                'inv_product.slug',
                'inv_category.name as category_name',
                'uti_product_unit.name as unit_name',
                'inv_brand.name as brand_name',
                'inv_product.min_quantity',
                'inv_product.remaining_quantity as quantity',
                'inv_product.purchase_price',
                'inv_product.sales_price',
                'inv_product.barcode',
                'inv_product.alternative_name',
            ]);
        $products = $products->orderBy('inv_product.id','DESC')->get();

        return $products;
    }
}
