<?php

namespace Modules\Core\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Modules\Inventory\App\Models\StockItemModel;

class FlutterModel extends Model
{
    use HasFactory;

    protected $table = 'dom_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [];

    public static function getPosStockItem($domain)
    {
        return StockItemModel::with([
            'product.measurement.unit',
            'product.unit',
            'product.category',
            'product.setting',
            'product.images',
            'multiplePrice.priceUnitName',
            'purchaseItemForSales' => function ($q) {
                $q->where(function ($query) {
                    $query->whereNull('expired_date')
                        ->orWhere(function ($sub) {
                            $sub->whereNotNull('expired_date')
                                ->where('expired_date', '>', now())
                                ->whereRaw('quantity > COALESCE(sales_quantity, 0)');
                        });
                });
            }
        ])
            ->where('inv_stock.config_id', $domain['config_id'])
            ->whereHas('product.setting', function ($query) {
                $query->whereIn('slug', [
                    'pre-production', 'stockable', 'mid-production', 'post-production'
                ]);
            })
            ->where('inv_stock.status', 1)
            ->orderBy('inv_stock.name')
            ->get()
            ->map(function ($stock) {
                $product = $stock->product;
                $category = $stock->product->category;
                return [
                    'id' => $stock->id,
                    'stock_id' => $stock->id,
                    'name' => $stock->display_name ?? $stock->name,
                    'display_name' => $stock->display_name ?? $stock->name,
                    'product_name' => $stock->name . '[' . ($stock->quantity ?? 0) . '] ' . ($product->unit->name ?? ''),
                    'slug' => $product->slug ?? null,
                    'vendor_id' => $product->vendor_id ?? null,
                    'category_id' => $product->category_id ?? null,
                    'category' => $category->name ?? null,
                    'brand_name' => $category->name ?? null,
                    'unit_id' => $product->unit_id ?? null,
                    'unit_name' => $product->unit->name ?? null,
                    'quantity' => $stock->quantity,
                    'price' => ROUND($stock->price, 2),
                    'sales_price' => ROUND($stock->sales_price, 2),
                    'purchase_price' => ROUND($stock->purchase_price, 2),
                    'average_price' => ROUND($stock->average_price, 2),
                    'barcode' => $stock->barcode,
                    'product_nature' => $product->setting->slug ?? null,
                    'feature_image' => $product?->parent_id ? optional(optional($product)->parent_images)->feature_image ?? null : optional(optional($product)->images)->feature_image ?? null,
                    'purchase_item_for_sales' => optional(optional($stock)->purchaseItemForSales)->map(function ($s) {
                    $salesQty = $s->sales_quantity ?? 0;
                    return [
                        'purchase_item_id' => $s->id,
                        'purchase_quantity' => $s->quantity,
                        'sales_quantity' => $salesQty,
                        'remain_quantity' => $s->quantity - $salesQty,
                        'expired_date' => $s->expired_date
                            ? Carbon::parse($s->expired_date)->format('d-M-Y')
                            : null,
                    ];
                }),
                    'multi_price' => optional(optional($stock)->multiplePrice)->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'price_unit_id' => $m->price_unit_id,
                            'price' => $m->price,
                            'field_name' => $m->priceUnitName->name ?? null,
                            'field_slug' => $m->priceUnitName->slug ?? null,
                            'parent_slug' => $m->priceUnitName->parent_slug ?? null,
                        ];
                    }),
                    'measurements' => optional(optional($product)->measurement)->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'unit_id' => $m->unit_id,
                            'unit_name' => $m->unit->name ?? null,
                            'slug' => $m->unit->slug ?? null,
                            'is_base_unit' => $m->is_base_unit,
                            'is_sales' => $m->is_sales,
                            'is_purchase' => $m->is_purchase,
                            'quantity' => $m->quantity,
                        ];
                    }),

                ];
            });
    }

}
