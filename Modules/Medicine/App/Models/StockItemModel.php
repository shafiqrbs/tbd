<?php

namespace Modules\Medicine\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Core\App\Models\WarehouseModel;
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

    public function currentWarehouseStock() :HasMany
    {
        return $this->hasMany(CurrentStockModel::class, 'stock_item_id');
    }

    public static function getStockItemMatrix($domain, $request)
    {
        // --- Pagination
        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 0;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)$request['offset'] : 25;
        $perPage = min($perPage, 100);
        $skip = $page * $perPage;

        // --- Base Query
        $query = self::with([
            'product.category',
            'currentWarehouseStock' => function ($q) use ($domain) {
                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
                $q->with('warehouse:id,name');
            }
        ])
            ->where('config_id', $domain['config_id'])
            #->where('status', 1)
            ->where('is_delete', 0);


        // --- Warehouse Stock Filter
        if (!empty($request['warehouse_id'])) {
            $warehouseId = $request['warehouse_id'];

            $query->whereHas('currentWarehouseStock', function ($q) use ($domain, $warehouseId) {
                if (!empty($domain['config_id'])) {
                    $q->where('config_id', $domain['config_id']);
                }
                $q->where('warehouse_id', $warehouseId);
            });
        }

        // ---  Filter expire product only
        if (isset($request['is_expire']) && $request['is_expire']) {
            $query->whereHas('product', function ($q) {
                $q->whereNotNull('expiry_duration');
            });
        }

        // --- Filter term only
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

        // --- Total Count (before pagination)
        $total = $query->count();

        // --- Paginated Results
        $stockItems = $query
            ->orderByDesc('id')
            ->skip($skip)
            ->take($perPage)
            ->get()
            ->map(function ($stock) use ($request) {
                $product = $stock->product;

                // product group by warehouse
                $warehouseQuantities = [];
                if (!empty($stock->currentWarehouseStock)) {
                    foreach ($stock->currentWarehouseStock as $s) {
                        if (!empty($s->warehouse)) {
                            $warehouseQuantities[$s->warehouse->id] = [
                                'name' => $s->warehouse->name,
                                'quantity' => $s->quantity,
                            ];
                        }
                    }
                }

                $data = [
                    'id' => $stock->id,
                    'category_name' => $product->category->name ?? null,
                    'name' => $stock->display_name ?? $stock->name,
                    'product_id' => $product->id ?? null,
                    'product_code' => $product->product_code ?? null,
                    'expiry_duration' => $product->expiry_duration ? $product->expiry_duration . ' days' : null,
                    'quantity' => $stock->quantity,
                    'barcode' => $stock->barcode,
                    'warehouses' => $warehouseQuantities,
                ];

                if (!empty($request['warehouse_id'])) {
                    // Get the stock qty for the filtered warehouse
                    $filterStock = optional($stock->currentWarehouseStock)
                        ->firstWhere('warehouse_id', $request['warehouse_id']);

                    $data['filter_warehouses_stock'] = $filterStock->quantity ?? 0;
                }

                return $data;
            });

        // --- All Active Warehouses for this domain

        $warehouses = UserWarehouseModel::join('cor_warehouses', 'cor_warehouses.id', '=', 'cor_user_warehouse.warehouse_id')
                        ->where('cor_user_warehouse.user_id',$domain['user_id'])
                        ->where('cor_user_warehouse.is_status', 1)
                        ->where('cor_warehouses.status', 1)
                        ->where('cor_warehouses.is_delete', 0)
                        ->select('cor_warehouses.id', 'cor_warehouses.name')
                        ->orderBy('cor_warehouses.name')
                        ->get();

        // --- Response

        return [

            'data' => [
                'data' => $stockItems,
                'warehouses' => $warehouses,
                'total' => $total,
                'page' => $page + 1,
                'perPage' => $perPage,
            ],
            'total' => $total,
            'page' => $page + 1,
            'perPage' => $perPage,
            'warehouses' => $warehouses,
        ];
    }


}
