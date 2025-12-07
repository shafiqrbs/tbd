<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemInventoryHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Models\ProductionStockHistory;

class MedicineStockItemHistoryModel extends Model
{

    protected $table = 'inv_stock_item_history';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'stock_item_id',
        'config_id',
        'quantity',
        'opening_quantity',
        'closing_quantity',
        'closing_balance',
        'warehouse_id',
        'mode',
        'process',
        'sales_price',
        'purchase_price',
        'opening_balance',
        'created_by',
        'item_name',
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
        });

        self::updating(function ($model) {
        });
    }
    public static function getStockItemHistory($params, $domain)
    {
        // default pagination setup (unchanged)
        $page = isset($params['page']) && $params['page'] > 0 ? ($params['page'] - 1) : 0;
        $perPage = isset($params['offset']) && $params['offset'] != '' ? (int)($params['offset']) : 0;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;

        // prepare date range
        $rawStart = $params['start_date'] ?? null;
        $rawEnd   = $params['end_date'] ?? null;

        $startDate = $rawStart ? Carbon::parse($rawStart)->startOfDay() : null;

        $endDate = $rawEnd
            ? Carbon::parse($rawEnd)->endOfDay()
            : ($rawStart ? Carbon::parse($rawStart)->endOfDay() : null);

        // main query builder
        $stockItems = self::join('inv_stock','inv_stock.id','=','inv_stock_item_history.stock_item_id')
            ->join('cor_warehouses','inv_stock_item_history.warehouse_id','=','cor_warehouses.id')
            ->where([
                ['inv_stock_item_history.config_id', $domain['config_id']],
                ['inv_stock_item_history.stock_item_id', $params['stock_item_id']],
                ['inv_stock_item_history.warehouse_id', $params['warehouse_id']],
            ])
            ->when(!empty($startDate) && !empty($endDate), function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('inv_stock_item_history.created_at', [$startDate, $endDate]);
            })
            ->select([
                'inv_stock_item_history.id',
                'cor_warehouses.name as warehouse_name',
                DB::raw('COALESCE(inv_stock_item_history.item_name, inv_stock.name) as item_name'),
                'inv_stock.uom',
                'inv_stock_item_history.sales_price',
                'inv_stock_item_history.purchase_price',
                'inv_stock_item_history.quantity',
                'inv_stock_item_history.opening_quantity',
                'inv_stock_item_history.opening_balance',
                'inv_stock_item_history.closing_quantity',
                'inv_stock_item_history.closing_balance',
                'inv_stock_item_history.mode',
                DB::raw('DATE_FORMAT(CONVERT_TZ(inv_stock_item_history.created_at, "+00:00", "+06:00"), "%d %b %Y, %h:%i %p") as created_date'),
            ]);

        // pagination block (same logic, just safer with cloned count)
        if ($params['page'] && $params['offset']) {
            $total = (clone $stockItems)->count();

            $items = $stockItems->skip($skip)
                ->take($perPage)
                ->orderBy('inv_stock_item_history.id', 'DESC')
                ->get();

            return [
                'count' => $total,
                'items' => $items,
            ];
        }

        // ✅ non-paginated case
        return $stockItems
            ->orderBy('inv_stock_item_history.id', 'DESC')
            ->get();
    }

}
