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
        $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')->find($id);
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



}
