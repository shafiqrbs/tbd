<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\VendorModel;

class StockTransferModel extends Model
{

    protected $table = 'inv_stock_transfer';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id','from_warehouse_id','to_warehouse_id','created_by_id','notes','process','approved_by_id'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }


    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItemModel::class, 'stock_transfer_id');
    }

    public static function getRecords($request, $domain)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        $entities = self::where([['inv_stock_transfer.config_id', $domain['config_id']]])
            ->leftjoin('users as createdBy', 'createdBy.id', '=', 'inv_stock_transfer.created_by_id')
            ->leftjoin('users as approveBy', 'approveBy.id', '=', 'inv_stock_transfer.approved_by_id')
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
            ->with(['stockTransferItems' => function ($query) {
                $query->select([
                    'inv_stock_transfer_item.id',
                    'inv_stock_transfer_item.stock_transfer_id',
                    'inv_stock_transfer_item.stock_item_id',
                    'inv_stock_transfer_item.purchase_item_id',
                    'inv_stock_transfer_item.quantity',
                    'inv_stock_transfer_item.name',
                    'inv_stock_transfer_item.uom',
                ]);
//                    ->join('inv_stock', 'inv_stock.id', '=', 'inv_stock_transfer_item.stock_item_id')
//                    ->leftjoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_stock_transfer_item.warehouse_id');
            }]);

        if (isset($request['term']) && !empty($request['term'])) {
            $entities = $entities->whereAny(['inv_stock_transfer.process', 'tw.name', 'fw.name', 'createdBy.name'], 'LIKE', '%' . $request['term'] . '%');
        }

        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['start_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('inv_stock_transfer.created_at', [$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date = $request['end_date'] . ' 23:59:59';
            $entities = $entities->whereBetween('inv_stock_transfer.created_at', [$start_date, $end_date]);
        }

        $total = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('inv_stock_transfer.id', 'DESC')
            ->get();
        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }


    public static function insertStockTransferItems($stockTransfer, array $items , int $configId): bool
    {
        if (empty($items)) {
            return false;
        }

        $timestamp = now();

        $insertData = [];

        foreach ($items as $record) {
            $insertData[] = [
                'config_id'             => $configId,
                'stock_transfer_id'     => $stockTransfer->id,
                'stock_item_id'         => $record['stock_item_id'],
                'purchase_item_id'      => $record['purchase_item_id'],
                'quantity'              => $record['quantity'],
                'name'                  => $record['display_name'],
                'uom'                   => $record['unit_name'],
                'created_at'            => $timestamp,
                'updated_at'            => $timestamp,
            ];
        }

        if (!empty($insertData)) {
            StockTransferItemModel::insert($insertData);
        }

        return true;
    }
}
