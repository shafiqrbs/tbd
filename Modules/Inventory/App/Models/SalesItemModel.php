<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Inventory\App\Entities\Product;
use Modules\Utility\App\Models\ProductUnitModel;
use Ramsey\Collection\Collection;

class SalesItemModel extends Model
{
    use HasFactory;

    protected $table = 'inv_sales_item';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'sale_id',
        'config_id',
        'name',
        'uom',
        'unit_id',
        'stock_item_id',
        'warehouse_id',
        'bonus_quantity',
        'quantity',
        'percent',
        'price',
        'sales_price',
        'purchase_price',
        'sub_total',
        'discount_price',
        'height',
        'width',
        'total_quantity',
        'sub_quantity',
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

     public function inv_stock()
    {
        return $this->belongsTo(ProductModel::class,'stock_item_id');
    }
    public function stock() : BelongsTo
    {
        return $this->belongsTo(StockItemModel::class , 'stock_item_id');
    }

    /**
     * Insert multiple sales items.
     *
     * @param SalesModel $sales Sales instance.
     * @param array $items Incoming items to insert.
     * @return bool
     */
    public static function insertSalesItems($sales, array $items): bool
    {
        $timestamp = Carbon::now();


        $formattedItems = array_map(function ($item) use ($sales, $timestamp) {
            return [
                'sale_id'       => $sales->id,
                'config_id'      => $sales->config_id,
                'stock_item_id'  => $item['product_id'] ?? null,
                'unit_id'  => $item['unit_id'] ?? null,
                'name'  => $item['item_name'] ?? null,
                'price'  => $item['price'] ?? null,
                'uom'  => $item['uom'] ?? null,
                'warehouse_id'   => $item['warehouse_id'] ?? null,
                'bonus_quantity' => $item['bonus_quantity'] ?? 0,
                'return_quantity' => $item['return_quantity'] ?? 0,
                'damage_quantity' => $item['damage_quantity'] ?? 0,
                'spoil_quantity' => $item['spoil_quantity'] ?? 0,
                'height' => $item['height'] ?? 0,
                'width' => $item['width'] ?? 0,
                'total_quantity' => $item['total_quantity'] ?? 0,
                'sub_quantity' => $item['sub_quantity'] ?? 0,
                'quantity'       => $item['quantity'] ?? 0,
                'percent'        => $item['percent'] ?? 0,
                'sales_price'    => $item['sales_price'] ?? 0,
                'purchase_price' => $item['purchase_price'] ?? 0,
                'sub_total'      => ($item['quantity'] ?? 0) * ($item['sales_price'] ?? 0),
                'discount_price' => self::calculateDiscountPrice($item['percent'] ?? 0, $item['sales_price'] ?? 0),
                'created_at'     => $timestamp,
                'updated_at'     => $timestamp,
            ];
        }, $items);

        return self::insert($formattedItems);
    }

    /**
     * Insert multiple sales items.
     *
     * @param SalesModel $sales Sales instance.
     * @param array $items Incoming items to insert.
     * @return bool
     */
    public static function insertSalesItemsForPos($sales, array $items): bool
    {
        $timestamp = Carbon::now();


        $formattedItems = array_map(function ($item) use ($sales, $timestamp) {
            $findStockItem = StockItemModel::find($item['stock_item_id']);
            $product = ProductModel::find($findStockItem->product_id);
//            dump($findStockItem);
            return [
                'sale_id'       => $sales->id,
                'config_id'      => $sales->config_id,
                'stock_item_id'  => $item['stock_item_id'] ?? null,
                'unit_id'  => $product->unit_id ?? null,
                'name'  => $findStockItem->display_name ?? null,
                'price'  => $item['sales_price'] ?? null,
                'uom'  => $findStockItem->uom ?? null,
                'warehouse_id'   => $findStockItem->warehouse_id ?? null,
                'bonus_quantity' => $item['bonus_quantity'] ?? 0,
                'return_quantity' => $item['return_quantity'] ?? 0,
                'damage_quantity' => $item['damage_quantity'] ?? 0,
                'spoil_quantity' => $item['spoil_quantity'] ?? 0,
                'height' => $item['height'] ?? 0,
                'width' => $item['width'] ?? 0,
                'total_quantity' => $item['total_quantity'] ?? 0,
                'sub_quantity' => $item['sub_quantity'] ?? 0,
                'quantity'       => $item['quantity'] ?? 0,
                'percent'        => $item['percent'] ?? 0,
                'sales_price'    => $item['sales_price'] ?? 0,
                'purchase_price' => $item['purchase_price'] ?? 0,
                'sub_total'      => ($item['quantity'] ?? 0) * ($item['sales_price'] ?? 0),
                'discount_price' => self::calculateDiscountPrice($item['percent'] ?? 0, $item['sales_price'] ?? 0),
                'created_at'     => $timestamp,
                'updated_at'     => $timestamp,
            ];
        }, $items);
        return self::insert($formattedItems);
    }

    /**
     * Calculate the discounted price based on percentage.
     *
     * @param float $percent Discount percentage.
     * @param float $salesPrice Original price.
     * @return float
     */
    private static function calculateDiscountPrice($percent, $salesPrice)
    {
        return $salesPrice - ($salesPrice * ($percent / 100));
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):0;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $products = self::where([['inv_product.config_id',$domain['config_id']]])
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_brand','inv_brand.id','=','inv_product.brand_id')
            ->select(['*']);

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




}
