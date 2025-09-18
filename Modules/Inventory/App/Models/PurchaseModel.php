<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Entities\PurchaseItem;

class PurchaseModel extends Model
{

    protected $table = 'inv_purchase';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->invoice = self::quickRandom();
            $model->invoice = self::salesEventListener($model)['generateId'];
            $model->code = self::salesEventListener($model)['code'];
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $model->invoice = self::quickRandom();
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function quickRandom($length = 12)
    {
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function salesEventListener($model)
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'inv_purchase',
            'prefix' => 'INV-',
        ];
        return $patternCodeService->invoiceNo($params);
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

    public function insertPurchaseItems($purchase, $items, $warehouse = null)
    {
        $timestamp = Carbon::now();

        $items = array_map(function ($item) use ($warehouse) {
            $item['stock_item_id'] = $item['product_id'];
            $item['warehouse_id'] = $item['warehouse_id'] ?? $warehouse;
            $item['name'] = $item['name'];
            unset($item['product_id']);
            return $item;
        }, $items);

        foreach ($items as &$record) {
            $record['purchase_id'] = $purchase->id;
            $record['config_id'] = $purchase->config_id;
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
        }
        PurchaseItemModel::insert($items);
    }

    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = self::where([['inv_purchase.config_id', $domain['config_id']]])
            ->leftjoin('users as createdBy', 'createdBy.id', '=', 'inv_purchase.created_by_id')
            ->leftjoin('acc_transaction_mode', 'acc_transaction_mode.id', '=', 'inv_purchase.transaction_mode_id')
            ->leftjoin('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.vendor_id')
            ->select([
                'inv_purchase.id',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%m-%Y") as created'),
                'inv_purchase.invoice as invoice',
                'inv_purchase.sub_total as sub_total',
                'inv_purchase.total as total',
                'inv_purchase.payment as payment',
                'inv_purchase.discount as discount',
                'inv_purchase.discount_calculation as discount_calculation',
                'inv_purchase.discount_type as discount_type',
                'inv_purchase.approved_by_id',
                'inv_purchase.mode as mode',
                'inv_purchase.is_requisition as is_requisition',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'inv_purchase.process as process',
                'acc_transaction_mode.name as mode_name',
                'cor_vendors.address as customer_address',
                'cor_vendors.opening_balance as balance',
            ])->with(['purchaseItems' => function ($query) {
                $query->select([
                    'inv_purchase_item.id',
                    'inv_purchase_item.purchase_id',
                    'inv_stock.name as item_name',
                    'inv_purchase_item.quantity',
                    'inv_purchase_item.purchase_price',
                    'inv_purchase_item.sales_price',
                    'inv_purchase_item.sub_total',
                    'inv_purchase_item.bonus_quantity',
                    'inv_particular.name as unit_name',
                    'cor_warehouses.name as warehouse_name',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id',
                ])
                    ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
                    ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
                    ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id')
                    ->leftjoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['inv_purchase.invoice', 'inv_purchase.sub_total', 'cor_vendors.name', 'cor_vendors.mobile', 'createdBy.username', 'acc_transaction_mode.name'], 'LIKE', '%' . $request['term'] . '%');
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
        $entity = self::where([['inv_purchase.config_id', $domain['config_id'], 'inv_purchase.id', $id]])
            ->leftjoin('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.customer_id')
            ->leftjoin('users as createdBy', 'createdBy.id', '=', 'inv_purchase.created_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode', 'transactionMode.id', '=', 'inv_purchase.transaction_mode_id')
            ->leftjoin('uti_transaction_method as method', 'method.id', '=', 'acc_transaction_mode.method_id')
            ->select([
                'inv_purchase.id',
                '(DATE_FORMAT(inv_purchase.updated_at,\'%Y-%m\')) as created',
                'inv_purchase.invoice as invoice',
                'inv_purchase.sub_total as sub_total',
                'inv_purchase.total as total',
                'inv_purchase.payment as payment',
                'inv_purchase.discount as discount',
                'inv_purchase.discount_calculation as discount_calculation',
                'inv_purchase.discount_type as discount_type',
                'cor_vendors.id as customerId',
                'cor_vendors.name as customerName',
                'cor_vendors.mobile as customerMobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'transactionMode.name as modeName',
                'transactionMode.name as modeName',

            ])->with(['purchaseItems' => function ($query) {
                $query->select([
                    'id',
                    'purchase_id',
                    'unit_id'
                ])->with([
                    'unit'
                ]);
            }])->first();

        return $entity;
    }


    public static function getEditData($id, $domain)
    {
        $entity = self::where([['inv_purchase.config_id', $domain['config_id']], ['inv_purchase.id', $id]])
            ->select([
                'inv_purchase.id',
                'inv_purchase.vendor_id',
                'inv_purchase.transaction_mode_id',
                'inv_purchase.payment as payment',
                'inv_purchase.discount as discount',
                'inv_purchase.discount_calculation as discount_calculation',
                'inv_purchase.discount_type as discount_type',
                'inv_purchase.process',
                'inv_purchase.warehouse_id',
                'inv_purchase.remark',
            ])->with(['purchaseItems' => function ($query) {
                $query->leftjoin('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id');
                $query->leftjoin('inv_product', 'inv_product.id', '=', 'inv_stock.product_id');
                $query->leftjoin('inv_particular as unit', 'unit.id', '=', 'inv_product.unit_id');
                $query->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id');
                $query->select([
                    'inv_purchase_item.id',
                    'inv_purchase_item.stock_item_id as product_id',
                    'purchase_id',
                    'inv_stock.name as display_name',
                    'unit.name as unit_name',
                    'inv_purchase_item.quantity',
                    'inv_purchase_item.bonus_quantity',
                    'inv_purchase_item.purchase_price',
                    'inv_purchase_item.sub_total',
                    'inv_product.unit_id as unit_id',
                    DB::raw("CONCAT(cor_warehouses.name, ' (', cor_warehouses.location, ')') as warehouse_name"),
                    'cor_warehouses.name as warehouse',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id'
                ]);
            }])->first();

        return $entity;
    }

    /**
     * Fetch vendor-wise purchases with eager loaded items.
     * $filters array can contain 'vendor_id' or other filters.
     */
    public static function getVendorWisePurchaseItem(array $filters = [], array $domain = []): array
    {
        $query = self::query()
            ->whereNotNull('approved_by_id')
            ->join('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.vendor_id')
            ->select([
                'inv_purchase.id',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%m-%Y") as created'),
                'inv_purchase.invoice',
                'inv_purchase.sub_total',
                'inv_purchase.total',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
            ]);


        if (!empty($domain['config_id'])) {
            $query->where('inv_purchase.config_id', $domain['config_id']);
        }


        if (!empty($filters['vendor_id'])) {
            $query->where('inv_purchase.vendor_id', $filters['vendor_id']);
        }


        $purchases = $query->get();

        // get purchase items for all purchase ids in one query to avoid N+1
        $purchaseIds = $purchases->pluck('id')->toArray();


        $items = PurchaseItemModel::whereIn('purchase_id', $purchaseIds)
            ->join('inv_stock', 'inv_stock.id', '=', 'inv_purchase_item.stock_item_id')
            ->join('inv_product', 'inv_product.id', '=', 'inv_stock.product_id')
            ->leftJoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id')
            ->leftJoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id')
            ->select([
                'inv_purchase_item.id',
                'inv_purchase_item.purchase_id',
                'inv_stock.name as item_name',
                'inv_purchase_item.quantity',
                'inv_purchase_item.purchase_price',
                'inv_purchase_item.sales_price',
                'inv_purchase_item.sub_total',
                'inv_purchase_item.bonus_quantity',
                'inv_particular.name as unit_name',
                'cor_warehouses.name as warehouse_name',
                'cor_warehouses.location as warehouse_location',
                'cor_warehouses.id as warehouse_id',
            ])
            ->get()
            ->groupBy('purchase_id');


        // build structured response: vendor -> purchases -> items
        $vendors = [];


        foreach ($purchases as $p) {
            $vendorId = $p->vendor_id;
            if (!isset($vendors[$vendorId])) {
                $vendors[$vendorId] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $p->vendor_name,
                    'vendor_mobile' => $p->vendor_mobile,
                    'purchases' => [],
                ];
            }


            $vendors[$vendorId]['purchases'][] = [
                'id' => $p->id,
                'invoice' => $p->invoice,
                'created' => $p->created,
                'sub_total' => $p->sub_total,
                'total' => $p->total,
                'items' => isset($items[$p->id]) ? $items[$p->id]->map(function ($it) {
                    return [
                        'id' => $it->id,
                        'item_name' => $it->item_name,
                        'quantity' => $it->quantity,
                        'purchase_price' => $it->purchase_price,
                        'sales_price' => $it->sales_price,
                        'sub_total' => $it->sub_total,
                        'bonus_quantity' => $it->bonus_quantity,
                        'unit_name' => $it->unit_name,
                        'warehouse_name' => $it->warehouse_name,
                        'warehouse_location' => $it->warehouse_location,
                        'warehouse_id' => $it->warehouse_id,
                    ];
                })->values()->toArray() : [],
            ];
        }


        // return numerically indexed array of vendors
        return array_values($vendors);

    }



}
