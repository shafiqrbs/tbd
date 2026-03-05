<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\App\Models\DamageItemModel;
use Modules\Inventory\App\Models\DamageModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\PurchaseReturnItemModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesReturnItemModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\getInsertStatement;

class SalesReturnController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function index(Request $request)
    {
        $data = SalesReturnModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(ResponseAlias::HTTP_OK);
        return $response;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseReturnRequest $request)
    {
        $input   = $request->validated();

        $input['process']       = "Created";
        $input['config_id']     = $this->domain['config_id'];
        $input['created_by_id'] = $this->domain['user_id'];

        DB::beginTransaction();
        try {
            // Create purchase return
            $purchaseReturn = PurchaseReturnModel::create($input);

            // Insert purchase return items
            $totals = PurchaseReturnModel::insertPurchaseReturnItems($purchaseReturn, $input['items']);

            // Update purchase return with totals
            $purchaseReturn->update([
                'quantity'  => $totals['quantity'],
                'sub_total' => $totals['sub_total'],
            ]);

            DB::commit();

            return response()->json(['status' => 200, 'message' => 'Purchase return created successfully.']);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }
    }

    /**
     * Show the specified resource.
     */
    public function edit($id)
    {
        $purchaseReturn = SalesReturnModel::with('salesReturnItems')->find($id);
        return response()->json(['status' => 200, 'message' => 'success', 'data' => $purchaseReturn]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseReturnRequest $request, $id)
    {
        $input = $request->validated();

        $input['process']       = "Created";

        DB::beginTransaction();
        try {
            // Find purchase return or fail
            $purchaseReturn = PurchaseReturnModel::findOrFail($id);

            // Delete old items
            $purchaseReturn->purchaseReturnItems()->delete();

            // Insert new purchase return items
            $totals = PurchaseReturnModel::insertPurchaseReturnItems($purchaseReturn, $input['items']);

            // Update totals
            $input['quantity']  = $totals['quantity'];
            $input['sub_total'] = $totals['sub_total'];

            // Update purchase return
            $purchaseReturn->update($input);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Purchase return updated successfully.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to update purchase return.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function vendorWisePurchaseItem(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'nullable|integer|exists:cor_vendors,id',
        ]);

        $domain = $this->domain->toArray();

        $data = PurchaseModel::getVendorWisePurchaseItem($validated, $domain);

        return response()->json(['status' => 200, 'message' => 'success' , 'data' => $data]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $findPurchaseReturn = PurchaseReturnModel::find($id);
        if ($findPurchaseReturn->process == "Created") {
            $findPurchaseReturn->delete();
            return response()->json(['status' => 200, 'message' => 'Delete successfully.']);
        }
        return response()->json(['status' => 400, 'message' => 'Approved data']);
    }


    public function approve(Request $request, $id, $approveType = 'sales_return')
    {
        DB::beginTransaction();

        try {

            $salesReturn = SalesReturnModel::with('salesReturnItems')
                ->findOrFail($id);

            // SALES RETURN PROCESS (Always Runs)
            foreach ($salesReturn->salesReturnItems as $item) {

                $item->config_id = $salesReturn->config_id;

                // Increase stock
                StockItemHistoryModel::openingStockQuantity(
                    $item,
                    'sales-return',
                    $this->domain
                );

                DailyStockService::maintainDailyStock(
                    date: now()->toDateString(),
                    field: 'sales_return_quantity',
                    configId: $salesReturn->config_id,
                    warehouseId: $item->warehouse_id,
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );
                $salesItem = SalesItemModel::find($item->sales_item_id);
                $salesItem->update([
                    'return_quantity' => ($salesItem->return_quantity ?? 0) + $item->quantity
                ]);
            }

            // DAMAGE PROCESS
            if (
                $approveType === 'sales_return_with_damage' ||
                $approveType === 'sales_return_with_purchase_with_damage'
            ) {

                $damage = DamageModel::create([
                    'config_id' => $salesReturn->config_id,
                    'created_by_id' => $this->domain['user_id'],
                    'quantity' => 0,
                    'sub_total' => 0,
                    'damage_type' => "Sales",
                    'process' => "Created",
                    'damage_mode' => "Damage",
                ]);

                $totalQty = 0;
                $totalAmount = 0;

                foreach ($salesReturn->salesReturnItems as $item) {

                    $salesItem = SalesItemModel::find($item->sales_item_id);

                    $damageItem = DamageItemModel::create([
                        'config_id' => $salesReturn->config_id,
                        'warehouse_id' => $item->warehouse_id,
                        'damage_mode' => 'Sales',
                        'sales_item_id' => $item->sales_item_id,
                        'damage_id' => $damage->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'purchase_price' => $item->price,
                        'sub_total' => $item->price * $item->quantity,
                        'process' => 'Completed'
                    ]);

                    // Deduct stock for damage
                    StockItemHistoryModel::openingStockQuantity(
                        (object)[
                            'id' => $damageItem->id,
                            'stock_item_id' => $salesItem->stock_item_id,
                            'name' => $salesItem->name ?? null,
                            'config_id' => $salesReturn->config_id,
                            'warehouse_id' => $item->warehouse_id,
                            'quantity' => $item->quantity,
                        ],
                        'damage',
                        $this->domain
                    );

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'damage_quantity',
                        configId: $salesReturn->config_id,
                        warehouseId: $item->warehouse_id,
                        stockItemId: $salesItem->stock_item_id,
                        quantity: $item->quantity
                    );

                    $totalQty += $item->quantity;
                    $totalAmount += ($item->price * $item->quantity);

                    $salesItem->update([
                        'damage_quantity' => ($salesItem->damage_quantity ?? 0) + $item->quantity
                    ]);
                }

                $damage->update([
                    'quantity' => $totalQty,
                    'sub_total' => $totalAmount,
                    'process' => 'Approved'
                ]);
            }

            // PURCHASE RETURN PROCESS
            if (
                $approveType === 'sales_return_with_purchase' ||
                $approveType === 'sales_return_with_purchase_with_damage'
            ) {

                if (!$salesReturn->purchase_return_id) {
                    throw new \Exception('Purchase return not found.');
                }

                $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')
                    ->findOrFail($salesReturn->purchase_return_id);

                foreach ($purchaseReturn->purchaseReturnItems as $item) {

                    $item->config_id = $purchaseReturn->config_id;

                    // Deduct stock
                    StockItemHistoryModel::openingStockQuantity(
                        $item,
                        'purchase-return',
                        $this->domain
                    );

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'purchase_return_quantity',
                        configId: $purchaseReturn->config_id,
                        warehouseId: $item->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );

                    $purchaseItem = PurchaseItemModel::find($item->purchase_item_id);
                    $remainingQuantity = PurchaseItemModel::getPurchaseItemRemainingQuantity($item->purchase_item_id);

                    $purchaseItem->update([
                        'purchase_return_quantity' => ($purchaseItem->purchase_return_quantity ?? 0) + $item->quantity,
                        'remaining_quantity' => ($remainingQuantity ?? 0) - $item->quantity,
                    ]);
                }

                $purchaseReturn->update([
                    'process' => 'Approved'
                ]);
            }

            // FINAL UPDATE
            $salesReturn->update([
                'process' => 'Approved',
                'approved_by_id' => $this->domain['user_id']
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Sales return approved successfully.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Approval failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function processReturn1(Request $request, $sales_return_id)
    {
        DB::beginTransaction();

        try {

            $items = $request->data; // array of items from frontend

            // find the sales return
            $salesReturn = SalesReturnModel::findOrFail($sales_return_id);

            $totalQuantity = 0;
            $totalAmount = 0;

            foreach ($items as $item) {

                // find each sales return item
                $returnItem = SalesReturnItemModel::findOrFail($item['id']);

                $stockQty  = (float) $item['stock_entry_quantity'];
                $damageQty = (float) $item['damage_entry_quantity'];
                $totalQty  = $stockQty + $damageQty;

                // validation: stock + damage must equal requested quantity
                if ($totalQty > $returnItem->request_quantity) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 422,
                        'message' => 'Stock + Damage must equal requested quantity'
                    ]);
                }

                // update the item (without updated_at)
                $returnItem->stock_entry_quantity  = $stockQty;
                $returnItem->damage_entry_quantity = $damageQty;
                $returnItem->quantity        = $totalQty;
                $returnItem->sub_total       = $totalQty*$returnItem->price;

                $returnItem->save(); // ✅ no updated_at column needed

                // accumulate totals
                $totalQuantity += $totalQty;
                $totalAmount   += $totalQty * $returnItem->price;
            }

            // update the main sales return
            $salesReturn->process  = 'Approved';
            $salesReturn->quantity  = $totalQuantity;
            $salesReturn->sub_total = $totalAmount;

            $salesReturn->save(); // ✅ no timestamps issue

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Sales return processed successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function processReturn(Request $request, $sales_return_id)
    {
        DB::beginTransaction();

        try {
            $items = $request->data;
            $salesReturn = SalesReturnModel::with('salesReturnItems')->findOrFail($sales_return_id);

            $totalQuantity = 0;
            $totalAmount = 0;
            $damageItems = [];

            // ----------------------------
            // STEP 1: UPDATE SALES RETURN ITEMS
            // ----------------------------
            foreach ($items as $item) {
                $returnItem = SalesReturnItemModel::findOrFail($item['id']);

                $stockQty  = (float) $item['stock_entry_quantity'];
                $damageQty = (float) $item['damage_entry_quantity'];
                $totalQty  = $stockQty + $damageQty;

                if ($totalQty > $returnItem->request_quantity) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 422,
                        'message' => 'Stock + Damage must not exceed requested quantity'
                    ]);
                }

                // Update sales return item
                $returnItem->update([
                    'stock_entry_quantity'  => $stockQty,
                    'damage_entry_quantity' => $damageQty,
                    'quantity'              => $totalQty,
                    'sub_total'             => $totalQty * $returnItem->price,
                ]);

                $totalQuantity += $totalQty;
                $totalAmount   += $totalQty * $returnItem->price;

                if ($damageQty > 0) {
                    $damageItems[] = [
                        'item' => $returnItem,
                        'quantity' => $damageQty
                    ];
                }

                // ----------------------------
                // STEP 2: UPDATE LINKED PURCHASE RETURN ITEM
                // ----------------------------
                if ($returnItem->purchase_return_item_id) {
                    $purchaseItem = PurchaseReturnItemModel::findOrFail($returnItem->purchase_return_item_id);

                    $purchaseItem->update([
                        'quantity'  => $totalQty,
                        'sub_total' => $totalQty * $purchaseItem->purchase_price,
                    ]);
                }
            }

            // ----------------------------
            // STEP 3: UPDATE MAIN SALES RETURN
            // ----------------------------
            $salesReturn->update([
                'quantity'  => $totalQuantity,
                'sub_total' => $totalAmount,
            ]);

            // ----------------------------
            // STEP 4: UPDATE MAIN PURCHASE RETURN
            // ----------------------------
            if ($salesReturn->purchase_return_id) {
                $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')
                    ->findOrFail($salesReturn->purchase_return_id);

                $purchaseTotalQty = $purchaseReturn->purchaseReturnItems->sum('quantity');
                $purchaseTotalAmt = $purchaseReturn->purchaseReturnItems->sum('sub_total');

                $purchaseReturn->update([
                    'quantity'  => $purchaseTotalQty,
                    'sub_total' => $purchaseTotalAmt,
                    'process'   => 'Approved',
                ]);
            }

            // ----------------------------
            // STEP 5: PROCESS SALES RETURN (stock & daily stock)
            // ----------------------------
            $salesReturn->refresh();
            foreach ($salesReturn->salesReturnItems as $item) {
                $item->config_id = $salesReturn->config_id;

                StockItemHistoryModel::openingStockQuantity(
                    $item,
                    'sales-return',
                    $this->domain
                );

                DailyStockService::maintainDailyStock(
                    date: now()->toDateString(),
                    field: 'sales_return_quantity',
                    configId: $salesReturn->config_id,
                    warehouseId: $item->warehouse_id,
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );

                $salesItem = SalesItemModel::find($item->sales_item_id);
                $salesItem->update([
                    'return_quantity' => ($salesItem->return_quantity ?? 0) + $item->quantity
                ]);
            }

            // ----------------------------
            // STEP 6: PROCESS PURCHASE RETURN (stock & daily stock)
            // ----------------------------
            if ($salesReturn->purchase_return_id) {
                $purchaseReturn->refresh();
                foreach ($purchaseReturn->purchaseReturnItems as $item) {
                    $item->config_id = $purchaseReturn->config_id;

                    StockItemHistoryModel::openingStockQuantity(
                        $item,
                        'purchase-return',
                        $this->domain
                    );

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'purchase_return_quantity',
                        configId: $purchaseReturn->config_id,
                        warehouseId: $item->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );

                    $purchaseItem = PurchaseItemModel::find($item->purchase_item_id);
                    $remainingQuantity = PurchaseItemModel::getPurchaseItemRemainingQuantity($item->purchase_item_id);

                    $purchaseItem->update([
                        'purchase_return_quantity' => ($purchaseItem->purchase_return_quantity ?? 0) + $item->quantity,
                        'remaining_quantity' => ($remainingQuantity ?? 0) - $item->quantity,
                    ]);
                }
            }

            // ----------------------------
            // STEP 7: DAMAGE PROCESS
            // ----------------------------
            if (count($damageItems) > 0) {
                $damage = DamageModel::create([
                    'config_id'     => $salesReturn->config_id,
                    'created_by_id' => $this->domain['user_id'] ?? null,
                    'quantity'      => 0,
                    'sub_total'     => 0,
                    'damage_type'   => 'Sales',
                    'process'       => 'Created',
                    'damage_mode'   => 'Damage',
                ]);

                $totalDamageQty = 0;
                $totalDamageAmt = 0;

                foreach ($damageItems as $d) {
                    $returnItem = $d['item'];
                    $qty        = $d['quantity'];

                    $damageItem = DamageItemModel::create([
                        'config_id'      => $salesReturn->config_id,
                        'warehouse_id'   => $returnItem->warehouse_id,
                        'damage_mode'    => 'Sales',
                        'sales_item_id'  => $returnItem->sales_item_id,
                        'damage_id'      => $damage->id,
                        'quantity'       => $qty,
                        'price'          => $returnItem->price,
                        'purchase_price' => $returnItem->price,
                        'sub_total'      => $qty * $returnItem->price,
                        'process'        => 'Completed'
                    ]);

                    StockItemHistoryModel::openingStockQuantity(
                        (object)[
                            'id'            => $damageItem->id,
                            'stock_item_id' => $returnItem->stock_item_id,
                            'name'          => $returnItem->item_name,
                            'config_id'     => $salesReturn->config_id,
                            'warehouse_id'  => $returnItem->warehouse_id,
                            'quantity'      => $qty,
                        ],
                        'damage',
                        $this->domain
                    );

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'damage_quantity',
                        configId: $salesReturn->config_id,
                        warehouseId: $returnItem->warehouse_id,
                        stockItemId: $returnItem->stock_item_id,
                        quantity: $qty
                    );

                    $totalDamageQty += $qty;
                    $totalDamageAmt += $qty * $returnItem->price;
                }

                $damage->update([
                    'quantity'  => $totalDamageQty,
                    'sub_total' => $totalDamageAmt,
                    'process'   => 'Approved'
                ]);
            }

            // ----------------------------
            // FINALIZE SALES RETURN
            // ----------------------------
            $salesReturn->update([
                'process' => 'Approved',
                'approved_by_id' => $this->domain['user_id'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Sales return processed successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Processing failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
