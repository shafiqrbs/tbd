<?php

namespace Modules\Medicine\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Medicine\App\Http\Requests\StockTransferRequest;
use Modules\Medicine\App\Models\PurchaseItemModel;
use Modules\Medicine\App\Models\StockItemModel;
use Modules\Medicine\App\Models\StockTransferItemModel;
use Modules\Medicine\App\Models\StockTransferModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class StockTransferController extends Controller
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
        $data = StockTransferModel::getRecords($request, $this->domain);
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

    public function indexForCentral(Request $request)
    {
        $data = StockTransferModel::getRecordsForCentral($request, $this->domain);
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
    public function store(StockTransferRequest $request)
    {
        $input = $request->validated();

        $input['process'] = "Created";
        $input['config_id'] = $this->domain['config_id'];
        $input['from_warehouse_id'] = $this->domain['warehouse_id'];
        $input['to_warehouse_id'] = UserWarehouseModel::where('user_id', $this->domain['user_id'])->first()?->warehouse_id;

        DB::beginTransaction();
        try {
            $stockTransfer = StockTransferModel::create($input);

            // Insert transfer items
            StockTransferModel::insertStockTransferItems($stockTransfer, $input['items'], $this->domain['config_id']);

            DB::commit();

            return response()->json(['status' => 200, 'message' => 'Stock transfer created successfully.']);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }
    }


    /**
     * show the specified resource from storage.
     */
    public function show($id)
    {
        $findStockTransfer = StockTransferModel::getDetails($id);
        if (!$findStockTransfer) {
            return response()->json(['status' => 400, 'message' => 'Data not found.']);
        }
        return response()->json(['status' => 200, 'message' => 'Data found successfully.', 'data' => $findStockTransfer]);
    }

    public function update(StockTransferRequest $request, $id)
    {
        $input = $request->validated();

        $data['process'] = "Created";
        $data['to_warehouse_id'] = $input['to_warehouse_id'];

        DB::beginTransaction();
        try {
            $stockTransfer = StockTransferModel::lockForUpdate()->findOrFail($id);

            $stockTransfer->stockTransferItems()->delete();
            $stockTransfer->update($data);

            // Insert new transfer items if provided
            if (!empty($input['items'])) {
                StockTransferModel::insertStockTransferItems(
                    $stockTransfer,
                    $input['items'],
                    $this->domain['config_id']
                );
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Stock transfer updated successfully.',
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while updating the stock transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $findStockTransfer = StockTransferModel::where('uid', $id)->first();
        if ($findStockTransfer->process == "Created") {
            $findStockTransfer->delete();
            return response()->json(['status' => 200, 'message' => 'Delete successfully.']);
        }
        return response()->json(['status' => 400, 'message' => 'Approved data']);
    }

    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {

                //Load the stock transfer with related items
                $findStockTransfer = StockTransferModel::with('stockTransferItems')
                    ->where('uid', $id)
                    ->first();

                if (!$findStockTransfer) {
                    return response()->json(['status' => 400, 'message' => 'Data not found.']);
                }

                $fromWarehouse = $findStockTransfer->from_warehouse_id;
                $toWarehouse = $findStockTransfer->to_warehouse_id;

                //Validate stock transfer state
                if (
                    !$fromWarehouse ||
                    !$toWarehouse ||
                    $fromWarehouse == $toWarehouse ||
                    $findStockTransfer->process !== "Created"
                ) {
                    throw new Exception('Invalid stock transfer details.');
                }

                $stockTransferItems = $findStockTransfer->stockTransferItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'config_id' => $item->config_id,
                        'stock_item_id' => $item->stock_item_id,
                        'purchase_item_id' => $item->purchase_item_id,
                        'quantity' => $item->quantity,
                        'uom' => $item->uom,
                        'name' => $item->name,
                    ];
                });

                if ($stockTransferItems->isEmpty()) {
                    throw new Exception('No items found for this stock transfer.');
                }


                //Update stock transfer status
                $findStockTransfer->update([
                    'process' => 'Approved',
                    'approved_date' => new DateTime('now'),
                    'approved_by_id' => $this->domain['user_id'],
                ]);
            });

            // Success response (only runs if transaction committed)
            return response()->json([
                'status' => 200,
                'message' => 'Stock transfer approved successfully.',
            ]);

        } catch (Throwable $e) {
            // Auto rollback handled by DB::transaction()
            logger()->error('Stock Transfer Approval Failed', [
                'stock_transfer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Failed to approve stock transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function receive($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $findStockTransfer = StockTransferModel::with('stockTransferItems')->findOrFail($id);

                $fromWarehouse = $findStockTransfer->from_warehouse_id;
                $toWarehouse = $findStockTransfer->to_warehouse_id;

                // Validate transfer state
                if (
                    !$fromWarehouse ||
                    !$toWarehouse ||
                    $fromWarehouse == $toWarehouse ||
                    $findStockTransfer->process !== "Approved"
                ) {
                    throw new Exception('Invalid stock transfer details.');
                }

                $stockTransferItems = $findStockTransfer->stockTransferItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'config_id' => $item->config_id,
                        'stock_item_id' => $item->stock_item_id,
                        'purchase_item_id' => $item->purchase_item_id,
                        'quantity' => $item->quantity,
                        'uom' => $item->uom,
                        'name' => $item->name,
                    ];
                });

                if ($stockTransferItems->isEmpty()) {
                    throw new Exception('No items found for this stock transfer.');
                }

                foreach ($stockTransferItems as $stockTransferItem) {

                    $purchaseItemId = $stockTransferItem['purchase_item_id'];
                    $stockTransferItemId = $stockTransferItem['id'];

                    if ($purchaseItemId) {

                        $findPurchaseItemStock = PurchaseItemModel::find($purchaseItemId);
                        $purchaseItemRemainQuantity = PurchaseItemModel::remainingQuantity($purchaseItemId);

                        if ($findPurchaseItemStock && ($purchaseItemRemainQuantity >= $stockTransferItem['quantity'])) {

                            $findStockItem = StockItemModel::find($stockTransferItem['stock_item_id']);
                            if (!$findStockItem) {
                                throw new Exception("Stock item not found: ID {$stockTransferItem['stock_item_id']}");
                            }

                            $purchasePrice = $findStockItem->purchase_price ?? 0;
                            $salesPrice = $findStockItem->sales_price ?? 0;
                            $quantity = $stockTransferItem['quantity'];

                            // Stock Transfer IN (to destination)
                            $dataForAdd = [
                                'stock_transfer_id' => $findStockTransfer->id,
                                'name' => $stockTransferItem['name'],
                                'stock_transfer_item_id' => $stockTransferItemId,
                                'config_id' => $stockTransferItem['config_id'],
                                'warehouse_id' => $toWarehouse,
                                'stock_item_id' => $stockTransferItem['stock_item_id'],
                                'quantity' => $quantity,
                                'purchase_price' => $purchasePrice,
                                'sales_price' => $salesPrice,
                                'sub_total' => $quantity * $purchasePrice,
                            ];

                            if (!StockItemHistoryModel::openingStockQuantity((object)$dataForAdd, 'stock-transfer-in', $this->domain)) {
                                throw new Exception("Failed to record stock-transfer-in for item ID {$stockTransferItem['stock_item_id']}");
                            }

                            DailyStockService::maintainDailyStock(
                                date: now()->toDateString(),
                                field: 'stock_transfer_in',
                                configId: $stockTransferItem['config_id'],
                                warehouseId: $toWarehouse,
                                stockItemId: $stockTransferItem['stock_item_id'],
                                quantity: $quantity
                            );

                            // Stock Transfer OUT (from source)
                            $dataForMinus = $dataForAdd;
                            $dataForMinus['warehouse_id'] = $fromWarehouse;

                            if (!StockItemHistoryModel::openingStockQuantity((object)$dataForMinus, 'stock-transfer-out', $this->domain)) {
                                throw new Exception("Failed to record stock-transfer-out for item ID {$stockTransferItem['stock_item_id']}");
                            }

                            DailyStockService::maintainDailyStock(
                                date: now()->toDateString(),
                                field: 'stock_transfer_out',
                                configId: $stockTransferItem['config_id'],
                                warehouseId: $fromWarehouse,
                                stockItemId: $stockTransferItem['stock_item_id'],
                                quantity: $quantity
                            );

                            $findPurchaseItemStock->warehouse_transfer_quantity =
                                ($findPurchaseItemStock->warehouse_transfer_quantity ?? 0) + $quantity;
                            $findPurchaseItemStock->save();

                        } else {
                            // Insufficient remaining quantity → mark as zero and continue
                            StockTransferItemModel::find($stockTransferItemId)?->update(['quantity' => 0]);
                        }

                    } else {
                        // Missing purchase_item_id → mark as zero and continue
                        StockTransferItemModel::find($stockTransferItemId)?->update(['quantity' => 0]);
                    }
                }

                // Update transfer status
                $findStockTransfer->update([
                    'process' => 'Received',
                    'issued_date' => now(),
                    'received_date' => now(),
                    'received_by_id' => $this->domain['user_id'],
                ]);
            });

            return response()->json([
                'status' => 200,
                'message' => 'Stock transfer received successfully.',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to receive stock transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function inlineUpdate(Request $request, $id)
    {
        $input = $request->only(['transfer_item_id', 'field_name', 'field_value', 'stock_item_id']);

        // Basic validation
        $validator = Validator::make($input, [
            'transfer_item_id' => 'required|integer',
            'field_name' => 'required|string|in:quantity,purchase_item_id',
            'field_value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => ResponseAlias::HTTP_BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        // Fetch the record
        $item = StockTransferItemModel::find($input['transfer_item_id']);

        if (!$item) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Indent item not found',
            ]);
        }

        // Update the field safely
        $item->update([
            $input['field_name'] => $input['field_value']
        ]);

        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => 'Indent item updated successfully',
            'data' => [
                'id' => $item->id,
                'field' => $input['field_name'],
                'value' => $input['field_value']
            ]
        ], ResponseAlias::HTTP_OK);
    }


}
