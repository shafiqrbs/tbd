<?php

namespace Modules\Medicine\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ProductGalleryModel;
use Modules\Inventory\App\Models\ProductMeasurementModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemModel;

class MedicineModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'hms_medicine_details';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

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
            $model->barcode = self::generatedEventListener($model)['generateId'];
            $model->code = self::generatedEventListener($model)['code'];
            $model->status = true;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function generatedEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_product',
        ];
        return $patternCodeService->productMedicineCode($params);
    }


    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(ParticularModel::class, 'unit_id');
    }

    public function setting()
    {
        return $this->belongsTo(SettingModel::class, 'product_type_id');
    }
    public function images()
    {
        return $this->hasOne(ProductGalleryModel::class, 'product_id', 'product_id')->where('status', 1);
    }

    public static function getMedicineDropdown($domain,$term){

        $config =  $domain['inv_config'];
        $entities = self::where('inv_product.config_id', $config)
            ->where(function ($query) use ($term) {
                $query->where('hms_medicine_details.name', 'LIKE', '%' . trim($term) . '%')
                    ->orWhere('inv_product.name', 'LIKE', '%' . trim($term) . '%');
            })
            ->leftjoin('hms_medicine_stock', 'hms_medicine_stock.id', '=', 'hms_medicine_details.medicine_stock_id')
            ->leftjoin('inv_product', 'hms_medicine_stock.product_id', '=', 'inv_product.id')
            ->leftjoin('hms_medicine_dosage as dosage', 'dosage.id', '=', 'hms_medicine_stock.medicine_dosage_id')
            ->leftjoin('hms_medicine_dosage as bymeal', 'bymeal.id', '=', 'hms_medicine_stock.medicine_bymeal_id')
            ->select([
                'hms_medicine_details.id as id',
                'hms_medicine_details.id as product_id',
                'hms_medicine_details.name as product_name',
                'inv_product.name as generic',
                'hms_medicine_details.company',
                'hms_medicine_details.formulation',
                'dosage.name as doses_details',
                'hms_medicine_details.doses_form',
                'bymeal.name as by_meal',
                'hms_medicine_stock.duration_day',
                'hms_medicine_stock.medicine_dosage_id',
                'hms_medicine_stock.medicine_bymeal_id',
                'hms_medicine_stock.opd_quantity',
            ])
            ->groupBy('inv_stock.id')
            ->orderBy('inv_product.name', 'ASC')
            ->take(100)
            ->get();

        return $entities;

    }

    public static function getRecords($request, $domain)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 100;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $products = self::where([['hms_medicine_details.config_id', $domain['hms_config']]])
            ->leftjoin('hms_medicine_stock', 'hms_medicine_stock.id', '=', 'hms_medicine_details.medicine_stock_id')
            ->leftjoin('inv_product', 'hms_medicine_stock.product_id', '=', 'inv_product.id')
            ->leftjoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->leftjoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->leftjoin('inv_setting', 'inv_setting.id', '=', 'inv_product.product_type_id')
            ->leftjoin('hms_medicine_dosage as dosage', 'dosage.id', '=', 'hms_medicine_stock.medicine_dosage_id')
            ->leftjoin('hms_medicine_dosage as bymeal', 'bymeal.id', '=', 'hms_medicine_stock.medicine_bymeal_id')
            ->select([
                'hms_medicine_details.id as id',
                'hms_medicine_details.id as product_id',
                'hms_medicine_details.name as product_name',
                'inv_product.name as generic',
                'hms_medicine_details.company',
                'hms_medicine_details.formulation',
                'dosage.name as doses_details',
                'hms_medicine_details.doses_form',
                'bymeal.name as by_meal',
                'hms_medicine_stock.duration_day',
                'hms_medicine_stock.medicine_dosage_id',
                'hms_medicine_stock.medicine_bymeal_id',
                'hms_medicine_stock.opd_quantity',
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $products = $products->whereAny([
                'inv_product.name',
                'inv_product.alternative_name',
                'inv_stock.display_name',
                'inv_stock.bangla_name',
                'inv_category.name'
            ], 'LIKE', '%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $products = $products->where('inv_product.name', $request['name']);
        }
        if (isset($request['alternative_name']) && !empty($request['alternative_name'])) {
            $products = $products->where('inv_product.alternative_name', $request['alternative_name']);
        }
        if (isset($request['sku']) && !empty($request['sku'])) {
            $products = $products->where('inv_product.sku', $request['sku']);
        }
        if (isset($request['sales_price']) && !empty($request['sales_price'])) {
            $products = $products->where('inv_product.sales_price', $request['sales_price']);
        }
        if (isset($request['type']) && !empty($request['type']) && $request['type'] == 'product') {
            $products = $products->where('inv_stock.is_master', 1);
        }
        if (isset($request['category_id']) && !empty($request['category_id'])) {
            $products = $products->where('inv_product.category_id', $request['category_id']);
        }
        if (isset($request['product_type_id']) && !empty($request['product_type_id'])) {
            $products = $products->where('inv_product.product_type_id', $request['product_type_id']);
        }
        $total = $products->count();
        $entities = $products->skip($skip)
            ->take($perPage)
            ->orderBy('inv_setting.name', 'ASC')
            ->orderBy('inv_category.name', 'ASC')
            ->orderBy('inv_product.name', 'ASC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    public static function getRawSql($query)
    {
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $binding = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
            $sql = preg_replace('/\?/', $binding, $sql, 1);
        }
        return $sql;
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
                DB::raw('ROUND(inv_stock.price, 2) as price'),
                DB::raw('ROUND(inv_stock.purchase_price, 2) as purchase_price'),
                DB::raw('ROUND(inv_stock.sales_price, 2) as sales_price'),
                DB::raw('ROUND(inv_stock.average_price, 2) as average_price'),
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
                'inv_stock.quantity as stock_quantity',
                'inv_stock.sku as sku',
                'inv_stock.id as stock_item_id',
                'inv_product_gallery.feature_image',
                'inv_product_gallery.path_one',
                'inv_product_gallery.path_two',
                'inv_product_gallery.path_three',
                'inv_product_gallery.path_four',
                'inv_product.status'
            ])->first();
        return $product;

    }


    public static function getStockItem($request,$domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 100;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = StockItemModel::where([['inv_product.config_id',$domain['config_id']]])
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'inv_stock.id',
                'inv_stock.quantity',
                'inv_stock.min_quantity',
                'inv_product.name as name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'inv_particular.id as unit_id',
                'inv_particular.name as unit_name',
                'inv_product.barcode',
                'inv_product.alternative_name',
                'inv_stock.sku',
                'inv_stock.status'
            ]);
        $entities = $entities->orderBy('inv_product.name','ASC');
        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_setting.name', 'ASC')
            ->orderBy('inv_category.name', 'ASC')
            ->orderBy('inv_product.name', 'ASC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    public static function insertExcelProducts($domain)
    {
        $invConfigId = $domain['inv_config'];
        $configId = $domain['hms_config'];
        $medicines = MedicineModel::where('config_id', $configId)->get();
        $productNature = SettingModel::firstWhere('setting_id', 3);
        foreach ($medicines as $medicine) {
            $product = ProductModel::updateOrCreate(
                [
                    'config_id' => $invConfigId,
                    'name'      => $medicine->name,
                ],
                [
                    'slug'            => strtolower($medicine->name),
                    'product_type_id' => $productNature->id ?? null,
                    'status'          => 1,
                ]
            );

            StockItemModel::updateOrCreate(
                [
                    'config_id' => $invConfigId,
                    'product_id'      => $product->id,
                ],
                [
                    'status'          => 1,
                ]
            );

            MedicineDetailsModel::updateOrCreate(
                [
                    'product_id'      => $product->id,
                ],
                [
                    'generic'          => $medicine->generic,
                    'company'          => $medicine->company,
                    'formulation'          => $medicine->formulation,
                    'dose_details'          => $medicine->dose_details,
                    'generic_id'          => $medicine->generic_id,
                    'doses_form'          => $medicine->doses_form,
                    'doses_details'          => $medicine->doses_details,
                    'by_meal'          => $medicine->by_meal,
                    'duration_month'          => $medicine->duration_month,
                    'duration_day'          => $medicine->duration_day,
                    'priority'          => $medicine->priority,
                    'price'          => $medicine->price,
                    'instruction'          => $medicine->instruction,
                    'status'          => 1,
                ],
            );

        }
    }
}
