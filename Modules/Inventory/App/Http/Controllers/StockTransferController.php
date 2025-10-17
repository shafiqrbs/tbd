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
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
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






    /*OLD*/

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

    /**
     * Approve the specified resource from storage.
     */

    public function approve(Request $request, $id, $approveType)
    {
        DB::beginTransaction();

        try {
            $salesReturn = SalesReturnModel::with('salesReturnItems')->findOrFail($id);
            $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')->findOrFail($salesReturn->purchase_return_id);

            date_default_timezone_set('Asia/Dhaka'); // set timezone once

            // Process Sales Return Items
            $salesReturn->salesReturnItems->each(function($item) use ($salesReturn) {
                $item->config_id = $salesReturn->config_id;
                StockItemHistoryModel::openingStockQuantity($item, 'sales-return', $this->domain);

                DailyStockService::maintainDailyStock(
                    date: date('Y-m-d'),
                    field: 'sales_return_quantity',
                    configId: $salesReturn->config_id,
                    warehouseId: $item->warehouse_id,
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );
            });

            // Process Purchase Return Items
            $purchaseReturn->purchaseReturnItems->each(function($item) use ($purchaseReturn) {
                $item->config_id = $purchaseReturn->config_id;
                StockItemHistoryModel::openingStockQuantity($item, 'purchase-return', $this->domain);

                DailyStockService::maintainDailyStock(
                    date: date('Y-m-d'),
                    field: 'purchase_return_quantity',
                    configId: $purchaseReturn->config_id,
                    warehouseId: $item->warehouse_id,
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );
            });

            // Update statuses
            $salesReturn->update([
                'process' => 'Approved',
                'approved_by_id' => $this->domain['user_id']
            ]);

            $purchaseReturn->update(['process' => 'Approved']);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Sales return successfully.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to send purchase return to vendor.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['status' => 400, 'message' => 'Invalid approval type.'], 400);
    }



}
