<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\AppsApi\App\Services\JsonRequestResponse;

use Modules\Core\App\Models\UserModel;

use Modules\Hospital\App\Models\EpharmaModel;

use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoicePathologicalReportModel;

use Modules\Hospital\App\Models\PrescriptionModel;
use Modules\Inventory\App\Models\PurchaseItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;


class EpharamaController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)){
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){

        $domain = $this->domain;
        $data = EpharmaModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
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
     * Show the specified resource.
     *//**/
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = EpharmaModel::getShow($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $domain = $this->domain;
        $input = $request->only(['comment']);

        // --- Fetch invoice ---
        $invoice = InvoiceModel::where('barcode', $id)->first();
        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found',
                'status' => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        // --- Fetch OPD warehouse ---
        $opdWarehouseId = HospitalConfigModel::find($domain['hms_config'])?->opd_store_id;

        if (empty($opdWarehouseId)) {
            return response()->json([
                'message' => 'OpdWarehouse not found',
                'status' => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        // --- Fetch Sales ---
        $sales = SalesModel::with('salesItems')->find($invoice->sales_id);
        if (!$sales) {
            return response()->json([
                'message' => 'Sales record not found',
                'status' => ResponseAlias::HTTP_NOT_FOUND
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();

        try {
            if ($invoice->is_medicine_delivered != 1) {
                $invoice->update([
                    'process' => 'Done',
                    'is_medicine_delivered' => 1,
                    'medicine_delivered_by_id' => $domain['user_id'],
                    'medicine_delivered_comment' => $input['comment'] ?? null,
                    'medicine_delivered_date' => now(),
                ]);

                foreach ($sales->salesItems as $item) {

                    // Update warehouse + config
                    $item->warehouse_id = $opdWarehouseId;
                    $item->config_id = $domain['config_id'];
                    $item->save();

                    // Validate update
                    if (!$item->warehouse_id) {
                        throw new Exception("Warehouse update failed for item ID: {$item->id}");
                    }

                    //--- STOCK HISTORY
                    StockItemHistoryModel::openingStockQuantity(
                        $item,
                        'sales',
                        $domain
                    );

                    //----DAILY STOCK MAINTAIN
                    DailyStockService::maintainDailyStock(
                        date: now()->format('Y-m-d'),
                        field: 'sales_quantity',
                        configId: $domain['config_id'],
                        warehouseId: $item->warehouse_id,
                        stockItemId: $item->stock_item_id,
                        quantity: $item->quantity
                    );
                }

                $sales->update([
                    'approved_by_id' => $this->domain['user_id'],
                    'process' => 'Closed'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Updated successfully',
                'status' => ResponseAlias::HTTP_OK
            ], ResponseAlias::HTTP_OK);

        } catch (Throwable $e) {

            // Rollback everything on error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update',
                'error' => $e->getMessage(),
                'status' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function inlineUpdate(Request $request,$id)
    {
        $input = $request->all();
        $findParticular = InvoicePathologicalReportModel::find($id);
        $findParticular->result = $input['result'];
        $findParticular->save();
        return response()->json(['success' => $findParticular]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        PrescriptionModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
