<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\InvoiceBatchTransactionModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionBatch;
use Modules\Production\App\Http\Requests\BatchItemQuantityInlineUpdateRequest;
use Modules\Production\App\Http\Requests\BatchItemRequest;
use Modules\Production\App\Http\Requests\BatchRequest;
use Modules\Production\App\Models\ProductionBatchItemModel;
use Modules\Production\App\Models\ProductionBatchModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionExpense;
use Modules\Production\App\Models\ProductionItems;
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
        $input['process'] = 'created';
        $input['created_by_id'] = $this->domain['user_id'];
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

        if ($input['process'] == 'approved') {
            $input['approved_by_id'] = $this->domain['user_id'];

            foreach($batch->batchItems as $batchItem) {
                foreach($batchItem->productionItems as $productionItem) {
                    $stockItem = StockItemModel::find($productionItem->material_id);
                    $productionItem->needed_quantity = $batchItem->issue_quantity * $productionItem->quantity;
                    StockItemHistoryModel::openingStockQuantity($stockItem,'production',$this->domain);
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
        //
    }
}
