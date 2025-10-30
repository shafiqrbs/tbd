<?php

namespace Modules\Medicine\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Medicine\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;

class PurchaseModel extends Model
{
    protected $table = 'inv_purchase';
    public $timestamps = true;

    protected $fillable = [
        'config_id',
        'vendor_id',
        'transaction_mode_id',
        'sub_total',
        'total',
        'payment',
        'discount',
        'discount_calculation',
        'warehouse_id',
        'discount_type',
        'approved_by_id',
        'status',
        'mode',
        'is_requisition',
        'process',
        'created_by_id',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $codes = self::salesEventListener($model);
            $model->invoice = $codes['generateId'];
            $model->code = $codes['code'];
        });
    }

    public static function salesEventListener($model): array
    {
        $patternCodeService = app(GeneratePatternCodeService::class);

        return $patternCodeService->invoiceNo([
            'config' => $model->config_id,
            'table'  => 'inv_purchase',
            'prefix' => 'INV-',
        ]);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItemModel::class, 'purchase_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(StockItemInventoryHistoryModel::class, 'purchase_id');
    }

    public static function insertPurchaseItems($purchase, $items, $warehouseId)
    {
        $timestamp = Carbon::now();

        $preparedItems = collect($items)->map(function ($item) use ($purchase, $warehouseId, $timestamp) {
            return [
                'purchase_id'    => $purchase->id,
                'config_id'      => $purchase->config_id,
                'warehouse_id'   => $item['warehouse_id'] ?? $warehouseId,
                'stock_item_id'  => $item['medicine_id'],
                'quantity'       => $item['quantity'],
                'name'           => $item['medicine_name'],
                'production_date'=> $item['production_date'],
                'expired_date'   => $item['expired_date'],
                'created_at'     => $timestamp,
                'updated_at'     => $timestamp,
                // other item fields you may need
            ];
        })->toArray();

        PurchaseItemModel::insert($preparedItems);
    }


    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = self::where([['inv_purchase.config_id', $domain['config_id']]])
            ->leftjoin('users as createdBy', 'createdBy.id', '=', 'inv_purchase.created_by_id')
            ->leftjoin('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.vendor_id')
            ->select([
                'inv_purchase.id',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%m-%Y") as created'),
                'inv_purchase.invoice as invoice',
                'inv_purchase.approved_by_id',
                'inv_purchase.warehouse_id',
                'inv_purchase.mode as mode',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'inv_purchase.process as process',
                'cor_vendors.address as customer_address',
            ])->with(['purchaseItems' => function ($query) {
                $query->select([
                    'inv_purchase_item.id',
                    'inv_purchase_item.purchase_id',
                    'inv_purchase_item.name as item_name',
                    'inv_purchase_item.quantity',
                    'cor_warehouses.name as warehouse_name',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id',
                ])
                    ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
                    ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['inv_purchase.invoice', 'cor_vendors.name'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['vendor_id']) && !empty($request['vendor_id'])) {
            $entities = $entities->where('inv_purchase.vendor_id', $request['vendor_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['start_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('inv_purchase.created_at', [$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['end_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('inv_purchase.created_at', [$start_date, $end_date]);
        }

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_purchase.id', 'DESC')
            ->get();
        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }


}
