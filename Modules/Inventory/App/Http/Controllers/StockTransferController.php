<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\WarehouseModel;
use Modules\Inventory\App\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\App\Http\Requests\StockTransferRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Inventory\App\Models\StockTransferModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\getInsertStatement;

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

    public function getItemsForTransfer(Request $request){
        $findWarehouseEnable = ConfigModel::where('domain_id',$this->domain['domain_id'])->value('sku_warehouse');

        if (!$findWarehouseEnable) {
            return response()->json(['status' => ResponseAlias::HTTP_BAD_REQUEST, 'message' => 'Warehouse not enable.']);
        }

        $getWarehouseIds = WarehouseModel::where('domain_id', $this->domain['domain_id'])->where('status',1)->where('is_delete',0)->pluck('id')->toArray();

        if (count($getWarehouseIds) == 0) {
            return response()->json(['status' => ResponseAlias::HTTP_NOT_FOUND, 'message' => 'Warehouse not found.']);
        }

        $items = CurrentStockModel::getItemsForTransfer($this->domain, $getWarehouseIds);
        return response()->json(['status' => ResponseAlias::HTTP_OK, 'message' => 'Warehouse wise data found.' , 'items' => $items]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StockTransferRequest $request)
    {
        $input   = $request->validated();

        $input['process']       = "Created";
        $input['config_id']     = $this->domain['config_id'];
        $input['created_by_id'] = $this->domain['user_id'];


        DB::beginTransaction();
        try {
            $stockTransfer = StockTransferModel::create($input);

            // Insert transfer items
            StockTransferModel::insertStockTransferItems($stockTransfer, $input['items'],$this->domain['config_id']);

            DB::commit();

            return response()->json(['status' => 200, 'message' => 'Stock transfer created successfully.']);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
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

    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {

                //Load the stock transfer with related items
                $findStockTransfer = StockTransferModel::with('stockTransferItems')->findOrFail($id);

                $fromWarehouse = $findStockTransfer->from_warehouse_id;
                $toWarehouse   = $findStockTransfer->to_warehouse_id;

                //Validate stock transfer state
                if (
                    !$fromWarehouse ||
                    !$toWarehouse ||
                    $fromWarehouse == $toWarehouse ||
                    $findStockTransfer->process !== "Created"
                ) {
                    throw new \Exception('Invalid stock transfer details.');
                }

                $stockTransferItems = $findStockTransfer->stockTransferItems->map(function ($item) {
                    return [
                        'id'                => $item->id,
                        'config_id'         => $item->config_id,
                        'stock_item_id'     => $item->stock_item_id,
                        'purchase_item_id'  => $item->purchase_item_id,
                        'quantity'          => $item->quantity,
                        'uom'               => $item->uom,
                        'name'              => $item->name,
                    ];
                });

                if ($stockTransferItems->isEmpty()) {
                    throw new \Exception('No items found for this stock transfer.');
                }

                //Loop through each stock item
                foreach ($stockTransferItems as $stockTransferItem) {
                    $findStockItem = StockItemModel::find($stockTransferItem['stock_item_id']);

                    if (!$findStockItem) {
                        throw new \Exception("Stock item not found: ID {$stockTransferItem['stock_item_id']}");
                    }

                    $purchasePrice = $findStockItem->purchase_price ?? 0;
                    $salesPrice    = $findStockItem->sales_price ?? 0;
                    $quantity      = $stockTransferItem['quantity'];

                    // Stock Transfer IN (to destination warehouse)
                    $dataForAdd = [
                        'stock_transfer_id'      => $id,
                        'name'                   => $stockTransferItem['name'],
                        'stock_transfer_item_id' => $stockTransferItem['id'],
                        'config_id'              => $stockTransferItem['config_id'],
                        'warehouse_id'           => $toWarehouse,
                        'stock_item_id'          => $stockTransferItem['stock_item_id'],
                        'quantity'               => $quantity,
                        'purchase_price'         => $purchasePrice,
                        'sales_price'            => $salesPrice,
                        'sub_total'              => $quantity * $purchasePrice,
                    ];

                    if (!StockItemHistoryModel::openingStockQuantity((object)$dataForAdd, 'stock-transfer-in', $this->domain)) {
                        throw new \Exception("Failed to record stock-transfer-in for item ID {$stockTransferItem['stock_item_id']}");
                    }

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'stock_transfer_in',
                        configId: $stockTransferItem['config_id'],
                        warehouseId: $toWarehouse,
                        stockItemId: $stockTransferItem['stock_item_id'],
                        quantity: $quantity
                    );

                    // Stock Transfer OUT (from source warehouse)
                    $dataForMinus = $dataForAdd;
                    $dataForMinus['warehouse_id'] = $fromWarehouse;

                    if (!StockItemHistoryModel::openingStockQuantity((object)$dataForMinus, 'stock-transfer-out', $this->domain)) {
                        throw new \Exception("Failed to record stock-transfer-out for item ID {$stockTransferItem['stock_item_id']}");
                    }

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'stock_transfer_out',
                        configId: $stockTransferItem['config_id'],
                        warehouseId: $fromWarehouse,
                        stockItemId: $stockTransferItem['stock_item_id'],
                        quantity: $quantity
                    );
                }

                //Update stock transfer status
                $findStockTransfer->update([
                    'process'        => 'Approved',
                    'approved_by_id' => $this->domain['user_id'],
                ]);
            });

            // Success response (only runs if transaction committed)
            return response()->json([
                'status'  => 200,
                'message' => 'Stock transfer approved successfully.',
            ]);

        } catch (\Throwable $e) {
            // Auto rollback handled by DB::transaction()
            logger()->error('Stock Transfer Approval Failed', [
                'stock_transfer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to approve stock transfer.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }






    /*OLD*/


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


}
