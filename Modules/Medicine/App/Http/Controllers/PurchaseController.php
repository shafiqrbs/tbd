<?php

namespace Modules\Medicine\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Medicine\App\Http\Requests\PurchaseRequest;
use Modules\Medicine\App\Models\PurchaseItemModel;
use Modules\Medicine\App\Models\PurchaseModel;
use Symfony\Component\HttpFoundation\Response;

class PurchaseController extends Controller
{
    protected UserModel $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if (!empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function index(Request $request)
    {
        $data = PurchaseModel::getRecords($request, $this->domain);
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
        $input = $request->validated();
        DB::beginTransaction();
        try {
            // Common fields
            $input['config_id'] = $this->domain['config_id'];
            $input['process'] = 'Created';
            $input['warehouse_id'] = $input['warehouse_id'] ?? $this->domain['warehouse_id'];
            $input['mode'] = 'Purchase';
            $input['status'] = 1;

            // 1️⃣ Create purchase master record
            $purchase = PurchaseModel::create($input);

            // 2️⃣ Insert related purchase items
            PurchaseModel::syncPurchaseItems($purchase, $input['items'], $input['warehouse_id']);

            // 3️⃣ Commit transaction only if everything succeeds
            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'success' => true,
                'message' => 'Purchase created successfully!',
            ]);
        } catch (\Throwable $e) {
            // Rollback on any failure
            DB::rollBack();

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'Failed to create purchase. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $purchase = PurchaseModel::getShow($id, $this->domain);
        if (empty($purchase)){
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'message' => 'Data not found.',
            ]);
        }
        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'message' => 'Purchase data retrieved successfully!',
            'data' => $purchase
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseRequest $request, $id)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $purchase = PurchaseModel::find($id);
            if (!$purchase) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'message' => 'Data not found.',
                ]);
            }

            // Update purchase master (vendor, comment, etc.)
            $data['warehouse_id'] = $data['warehouse_id'] ?? $this->domain['warehouse_id'];
            $purchase->update($data);

            // Sync related items (insert, update, delete)
            PurchaseModel::syncPurchaseItems($purchase, $data['items'], $data['warehouse_id']);

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'success' => true,
                'message' => 'Purchase updated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'Failed to update purchase. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $purchase = PurchaseModel::find($id);
        if (!$purchase) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'message' => 'Data not found.',
            ]);
        }
        if ($purchase->process === 'Received') {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'message' => 'Cannot delete purchase — it has already been received.',
            ]);
        }
        DB::beginTransaction();

        try {
            // delete related items first
            PurchaseItemModel::where('purchase_id', $id)->delete();

            // delete main record
            $purchase->delete();

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'success' => true,
                'message' => 'Purchase deleted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'Failed to delete purchase. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approve the specified resource from storage.
     */

    public function approve($id)
    {

        DB::beginTransaction();
        try {
            $purchase = PurchaseModel::find($id);
            if (!$purchase) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'message' => 'Data not found.',
                ]);
            }

            // Update master
            $data['approved_by_id'] = $this->domain['user_id'];
            $data['process'] = 'Approved';
            $purchase->update($data);

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'success' => true,
                'message' => 'Purchase approved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'Failed to approve purchase. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function receive($id)
    {
        DB::beginTransaction();
        try {
            $purchase = PurchaseModel::find($id);
            if (!$purchase) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'message' => 'Data not found.',
                ]);
            }

            if ($purchase->process === 'Received') {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'message' => 'This purchase has already been received.',
                ]);
            }

            // Update master
            $data['received_by_id'] = $this->domain['user_id'];
            $data['received_date'] = now();
            $data['process'] = 'Received';
            $purchase->update($data);


            // Maintain stock for all received items
            $purchase->refresh();
            foreach ($purchase->purchaseItems as $item) {
                $item->update(['approved_by_id' => $this->domain['user_id']]);
                StockItemHistoryModel::openingStockQuantity($item, 'purchase', $this->domain);

                DailyStockService::maintainDailyStock(
                    date: now()->format('Y-m-d'),
                    field: 'purchase_quantity',
                    configId: $this->domain['config_id'],
                    warehouseId: $item->warehouse_id ?? $this->domain['warehouse_id'],
                    stockItemId: $item->stock_item_id,
                    quantity: $item->quantity
                );
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'success' => true,
                'message' => 'Purchase received successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'message' => 'Failed to receive purchase. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
