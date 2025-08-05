<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Modules\Inventory\App\Models\DailyStockModel;
use Modules\Inventory\App\Models\StockItemModel;
use RuntimeException;
use InvalidArgumentException;

class DailyStockService
{
    /**
     * Maintains and updates daily stock records.
     *
     * @param string $date The inventory date (e.g., 'YYYY-MM-DD').
     * @param string $field The type of quantity to update (e.g., 'production_quantity').
     * @param int|null $configId The configuration ID.
     * @param int|null $warehouseId The warehouse ID.
     * @param int|null $stockItemId The stock item ID.
     * @param int $quantity The quantity to add/update for the specified field.
     * @return DailyStockModel The updated DailyStockModel instance.
     * @throws RuntimeException If a critical dependency (like StockItem) is not found.
     * @throws InvalidArgumentException If an invalid field type is provided or required parameters are missing.
     * @throws \Throwable For other unexpected database errors.
     */
    public static function maintainDailyStock(
        string $date,
        string $field,
        ?int $configId = null,
        ?int $warehouseId = null,
        ?int $stockItemId = null,
        int $quantity = 0
    ): DailyStockModel {
        // Basic validation for essential parameters
        if (is_null($configId) || is_null($stockItemId)) {
            Log::error("maintainDailyStock: configId or stockItemId cannot be null.", compact('configId', 'stockItemId'));
            throw new InvalidArgumentException("configId and stockItemId are required.");
        }

        // Define the mapping for quantity fields and their associated price type
        $fieldMap = [
            'production_quantity'       => ['price_type' => 'purchase_price', 'quantity_field' => 'production_quantity'],
            'purchase_quantity'         => ['price_type' => 'purchase_price', 'quantity_field' => 'purchase_quantity'],
            'sales_return_quantity'     => ['price_type' => 'sales_price',    'quantity_field' => 'sales_return_quantity'],
            'asset_in_quantity'         => ['price_type' => 'purchase_price', 'quantity_field' => 'asset_in_quantity'],
            'sales_quantity'            => ['price_type' => 'sales_price',    'quantity_field' => 'sales_quantity'],
            'damage_quantity'           => ['price_type' => 'purchase_price', 'quantity_field' => 'damage_quantity'],
            'purchase_return_quantity'  => ['price_type' => 'purchase_price', 'quantity_field' => 'purchase_return_quantity'],
            'production_expense_quantity' => ['price_type' => 'purchase_price', 'quantity_field' => 'production_expense_quantity'],
            'asset_out_quantity'        => ['price_type' => 'purchase_price', 'quantity_field' => 'asset_out_quantity'],
        ];

        if (!isset($fieldMap[$field])) {
            Log::warning("maintainDailyStock: Invalid field type provided: {$field}");
            throw new InvalidArgumentException("Invalid quantity field type: {$field}");
        }

        $priceType = $fieldMap[$field]['price_type'];
        $quantityField = $fieldMap[$field]['quantity_field'];

        // First, find the StockItem. This must happen before firstOrCreate
        $stockItem = StockItemModel::find($stockItemId);
        if (!$stockItem) {
            Log::error("StockItem with ID {$stockItemId} not found. Cannot create DailyStock record.");
            throw new RuntimeException("Stock item not found for ID: {$stockItemId}");
        }

        // Define the attributes to search for
        $searchAttributes = [
            'config_id' => $configId,
            'stock_item_id' => $stockItemId,
            'inv_date' => $date,
        ];
        // Conditionally add warehouse_id to the search criteria
        if (!is_null($warehouseId)) {
            $searchAttributes['warehouse_id'] = $warehouseId;
        }

        // Define the attributes to set if a new record is created
        $createAttributes = [
            'warehouse_id' => $warehouseId, // Ensure warehouse_id is set on creation
            'item_name' => $stockItem->name,
            'uom' => $stockItem->uom,
            'sales_price' => $stockItem->sales_price,
            'purchase_price' => $stockItem->purchase_price,
            // Initialize all quantity and balance fields to 0 for new records
            'opening_quantity' => 0,
            'production_quantity' => 0,
            'purchase_quantity' => 0,
            'sales_return_quantity' => 0,
            'asset_in_quantity' => 0,
            'sales_quantity' => 0,
            'damage_quantity' => 0,
            'purchase_return_quantity' => 0,
            'production_expense_quantity' => 0,
            'asset_out_quantity' => 0,
            'opening_balance' => 0,
            'total_in_quantity' => 0,
            'total_out_quantity' => 0,
            'closing_quantity' => 0,
            'closing_balance' => 0,
            'price' => 0, // Initialize 'price' as well
        ];

        // Find or create the daily stock record using firstOrCreate
        $dailyStock = DailyStockModel::firstOrCreate($searchAttributes, $createAttributes);

        // Update the specific quantity field using atomic increment
        $dailyStock->increment($quantityField, $quantity);

        // Refresh the model to get the latest quantities from the database
        $dailyStock->refresh();

        // Now that the model is refreshed with the latest quantities,
        $dailyStock->price = $dailyStock->$priceType;

        $dailyStock->total_in_quantity = $dailyStock->opening_quantity +
            $dailyStock->production_quantity +
            $dailyStock->purchase_quantity +
            $dailyStock->sales_return_quantity +
            $dailyStock->asset_in_quantity;

        $dailyStock->total_out_quantity = $dailyStock->sales_quantity +
            $dailyStock->damage_quantity +
            $dailyStock->purchase_return_quantity +
            $dailyStock->production_expense_quantity +
            $dailyStock->asset_out_quantity;

        $dailyStock->closing_quantity = $dailyStock->total_in_quantity - $dailyStock->total_out_quantity;
        $dailyStock->opening_balance = $dailyStock->opening_quantity * $dailyStock->price;
        $dailyStock->closing_balance = $dailyStock->closing_quantity * $dailyStock->price;

        // Save all calculated changes in a single operation to the database
        $dailyStock->save();

        return $dailyStock;
    }
}
