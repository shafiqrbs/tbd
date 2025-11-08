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
        'grn',
        'status',
        'mode',
        'is_requisition',
        'remark',
        'process',
        'created_by_id',
        'received_by_id',
        'received_date',
    ];


    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $codes = self::salesEventListener($model);
            $model->invoice = $codes['generateId'];
            $model->code = $codes['code'];
            if (empty($model->uid)) {
                $model->uid = self::generateUniqueCode(12);
            }
        });
    }

    public static function salesEventListener($model): array
    {
        $patternCodeService = app(GeneratePatternCodeService::class);

        return $patternCodeService->invoiceNo([
            'config' => $model->config_id,
            'table'  => 'inv_purchase',
            'prefix' => 'GRN-',
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

    public static function syncPurchaseItems($purchase, $items, $warehouseId)
    {
        $timestamp = now();
        $existingItems = PurchaseItemModel::where('purchase_id', $purchase->id)
            ->pluck('id')
            ->toArray();

        $sentItemIds = collect($items)
            ->pluck('id')
            ->filter()
            ->toArray();

        // 1️⃣ Delete removed items
        $itemsToDelete = array_diff($existingItems, $sentItemIds);
        if (!empty($itemsToDelete)) {
            PurchaseItemModel::whereIn('id', $itemsToDelete)->delete();
        }

        // 2️⃣ Insert or update items
        foreach ($items as $item) {
            $data = [
                'purchase_id'    => $purchase->id,
                'config_id'      => $purchase->config_id,
                'warehouse_id'   => $item['warehouse_id'] ?? $warehouseId,
                'stock_item_id'  => $item['stock_item_id'],
                'quantity'       => $item['quantity'],
                'name'           => $item['name'] ?? null,
                'production_date'=> $item['production_date'] ?? null,
                'expired_date'   => $item['expired_date'] ?? null,
                'updated_at'     => $timestamp,
            ];

            if (isset($item['id']) && in_array($item['id'], $existingItems)) {
                // update
                PurchaseItemModel::where('id', $item['id'])->update($data);
            } else {
                // insert new
                $data['created_at'] = $timestamp;
                PurchaseItemModel::create($data);
            }
        }
    }

    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = self::where([['inv_purchase.config_id', $domain['config_id']]])
            ->leftjoin('users as cb', 'cb.id', '=', 'inv_purchase.created_by_id')
            ->leftjoin('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.vendor_id')
            ->select([
                'inv_purchase.id',
                'inv_purchase.uid',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%m-%Y") as created'),
                'inv_purchase.invoice as invoice',
                'inv_purchase.received_by_id',
                'inv_purchase.approved_by_id',
                'inv_purchase.warehouse_id',
                'inv_purchase.mode as mode',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                'cb.username as cbUser',
                'cb.name as cbName',
                'cb.id as cbId',
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


    public static function getShow($id, $domain)
    {
        $entity = self::where([['inv_purchase.config_id', $domain['config_id']], ['inv_purchase.id', $id]])
            ->leftjoin('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.vendor_id')
            ->leftjoin('users as cb', 'cb.id', '=', 'inv_purchase.created_by_id')
            ->leftjoin('users as ab', 'ab.id', '=', 'inv_purchase.approved_by_id')
            ->leftjoin('users as re', 're.id', '=', 'inv_purchase.received_by_id')
            ->select([
                'inv_purchase.id',
                'inv_purchase.invoice',
                'inv_purchase.process',
                'inv_purchase.grn',
                'inv_purchase.warehouse_id',
                'inv_purchase.created_by_id',
                'inv_purchase.approved_by_id',
                'inv_purchase.received_by_id',
                're.username as re_username',
                're.name as re_name',
                'ab.username as ab_username',
                'ab.name as ab_name',
                'inv_purchase.remark',
                'cb.username as cb_username',
                'cb.name as cb_name',
                'inv_purchase.vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%M-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_purchase.received_date, "%d-%M-%Y") as received_date'),


            ])->with(['purchaseItems' => function ($query) {
                $query->leftjoin('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id');
                $query->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id');
                $query->select([
                    'inv_purchase_item.id',
                    'inv_purchase_item.stock_item_id',
                    'purchase_id',
                    'inv_purchase_item.name',
                    'inv_purchase_item.quantity',
                    'inv_purchase_item.production_date',
                    'inv_purchase_item.expired_date',
                    DB::raw("CONCAT(cor_warehouses.name, ' (', cor_warehouses.location, ')') as warehouse_name"),
                    'cor_warehouses.name as warehouse',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id'
                ]);
            }])->first();

        return $entity;
    }



}
