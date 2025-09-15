<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Inventory\App\Models\PurchaseItemModel;
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
        'discount_type',
        'approved_by_id',
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

    public function scopeFilter($query, array $filters, array $domain)
    {
        $query->where('inv_purchase.config_id', $domain['config_id'])
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'inv_purchase.created_by_id')
            ->leftJoin('acc_transaction_mode', 'acc_transaction_mode.id', '=', 'inv_purchase.transaction_mode_id')
            ->leftJoin('cor_vendors', 'cor_vendors.id', '=', 'inv_purchase.vendor_id')
            ->select([
                'inv_purchase.id',
                DB::raw('DATE_FORMAT(inv_purchase.created_at, "%d-%m-%Y") as created'),
                'inv_purchase.invoice',
                'inv_purchase.sub_total',
                'inv_purchase.total',
                'inv_purchase.payment',
                'inv_purchase.discount',
                'inv_purchase.discount_calculation',
                'inv_purchase.discount_type',
                'inv_purchase.approved_by_id',
                'inv_purchase.mode',
                'inv_purchase.is_requisition',
                'cor_vendors.id as vendor_id',
                'cor_vendors.name as vendor_name',
                'cor_vendors.mobile as vendor_mobile',
                'createdBy.username as createdByUser',
                'createdBy.name as createdByName',
                'createdBy.id as createdById',
                'inv_purchase.process',
                'acc_transaction_mode.name as mode_name',
                'cor_vendors.address as customer_address',
                'cor_vendors.opening_balance as balance',
            ])
            ->with(['purchaseItems' => function ($query) {
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
                    ->leftJoin('cor_warehouses', 'cor_warehouses.id', '=', 'inv_purchase_item.warehouse_id')
                    ->leftJoin('inv_particular', 'inv_particular.id', '=', 'inv_product.unit_id');
            }]);

        if (!empty($filters['term'])) {
            $term = $filters['term'];
            $query->where(function ($q) use ($term) {
                $q->where('inv_purchase.invoice', 'LIKE', "%{$term}%")
                    ->orWhere('inv_purchase.sub_total', 'LIKE', "%{$term}%")
                    ->orWhere('cor_vendors.name', 'LIKE', "%{$term}%")
                    ->orWhere('cor_vendors.mobile', 'LIKE', "%{$term}%")
                    ->orWhere('createdBy.username', 'LIKE', "%{$term}%")
                    ->orWhere('acc_transaction_mode.name', 'LIKE', "%{$term}%");
            });
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('inv_purchase.vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['start_date']) && empty($filters['end_date'])) {
            $start = $filters['start_date'] . ' 00:00:00';
            $end   = $filters['start_date'] . ' 23:59:59';
            $query->whereBetween('inv_purchase.created_at', [$start, $end]);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = $filters['start_date'] . ' 00:00:00';
            $end   = $filters['end_date'] . ' 23:59:59';
            $query->whereBetween('inv_purchase.created_at', [$start, $end]);
        }

        return $query->orderByDesc('inv_purchase.id');
    }

    /**
     * Retrieve records with manual pagination (skip + take)
     */
    public static function getRecords(array $filters, array $domain) : array
    {
        $page    = !empty($filters['page']) && $filters['page'] > 0 ? ((int)$filters['page'] - 1) : 0;
        $perPage = !empty($filters['offset']) ? (int)$filters['offset'] : 50;
        $skip    = $page * $perPage;

        $query = self::query()->filter($filters, $domain);

        $total = (clone $query)->count();

        $data = $query->skip($skip)->take($perPage)->get();

        return [
            'total'    => $total,
            'data' => $data,
        ];
    }
}
