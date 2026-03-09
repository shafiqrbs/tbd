<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItemModel extends Model
{

    protected $table = 'inv_sales_return_item';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $fillable = ['sales_item_id','sales_return_id','stock_item_id','warehouse_id','purchase_return_item_id','item_name','uom','request_quantity','price','sub_total','status','stock_entry_quantity','damage_entry_quantity','quantity'];

    public static function insertSalesReturnItems($salesReturn, array $items): array
    {
        if (empty($items)) {
            return ['quantity' => 0, 'sub_total' => 0];
        }

        $salesItems = SalesItemModel::with('stock')
            ->whereIn('id', array_column($items, 'sales_item_id'))
            ->get()
            ->keyBy('id');

        $insertData = [];
        $totalQuantity = 0;
        $totalSubTotal = 0;

        foreach ($items as $record) {

            $salesItem = $salesItems[$record['sales_item_id']] ?? null;

            if (!$salesItem) {
                continue;
            }

            $price = $salesItem->price ?? 0;
            $quantity = $record['quantity'];

            $insertData[] = [
                'sales_return_id' => $salesReturn->id,
                'sales_item_id' => $salesItem->id,
                'stock_item_id' => $salesItem->stock_item_id,
                'warehouse_id' => $salesItem->warehouse_id,
                'item_name' => $salesItem->stock?->name,
                'uom' => $salesItem->stock?->uom,

                'request_quantity' => $quantity,
                'quantity' => $quantity,
                'stock_entry_quantity' => $record['stock_entry_quantity'],
                'damage_entry_quantity' => $record['damage_entry_quantity'],

                'price' => $price,
                'sub_total' => $price * $quantity,

                'status' => 1,
            ];

            $totalQuantity += $quantity;
            $totalSubTotal += $price * $quantity;
        }

        if (!empty($insertData)) {
            SalesReturnItemModel::insert($insertData);
        }

        return [
            'quantity' => $totalQuantity,
            'sub_total' => $totalSubTotal,
        ];
    }

}
