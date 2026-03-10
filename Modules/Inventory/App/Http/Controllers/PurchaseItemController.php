<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Http\Requests\ProductRequest;
use Modules\Inventory\App\Http\Requests\PurchaseRequest;
use Modules\Inventory\App\Models\DamageItemModel;
use Modules\Inventory\App\Models\DamageModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\PurchaseReturnItemModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Models\ReportModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesReturnItemModel;
use Modules\Inventory\App\Models\SalesReturnModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use function Symfony\Component\HttpFoundation\Session\Storage\Handler\getInsertStatement;

class PurchaseItemController extends Controller
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
        $configId = $this->domain['inv_config'];
        $request = $request->all();
        $data = ReportModel::purchaseStockReport($configId,$request);
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
     * Store a newly created resource in storage.
     */
    public function store(PurchaseRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        $input['config_id'] = $this->domain['config_id'];
        $entity = PurchaseModel::create($input);

        $process = new PurchaseModel();
        $process->insertPurchaseItems($entity,$input['items']);
        $data = $service->returnJosnResponse($entity);
        return $data;

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = PurchaseModel::getShow($id, $this->domain);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        $data = $request->validated();
        $entity = PurchaseModel::find($id);
        $entity->update($data);

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        PurchaseModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

    public function itemsForDamage($stockItemId){
        $items = PurchaseItemModel::getPurchaseItemsForDamage($stockItemId,$this->domain);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Purchase items for damage.',
            'data' => $items,
        ]);
    }

    public function manualDamageProcess(Request $request, $stock_item_id)
    {
        DB::beginTransaction();

        try {
            $itemNatureType = $request->input('item_nature_type');
            $items = $request->input('data');
            $domain = $this->domain;

//            $stockItem = StockItemModel::findOrFail($stock_item_id);
            $stockItem = StockItemModel::with([
                    'currentWarehouseStock' => function ($q) use ($domain) {
                        if (!empty($domain['config_id'])) {
                            $q->where('config_id', $domain['config_id']);
                        }
                        $q->with('warehouse:id,name');
                    }
                ])
                ->findOrFail($stock_item_id);
            dump($stockItem);

            if ($itemNatureType === 'Stockable'){
                if (count($items) > 0) {
                    $damage = DamageModel::create([
                        'config_id' => $this->domain['config_id'],
                        'created_by_id' => $this->domain['user_id'] ?? null,
                        'quantity' => 0,
                        'sub_total' => 0,
                        'damage_type' => 'Manual-purchase',
                        'process' => 'Created',
                        'damage_mode' => 'Damage',
                    ]);
                    $totalDamageQty = 0;
                    $totalDamageAmt = 0;
                    foreach ($items as $item) {
                        $purchaseItem = PurchaseItemModel::findOrFail($item['purchase_item_id']);

                        $damageItem = DamageItemModel::create([
                            'config_id' => $this->domain['config_id'],
                            'warehouse_id' => $purchaseItem->warehouse_id,
                            'damage_mode' => 'Manual-purchase',
                            'purchase_item_id' => $purchaseItem->id,
                            'damage_id' => $damage->id,
                            'quantity' => $item['damage_quantity'],
                            'price' => $purchaseItem->purchase_price,
                            'purchase_price' => $purchaseItem->purchase_price,
                            'sub_total' => $item['damage_quantity'] * $purchaseItem->purchase_price,
                            'process' => 'Completed'
                        ]);

                            StockItemHistoryModel::openingStockQuantity(
                                (object)[
                                    'id' => $damageItem->id,
                                    'stock_item_id' => $stock_item_id,
                                    'name' => $stockItem->name,
                                    'config_id' => $this->domain['config_id'],
                                    'warehouse_id' => $purchaseItem->warehouse_id,
                                    'quantity' => $item['damage_quantity'],
                                ],
                                'damage',
                                $this->domain
                            );

                            DailyStockService::maintainDailyStock(
                                date: now()->toDateString(),
                                field: 'damage_quantity',
                                configId: $this->domain['config_id'],
                                warehouseId: $purchaseItem->warehouse_id,
                                stockItemId: $stockItem->id,
                                quantity: $item['damage_quantity']
                            );

                            $purchaseItem->update([
                                'damage_quantity' => ($purchaseItem->damage_quantity ?? 0) + $item['damage_quantity'],
                                'remaining_quantity' => ($purchaseItem->remaining_quantity ?? 0) - $item['damage_quantity']
                            ]);

                            $totalDamageQty += $item['damage_quantity'];
                            $totalDamageAmt += $item['damage_quantity'] * $purchaseItem->purchase_price;

                            $damage->update([
                                'quantity' => $totalDamageQty,
                                'sub_total' => $totalDamageAmt,
                                'process' => 'Approved'
                            ]);
                        }
                    }
                }

            /*if ($itemNatureType === 'Production'){
                if (count($items) > 0) {
                    $damage = DamageModel::create([
                        'config_id' => $this->domain['config_id'],
                        'created_by_id' => $this->domain['user_id'] ?? null,
                        'quantity' => 0,
                        'sub_total' => 0,
                        'damage_type' => 'Manual-production',
                        'process' => 'Created',
                        'damage_mode' => 'Damage',
                    ]);
                    $totalDamageQty = 0;
                    $totalDamageAmt = 0;
                    foreach ($items as $item) {

                        $damageItem = DamageItemModel::create([
                            'config_id' => $this->domain['config_id'],
                            'warehouse_id' => $purchaseItem->warehouse_id,
                            'damage_mode' => 'Manual-purchase',
                            'purchase_item_id' => $purchaseItem->id,
                            'damage_id' => $damage->id,
                            'quantity' => $item['damage_quantity'],
                            'price' => $purchaseItem->purchase_price,
                            'purchase_price' => $purchaseItem->purchase_price,
                            'sub_total' => $item['damage_quantity'] * $purchaseItem->purchase_price,
                            'process' => 'Completed'
                        ]);

                            StockItemHistoryModel::openingStockQuantity(
                                (object)[
                                    'id' => $damageItem->id,
                                    'purchase_item_id' => $purchaseItem->id,
                                    'name' => $stockItem->name,
                                    'config_id' => $this->domain['config_id'],
                                    'warehouse_id' => $purchaseItem->warehouse_id,
                                    'quantity' => $item['damage_quantity'],
                                ],
                                'damage',
                                $this->domain
                            );

                            DailyStockService::maintainDailyStock(
                                date: now()->toDateString(),
                                field: 'damage_quantity',
                                configId: $this->domain['config_id'],
                                warehouseId: $purchaseItem->warehouse_id,
                                stockItemId: $stockItem->id,
                                quantity: $item['damage_quantity']
                            );

                            $purchaseItem->update([
                                'damage_quantity' => ($purchaseItem->damage_quantity ?? 0) + $item['damage_quantity']
                            ]);

                            $totalDamageQty += $item['damage_quantity'];
                            $totalDamageAmt += $item['damage_quantity'] * $purchaseItem->purchase_price;

                            $damage->update([
                                'quantity' => $totalDamageQty,
                                'sub_total' => $totalDamageAmt,
                                'process' => 'Approved'
                            ]);
                        }
                    }
                }*/


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
