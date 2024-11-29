<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockItemModel extends Model
{
    use HasFactory;

    protected $table = 'inv_stock';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'product_id',
        'barcode',
        'sales_price',
        'purchase_price',
        'min_quantity',
        'reorder_quantity',
        'remaining_quantity',
        'status',
        'config_id',
        'brand_id',
        'color_id',
        'size_id',
        'grade_id',
        'price',
        'name',
        'display_name',
        'uom',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->status = true;
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
            ->leftjoin('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'inv_product.id',
                'inv_product.name as product_name',
                'inv_stock.slug',
                'inv_category.name as category_name',
                'inv_particular.name as unit_name',
                'inv_stock.opening_quantity',
                'inv_stock.min_quantity',
                'inv_stock.reorder_quantity',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.barcode',
                'inv_product.alternative_name',
                'inv_setting.name as product_type',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $products = $products->whereAny(['inv_product.name','inv_product.slug','inv_category.name','inv_particular.name','inv_brand.name','inv_product.sales_price','inv_setting.name'],'LIKE','%'.$request['term'].'%');
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
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'inv_product.product_type_id',
                'inv_setting.name as product_type',
                'inv_category.name as category_name',
                'inv_particular.name as unit_name',
                'inv_product.id',
                'inv_product.name as product_name',
                'inv_product.alternative_name',
                'inv_product.slug',
                'inv_product.category_id',
                'inv_product.unit_id',
                'inv_stock.opening_quantity',
                'inv_stock.min_quantity',
                'inv_stock.reorder_quantity',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.barcode',
                'inv_stock.sku',
                'inv_stock.status'
            ])->first();

        return $product;
    }


    public static function getStockItem($domain)
    {
        $products = self::where([['inv_product.config_id',$domain['config_id']]])
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'inv_stock.id',
                \DB::raw("CONCAT(inv_stock.name, '[',IFNULL(inv_stock.remaining_quantity, 0),'] ', IFNULL(inv_particular.name,'')) AS product_name"),
                'inv_stock.name as name',
                'inv_stock.display_name as display_name',
                'inv_product.slug',
                'inv_product.category_id',
                'inv_product.unit_id',
                'inv_stock.remaining_quantity as quantity',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.barcode',
                'inv_particular.name as uom',
            ]);
        $products = $products->orderBy('inv_product.id','DESC')->get();

        return $products;
    }


    public static function getProductForRecipe($domain)
    {
        $products = self::where([['inv_product.config_id',$domain['config_id']]])
            ->whereIN('inv_setting.slug',['raw-materials','stockable','mid-production'])
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->join('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'inv_stock.id',
                'inv_setting.name as product_type',
                'inv_setting.slug as product_slug',
                'inv_category.id as category_id',
                'inv_category.name as category_name',
                'inv_particular.id as unit_id',
                'inv_particular.name as unit_name',
                \DB::raw("CONCAT(inv_stock.name, ' [',IFNULL(inv_stock.remaining_quantity, 0),'] ', inv_particular.name) AS product_name"),
                'inv_stock.display_name as display_name',
                'inv_stock.slug',
                'inv_stock.opening_quantity',
                'inv_stock.min_quantity',
                'inv_stock.reorder_quantity',
                'inv_stock.remaining_quantity as quantity',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.barcode',
                'inv_product.alternative_name',
                'inv_stock.sku',
                'inv_stock.status'
            ]);
        $products = $products->orderBy('inv_product.id','DESC')->get();
        return $products;
    }

    public static function getStockSkuItem($product_id,$domain)
    {
        return self::where([['inv_stock.config_id',$domain['config_id']]])->where('product_id',$product_id)->where('is_master',0)
            ->leftjoin('inv_particular as grade','grade.id','=','inv_stock.grade_id')
            ->leftjoin('inv_particular as color','color.id','=','inv_stock.color_id')
            ->leftjoin('inv_particular as brand','brand.id','=','inv_stock.brand_id')
            ->leftjoin('inv_particular as size','size.id','=','inv_stock.size_id')
            ->select([
                'inv_stock.id as stock_id',
                'inv_stock.product_id',
                'inv_stock.name',
                'inv_stock.display_name',
                'inv_stock.price',
                'inv_stock.grade_id',
                'grade.name as grade_name',
                'inv_stock.color_id',
                'color.name as color_name',
                'inv_stock.brand_id',
                'brand.name as brand_name',
                'inv_stock.size_id',
                'size.name as size_name'
            ])->get()->toArray();
    }

}
