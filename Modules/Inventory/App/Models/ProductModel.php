<?php

namespace Modules\Inventory\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

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
        'name',
        'code',
        'alternative_name',
        'barcode',
        'status',
        'config_id',
        'parent_id',
        'description',
        'quantity',
        'vendor_id',
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
            $model->status = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function images()
    {
        return $this->hasOne(ProductGalleryModel::class, 'product_id');
    }



    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $products = self::where([['inv_product.config_id',$domain['config_id']]])
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->join('inv_stock','inv_stock.product_id','=','inv_product.id')
            ->leftjoin('inv_particular as brand','brand.id','=','inv_stock.brand_id')
            ->leftjoin('inv_particular as model','model.id','=','inv_stock.model_id')
            ->leftjoin('inv_particular as color','color.id','=','inv_stock.color_id')
            ->leftjoin('inv_particular as grade','grade.id','=','inv_stock.grade_id')
            ->leftjoin('inv_particular as size','size.id','=','inv_stock.size_id')
            ->select([
                'inv_product.id',
                'inv_stock.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'inv_particular.name as unit_name',
                'inv_stock.barcode',
                'inv_product.alternative_name',
                'inv_setting.name as product_type',
                'inv_stock.quantity',
                'inv_stock.bangla_name',
                'inv_product.status',
                'inv_product.parent_id',
                'brand.name as brand_name',
                'model.name as model_name',
                'color.name as color_name',
                'grade.name as grade_name',
                'size.name as size_name',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.average_price',
            ])->with(['images' => function ($query) {
                $query->select([
                    'inv_product_gallery.id',
                    'inv_product_gallery.product_id',
                    DB::raw("CONCAT('uploads/inventory/product/feature_image/', inv_product_gallery.feature_image) AS feature_image"),
                    DB::raw("CONCAT('uploads/inventory/product/path_one/', inv_product_gallery.path_one) AS path_one"),
                    DB::raw("CONCAT('uploads/inventory/product/path_two/', inv_product_gallery.path_two) AS path_two"),
                    DB::raw("CONCAT('uploads/inventory/product/path_three/', inv_product_gallery.path_three) AS path_three"),
                    DB::raw("CONCAT('uploads/inventory/product/path_four/', inv_product_gallery.path_four) AS path_four")
                ])->where('inv_product_gallery.status', 1);
            }]);

        if (isset($request['term']) && !empty($request['term'])){
            $products = $products->whereAny([
                'inv_product.name',
                'inv_product.alternative_name',
                'inv_stock.display_name',
                'inv_stock.bangla_name',
//                'inv_product.slug',
//                'inv_category.name',
//                'inv_particular.name',
//                'inv_setting.name'
            ],'LIKE','%'.$request['term'].'%');
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
        if (isset($request['type']) && !empty($request['type']) && $request['type']=='product'){
            $products = $products->where('inv_stock.is_master',1);
        }

        $total  = $products->count();
        $entities = $products->skip($skip)
            ->take($perPage)
            ->orderBy('inv_product.id','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }
    public static function getStockDataFormDownload($domain)
    {
        $products = self::where([['inv_product.config_id',$domain['config_id']]])
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->join('inv_stock','inv_stock.product_id','=','inv_product.id')
            ->leftjoin('inv_particular as brand','brand.id','=','inv_stock.brand_id')
            ->leftjoin('inv_particular as model','model.id','=','inv_stock.model_id')
            ->leftjoin('inv_particular as color','color.id','=','inv_stock.color_id')
            ->leftjoin('inv_particular as grade','grade.id','=','inv_stock.grade_id')
            ->leftjoin('inv_particular as size','size.id','=','inv_stock.size_id')
            ->select([
                'inv_product.id',
                'inv_stock.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'inv_particular.name as unit_name',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'inv_setting.name as product_type',
                'inv_stock.quantity',
                'inv_stock.bangla_name',
                'inv_product.status',
                'inv_product.parent_id',
                'brand.name as brand_name',
                'model.name as model_name',
                'color.name as color_name',
                'grade.name as grade_name',
                'size.name as size_name',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.average_price',
            ]);

        $entities = $products->orderBy('inv_product.id','DESC')->get()->toArray();
        return $entities;
    }


    public static function getProductDetails($id,$domain)
    {

        $product = DB::table('inv_stock as inv_stock')
            ->join('inv_product', 'inv_stock.product_id', '=', 'inv_product.id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->leftjoin('inv_product_gallery','inv_product_gallery.product_id','=','inv_product.id')
            ->where([['inv_product.config_id',$domain['config_id']],['inv_stock.product_id',$id],['inv_stock.is_master',1]])
            ->select([
                'inv_product.id',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_product.category_id',
                'inv_category.name as category_name',
                'inv_product.unit_id',
                'inv_particular.name as unit_name',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'inv_product.product_type_id',
                'inv_setting.name as product_type',
                'inv_stock.purchase_price as purchase_price',
                'inv_stock.price as price',
                'inv_stock.display_name',
                'inv_stock.bangla_name',
                'inv_stock.sales_price as sales_price',
                'inv_stock.min_quantity as min_quantity',
                'inv_stock.sku as sku',
                DB::raw("CONCAT('".url('')."/uploads/inventory/product/feature_image/', inv_product_gallery.feature_image) AS feature_image"),
                DB::raw("CONCAT('".url('')."/uploads/inventory/product/path_one/', inv_product_gallery.path_one) AS path_one"),
                DB::raw("CONCAT('".url('')."/uploads/inventory/product/path_two/', inv_product_gallery.path_two) AS path_two"),
                DB::raw("CONCAT('".url('')."/uploads/inventory/product/path_three/', inv_product_gallery.path_three) AS path_three"),
                DB::raw("CONCAT('".url('')."/uploads/inventory/product/path_four/', inv_product_gallery.path_four) AS path_four"),
                'inv_product.status'
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
                'inv_product.id',
                'inv_product.name as display_name',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'inv_particular.id as unit_id',
                'inv_particular.name as unit_name',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'inv_stock.sku',
                'inv_stock.status'
            ]);
        $products = $products->orderBy('inv_product.id','DESC')->get();
        return $products;
    }
}
