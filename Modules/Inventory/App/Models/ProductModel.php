<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'inv_product';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'product_type_id',
        'category_id',
        'unit_id',
        'brand_id',
        'name',
        'alternative_name',
        'sku',
        'barcode',
        'opening_quantity',
        'sales_price',
        'purchase_price',
        'min_quantity',
        'reorder_quantity',
        'status',
        'config_id',
    /*name
    quantity
    content
    price
    code
    status
    path
    product_type
    sorting
    production_type
    commission
    slug
    tlo_price
    opening_approve
    brand_id
    config_id
    wear_house_id
    business_particular_type_id
    rack_no_id
    opening_quantity
    adjustment_quantity
    min_quantity
    reorder_quantity
    transfer_quantity
    stock_in
    purchase_quantity
    sales_quantity
    remaining_quantity
    purchase_return_quantity
    sales_return_quantity
    damage_quantity
    bonus_quantity
    bonus_adjustment
    return_bonus_quantity
    bonus_purchase_quantity
    bonus_sales_quantity
    purchase_price
    avg_purchase_price
    production_sales_price
    sales_price
    discount_price
    minimum_price
    particular_code
    sku
    barcode
    model_no
    created_at
    updated_at
    size_id
    color_id
    alternative_name*/
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
