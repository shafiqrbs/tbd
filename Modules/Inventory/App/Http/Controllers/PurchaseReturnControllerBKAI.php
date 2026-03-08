<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\PurchaseReturnModel;
use Modules\Inventory\App\Services\PurchaseReturnService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class PurchaseReturnControllerBKAI extends Controller
{
    protected $domain;
    protected PurchaseReturnService $purchaseReturnService;

    public function __construct(Request $request, PurchaseReturnService $purchaseReturnService)
    {
        $this->purchaseReturnService = $purchaseReturnService;

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
            $purchaseReturn = PurchaseReturnModel::create($input);

            $totals = PurchaseReturnModel::insertPurchaseReturnItems($purchaseReturn, $input['items']);

            $purchaseReturn->update([
                'quantity'  => $totals['quantity'],
                'sub_total' => $totals['sub_total'],
            ]);

            DB::commit();

            return response()->json(['status' => 200, 'message' => 'Purchase return created successfully.']);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to create purchase return.',
                'error'   => $e->getMessage(),
            ], 500);
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

        $input['process'] = "Created";

        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturnModel::findOrFail($id);

            $purchaseReturn->purchaseReturnItems()->delete();

            $totals = PurchaseReturnModel::insertPurchaseReturnItems($purchaseReturn, $input['items']);

            $input['quantity']  = $totals['quantity'];
            $input['sub_total'] = $totals['sub_total'];

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
     * Approve the specified purchase return.
     */
    public function approve(Request $request, $id, $approveType)
    {
        if (!in_array($approveType, ['purchase', 'vendor'])) {
            return response()->json(['status' => 400, 'message' => 'Invalid approval type.'], 400);
        }

        DB::beginTransaction();
        try {
            $result = match ($approveType) {
                'purchase' => $this->purchaseReturnService->approvePurchase($id, $this->domain),
                'vendor'   => $this->purchaseReturnService->approveVendor($id, $this->domain),
            };

            if ($result['status'] !== 200) {
                DB::rollBack();
                return response()->json($result, $result['status']);
            }

            DB::commit();

            return response()->json($result);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to process purchase return.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
