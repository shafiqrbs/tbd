<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\CurrentStockModel;
use Modules\Inventory\App\Models\DailyStockModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Http\Requests\BatchItemQuantityInlineUpdateRequest;
use Modules\Production\App\Http\Requests\BatchItemRequest;
use Modules\Production\App\Http\Requests\BatchRawItemInlineUpdateRequest;
use Modules\Production\App\Http\Requests\BatchRequest;
use Modules\Production\App\Models\ProductionBatchItemModel;
use Modules\Production\App\Models\ProductionBatchModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionExpense;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionStockHistory;
use Modules\Production\App\Models\SettingModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductionBatchController extends Controller
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
        $data = ProductionBatchModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(BatchRequest $request, GeneratePatternCodeService $patternCodeService)
    {
        $service = new JsonRequestResponse();
        $config = $this->domain['pro_config'];
        $input = $request->validated();
        $params = ['config' => $this->domain['pro_config'],'table' => 'pro_batch','prefix' => 'PB-'];
        $pattern = $patternCodeService->productBatch($params);
        $input['config_id'] = $config;
        $input['code'] = $pattern['code'];
        $input['invoice'] = $pattern['generateId'];
        $input['process'] = 'Created';
        $input['created_by_id'] = $this->domain['user_id'];
        if (empty($input['warehouse_id'])){
            $input['warehouse_id'] = $this->domain['warehouse_id'];
        }
        $entity = ProductionBatchModel::create($input);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */

    public function insertBatchItem(BatchItemRequest $request)
    {
        try {
            // Validate input
            $input = $request->validated();
            $input['config_id'] = $this->domain['pro_config'];
            $batch = ProductionBatchModel::find($input['batch_id']);
            $input['warehouse_id'] = $batch->warehouse_id ?? null;

            // Check if production item exists
            $productionItem = ProductionItems::find($input['production_item_id']);
            if (!$productionItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Production item not found.',
                ], 404);
            }

            // Start database transaction
            DB::beginTransaction();

            // Create production batch item
            $productionBatchItem = ProductionBatchItemModel::create($input);

            // Process production elements and calculate related expenses
            $recipeItems = ProductionElements::join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id')
                ->select('pro_element.*', 'inv_stock.name', 'inv_stock.purchase_price as item_purchase_price', 'inv_stock.sales_price as item_sale_price')
                ->where('pro_element.production_item_id', $input['production_item_id'])
                ->get();

            if ($recipeItems->isNotEmpty()) {
                $productionExpenses = [];
                foreach ($recipeItems as $item) {
                    $productionExpenses[] = [
                        'config_id' => $this->domain['pro_config'],
                        'production_item_id' => $input['production_item_id'],
                        'production_batch_item_id' => $productionBatchItem->id,
                        'production_element_id' => $item->id,
                        'purchase_price' => $item->item_purchase_price,
                        'sales_price' => $item->item_sale_price,
                        'quantity' => $input['issue_quantity'] * $item->quantity,
                        'issue_quantity' => $input['issue_quantity'] * $item->quantity,
                        'created_at' => now()
                    ];
                }
                ProductionExpense::insert($productionExpenses); // Batch insert
            }

            DB::commit(); // Commit transaction

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Production batch item successfully created.',
                'data' => $productionBatchItem,
            ], 201);

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function batchItemDelete($id){
        $service = new JsonRequestResponse();
        ProductionBatchItemModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

    /**
     * batch item quantity inline update.
     */

    public function batchItemQuantityInlineUpdate(BatchItemQuantityInlineUpdateRequest $request)
    {
        try {
            // Validate input
            $input = $request->validated();

            // Check if production item exists
            $productionBatchItem = ProductionBatchItemModel::where('batch_id', $input['batch_id'])
                ->where('id', $input['batch_item_id'])
                ->where('config_id',$this->domain['pro_config'])
                ->first();
            if (!$productionBatchItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Production batch item not found.',
                ], 404);
            }

            // Start database transaction
            DB::beginTransaction();

            if ($input['type'] === 'issue_quantity') {
                // Process production elements and calculate related expenses
                $recipeItems = ProductionElements::join('inv_stock', 'inv_stock.id', '=', 'pro_element.material_id')
                    ->select('pro_element.*', 'inv_stock.name', 'inv_stock.purchase_price as item_purchase_price', 'inv_stock.sales_price as item_sale_price')
                    ->where('pro_element.production_item_id', $productionBatchItem->production_item_id)
                    ->get();

                if ($recipeItems->isNotEmpty()) {
                    foreach ($recipeItems as $item) {
                        $filter = [
                            'config_id' => $this->domain['pro_config'],
                            'production_item_id' => $item['production_item_id'],
                            'production_batch_item_id' => $input['batch_item_id'],
                            'production_element_id' => $item->id,
                        ];

                        $data = [
                            'purchase_price' => $item->item_purchase_price,
                            'sales_price' => $item->item_sale_price,
                            'quantity' => $input['quantity'] * $item->quantity,
                            'issue_quantity' => $input['quantity'] * $item->quantity,
                            'created_at' => now(),
                        ];

                        ProductionExpense::updateOrCreate($filter, $data); // create or update
                    }
                }
            }

            // Dynamically update the specified field using the `type` key.
            $fieldToUpdate = $input['type'];
            $productionBatchItem->$fieldToUpdate = $input['quantity'];
            $productionBatchItem->save();

            DB::commit(); // Commit transaction

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Production batch item successfully updated.',
                'data' => $productionBatchItem,
            ], 200);

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function batchRawItemQuantityInlineUpdate(BatchRawItemInlineUpdateRequest $request)
    {
        try {
            // Validate input
            $input = $request->validated();

            $productionExpense = ProductionExpense::find($input['expense_id']);
            if (!$productionExpense) {
                return response()->json([
                    'success' => false,
                    'message' => 'Production expense item not found.',
                ], 404);
            }

            // Start database transaction
            DB::beginTransaction();

            if ($input['type'] === 'raw_issue_quantity') {
                $productionExpense->update([
                    'quantity' => $input['value']
                ]);
            }

            DB::commit(); // Commit transaction

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Production batch item successfully updated.',
                'data' => $productionExpense,
            ], 200);

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = ProductionBatchModel::getShow($id, $this->domain);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('production::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BatchRequest $request, $id)
    {
        $input = $request->validated();

        $batch = ProductionBatchModel::find($id);
        if (!$batch) {
            return response()->json([
                'message' => 'Data not found',
                'status' => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($input['process'] == 'Approved') {
            $input['approved_by_id'] = $this->domain['user_id'];
            $input['process'] = 'Approved';
            $input['approve_date'] = date('Y-m-d');

            foreach ($batch->batchItems as $batchItem) {
                // get batch expense data
                foreach ($batchItem->productionExpenses as $expenseItem) {
                    // recipe item quantity process into stock
                    $this->processProductionExpense($expenseItem, $batchItem, $batch);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    $productionElement = ProductionElements::find($expenseItem->production_element_id);
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'production_expense_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $batch->warehouse_id,
                        stockItemId: $productionElement->material_id,
                        quantity: $expenseItem->quantity
                    );
                }
            }
        }

        $batch->update($input);


        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK
        ],ResponseAlias::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        ProductionBatchModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }


    public function batchApproved(Request $request,$id)
    {
        DB::beginTransaction();

        try {
            // find batch
            $batch = ProductionBatchModel::find($id);
            if (!$batch) {
                return response()->json([
                    'message' => 'Production batch not found',
                    'status' => ResponseAlias::HTTP_NOT_FOUND
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            // get batch item
            foreach ($batch->batchItems as $batchItem) {
                // get batch expense data
                foreach ($batchItem->productionExpenses as $expenseItem) {
                    // recipe item quantity process into stock
                    $this->processProductionExpense($expenseItem, $batchItem, $batch);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    $productionElement = ProductionElements::find($expenseItem->production_element_id);
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'production_expense_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $batch->warehouse_id,
                        stockItemId: $productionElement->material_id,
                        quantity: $expenseItem->quantity
                    );
                }
            }

            // approve batch
            $batch->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Approved',
                'approve_date' => date('Y-m-d')
            ]);

            DB::commit();

            return response()->json([
                'message' => 'success',
                'status' => ResponseAlias::HTTP_OK
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function batchConfirmReceive(Request $request,$id)
    {
        DB::beginTransaction();
        try {
            // find batch
            $batch = ProductionBatchModel::find($id);
            if (!$batch) {
                return response()->json([
                    'message' => 'Production batch not found',
                    'status' => ResponseAlias::HTTP_NOT_FOUND
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            // get batch item
            foreach ($batch->batchItems as $batchItem) {
                    // batch item quantity process into stock
                    $this->processProductionReceiveConfirm($batchItem, $batch);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    $productionItem = ProductionItems::find($batchItem->production_item_id);
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'production_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $batchItem->warehouse_id,
                        stockItemId: $productionItem->item_id,
                        quantity: ($batchItem->receive_quantity) ? $batchItem->receive_quantity : $batchItem->issue_quantity
                    );
            }

            // approve batch
            $batch->update([
                'process' => 'Received',
                'receive_date' => date('Y-m-d')

            ]);

            DB::commit();

            return response()->json([
                'message' => 'success',
                'status' => ResponseAlias::HTTP_OK
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Process production expense and update stock/inventory information.
     * @param $batchItem
     * @param $batch
     */
    private function processProductionReceiveConfirm($batchItem, $batch)
    {
        $getStockItemId = ProductionItems::find($batchItem['production_item_id'])->item_id;

        // find stock item
        $stockItem = StockItemModel::find($getStockItemId);

        // find existing stock
        $existingStockHistory = StockItemHistoryModel::where('stock_item_id', $getStockItemId)
            ->where('config_id', $stockItem->config_id)
            ->where('warehouse_id', $batchItem->warehouse_id)
            ->latest()
            ->first();


        // calculate closing quantity & stock
        $quantity = $batchItem['receive_quantity'] ?? 0;
        $subTotal = $quantity * $stockItem->sales_price ?? 0;

        $closing_quantity = ($existingStockHistory->closing_quantity ?? 0) + $quantity;
        $closing_balance = ($existingStockHistory->closing_balance ?? 0) + $subTotal;

        // prepare data for stock item history
        $data = [
            'stock_item_id' => $getStockItemId,
            'config_id' => $stockItem->config_id,
            'item_name' => $stockItem->item_name ?? $stockItem->name,
            'quantity' => $quantity,
            'purchase_price' => $stockItem->purchase_price ?? 0,
            'sales_price' => $stockItem->sales_price ?? 0,
            'opening_quantity' => $existingStockHistory->closing_quantity ?? 0,
            'opening_balance' => $existingStockHistory->closing_balance ?? 0,
            'closing_quantity' => $closing_quantity,
            'closing_balance' => $closing_balance,
            'warehouse_id' => $batchItem->warehouse_id,
            'mode' => 'production',
            'process' => 'approved',
            'created_by' => $this->domain['user_id']
        ];

        // create stock item history
        $stockHistory = StockItemHistoryModel::create($data);

        // maintain current stock quantity
        CurrentStockModel::maintainCurrentStock($stockItem->config_id,$batchItem->warehouse_id,$getStockItemId,$closing_quantity);


        // update stock item
        if ($stockItem) {
            $stockItem->update(['quantity' => $stockHistory->closing_quantity]);
        }

        // find total stock item
        $totalProductQty = StockItemModel::calculateTotalStockQuantity(
            $stockItem->product_id,
            $stockItem->config_id
        );

        // update product quantity
        $product = ProductModel::find($stockItem->product_id);
        if ($product) {
            $product->update(['quantity' => $totalProductQty]);
        }

        /*// prepare data for production history
        $inventoryData = [
            'config_id' => $this->domain['pro_config'],
            'stock_item_history_id' => $stockHistory->id,
            'production_batch_item_id' => $batchItem->id,
            'production_batch_id' => $batch->id,
            'production_expense_id' => $expenseItem->id,
            'production_batch_item_quantity' => $batchItem->quantity,
            'production_expense_quantity' => $expenseItem->quantity,
        ];
         // create production history
        ProductionStockHistory::create($inventoryData);
        */
    }

    /**
     * Process production expense and update stock/inventory information.
     *
     * @param $expenseItem
     * @param $batchItem
     * @param $batch
     */
    private function processProductionExpense($expenseItem, $batchItem, $batch)
    {
        // find recipe element
        $findElement = ProductionElements::find($expenseItem->production_element_id);
        // find recipe element stock
        $findStockItem = StockItemModel::find($findElement->material_id);

        // find existing stock
        $existingStockHistory = StockItemHistoryModel::where('stock_item_id', $findElement->material_id)
            ->where('config_id', $findStockItem->config_id)
            ->where('warehouse_id', $batch->warehouse_id)
            ->latest()
            ->first();

        // calculate closing quantity & stock
        $quantity = $expenseItem->quantity ?? 0;
        $subTotal = $quantity * $expenseItem->sales_price ?? 0;

        $closing_quantity = ($existingStockHistory->closing_quantity ?? 0) - $quantity;
        $closing_balance = ($existingStockHistory->closing_balance ?? 0) - $subTotal;

        $quantity = -$quantity;

        // prepare data for stock item history
        $data = [
            'stock_item_id' => $findElement->material_id,
            'config_id' => $findStockItem->config_id,
            'item_name' => $findStockItem->name,
            'quantity' => $quantity,
            'purchase_price' => $expenseItem->purchase_price ?? 0,
            'sales_price' => $expenseItem->sales_price ?? 0,
            'opening_quantity' => $existingStockHistory->closing_quantity ?? 0,
            'opening_balance' => $existingStockHistory->closing_balance ?? 0,
            'closing_quantity' => $closing_quantity,
            'closing_balance' => $closing_balance,
            'warehouse_id' => $batchItem->warehouse_id ?? null,
            'mode' => 'production-expense',
            'process' => 'approved',
            'created_by' => $this->domain['user_id']
        ];

        // create stock item history
        $stockHistory = StockItemHistoryModel::create($data);

        // maintain current stock quantity
        CurrentStockModel::maintainCurrentStock($findStockItem->config_id,$batch->warehouse_id,$findElement->material_id,$closing_quantity);

        // update stock item
        $stockItem = StockItemModel::find($findElement->material_id);
        if ($stockItem) {
            $stockItem->update(['quantity' => $stockHistory->closing_quantity]);
        }

        // find total stock item
        $totalProductQty = StockItemModel::calculateTotalStockQuantity(
            $stockItem->product_id,
            $stockItem->config_id
        );

        // update product quantity
        $product = ProductModel::find($stockItem->product_id);
        if ($product) {
            $product->update(['quantity' => $totalProductQty]);
        }

        // prepare data for production history
        $inventoryData = [
            'config_id' => $this->domain['pro_config'],
            'stock_item_history_id' => $stockHistory->id,
            'production_batch_item_id' => $batchItem->id,
            'production_batch_id' => $batch->id,
            'production_expense_id' => $expenseItem->id,
            'production_batch_item_quantity' => $batchItem->quantity,
            'production_expense_quantity' => $expenseItem->quantity,
        ];
         // create production history
        ProductionStockHistory::create($inventoryData);
    }


    public function batchDropdown(){
        try {
            $productionBatchDropdown = ProductionBatchModel::getBatchDropdown($this->domain);
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Production batch dropdown.',
                'data' => $productionBatchDropdown,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBatchRecipe($id){
        try {
            $findBatch = ProductionBatchModel::find($id);
            if (!$findBatch) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Data not found',
                ], 404);
            }
            $batchItemsId = $findBatch->batchItems->pluck('production_item_id')->toArray();
            $batchItems = $findBatch->batchItems->pluck('id')->toArray();
            $recipeItems = [];
            if (count($batchItemsId) > 0) {
                $recipeItems = ProductionExpense::getProItemsWiseRecipeItems($batchItemsId,$this->domain['pro_config'],$batchItems);
            }

            // Return success response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Production recipe items',
                'data' => $recipeItems,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
