<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\ProductModel;

class StockItemModel extends Model
{

    protected $table = 'inv_stock';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function currentWarehouseStock(): HasMany
    {
        return $this->hasMany(CurrentStockModel::class, 'stock_item_id');
    }


    public static function getStockItemMatrix($domain, $request)
    {
        $page = isset($request['page']) && $request['page'] > 0 ? (int)$request['page'] : 1;
        $perPage = isset($request['recordsPerPage']) && $request['recordsPerPage'] !== ''
            ? (int)$request['recordsPerPage']
            : 25;

        $skip = ($page - 1) * $perPage;
        $perPage = min($perPage, 100);

        $stores = UserModel::getUserActiveWarehouse($domain['user_id']);
        $userWarehouseId = $stores->pluck('id')->toArray();

        $query = self::with([
            'product.category',
            'currentWarehouseStock' => function ($q) use ($domain, $userWarehouseId, $request) {
                $q->whereIn('warehouse_id', $userWarehouseId)
                    ->where('quantity', '!=', 0)
                    ->with('warehouse:id,name');

                if (!empty($request['warehouse_id'])) {
                    $q->where('warehouse_id', $request['warehouse_id']);
                }

                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
            }
        ])
            ->where('config_id', $domain['config_id'])
            ->where('is_delete', 0)
            ->whereHas('currentWarehouseStock', function ($q) use ($userWarehouseId, $request) {

                $q->whereIn('warehouse_id', $userWarehouseId)
                    ->where('quantity', '!=', 0);

                if (!empty($request['warehouse_id'])) {
                    $q->where('warehouse_id', $request['warehouse_id']);
                }
            });

        // Filter expiry
        if (!empty($request['is_expire'])) {
            $query->whereHas('product', function ($q) {
                $q->whereNotNull('expiry_duration');
            });
        }

        // Keyword search
        if (!empty($request['term'])) {
            $term = $request['term'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('barcode', 'LIKE', "%{$term}%")
                    ->orWhereHas('product', function ($p) use ($term) {
                        $p->where('name', 'LIKE', "%{$term}%");
                    });
            });
        }

        $sortField = $request['sortField'] ?? 'name';
        $sortOrder = $request['sortOrder'] ?? 'asc';

        // Whitelist allowed columns to prevent SQL injection
        $allowedSortFields = [
            'name',
            'barcode',
            'quantity',
            'category_name',
        ];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        if ($sortField == 'category_name') {
            $query->join('inv_products', 'inv_stock_items.product_id', '=', 'inv_products.id')
                ->join('inv_categories', 'inv_products.category_id', '=', 'inv_categories.id')
                ->orderBy('inv_categories.name', $sortOrder);
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        // Total
        $total = $query->count();

        // Fetch paginated data
        $stockItems = $query
            ->skip($skip)
            ->take($perPage)
            ->get()
            ->map(function ($stock) use ($request) {

                $product = $stock->product;

                $warehouseQuantities = [];
                foreach ($stock->currentWarehouseStock as $s) {
                    if (!empty($s->warehouse) && $s->quantity != 0) {
                        $warehouseQuantities[$s->warehouse->id] = [
                            'name' => $s->warehouse->name,
                            'quantity' => $s->quantity,
                        ];
                    }
                }

                $data = [
                    'id' => $stock->id,
                    'category_name' => $product->category->name ?? null,
                    'name' => $stock->display_name ?? $stock->name,
                    'product_id' => $product->id ?? null,
                    'product_code' => $product->product_code ?? null,
                    'expiry_duration' => $product->expiry_duration
                        ? $product->expiry_duration . ' days'
                        : null,
                    'quantity' => $stock->quantity,
                    'barcode' => $stock->barcode,
                    'warehouses' => $warehouseQuantities,
                ];

                if (!empty($request['warehouse_id'])) {
                    $filterStock = $stock->currentWarehouseStock
                        ->firstWhere('warehouse_id', $request['warehouse_id']);

                    $data['filter_warehouses_stock'] = $filterStock ? $filterStock->quantity : 0;
                }

                return $data;
            });

        // User warehouses
        $warehouses = UserWarehouseModel::join('cor_warehouses', 'cor_warehouses.id', '=', 'cor_user_warehouse.warehouse_id')
            ->where('cor_user_warehouse.user_id', $domain['user_id'])
            ->where('cor_user_warehouse.is_status', 1)
            ->where('cor_warehouses.status', 1)
            ->where('cor_warehouses.is_delete', 0)
            ->select('cor_warehouses.id', 'cor_warehouses.name')
            ->orderBy('cor_warehouses.name')
            ->get();

        return [
            'data' => $stockItems,
            'warehouses' => $warehouses,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ];
    }



}
