<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Http\Requests\AccountHeadRequest;
use Modules\Accounting\App\Http\Requests\AccountJournalRequest;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalItemModel;
use Modules\Accounting\App\Models\AccountJournalModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\SettingModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\ConfigSalesModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Throwable;


class AccountVoucherEntryController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $entityId = $request->header('X-Api-User');
        if ($entityId && !empty($entityId)){
            $entityData = UserModel::getUserData($entityId);
            $this->domain = $entityData;
        }
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request){

        $data = AccountJournalModel::getRecords($request,$this->domain);
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
     * Store a newly created resource in storage.
     */
    public function store(AccountJournalRequest $request)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['acc_config'];
        $input['created_by_id'] = $this->domain['user_id'];
        $input['process'] = "Created";

        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {
            // Create journal Record
            $journal = AccountJournalModel::create($input);
            $journal->refresh();

            // Insert journal Items
            AccountJournalItemModel::insertJournalItems($journal, $input['items']);

            DB::commit();

            // Send Success Response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Journal created successfully.',
                'data' => $journal,
            ]);
        } catch (Exception $e) {
            // Rollback Transaction on Failure
            DB::rollBack();

            // Log the Error (For Debugging Purposes)
            \Log::error('Journal transaction failed: ' . $e->getMessage());

            // Send Error Response
            return response()->json([
                'message' => 'An error occurred while processing the journal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = SettingModel::find($id);
        if (!$entity){
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
        $service = new JsonRequestResponse();
        $entity = SettingModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountHeadRequest $request, $id)
    {

        $data = $request->validated();
        $entity = SettingModel::find($id);
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
        SettingModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public function accountVoucherApprove(Request $request,$id): JsonResponse
    {
        $journal = AccountJournalModel::with('journalItems')->find($id);

        if (! $journal) {
            return response()->json([
                'status'  => 404,
                'success' => false,
                'message' => 'Journal not found.',
            ], 404);
        }

        if ($journal->journalItems->isEmpty()) {
            return response()->json([
                'status'  => 404,
                'success' => false,
                'message' => 'No journal entries found.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($journal->journalItems as $journalItem) {


                $opening = AccountJournalItemModel::getLedgerWiseOpeningBalance(
                    ledgerId: $journalItem->account_ledger_id,
                    configId: $journal->config_id,
                    journalItemId: $journalItem->id
                );

                $closing = $journalItem->mode === 'debit'
                    ? $opening + $journalItem->amount
                    : ($journalItem->mode === 'credit' ? $opening - $journalItem->amount : 0);

                $journalItem->update([
                    'opening_amount' => $opening,
                    'closing_amount' => $closing,
                ]);

                $findAccoundLegderHead = AccountHeadModel::find($journalItem->account_sub_head_id);
                $findAccoundLegderHead->update([
                    'amount' => $closing,
                ]);
            }

            $journal->update([
                'approved_by_id' => $this->domain['user_id'] ?? null,
                'process'        => 'Approved',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => 'Approved successfully.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            logger()->error('Account journal approval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 500,
                'success' => false,
                'message' => 'An error occurred while processing approval.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


}
