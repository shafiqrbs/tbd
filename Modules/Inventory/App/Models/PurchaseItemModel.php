<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseItemModel extends Model
{

    protected $table = 'inv_purchase_item';
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


    public function purchase()
    {
        return $this->belongsTo(PurchaseModel::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItemHistoryModel::class,'purchase_item_id');
    }
    public function inventoryItemHistory() : HasMany
    {
        return $this->hasMany(StockItemInventoryHistoryModel::class,'purchase_item_id');
    }

    public function stock() : BelongsTo
    {
        return $this->belongsTo(StockItemModel::class , 'stock_item_id');
    }

    public static function getRecords($request,$domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 0;
        $skip = $page * $perPage;

        $isApprovedCondition = $request['is_approved'] == 0 ? 'whereNull' : 'whereNotNull';
        $purchaseItems = self::where([
            ['inv_purchase_item.config_id',$domain['config_id']],
            ['inv_purchase_item.mode', 'opening']
        ])->$isApprovedCondition('approved_by_id');

        $purchaseItems = $purchaseItems->leftjoin('inv_stock','inv_stock.id','=','inv_purchase_item.stock_item_id')
            ->leftjoin('inv_product','inv_product.id','=','inv_stock.product_id')
            ->leftjoin('cor_warehouses','cor_warehouses.id','=','inv_purchase_item.warehouse_id')
            ->leftjoin('inv_category','inv_category.id','=','inv_product.category_id')
            ->leftjoin('inv_particular as unit','unit.id','=','inv_product.unit_id')
            ->leftjoin('inv_setting','inv_setting.id','=','inv_product.product_type_id')
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.stock_item_id',
                'inv_purchase_item.warehouse_id',
                'cor_warehouses.name as warehouse_name',
                'inv_purchase_item.opening_quantity',
                'inv_purchase_item.remaining_quantity',
                'inv_purchase_item.sales_price',
                'inv_purchase_item.purchase_price',
                'inv_purchase_item.sub_total',
                'inv_stock.name as product_name',
                'inv_category.name as category_name',
                'unit.name as unit_name',
                'inv_stock.barcode',
                'inv_setting.name as product_type',
                DB::raw('DATE_FORMAT(inv_purchase_item.updated_at, "%d-%M-%Y") as created'),
            ]);

        if (!empty($request['term'])) {
            $purchaseItems = $purchaseItems->whereAny(
                ['inv_product.name', 'inv_product.slug', 'inv_category.name', 'inv_stock.sales_price', 'inv_setting.name'],
                'LIKE',
                '%' . $request['term'] . '%'
            );
        }

        if (!empty($request['start_date'])) {
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = (!empty($request['end_date']))
                ? $request['end_date'].' 23:59:59'
                : $request['start_date'].' 23:59:59';

            $purchaseItems = $purchaseItems->whereBetween('inv_purchase_item.updated_at',[$start_date, $end_date]);
        }

        $total = $purchaseItems->count();
        $entities = $purchaseItems->skip($skip)
            ->take($perPage)
            ->orderBy('inv_purchase_item.updated_at','DESC')
            ->get();

        $data = ['count' => $total, 'entities' => $entities];
        return $data;

    }
    public static function getPurchaseItemsForDamage($stockItemId, $domain)
    {
        return self::query()
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
            ->join('inv_purchase', 'inv_purchase.id', '=', 'inv_purchase_item.purchase_id')
            ->where('inv_purchase_item.stock_item_id', $stockItemId)
            ->where('inv_purchase_item.config_id', $domain['config_id'])
            ->whereNull('inv_purchase_item.parent_sales_item_id')
            ->whereNotNull('inv_purchase.approved_by_id')
            ->where('inv_purchase_item.remaining_quantity', '>', 0)
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.id as purchase_item_id',
                'inv_purchase_item.purchase_id',
                'inv_purchase_item.warehouse_id',
                'inv_purchase_item.remaining_quantity',
                'inv_purchase_item.purchase_price',
                'inv_purchase_item.sales_price',
                'inv_stock.name',
                'inv_purchase_item.expired_date',
                'inv_purchase.invoice',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%M-%Y") as created_date')
            ])
            ->orderBy('inv_purchase.id', 'ASC')
            ->get()
            ->toArray();
    }

    public static function getProductGroupPrice($entity)
    {
        $purchaseItems = self::where('purchase_id', $entity)
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->join('inv_category', 'inv_category.id', '=', 'inv_product.category_id')
            ->join('inv_category as parent', 'parent.id', '=', 'inv_category.parent')
            ->selectRaw('SUM(inv_purchase_item.sub_total) as amount, parent.id as product_group_id')
            ->groupBy('parent.id')
            ->get();

        return $purchaseItems;
    }

    public static function updateSalesQuantity($purchaseItemId, $quantity)
    {
        if (!is_numeric($purchaseItemId) || $quantity == 0) {
            return false;
        }

        // Ensure null values are fixed before increment
        self::where('id', $purchaseItemId)
            ->whereNull('sales_quantity')
            ->update(['sales_quantity' => 0]);

        self::where('id', $purchaseItemId)
            ->increment('sales_quantity', $quantity);

        $remainingQuantity = self::getPurchaseItemRemainingQuantity($purchaseItemId);

        return self::where('id', $purchaseItemId)
            ->update(['remaining_quantity' => $remainingQuantity]);
    }

    public static function getPurchaseItemRemainingQuantity($id)
    {
        $item = self::find($id);

        if (!$item) {
            return 0;
        }

        // Calculate remaining quantity
        return ($item->quantity ?? 0)
            + ($item->sales_return_quantity ?? 0)
            + ($item->bonus_quantity ?? 0)
            - (
                ($item->warehouse_transfer_quantity ?? 0)
                + ($item->sales_quantity ?? 0)
                + ($item->purchase_return_quantity ?? 0)
                + ($item->damage_quantity ?? 0)
            );
    }

    public static function saleItemsWisePurchaseItemsAutoDeduct($stock_item_id, $sales_item_id, $quantity, $warehouse_id, $domain)
    {
        if (empty($stock_item_id) || empty($sales_item_id) || empty($quantity)) {
            return false;
        }

        $config = $domain['inv_config'];
        $salesConfig = ConfigSalesModel::where('config_id',$config)->first();

        $findStockItem = StockItemModel::find($stock_item_id);
        if (!$findStockItem) {
            return false;
        }

        $findSalesItem = SalesItemModel::find($sales_item_id);
        if (!$findSalesItem) {
            return false;
        }

        $getPurchaseItems = self::join('inv_purchase', 'inv_purchase.id', '=', 'inv_purchase_item.purchase_id')
            ->where('inv_purchase_item.config_id', $domain['config_id'])
            ->where('inv_purchase_item.stock_item_id', $stock_item_id)
            ->where('inv_purchase_item.warehouse_id', $warehouse_id)
            ->where('inv_purchase_item.remaining_quantity', '>', 0)
            ->whereNotNull('inv_purchase.approved_by_id')
            ->where(function ($query) {
                $query->whereNull('inv_purchase_item.expired_date')
                    ->orWhere('inv_purchase_item.expired_date', '>', now());
            })
            ->orderByRaw('inv_purchase_item.expired_date IS NULL')
            ->orderBy('inv_purchase_item.expired_date', 'asc')
            ->orderBy('inv_purchase_item.id', 'asc')
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.remaining_quantity',
                'inv_purchase_item.sales_quantity',
            ])
            ->get();

        $remainingToIssue = $quantity;

        foreach ($getPurchaseItems as $row) {

            if ($remainingToIssue <= 0) {
                break;
            }

            $purchaseItem = self::find($row->id);

            $availableQty = $purchaseItem->remaining_quantity;
            $issueQty = min($availableQty, $remainingToIssue);

            $purchaseItem->sales_quantity = ($purchaseItem->sales_quantity ?? 0) + $issueQty;
            $purchaseItem->remaining_quantity = $availableQty - $issueQty;
            $purchaseItem->save();
            $remainingToIssue -= $issueQty;
        }

        if ($remainingToIssue > 0 && $salesConfig->zero_stock !== 1) {
            throw ValidationException::withMessages([
                'stock' => ["{$findStockItem->name} stock low."]
            ]);
        }

        return true;
    }



}
