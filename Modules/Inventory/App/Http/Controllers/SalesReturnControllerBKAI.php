<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Http\Requests\ProcessSalesReturnRequest;
use Modules\Inventory\App\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\App\Models\DamageItemModel;
use Modules\Inventory\App\Models\DamageModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Services\SalesReturnService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class SalesReturnControllerBKAI extends Controller
{
    protected $domain;
    protected SalesReturnService $salesReturnService;

    public function __construct(Request $request, SalesReturnService $salesReturnService)
    {
        $this->salesReturnService = $salesReturnService;

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
            $purchaseReturn = PurchaseReturnModel::create($input);

            $totals = PurchaseReturnModel::insertPurchaseReturnItems($purchaseReturn, $input['items']);

            $purchaseReturn->update([
                'quantity'  => $totals['quantity'],
                'sub_total' => $totals['sub_total'],
            ]);

            DB::commit();

            return response()->json(['status' => 200, 'message' => 'Purchase return created successfully.']);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to create purchase return.',
                'error'   => $e->getMessage(),
            ], 500);
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

        $input['process'] = "Created";

        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturnModel::findOrFail($id);

            $purchaseReturn->purchaseReturnItems()->delete();

            $totals = PurchaseReturnModel::insertPurchaseReturnItems($purchaseReturn, $input['items']);

            $input['quantity']  = $totals['quantity'];
            $input['sub_total'] = $totals['sub_total'];

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

        $data = \Modules\Inventory\App\Models\PurchaseModel::getVendorWisePurchaseItem($validated, $domain);

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
                ->lockForUpdate()
                ->findOrFail($id);

            // Prevent duplicate processing
            if ($salesReturn->process !== 'Created') {
                DB::rollBack();
                return response()->json([
                    'status'  => 409,
                    'message' => 'Sales return has already been processed.',
                ], 409);
            }

            // SALES RETURN PROCESS (Always Runs)
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
                if ($salesItem) {
                    $salesItem->update([
                        'return_quantity' => ($salesItem->return_quantity ?? 0) + $item->quantity
                    ]);
                }
            }

            // DAMAGE PROCESS
            if (
                $approveType === 'sales_return_with_damage' ||
                $approveType === 'sales_return_with_purchase_with_damage'
            ) {
                $damage = DamageModel::create([
                    'config_id'     => $salesReturn->config_id,
                    'created_by_id' => $this->domain['user_id'],
                    'quantity'      => 0,
                    'sub_total'     => 0,
                    'damage_type'   => 'Sales',
                    'process'       => 'Created',
                    'damage_mode'   => 'Damage',
                ]);

                $totalQty = 0;
                $totalAmount = 0;

                foreach ($salesReturn->salesReturnItems as $item) {
                    $salesItem = SalesItemModel::find($item->sales_item_id);

                    $damageItem = DamageItemModel::create([
                        'config_id'      => $salesReturn->config_id,
                        'warehouse_id'   => $item->warehouse_id,
                        'damage_mode'    => 'Sales',
                        'sales_item_id'  => $item->sales_item_id,
                        'damage_id'      => $damage->id,
                        'quantity'       => $item->quantity,
                        'price'          => $item->price,
                        'purchase_price' => $item->price,
                        'sub_total'      => $item->price * $item->quantity,
                        'process'        => 'Completed',
                    ]);

                    StockItemHistoryModel::openingStockQuantity(
                        (object) [
                            'id'            => $damageItem->id,
                            'stock_item_id' => $salesItem->stock_item_id,
                            'name'          => $salesItem->name ?? null,
                            'config_id'     => $salesReturn->config_id,
                            'warehouse_id'  => $item->warehouse_id,
                            'quantity'      => $item->quantity,
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

                    if ($salesItem) {
                        $salesItem->update([
                            'damage_quantity' => ($salesItem->damage_quantity ?? 0) + $item->quantity
                        ]);
                    }
                }

                $damage->update([
                    'quantity'  => $totalQty,
                    'sub_total' => $totalAmount,
                    'process'   => 'Approved',
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
                    ->lockForUpdate()
                    ->findOrFail($salesReturn->purchase_return_id);

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

                    // Atomic update to prevent race conditions
                    PurchaseItemModel::where('id', $item->purchase_item_id)->update([
                        'purchase_return_quantity' => DB::raw("COALESCE(purchase_return_quantity, 0) + {$item->quantity}"),
                        'remaining_quantity'       => DB::raw("COALESCE(remaining_quantity, 0) - {$item->quantity}"),
                    ]);
                }

                $purchaseReturn->update([
                    'process' => 'Approved',
                ]);
            }

            // FINAL UPDATE
            $salesReturn->update([
                'process'        => 'Approved',
                'approved_by_id' => $this->domain['user_id'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Sales return approved successfully.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Approval failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process a sales return (split stock vs damage quantities).
     */
    public function processReturn(ProcessSalesReturnRequest $request, $sales_return_id)
    {
        DB::beginTransaction();

        try {
            $result = $this->salesReturnService->processReturn(
                $sales_return_id,
                $request->validated()['data'],
                $this->domain
            );

            if ($result['status'] !== 200) {
                DB::rollBack();
                return response()->json($result, $result['status']);
            }

            DB::commit();

            return response()->json($result);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Processing failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
