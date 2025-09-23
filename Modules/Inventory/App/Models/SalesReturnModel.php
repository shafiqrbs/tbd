<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\VendorModel;

class SalesReturnModel extends Model
{

    protected $table = 'inv_sales_return';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = self::salesEventListener($model)['generateId'];
            $model->code = self::salesEventListener($model)['code'];
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function salesEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_sales_return',
            'prefix' => 'INV-',
        ];
        return $patternCodeService->invoiceNo($params);
    }

    public function salesReturnItems()
    {
        return $this->hasMany(SalesReturnItemModel::class, 'sales_return_id');
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItemModel::class, 'purchase_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(StockItemInventoryHistoryModel::class, 'purchase_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendor_id');
    }

    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = self::where([['inv_sales_return.config_id', $domain['config_id']]])
            ->leftjoin('users as createdBy', 'createdBy.id', '=', 'inv_sales_return.created_by_id')
//            ->leftjoin('users as issueBy', 'issueBy.id', '=', 'inv_sales_return.issue_by_id')
            ->leftjoin('cor_customers', 'cor_customers.id', '=', 'inv_sales_return.customer_id')
            ->select([
                'inv_sales_return.id',
                DB::raw('DATE_FORMAT(inv_sales_return.created_at, "%d-%m-%Y") as created'),
//                DB::raw('DATE_FORMAT(inv_sales_return.invoice_date, "%d-%m-%Y") as invoice_date'),
                'inv_sales_return.invoice',
                'inv_sales_return.code',
                'inv_sales_return.quantity',
                'inv_sales_return.process',
                'inv_sales_return.sub_total',
//                'inv_sales_return.narration',
//                'inv_sales_return.vendor_id',
                'inv_sales_return.approved_by_id',
//                'cor_vendors.sub_domain_id',
//                'cor_vendors.name as vendor_name',
//                'cor_vendors.mobile as vendor_mobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
//                'issueBy.username as issueByUser',
//                'issueBy.name as issueByName',
//                'issueBy.id as issueById',
            ])->with(['salesReturnItems' => function ($query) {
                $query->select([
                    'inv_sales_return_item.id',
                    'inv_sales_return_item.sales_return_id',
                    'inv_sales_return_item.quantity',
                    'inv_sales_return_item.price',
                    'inv_sales_return_item.sub_total',
                    'inv_sales_return_item.stock_item_id',
                    'inv_sales_return_item.warehouse_id',
                    'inv_sales_return_item.item_name',
                    'inv_sales_return_item.uom',
//                    'inv_sales_return_item.purchase_item_id',
                    'inv_sales_return_item.sales_item_id',
                    'cor_warehouses.name as warehouse_name',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id',
                ])
                    ->join('inv_stock', 'inv_stock.id', '=', 'inv_sales_return_item.stock_item_id')
                    ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_sales_return_item.warehouse_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['inv_sales_return.invoice', 'inv_sales_return.sub_total', 'inv_sales_return.process', 'cor_vendors.name', 'cor_vendors.mobile', 'createdBy.username'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['vendor_id']) && !empty($request['vendor_id'])) {
            $entities = $entities->where('inv_sales_return.vendor_id', $request['vendor_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['start_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('inv_sales_return.created_at', [$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['end_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('inv_sales_return.created_at', [$start_date, $end_date]);
        }

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_sales_return.id', 'DESC')
            ->get();
        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }


    public static function insertPurchaseReturnItems($purchaseReturn, array $items): array
    {
        if (empty($items)) {
            return ['quantity' => 0, 'sub_total' => 0];
        }

        $timestamp = now();

        // Fetch purchase items with stock in one query
        $purchaseItems = PurchaseItemModel::with('stock')
            ->whereIn('id', array_column($items, 'id'))
            ->get()
            ->keyBy('id');

        $insertData = [];
        $totalQuantity = 0;
        $totalSubTotal = 0;

        foreach ($items as $record) {
            $purchaseItem = $purchaseItems[$record['id']] ?? null;
            if (!$purchaseItem) {
                continue;
            }

            $insertData[] = [
                'purchase_return_id' => $purchaseReturn->id,
                'purchase_item_id'   => $purchaseItem->id,
                'purchase_price'     => $record['purchase_price'],
                'stock_item_id'      => $purchaseItem->stock_item_id,
                'warehouse_id'       => $purchaseItem->warehouse_id,
                'item_name'          => $purchaseItem->stock?->name,
                'uom'                => $purchaseItem->stock?->uom,
                'quantity'           => $record['quantity'],
                'sub_total'          => $record['sub_total'],
                'created_at'         => $timestamp,
                'updated_at'         => $timestamp,
            ];

            $totalQuantity += (float) $record['quantity'];
            $totalSubTotal += (float) $record['sub_total'];
        }

        if (!empty($insertData)) {
            PurchaseReturnItemModel::insert($insertData);
        }

        return [
            'quantity'  => $totalQuantity,
            'sub_total' => $totalSubTotal,
        ];
    }



}
