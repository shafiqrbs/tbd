<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\WarehouseModel;

class StockItemModel extends Model
{
    use SoftDeletes;

    protected $table = 'inv_stock';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'product_id',
        'quantity',
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
        'model_id',
        'price',
        'name',
        'sku',
        'display_name',
        'is_master',
        'uom',
        'item_size',
        'bangla_name',
        'average_price',
        'parent_stock_item',
        'is_private',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->barcode = self::generatedEventListener($model)['generateId'];
            $model->product_code = self::generatedEventListener($model)['productCode'];
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

        $product = DB::table('inv_product as inv_product')
            ->where('inv_product.id', $model['product_id'])
            ->select('inv_product.id','inv_product.barcode','inv_product.product_code')
            ->first();

        $params = [
            'product' => $product,
            'table' => 'inv_stock',
        ];
        return $patternCodeService->productStockBarcodeCode($params);
    }

    public function brand()
    {
        return $this->belongsTo(ParticularModel::class, 'brand_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }


    public function measurments():HasMany
    {
        return $this->hasMany(ProductMeasurementModel::class, 'product_id');
    }


    public function stockItemHistory() :HasMany
    {
        return $this->hasMany(StockItemHistoryModel::class, 'stock_item_id');
    }
    public function multiplePrice() :HasMany
    {
        return $this->hasMany(StockItemPriceMatrixModel::class, 'stock_item_id');
    }
    public function currentWarehouseStock() :HasMany
    {
        return $this->hasMany(CurrentStockModel::class, 'stock_item_id');
    }
    public function purchaseItemForSales() :HasMany
    {
        return $this->hasMany(PurchaseItemModel::class, 'stock_item_id');
    }

    // In StockItemModel.php
    public function parentStock()
    {
        return $this->belongsTo(StockItemModel::class, 'parent_stock_item');
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
                'inv_product.id as id',
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
            ->orderBy('inv_product.name','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getProductStockDetails($id,$domain)
    {
        $product = self::where([['inv_product.config_id',$domain['config_id']],['inv_stock.id',$id]])
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
                'inv_product.expiry_duration',
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
                'inv_product.expiry_duration',
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
        return self::with([
            'product.measurement.unit',
            'product.unit',
            'product.category',
            'product.setting',
            'product.images',
            'product.parent_images',
            'multiplePrice.priceUnitName',
            'currentWarehouseStock' => function ($q) use ($domain) {
                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
            },
            'purchaseItemForSales' => function ($q) use ($domain) {
                $q->whereNotNull('expired_date')
                    ->where('expired_date', '>', now()) // only not expired
                    ->whereRaw('quantity > COALESCE(sales_quantity, 0)');
            }
            ])
            ->where('config_id', $domain['config_id'])
            ->where('status', 1)
            ->orderByDesc('id')
            ->get()
            ->map(function($stock) {
                $product = $stock->product;
                return [
                    'id'                => $stock->id,
                    'stock_id'          => $stock->id,
                    'name'              => $stock->display_name ?? $stock->name,
                    'display_name'      => $stock->display_name ?? $stock->name,
                    'product_name'      => $stock->name . '[' . ($stock->quantity ?? 0) . '] ' . ($product->unit->name ?? ''),
                    'slug'              => $product->slug ?? null,
                    'vendor_id'         => $product->vendor_id ?? null,
                    'category_id'       => $product->category_id ?? null,
                    'unit_id'           => $product->unit_id ?? null,
                    'unit_name'         => $product->unit->name ?? null,
                    'quantity'          => $stock->quantity,
                    'price'       => ROUND($stock->price,2),
                    'sales_price'       => ROUND($stock->sales_price,2),
                    'purchase_price'    => ROUND($stock->purchase_price,2),
                    'average_price'    => ROUND($stock->average_price,2),
                    'barcode'           => $stock->barcode,
                    'product_nature'    => $product->setting->slug ?? null,
                    'product_nature_id'    => $product->setting->id ?? null,
                    'feature_image'     => $product->parent_id ? optional(optional($product)->parent_images)->feature_image ?? null : optional(optional($product)->images)->feature_image ?? null,
                    'multi_price' => optional(optional($stock)->multiplePrice)->map(function ($m) {
                        return [
                            'id'                => $m->id,
                            'price_unit_id'     => $m->price_unit_id,
                            'price'             => $m->price,
                            'field_name'        => $m->priceUnitName->name ?? null,
                            'field_slug'        => $m->priceUnitName->slug ?? null,
                            'parent_slug'       => $m->priceUnitName->parent_slug ?? null,
                        ];
                    }),
                    'current_warehouse_stock' => optional(optional($stock)->currentWarehouseStock)->map(function ($s) {
                        return [
                            'id'                => $s->id,
                            'config_id'         => $s->config_id,
                            'quantity'          => $s->quantity,
                            'warehouse_id'      => $s->warehouse_id,
                            'stock_item_id'     => $s->stock_item_id,
                            'warehouse_name'    => $s->warehouse->name ?? null,
                        ];
                    }),
                    'purchase_item_for_sales' => optional(optional($stock)->purchaseItemForSales)->map(function ($s) {
                        $salesQty = $s->sales_quantity ?? 0; // if null → 0
                        return [
                            'id'                => $s->id,
                            'warehouse_id'      => $s->warehouse_id,
                            'purchase_quantity' => $s->quantity,
                            'sales_quantity'    => $salesQty,
                            'remain_quantity'   => $s->quantity - $salesQty,
                            'expired_date'      => $s->expired_date
                                ? Carbon::parse($s->expired_date)->format('d-M-Y')
                                : null,
                        ];
                    }),
                    'measurements' => optional(optional($product)->measurement)->map(function ($m) {
                        return [
                            'id'            => $m->id,
                            'unit_id'       => $m->unit_id,
                            'unit_name'     => $m->unit->name ?? null,
                            'slug'          => $m->unit->slug ?? null,
                            'is_base_unit'  => $m->is_base_unit,
                            'is_sales'      => $m->is_sales,
                            'is_purchase'   => $m->is_purchase,
                            'quantity'      => $m->quantity,
                        ];
                    }),

                ];
            });
    }

    public static function getPosStockItem($domain)
    {
        return self::with(
            [
                'product.measurement.unit',
                'product.unit',
                'product.category',
                'product.setting',
                'product.images',
                'multiplePrice.priceUnitName',
                'purchaseItemForSales' => function ($q) use ($domain) {
                    $q->whereNotNull('expired_date')
                        ->where('expired_date', '>', now()) // only not expired
                        ->whereRaw('quantity > COALESCE(sales_quantity, 0)');
                }
            ]
        )
            ->where('inv_stock.config_id', $domain['config_id'])
            ->whereHas('product.setting', function ($query) {
                $query->whereIn('slug', [
                    'pre-production', 'stockable', 'mid-production', 'post-production'
                ]);
            })
            ->where('inv_stock.status', 1)
            ->orderByDesc('inv_stock.name')
            ->get()
            ->map(function($stock) {
                $product = $stock->product;
                return [
                    'id'                => $stock->id,
                    'stock_id'          => $stock->id,
                    'name'              => $stock->display_name ?? $stock->name,
                    'display_name'      => $stock->display_name ?? $stock->name,
                    'product_name'      => $stock->name . '[' . ($stock->quantity ?? 0) . '] ' . ($product->unit->name ?? ''),
                    'slug'              => $product->slug ?? null,
                    'vendor_id'         => $product->vendor_id ?? null,
                    'category_id'       => $product->category_id ?? null,
                    'unit_id'           => $product->unit_id ?? null,
                    'unit_name'         => $product->unit->name ?? null,
                    'quantity'          => $stock->quantity,
                    'price'       => ROUND($stock->price,2),
                    'sales_price'       => ROUND($stock->sales_price,2),
                    'purchase_price'    => ROUND($stock->purchase_price,2),
                    'average_price'    => ROUND($stock->average_price,2),
                    'barcode'           => $stock->barcode,
                    'product_nature'    => $product->setting->slug ?? null,
                    'feature_image'     => $product->parent_id ? optional(optional($product)->parent_images)->feature_image ?? null : optional(optional($product)->images)->feature_image ?? null,
                    'purchase_item_for_sales' => optional(optional($stock)->purchaseItemForSales)->map(function ($s) {
                        $salesQty = $s->sales_quantity ?? 0; // if null → 0
                        return [
                            'purchase_item_id'  => $s->id,
                            'purchase_quantity' => $s->quantity,
                            'sales_quantity'    => $salesQty,
                            'remain_quantity'   => $s->quantity - $salesQty,
                            'expired_date'      => $s->expired_date
                                ? Carbon::parse($s->expired_date)->format('d-M-Y')
                                : null,
                        ];
                    }),
                    'multi_price' => optional(optional($stock)->multiplePrice)->map(function ($m) {
                        return [
                            'id'                => $m->id,
                            'price_unit_id'     => $m->price_unit_id,
                            'price'             => $m->price,
                            'field_name'        => $m->priceUnitName->name ?? null,
                            'field_slug'        => $m->priceUnitName->slug ?? null,
                            'parent_slug'       => $m->priceUnitName->parent_slug ?? null,
                        ];
                    }),
                    'measurements' => optional(optional($product)->measurement)->map(function ($m) {
                        return [
                            'id'            => $m->id,
                            'unit_id'       => $m->unit_id,
                            'unit_name'     => $m->unit->name ?? null,
                            'slug'          => $m->unit->slug ?? null,
                            'is_base_unit'  => $m->is_base_unit,
                            'is_sales'      => $m->is_sales,
                            'is_purchase'   => $m->is_purchase,
                            'quantity'      => $m->quantity,
                        ];
                    }),

                ];
            });
    }


    public static function getStockItemBK($domain)
    {
        $products = self::with(['product.measurement'])
            ->where([['inv_product.config_id',$domain['config_id']]])->where('inv_stock.status',1)
//            ->whereIN('inv_setting.slug',['pre-production','stockable','mid-production','post-production'])
            ->join('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->leftjoin('inv_product_gallery','inv_product_gallery.product_id','=','inv_product.id')
            ->leftjoin('inv_product_unit_measurment','inv_product_unit_measurment.product_id','=','inv_product.id')
            ->select([
                'inv_stock.id',
                'inv_product.id as product_id',
                \DB::raw("CONCAT(inv_stock.name,'[',IFNULL(inv_stock.quantity, 0),'] ', IFNULL(inv_particular.name,'')) AS product_name"),
                'inv_stock.name as name',
                'inv_setting.slug as product_nature',
                'inv_stock.display_name as display_name',
                'inv_product.slug',
                'inv_product.vendor_id',
                'inv_product.category_id',
                'inv_product.unit_id',
                'inv_stock.quantity as quantity',
                'inv_stock.purchase_price',
                'inv_stock.sales_price',
                'inv_stock.barcode',
                'inv_particular.name as unit_name',
                'inv_product_gallery.feature_image',
            ])->with(['measurments' => function ($query) {
                $query->select([
                    'inv_product_unit_measurment.id']);
            }]);
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
                \DB::raw("CONCAT(inv_stock.name, ' [',IFNULL(inv_stock.quantity, 0),'] ', inv_particular.name) AS product_name"),
                \DB::raw("IFNULL(inv_stock.display_name, inv_stock.name) AS display_name"),
                'inv_stock.slug',
                'inv_stock.opening_quantity',
                'inv_stock.min_quantity',
                'inv_stock.reorder_quantity',
                'inv_stock.quantity as quantity',
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
        return self::where([['inv_stock.config_id',$domain['config_id']]])->where('product_id',$product_id)->where('inv_stock.is_delete',0)
            ->leftjoin('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('inv_particular as grade','grade.id','=','inv_stock.grade_id')
            ->leftjoin('inv_particular as color','color.id','=','inv_stock.color_id')
            ->leftjoin('inv_particular as brand','brand.id','=','inv_stock.brand_id')
            ->leftjoin('inv_particular as size','size.id','=','inv_stock.size_id')
            ->leftjoin('inv_particular as model','model.id','=','inv_stock.model_id')
            ->select([
                'inv_stock.id as stock_id',
                'inv_stock.is_master',
                'inv_stock.barcode',
                'inv_stock.product_id',
                'inv_product.name',
                'inv_stock.display_name',
                'inv_stock.sales_price as price',
                'inv_stock.purchase_price',
                'inv_stock.grade_id',
                'grade.name as grade_name',
                'inv_stock.color_id',
                'color.name as color_name',
                'inv_stock.brand_id',
                'brand.name as brand_name',
                'inv_stock.size_id',
                'size.name as size_name',
                'inv_stock.model_id',
                'model.name as model_name'
            ])->get()->toArray();
    }

    public static function insertStockItem($id, $data)
    {
        // Fetch the product by ID
        $product = ProductModel::find($id);

        // Ensure the product exists
        if (!$product) {
            throw new \InvalidArgumentException("Product with ID {$id} not found.");
        }

        // Check if a master stock item already exists for the product
        if (self::where('product_id', $id)->where('is_master', 1)->exists()) {
            return; // Exit early if the stock item already exists
        }

        // Retrieve related unit and SKU information
        $findUnit = ParticularModel::find($product->unit_id);

        // Prepare default values with data validation
        $minQuantity = (isset($data['min_quantity']) && $data['min_quantity'] > 1) ? $data['min_quantity'] : 0;
        $purchasePrice = isset($data['purchase_price']) ? (float)$data['purchase_price'] : 0.0;
        $salesPrice = isset($data['sales_price']) ? (float)$data['sales_price'] : 0.0;

        // Create the new stock item
        self::create([
            'product_id' => $id,
            'config_id' => $product->config_id,
            'name' => $product->name ?? null,
            'display_name' => $product->name ?? null,
            'uom' => $findUnit->name ?? null,
            'purchase_price' => $purchasePrice,
            'price' => $salesPrice,
            'sales_price' => $salesPrice,
            'sku' => $data['sku'] ?? null,
            'min_quantity' => $minQuantity,
            'is_master' => 1,
        ]);
    }

    public static function calculateTotalStockQuantity($productId, $configId) {
        $query = self::where('product_id', $productId)
            ->where('config_id', $configId)
            ->where('is_delete', 0)
            ->where('status', 1);

        return $query->sum('quantity');
    }

    public static function calculateStockItemAveragePrice($itemId, $configId, $currentItem)
    {
        // Fetch the existing stock item
        $query = self::find($itemId);

        // Handle missing stock item (set defaults)
        $existingQuantity = $query->quantity ?? 0;
        $existingPrice = $query->purchase_price ?? 0;

        // Ensure current item has valid values
        $currentQuantity = $currentItem->quantity ?? 0;
        $currentPrice = $currentItem->purchase_price ?? 0;

        // Calculate total prices
        $existingTotalPrice = $existingQuantity * $existingPrice;
        $currentTotalPrice = $currentQuantity * $currentPrice;
        $totalPrice = $existingTotalPrice + $currentTotalPrice;

        // Calculate total quantity
        $totalQuantity = $existingQuantity + $currentQuantity;
        if (($totalQuantity == 0) || ($totalQuantity < 0) ){
            $averagePrice = $query->purchase_price;
            return $averagePrice;
        }

        // Calculate and return the average price
        $averagePrice = $totalPrice / $totalQuantity;
        return $averagePrice;
    }

    public static function getStockItemMatrix($domain, $request, $dataFor = "FOR_LIST_SHOW")
    {
        // --- Pagination
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 25;
        $perPage = min($perPage, 100);
        $skip = $page * $perPage;

        // --- Base Query
        $query = self::with([
            'product.measurement.unit',
            'product.unit',
            'product.category',
            'product.setting',
            'product.images',
            'currentWarehouseStock' => function ($q) use ($domain) {
                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
                $q->with('warehouse:id,name');
            }
        ])
            ->where('config_id', $domain['config_id'])
            ->where('status', 1)
            ->where('is_delete', 0);

            /*->whereHas('currentWarehouseStock', function ($q) use ($domain) {
                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
            });*/

        // --- Warehouse Stock Filter
        if (!empty($request['warehouse_id'])) {
            $warehouseId = $request['warehouse_id'];

            $query->whereHas('currentWarehouseStock', function ($q) use ($domain, $warehouseId) {
                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
                $q->where('warehouse_id', $warehouseId);
            });
        }


        // --- Product Nature Filter
        if (!empty($request['product_nature'])) {
            $natureMap = [
                'stockable' => ['stockable'],
                'raw-material' => ['raw-materials'],
                'production' => ['pre-production', 'mid-production', 'post-production'],
            ];

            if (isset($natureMap[$request['product_nature']])) {
                $findProductNatureIds = SettingModel::whereIn('slug', $natureMap[$request['product_nature']])
                    ->where('config_id', $domain['config_id'])
                    ->pluck('id')
                    ->toArray();

                $query->whereHas('product', function ($q) use ($findProductNatureIds) {
                    $q->whereIn('product_type_id', $findProductNatureIds);
                });
            }
        }

        // ---  Filter expire product only
        if (isset($request['is_expire']) && $request['is_expire']) {
            $query->whereHas('product', function ($q) {
                $q->whereNotNull('expiry_duration');
            });
        }

        // --- Filter term only
        if (!empty($request['term'])) {
            $term = $request['term'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('barcode', 'LIKE', "%{$term}%")
                    ->orWhereHas('product', function ($p) use ($term) {
                        $p->where('name', 'LIKE', "%{$term}%");
                    });
            });
        }

        // --- Total Count (before pagination)
        $total = 0;
        if ($dataFor == "FOR_LIST_SHOW") {
            $total = $query->count();
        }

        // --- Paginated Results
        $stockItems = $query->orderByDesc('id');
            if ($dataFor == "FOR_LIST_SHOW") {
                $stockItems = $stockItems->skip($skip)->take($perPage);
            }
            $stockItems = $stockItems->get()
            ->map(function ($stock) use ($request) {
                $product = $stock->product;

                // product group by warehouse
                $warehouseQuantities = [];
                if (!empty($stock->currentWarehouseStock)) {
                    foreach ($stock->currentWarehouseStock as $s) {
                        if (!empty($s->warehouse)) {
                            $warehouseQuantities[$s->warehouse->id] = [
                                'name' => $s->warehouse->name,
                                'quantity' => $s->quantity,
                            ];
                        }
                    }
                }

                $data = [
                    'id' => $stock->id,
                    'name' => $stock->name ?? $stock->name,
                    'category_name' => $product->category->name ?? null,
                    'product_id' => $product->id ?? null,
                    'product_code' => $product->product_code ?? null,
                    'expiry_duration' => $product?->expiry_duration ? $product->expiry_duration . ' days' : null,
                    'unit_name' => $product->unit->name ?? null,
                    'quantity' => $stock->quantity,
                    'average_price' => round($stock->average_price, 2),
                    'sales_price' => round($stock->sales_price, 2),
                    'purchase_price' => round($stock->purchase_price, 2),
                    'barcode' => $stock->barcode,
                    'product_nature' => $product->setting->slug ?? null,
                    'feature_image' => optional($product?->images)->feature_image ?? null,
                    'warehouses' => $warehouseQuantities,
                ];

                if (!empty($request['warehouse_id'])) {
                    // Get the stock qty for the filtered warehouse
                    $filterStock = optional($stock->currentWarehouseStock)
                        ->firstWhere('warehouse_id', $request['warehouse_id']);

                    $data['filter_warehouses_stock'] = $filterStock->quantity ?? 0;
                }

                return $data;
            });

        // --- All Active Warehouses for this domain
        $warehouses = WarehouseModel::where('domain_id', $domain['domain_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // --- Response
        return [
            'data' => $stockItems,
            'warehouses' => $warehouses,
            'total' => $total,
            'page' => $page + 1,
            'perPage' => $perPage,
        ];
    }


}
