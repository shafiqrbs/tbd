<?php

namespace Modules\Medicine\App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransferModel extends Model
{

    protected $table = 'inv_stock_transfer';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id', 'from_warehouse_id', 'to_warehouse_id', 'created_by_id', 'notes', 'process', 'approved_by_id'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new DateTime("now");
            $model->updated_at = $date;
        });
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

        // Base query
        $entities = self::where('inv_stock_transfer.config_id', $domain['config_id'])
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'inv_stock_transfer.created_by_id')
            ->leftJoin('users as approveBy', 'approveBy.id', '=', 'inv_stock_transfer.approved_by_id')
            ->join('cor_warehouses as fw', 'fw.id', '=', 'inv_stock_transfer.from_warehouse_id')
            ->join('cor_warehouses as tw', 'tw.id', '=', 'inv_stock_transfer.to_warehouse_id')
            ->select([
                'inv_stock_transfer.id',
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
        if (!empty($request['to_warehouse_id'])) {
            $entities->where('inv_stock_transfer.to_warehouse_id', $request['to_warehouse_id']);
        }

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
        $entities = self::where('inv_stock_transfer.id',$id)
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'inv_stock_transfer.created_by_id')
            ->leftJoin('users as approveBy', 'approveBy.id', '=', 'inv_stock_transfer.approved_by_id')
            ->join('cor_warehouses as fw', 'fw.id', '=', 'inv_stock_transfer.from_warehouse_id')
            ->join('cor_warehouses as tw', 'tw.id', '=', 'inv_stock_transfer.to_warehouse_id')
            ->select([
                'inv_stock_transfer.id',
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

        // Pagination + sorting
        $entities = $entities->first();

        return $entities;
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
