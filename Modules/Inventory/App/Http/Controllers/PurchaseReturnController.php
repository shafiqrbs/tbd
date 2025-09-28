<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Entities\Purchase;
use Modules\Inventory\App\Entities\SalesReturnItem;
use Modules\Inventory\App\Http\Requests\PurchaseRequest;
use Modules\Inventory\App\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\App\Models\ConfigPurchaseModel;
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

class PurchaseReturnController extends Controller
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
        $data = PurchaseReturnModel::getRecords($request, $this->domain);
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
        $purchaseReturn = PurchaseReturnModel::with(['vendor', 'purchaseReturnItems'])->find($id);

        if (!$purchaseReturn) {
            return response()->json(['status' => 404, 'message' => 'Purchase return not found.'], 404);
        }

        // Case 1: Simple approve
        if ($approveType === "purchase") {
            DB::beginTransaction();

            try {
                $purchaseReturn = PurchaseReturnModel::with('purchaseReturnItems')->findOrFail($id);

                date_default_timezone_set('Asia/Dhaka'); // set timezone once

                // Process Purchase Return Items
                $purchaseReturn->purchaseReturnItems->each(function($item) use ($purchaseReturn) {
                    $item->config_id = $purchaseReturn->config_id;
                    StockItemHistoryModel::openingStockQuantity($item, 'purchase-return', $this->domain);

                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'purchase_return_quantity',
                        configId: $purchaseReturn->config_id,
                        warehouseId: $item->warehouse_id ?? $this->domain['warehouse_id'],
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );
                });

                // Update statuses
                $purchaseReturn->update(['process' => 'Approved','approved_by_id' => $this->domain['user_id']]);

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Purchase return successfully.',
                ]);

            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 500,
                    'message' => 'Failed to send purchase return to vendor.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        // Case 2: Send to vendor
        if ($approveType === "vendor") {
            DB::beginTransaction();
            try {
                $purchaseReturnItems = $purchaseReturn->purchaseReturnItems;
//                dump($purchaseReturnItems->purchase_item_id);

                $purchaseReturnStockItemIds = $purchaseReturnItems->pluck('stock_item_id')->toArray();

                // Load all stock items in ONE query
                $stockItems = StockItemModel::whereIn('id', $purchaseReturnStockItemIds)
                    ->with('parentStock')
                    ->get()
                    ->keyBy('id');

                $salesReturnData = [
                    'customer_id'        => $purchaseReturn->vendor->customer_id,
                    'created_by_id'      => $this->domain['user_id'],
                    'sub_total'          => $purchaseReturn->sub_total,
                    'process'            => "Created",
                    'purchase_return_id' => $purchaseReturn->id,
                    'quantity'           => $purchaseReturn->quantity,
                ];

                $salesReturn = SalesReturnModel::create($salesReturnData);

                $salesReturnItemData = [];
                $salesConfig = null;

                foreach ($purchaseReturnItems as $item) {
                    $purchaseStock = $stockItems[$item->stock_item_id];
                    $salesStock    = $purchaseStock->parentStock;

                    if (!$salesStock) {
                        throw new \Exception("Parent stock not found for item {$item->id}");
                    }

                    $parentSalesItemId = PurchaseItemModel::where('id', $item->purchase_item_id)->value('parent_sales_item_id');
                    $findParentSalesItem = SalesItemModel::find($parentSalesItemId);

                    if (!$findParentSalesItem) {
                        throw new \Exception("Parent sales item not found for item {$item->id}");
                    }

                    $salesConfig = $salesStock->config_id;

                    $salesReturnItemData[] = [
                        'item_name'               => $salesStock->name,
                        'uom'                     => $salesStock->uom,
                        'stock_item_id'           => $salesStock->id,
                        'quantity'                => $item->quantity,
                        'price'                   => $item->purchase_price,
                        'sub_total'               => $item->sub_total,
                        'warehouse_id'            => $salesStock->warehouse_id,
                        'sales_item_id'           => $parentSalesItemId,
                        'purchase_return_item_id' => $item->id,
                        'sales_return_id'         => $salesReturn->id,
                        'status'                  => 1,
                    ];
                }

                SalesReturnItemModel::insert($salesReturnItemData);

                $salesReturn->update(['config_id' => $salesConfig]);

                $purchaseReturn->update([
                    'process'        => "Send-to-vendor",
                    'approved_by_id' => $this->domain['user_id'],
                ]);

                DB::commit();

                return response()->json([
                    'status'  => 200,
                    'message' => 'Purchase return sent to vendor successfully.',
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => 500,
                    'message' => 'Failed to send purchase return to vendor.',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['status' => 400, 'message' => 'Invalid approval type.'], 400);
    }



}
