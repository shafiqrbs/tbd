<?php

namespace Modules\Medicine\App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\UserModel;

class StockTransferModel extends Model
{

    protected $table = 'inv_stock_transfer';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id', 'from_warehouse_id', 'to_warehouse_id', 'created_by_id', 'notes', 'process', 'approved_by_id','request_quantity','approved_date'
    ];

    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->created_at = $date;
            $codes = self::invoiceEventListener($model);
            $model->invoice = $codes['generateId'];
            $model->code = $codes['code'];
            if (empty($model->uid)) {
                $model->uid = self::generateUniqueCode(12);
            }
        });

        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function invoiceEventListener($model): array
    {
        $patternCodeService = app(GeneratePatternCodeService::class);
        return $patternCodeService->invoiceNo([
            'config' => $model->config_id,
            'table'  => 'inv_stock_transfer',
            'prefix' => 'IND-',
        ]);
    }



    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItemModel::class, 'stock_transfer_id');
    }

    public static function getRecords($request, $domain)
    {
        // Pagination setup
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = !empty($request['offset']) ? (int)$request['offset'] : 50;
        $skip = $page * $perPage;

        $stores = UserModel::getUserActiveWarehouse($domain['user_id']);
        $userWarehouseId = $stores->pluck('id')->toArray();

        // Base query
        $entities = self::where('inv_stock_transfer.config_id', $domain['config_id'])
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'inv_stock_transfer.created_by_id')
            ->leftJoin('users as approveBy', 'approveBy.id', '=', 'inv_stock_transfer.approved_by_id')
            ->join('cor_warehouses as fw', 'fw.id', '=', 'inv_stock_transfer.from_warehouse_id')
            ->join('cor_warehouses as tw', 'tw.id', '=', 'inv_stock_transfer.to_warehouse_id')
            ->select([
                'inv_stock_transfer.id',
                'inv_stock_transfer.uid',
                'inv_stock_transfer.invoice',
                'inv_stock_transfer.config_id',
                'inv_stock_transfer.from_warehouse_id',
                'fw.name as from_warehouse',
                'inv_stock_transfer.to_warehouse_id',
                'tw.name as to_warehouse',
                'inv_stock_transfer.created_by_id',
                'createdBy.name as created_by',
                'inv_stock_transfer.approved_by_id',
                'approveBy.name as approved_by',
                'inv_stock_transfer.notes',
                'inv_stock_transfer.process',
                DB::raw('DATE_FORMAT(inv_stock_transfer.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_stock_transfer.updated_at, "%d-%m-%Y") as invoice_date'),
            ])
            ->with([
                'stockTransferItems' => function ($query) {
                    $query->select([
                        'inv_stock_transfer_item.id',
                        'inv_stock_transfer_item.stock_transfer_id',
                        'inv_stock_transfer_item.stock_item_id',
                        'inv_stock_transfer_item.purchase_item_id',
                        'inv_stock_transfer_item.quantity',
                        'inv_stock_transfer_item.name',
                        'inv_stock_transfer_item.uom',
                    ]);
                }
            ]);

        // 🔍 Search filter
        if (!empty($request['term'])) {
            $term = '%' . $request['term'] . '%';
            $entities->where(function ($query) use ($term) {
                $query->where('inv_stock_transfer.process', 'LIKE', $term)
                    ->orWhere('tw.name', 'LIKE', $term)
                    ->orWhere('fw.name', 'LIKE', $term)
                    ->orWhere('createdBy.name', 'LIKE', $term);
            });
        }

        // 🏭 From warehouse filter
        if (!empty($request['from_warehouse_id'])) {
            $entities->where('inv_stock_transfer.from_warehouse_id', $request['from_warehouse_id']);
        }

        // 🏭 To warehouse filter
        $entities->whereIn('inv_stock_transfer.to_warehouse_id', $userWarehouseId);

        // 📅 Date range filter
        if (!empty($request['start_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = !empty($request['end_date'])
                ? $request['end_date'] . ' 23:59:59'
                : $request['start_date'] . ' 23:59:59';

            $entities->whereBetween('inv_stock_transfer.created_at', [$start_date, $end_date]);
        }

        // Get total count before pagination
        $total = $entities->count();

        // Pagination + sorting
        $entities = $entities->orderBy('inv_stock_transfer.id', 'DESC')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return [
            'count' => $total,
            'entities' => $entities,
        ];
    }
    public static function getRecordsForCentral($request, $domain)
    {
        // Pagination setup
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = !empty($request['offset']) ? (int)$request['offset'] : 50;
        $skip = $page * $perPage;

        // Base query
        $entities = self::where('inv_stock_transfer.config_id', $domain['config_id'])->whereIn('inv_stock_transfer.process',['Approved','Received'])
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'inv_stock_transfer.created_by_id')
            ->leftJoin('users as approveBy', 'approveBy.id', '=', 'inv_stock_transfer.approved_by_id')
            ->join('cor_warehouses as fw', 'fw.id', '=', 'inv_stock_transfer.from_warehouse_id')
            ->join('cor_warehouses as tw', 'tw.id', '=', 'inv_stock_transfer.to_warehouse_id')
            ->select([
                'inv_stock_transfer.id',
                'inv_stock_transfer.uid',
                'inv_stock_transfer.invoice',
                'inv_stock_transfer.config_id',
                'inv_stock_transfer.from_warehouse_id',
                'fw.name as from_warehouse',
                'inv_stock_transfer.to_warehouse_id',
                'tw.name as to_warehouse',
                'inv_stock_transfer.created_by_id',
                'createdBy.name as created_by',
                'inv_stock_transfer.approved_by_id',
                'approveBy.name as approved_by',
                'inv_stock_transfer.notes',
                'inv_stock_transfer.process',
                DB::raw('DATE_FORMAT(inv_stock_transfer.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_stock_transfer.updated_at, "%d-%m-%Y") as invoice_date'),
            ])
            ->with([
                'stockTransferItems' => function ($query) {
                    $query->select([
                        'inv_stock_transfer_item.id',
                        'inv_stock_transfer_item.stock_transfer_id',
                        'inv_stock_transfer_item.stock_item_id',
                        'inv_stock_transfer_item.purchase_item_id',
                        'inv_stock_transfer_item.quantity',
                        'inv_stock_transfer_item.name',
                        'inv_stock_transfer_item.uom',
                    ]);
                }
            ]);

        // 🔍 Search filter
        if (!empty($request['term'])) {
            $term = '%' . $request['term'] . '%';
            $entities->where(function ($query) use ($term) {
                $query->where('inv_stock_transfer.process', 'LIKE', $term)
                    ->orWhere('tw.name', 'LIKE', $term)
                    ->orWhere('fw.name', 'LIKE', $term)
                    ->orWhere('createdBy.name', 'LIKE', $term);
            });
        }

        // 🏭 From warehouse filter
        if (!empty($request['from_warehouse_id'])) {
            $entities->where('inv_stock_transfer.from_warehouse_id', $request['from_warehouse_id']);
        }

        // 🏭 To warehouse filter
        $entities->where('inv_stock_transfer.from_warehouse_id', $domain['warehouse_id']);

        // 📅 Date range filter
        if (!empty($request['start_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = !empty($request['end_date'])
                ? $request['end_date'] . ' 23:59:59'
                : $request['start_date'] . ' 23:59:59';

            $entities->whereBetween('inv_stock_transfer.created_at', [$start_date, $end_date]);
        }

        // Get total count before pagination
        $total = $entities->count();

        // Pagination + sorting
        $entities = $entities->orderBy('inv_stock_transfer.id', 'DESC')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return [
            'count' => $total,
            'entities' => $entities,
        ];
    }
    public static function getDetails($id)
    {
        $entity = self::where('inv_stock_transfer.uid', $id)
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'inv_stock_transfer.created_by_id')
            ->leftJoin('users as approveBy', 'approveBy.id', '=', 'inv_stock_transfer.approved_by_id')
            ->join('cor_warehouses as fw', 'fw.id', '=', 'inv_stock_transfer.from_warehouse_id')
            ->join('cor_warehouses as tw', 'tw.id', '=', 'inv_stock_transfer.to_warehouse_id')
            ->select([
                'inv_stock_transfer.id',
                'inv_stock_transfer.uid',
                'inv_stock_transfer.config_id',
                'inv_stock_transfer.from_warehouse_id',
                'fw.name as from_warehouse',
                'inv_stock_transfer.to_warehouse_id',
                'tw.name as to_warehouse',
                'inv_stock_transfer.created_by_id',
                'createdBy.name as created_by',
                'inv_stock_transfer.approved_by_id',
                'approveBy.name as approved_by',
                'inv_stock_transfer.notes',
                'inv_stock_transfer.process',
                DB::raw('DATE_FORMAT(inv_stock_transfer.created_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_stock_transfer.updated_at, "%d-%m-%Y") as invoice_date'),
            ])
            ->with([
                'stockTransferItems' => function ($query) {
                    $query->select([
                        'inv_stock_transfer_item.id',
                        'inv_stock_transfer_item.stock_transfer_id',
                        'inv_stock_transfer_item.stock_item_id',
                        'inv_stock_transfer_item.purchase_item_id',
                        'inv_stock_transfer_item.stock_quantity',
                        'inv_stock_transfer_item.request_quantity',
                        'inv_stock_transfer_item.quantity',
                        'inv_stock_transfer_item.name',
                        'inv_stock_transfer_item.uom',
                    ])->with([
                        'purchaseItems' => function ($subQuery) {
                            $subQuery->whereNotNull('expired_date')
                                ->where('expired_date', '>', now()) // only not expired
                                ->whereRaw('quantity > COALESCE(sales_quantity,0)')
                                ->select([
                                    'id',
                                    'stock_item_id',
                                    'warehouse_id',
                                    'quantity',
                                    'sales_quantity',
                                    'expired_date'
                                ]);
                        }
                    ]);
                }
            ])
            ->first();

        // 🔹 Map and rename to stock_transfer_items
        if ($entity) {
            $entity->stock_transfer_items = $entity->stockTransferItems->map(function ($item) {
                $item->purchaseItems = $item->purchaseItems->map(function ($p) {
                    $salesQty = $p->sales_quantity ?? 0;
                    return [
                        'id'                => $p->id,
                        'warehouse_id'      => $p->warehouse_id,
                        'purchase_quantity' => $p->quantity,
                        'sales_quantity'    => $salesQty,
                        'remain_quantity'   => $p->quantity - $salesQty,
                        'expired_date'      => $p->expired_date
                            ? Carbon::parse($p->expired_date)->format('d-M-Y')
                            : null,
                    ];
                });

                return [
                    'id'              => $item->id,
                    'stock_item_id'   => $item->stock_item_id,
                    'name'            => $item->name,
                    'uom'             => $item->uom,
                    'quantity'  => $item->quantity,
                    'stock_quantity'  => $item->stock_quantity,
                    'request_quantity'  => $item->request_quantity,
                    'purchase_item_id'  => $item->purchase_item_id,
                    'purchase_items'  => $item->purchaseItems,
                ];
            });

            // 🔸 Remove the original relation to avoid duplicate output
            unset($entity->stockTransferItems);
        }

        return $entity;
    }




    public static function insertStockTransferItems($stockTransfer, array $items, int $configId): bool
    {
        if (empty($items)) {
            return false;
        }

        $timestamp = now();

        $insertData = [];

        foreach ($items as $record) {
            $insertData[] = [
                'config_id' => $configId,
                'stock_transfer_id' => $stockTransfer->id,
                'stock_item_id' => $record['stock_item_id'],
                'purchase_item_id' => $record['purchase_item_id'] ?? null,
                'quantity' => $record['quantity'],
                'request_quantity' => $record['request_quantity'] ?? $record['quantity'],
                'name' => $record['name'] ?? null,
                'uom' => $record['unit_name'] ?? null,
                'stock_quantity' => $record['stock_quantity'] ?? null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (!empty($insertData)) {
            StockTransferItemModel::insert($insertData);
        }

        return true;
    }
}
