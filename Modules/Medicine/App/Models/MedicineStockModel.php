<?php

namespace Modules\Medicine\App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Hospital\App\Models\MedicineDetailsModel;
use Modules\Hospital\App\Models\MedicineDosageModel;
use Modules\Inventory\App\Entities\Particular;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductGalleryModel;
use Modules\Inventory\App\Models\ProductMeasurementModel;
use Modules\Inventory\App\Models\SettingModel;
use Modules\Inventory\App\Models\StockItemModel;

class MedicineStockModel extends Model
{
    use HasFactory,Sluggable;

    protected $table = 'hms_medicine_stock';
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

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function stock()
    {
        return $this->belongsTo(StockItemModel::class, 'stock_item_id');
    }

    public function dosage()
    {
        return $this->belongsTo(MedicineDosageModel::class, 'medicine_dosage_id');
    }

    public function bymeal()
    {
        return $this->belongsTo(MedicineDosageModel::class, 'medicine_bymeal_id');
    }


    public static function getCategoryStock($domain,$category){

        $entities = StockItemModel::where([['inv_stock.config_id', $domain['config_id']],['inv_product.is_delete',0]])
            ->where(function ($query) use ($category) {
                $query->where('inv_product.category_id', '=',$category);
            })
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->select([
                'inv_stock.id as id',
                'inv_stock.id as stock_item_id',
                'inv_product.name as name',
            ])
            ->orderBy('inv_product.name', 'ASC')
            ->get();
        return $entities;

    }
    public static function getCategoryStockForScrolling($domain, $category, $request): array
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 25;
        $perPage = min($perPage, 100);
        $skip = $page * $perPage;

        // Build query
        $entities = StockItemModel::where([
            ['inv_stock.config_id', $domain['config_id']],
            ['inv_product.is_delete', 0]
        ])
            ->where(function ($query) use ($category) {
                $query->where('inv_product.category_id', '=', $category);
            })
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->select([
                'inv_stock.id as id',
                'inv_stock.id as stock_item_id',
                'inv_product.name as name',
            ]);

        if ($request->filled('term')) {
            $search = $request->term;
            $entities->where('inv_stock.name', 'LIKE', "%{$search}%");
        }


        // Get total count before pagination
        $total = $entities->count();

        // Apply pagination
        $stockItems = $entities
            ->orderBy('name')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return ['data' => $stockItems, 'count' => $total];
    }


    public static function getStockDropdown($domain,$term){

        $config =  $domain['config_id'];
        $entities = StockItemModel::where([['inv_stock.config_id', $domain['config_id']],['inv_product.is_delete',0]])
            ->where(function ($query) use ($term) {
                $query->where('inv_product.name', 'LIKE', '%' . trim($term) . '%');
            })
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->select([
                'inv_stock.id as id',
                'inv_stock.id as stock_item_id',
                'inv_product.name as name',
            ])
            ->orderBy('inv_product.name', 'ASC')
            ->take(100)
            ->get();
        return $entities;

    }

    public static function getRecords($request, $domain)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $products = StockItemModel::where([['inv_stock.config_id', $domain['config_id']],['inv_product.is_delete',0]])
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->leftjoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->leftjoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->leftjoin('inv_setting', 'inv_setting.id', '=', 'inv_product.product_type_id')
            ->select([
                'inv_stock.id as id',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'inv_particular.name as unit_name',
                'inv_stock.barcode',
                'inv_stock.quantity',
            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $products = $products->whereAny([
                'inv_product.name',
                'inv_product.slug',
                'inv_category.name',
                'inv_category.slug'
            ], 'LIKE', '%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $products = $products->where('inv_product.name', $request['name']);
        }
        if (isset($request['sku']) && !empty($request['sku'])) {
            $products = $products->where('inv_product.sku', $request['sku']);
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
            ->orderBy('inv_category.name', 'ASC')
            ->orderBy('inv_product.name', 'ASC')
            ->get();
        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }

    public static function getGenericRecords($request, $domain)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 100;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $products = self::where([['inv_stock.config_id', $domain['config_id']],['inv_product.is_delete',0]])
            ->join('inv_product', 'inv_product.id', '=', 'hms_medicine_stock.product_id')
            ->leftjoin('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->leftjoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->leftjoin('inv_setting', 'inv_setting.id', '=', 'inv_product.product_type_id')
            ->join('inv_stock', 'inv_stock.id', '=', 'hms_medicine_stock.stock_item_id')
            ->select([
                'hms_medicine_stock.id as id',
                'inv_stock.id as stock_id',
                'inv_product.name as product_name',
                'inv_product.slug',
                'inv_category.name as category_name',
                'inv_category.id as category_id',
                'inv_particular.name as unit_name',
                'inv_stock.barcode',
                'inv_stock.quantity',
                'hms_medicine_stock.medicine_dosage_id',
                'hms_medicine_stock.medicine_bymeal_id',
                'hms_medicine_stock.opd_quantity as opd_quantity',
                'hms_medicine_stock.opd_status',
                'hms_medicine_stock.ipd_status',
                'hms_medicine_stock.admin_status',
                'hms_medicine_stock.duration_day',
                'hms_medicine_stock.status',

            ]);

        if (isset($request['term']) && !empty($request['term'])) {
            $products = $products->whereAny([
                'inv_product.name',
                'inv_category.name'
            ], 'LIKE', '%'.$request['term'].'%');
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $products = $products->where('inv_product.name', $request['name']);
        }
        if (isset($request['sku']) && !empty($request['sku'])) {
            $products = $products->where('inv_product.sku', $request['sku']);
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

    public static function getProductDetails($id)
    {

        $product = DB::table('hms_medicine_stock as hms_medicine_stock')
            ->join('inv_product', 'hms_medicine_stock.product_id', '=', 'inv_product.id')
            ->where('hms_medicine_stock.id',$id)
            ->select([
                'hms_medicine_stock.id',
                'inv_product.name as name',
                'inv_product.category_id',
                'inv_product.category_id',
                'hms_medicine_stock.medicine_dosage_id',
                'hms_medicine_stock.medicine_bymeal_id',
                'hms_medicine_stock.opd_quantity as opd_quantity',
            ])->first();
        return $product;

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
