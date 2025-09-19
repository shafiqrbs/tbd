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
use Modules\Inventory\App\Http\Requests\PurchaseRequest;
use Modules\Inventory\App\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\App\Models\ConfigPurchaseModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
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
        $input['narration']       = "Created";

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




    /*PREVIOUS CODE*/

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

    /**
     * Approve the specified resource from storage.
     */
    public function approve($id)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // Start the database transaction
        DB::beginTransaction();

        try {
            $purchase = PurchaseModel::find($id);
            $purchase->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Approved'
            ]);

            if (sizeof($purchase->purchaseItems)>0){
                foreach ($purchase->purchaseItems as $item){
                    // get average price
                    $itemAveragePrice = StockItemModel::calculateStockItemAveragePrice($item->stock_item_id,$item->config_id,$item);
                    //set average price
                    StockItemModel::where('id', $item->stock_item_id)->where('config_id',$item->config_id)->update([
                        'average_price' => $itemAveragePrice,
                        'purchase_price' => $item['purchase_price'],
                        'price' => $item['sales_price'] ?? 0,
                        'sales_price' => $item['sales_price'] ?? 0
                    ]);

                    $item->update(['approved_by_id' => $this->domain['user_id']]);
                    StockItemHistoryModel::openingStockQuantity($item,'purchase',$this->domain);

                    // for maintain inventory daily stock
                    date_default_timezone_set('Asia/Dhaka');
                    DailyStockService::maintainDailyStock(
                        date: date('Y-m-d'),
                        field: 'purchase_quantity',
                        configId: $this->domain['config_id'],
                        warehouseId: $purchase->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );
                }
                AccountJournalModel::insertPurchaseAccountJournal($this->domain,$purchase->id);
            }
            // Commit the transaction after all updates are successful
            DB::commit();
            $response->setContent(json_encode([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'Approved successfully',
            ]));
            $response->setStatusCode(ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            $response->setContent(json_encode([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    public function purchaseCopy($id)
    {
        try {
            DB::beginTransaction();

            $original = PurchaseModel::with('purchaseItems')->findOrFail($id);

            $newPurchase = $original->replicate([
                'approved_by_id','process','invoice_date'
            ]);
            $newPurchase->approved_by_id = null;
            $newPurchase->invoice_date = now();
            $newPurchase->process = 'Created';
            $newPurchase->save();

            foreach ($original->purchaseItems as $item) {
                $newItem = $item->replicate(['purchase_id','approved_by_id']);
                $newItem->purchase_id = $newPurchase->id;
                $newItem->approved_by_id = null;
                $newItem->save();
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Purchase duplicated successfully!',
                'new_purchase_id' => $newPurchase->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Duplication failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
