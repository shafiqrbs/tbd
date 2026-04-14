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

class DamageController extends Controller
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


    public function manualExpiryDamageProcess()
    {
        $config =  $this->domain['inv_config'];
        PurchaseItemModel::whereDate('expired_date', '<', now())
            ->where('config_id',$config)
            ->whereNotNull('expired_date')
            ->where('remaining_quantity', '>', 0)
            ->select('id', 'remaining_quantity', 'stock_item_id', 'name', 'warehouse_id', 'config_id', 'purchase_price',
                'quantity','sales_return_quantity','bonus_quantity','warehouse_transfer_quantity',
                'sales_quantity','purchase_return_quantity','damage_quantity'
            )
            ->chunk(/**
             * @throws \Throwable
             */ 100, function ($items) {
                foreach ($items as $item) {
                    DB::transaction(function () use ($item) {
                        $qty = $item->remaining_quantity;
                        if ($qty <= 0) {
                            return;
                        }

                        $findStockItem = StockItemModel::find($item->stock_item_id);

                        // =========================
                        // PURCHASE DAMAGE PROCESS
                        // =========================
                        $this->processDamage(
                            type: 'purchase',
                            refId: $item->id,
                            qty: $qty,
                            stockItemId: $item->stock_item_id,
                            name: $item->name??($findStockItem->name??null),
                            warehouseId: $item->warehouse_id,
                            configId: $item->config_id,
                            price: $item->purchase_price ?? 0
                        );

                        // update purchase item (atomic + correct calc)
                        $newDamageQty = $item->damage_quantity + $qty;
                        $remainingQuantity = PurchaseItemModel::getPurchaseItemRemainingQuantity($item->id);
                        $item->update([
                            'damage_quantity' => $newDamageQty,
                            'remaining_quantity' => $remainingQuantity-$qty,
                        ]);
                    });
                }
            });
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse('success');

    }

    /**
     * Common Damage Processing
     */
    private function processDamage(
        string $type,
        int $refId,
        float $qty,
        int $stockItemId,
        $name,
        int $warehouseId,
        int $configId,
        float $price
    ): void {

//        $damageMode = $type === 'purchase' ? 'Purchase' : 'Stock-transfer';
        $damageMode = 'Purchase';

        $attributes = [
            'config_id' => $configId,
            'warehouse_id' => $warehouseId,
            'damage_mode' => $damageMode
        ];

        if ($type === 'purchase') {
            $attributes['purchase_item_id'] = $refId;
        }

        $damageItem = DamageItemModel::updateOrCreate(
            $attributes,
            [
                'quantity' => $qty,
                'price' => $price,
                'stock_item_id' => $stockItemId,
                'purchase_price' => $price,
                'sub_total' => $price * $qty,
                'warehouse_id' => $warehouseId,
                'damage_mode' => 'Expired',
                'process' => 'Created'
            ]
        );

        // stock history
        $domain = UserModel::getUserDataByConfigId($configId);

        StockItemHistoryModel::openingStockQuantity(
            (object)[
                'id' => $damageItem->id,
                'stock_item_id' => $stockItemId,
                'name' => $name ?? null,
                'config_id' => $configId,
                'warehouse_id' => $warehouseId,
                'quantity' => $qty,
            ],
            'damage',
            $domain
        );

        // daily stock
        DailyStockService::maintainDailyStock(
            date: now()->toDateString(),
            field: 'damage_quantity',
            configId: $configId,
            warehouseId: $warehouseId,
            stockItemId: $stockItemId,
            quantity: $qty
        );

        // mark completed
        $damageItem->update([
            'process' => 'Completed'
        ]);
    }


}
