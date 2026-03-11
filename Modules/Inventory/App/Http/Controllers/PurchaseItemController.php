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

            $request->validate([
                'item_nature_type' => 'required|string',
                'data' => 'required|array|min:1',
                'data.*.damage_quantity' => 'required|numeric|min:0.01'
            ]);

            $itemNatureType = $request->input('item_nature_type');
            $items = $request->input('data');
            $domain = $this->domain;


            // GET STOCK ITEM
            $stockItem = StockItemModel::with([
                'currentWarehouseStock' => function ($q) use ($domain) {
                    if (!empty($domain['config_id'])) {
                        $q->where('config_id', $domain['config_id']);
                    }
                    $q->with('warehouse:id,name');
                }
            ])->findOrFail($stock_item_id);

            // CREATE DAMAGE MASTER
            $damage = DamageModel::create([
                'config_id' => $domain['config_id'],
                'created_by_id' => $domain['user_id'] ?? null,
                'quantity' => 0,
                'sub_total' => 0,
                'damage_type' => $itemNatureType === 'Stockable'
                    ? 'Manual-purchase'
                    : 'Manual-production',
                'process' => 'Created',
                'damage_mode' => 'Damage',
            ]);

            $totalDamageQty = 0;
            $totalDamageAmt = 0;


            // STOCKABLE ITEMS DAMAGE
            if ($itemNatureType === 'Stockable') {

                $purchaseItemIds = collect($items)->pluck('purchase_item_id')->toArray();

                $purchaseItems = PurchaseItemModel::whereIn('id', $purchaseItemIds)
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {

                    $purchaseItem = $purchaseItems[$item['purchase_item_id']] ?? null;

                    if (!$purchaseItem) {
                        continue;
                    }

                    $damageQty = $item['damage_quantity'];
                    $price = $purchaseItem->purchase_price;

                    $damageItem = DamageItemModel::create([
                        'config_id' => $domain['config_id'],
                        'warehouse_id' => $purchaseItem->warehouse_id,
                        'damage_mode' => 'Manual-purchase',
                        'purchase_item_id' => $purchaseItem->id,
                        'damage_id' => $damage->id,
                        'quantity' => $damageQty,
                        'price' => $price,
                        'purchase_price' => $price,
                        'sub_total' => $damageQty * $price,
                        'process' => 'Completed'
                    ]);

                    /* ----- STOCK HISTORY ----- */

                    StockItemHistoryModel::openingStockQuantity(
                        (object)[
                            'id' => $damageItem->id,
                            'stock_item_id' => $stock_item_id,
                            'name' => $stockItem->name,
                            'config_id' => $domain['config_id'],
                            'warehouse_id' => $purchaseItem->warehouse_id,
                            'quantity' => $damageQty,
                        ],
                        'damage',
                        $domain
                    );

                    /* ----- DAILY STOCK ----- */

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'damage_quantity',
                        configId: $domain['config_id'],
                        warehouseId: $purchaseItem->warehouse_id,
                        stockItemId: $stockItem->id,
                        quantity: $damageQty
                    );

                    /* ----- UPDATE PURCHASE ITEM ----- */

                    $purchaseItem->update([
                        'damage_quantity' => ($purchaseItem->damage_quantity ?? 0) + $damageQty,
                        'remaining_quantity' => ($purchaseItem->remaining_quantity ?? 0) - $damageQty
                    ]);

                    $totalDamageQty += $damageQty;
                    $totalDamageAmt += $damageQty * $price;
                }
            }


            // PRODUCTION ITEMS DAMAGE
            if ($itemNatureType === 'Production') {

                foreach ($items as $item) {

                    if (empty($item['warehouse_id'])) {
                        continue;
                    }

                    $damageQty = $item['damage_quantity'];
                    $price = $stockItem->purchase_price;

                    $damageItem = DamageItemModel::create([
                        'config_id' => $domain['config_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'damage_mode' => 'Manual-production',
                        'stock_item_id' => $stockItem->id,
                        'damage_id' => $damage->id,
                        'quantity' => $damageQty,
                        'price' => $price,
                        'purchase_price' => $price,
                        'sub_total' => $damageQty * $price,
                        'process' => 'Completed'
                    ]);

                    /* ----- STOCK HISTORY ----- */

                    StockItemHistoryModel::openingStockQuantity(
                        (object)[
                            'id' => $damageItem->id,
                            'stock_item_id' => $stockItem->id,
                            'name' => $stockItem->name,
                            'config_id' => $domain['config_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'quantity' => $damageQty,
                        ],
                        'damage',
                        $domain
                    );

                    /* ----- DAILY STOCK ----- */

                    DailyStockService::maintainDailyStock(
                        date: now()->toDateString(),
                        field: 'damage_quantity',
                        configId: $domain['config_id'],
                        warehouseId: $item['warehouse_id'],
                        stockItemId: $stockItem->id,
                        quantity: $damageQty
                    );

                    $totalDamageQty += $damageQty;
                    $totalDamageAmt += $damageQty * $price;
                }
            }

            /* -----------------------------
             UPDATE DAMAGE MASTER
            ------------------------------*/

            $damage->update([
                'quantity' => $totalDamageQty,
                'sub_total' => $totalDamageAmt,
                'process' => 'Approved'
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Manual damage processed successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
