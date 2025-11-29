<?php

namespace App\Services\Production;

use Illuminate\Support\Facades\Log;
use Modules\Production\App\Models\ProductionBatchModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionExpense;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionValueAdded;

class ProductionMaterialService
{
    /**
     * Handles the full material + expense update flow for a batch.
     */
    public function updateFullMaterialProcess(int $batchId): void
    {
        // Load batch with relations to avoid N+1
        $batch = ProductionBatchModel::with([
            'batchItems.productionItem.elements.material',
            'batchItems.productionItem.valueAdded'
        ])->find($batchId);

        if (!$batch) {
            Log::warning("Batch {$batchId} not found for material update.");
            return;
        }

        // Chunk batch items to avoid memory issues with large batches
        $batch->batchItems()->chunk(100, function ($batchItems) {
            foreach ($batchItems as $batchItem) {

                $productionItem = $batchItem->productionItem;

                if (!$productionItem) {
                    Log::warning('Missing production item for batch item', [
                        'batch_item_id' => $batchItem->id
                    ]);
                    continue;
                }

                // Update material prices, totals, waste, etc.
                $this->updateProductionRawMaterialPrice($productionItem);

                // Update batch item price based on new recalculated totals
                $batchItem->update([
                    'price' => $productionItem->sub_total ?? 0,
                ]);

                // Update expenses linked to this item
                $this->updateProductionExpensePurchase(
                    $batchItem->id,
                    $productionItem->id
                );
            }
        });
    }

    /**
     * Recalculates pricing & totals for production item materials.
     */
    private function updateProductionRawMaterialPrice(ProductionItems $item): void
    {
        $item->loadMissing(['elements.material']);

        foreach ($item->elements as $element) {
            $material = $element->material;

            if (!$material) {
                $element->update([
                    'purchase_price' => 0,
                    'price' => 0,
                    'sub_total' => 0,
                ]);
                continue;
            }

            $price = $material->average_price ?? $material->purchase_price ?? 0;

            $element->update([
                'purchase_price' => $price,
                'price'          => $price,
                'sub_total'      => $price * $element->quantity,
                'wastage_amount' => $element->wastage_quantity > 0
                    ? $element->wastage_quantity * $price
                    : 0,
            ]);
        }

        // Totals
        $totals = ProductionElements::where('production_item_id', $item->id)
            ->where('status', 1)
            ->selectRaw("
                SUM(sub_total) as sub,
                SUM(wastage_amount) as waste_amount,
                SUM(quantity) as qty,
                SUM(wastage_quantity) as waste_qty
            ")
            ->first();

        $valueAdded = ProductionValueAdded::where('production_item_id', $item->id)->sum('amount');

        if ($totals && $totals->sub > 0) {

            $item->material_amount = $totals->sub;
            $item->material_quantity = $totals->qty;
            $item->waste_material_quantity = $totals->waste_qty;
            $item->waste_amount = $totals->waste_amount;
            $item->value_added_amount = $valueAdded;

            $total = round($totals->sub + $totals->waste_amount + $valueAdded);

            $item->sub_total = $total;
            $item->price     = $total;
            $item->quantity  = $totals->qty + $totals->waste_qty;

        } else {
            $item->material_amount = 0;
            $item->material_quantity = 0;
            $item->waste_material_quantity = 0;
            $item->waste_amount = 0;
            $item->sub_total = 0;
            $item->price = 0;
            $item->quantity = 0;
        }

        $item->save();
    }

    /**
     * Updates expense purchase prices based on updated elements.
     */
    private function updateProductionExpensePurchase(int $batchItemId, int $productionItemId): void
    {
        $expenses = ProductionExpense::where('production_item_id', $productionItemId)
            ->where('production_batch_item_id', $batchItemId)
            ->with(['element'])
            ->get();

        foreach ($expenses as $expense) {
            $purchasePrice = $expense->element->purchase_price ?? 0;
            if ($expense->purchase_price != $purchasePrice) {
                $expense->update([
                    'purchase_price' => $purchasePrice
                ]);
            }
        }
    }
}
