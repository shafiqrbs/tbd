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
use Modules\Accounting\App\Models\DailyLedger;
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
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
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

        if (empty($input['branch_id'])){
            $input['branch_id'] = $this->domain['domain_id'];
            $input['is_branch'] = false;
        }else{
            $input['is_branch'] = true;
        }

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
        $entity = AccountJournalModel::show($id);
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
        $findJournal = AccountJournalModel::find($id);
        if ($findJournal->approved_by_id){
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Journal already approved & not deleted.',
            ]);
        }
        $findJournal->delete();
        $entity = ['message'=>'Journal delete success.'];
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
                // manage journal opening & closing quantity
                AccountJournalModel::journalOpeningClosing($journal,$journalItem);

                // for daily ledger maintain
                DailyLedger::dailyLedgerManage(
                    configId: $journal->config_id,
                    accountHeadId: $journalItem->account_head_id,
                    accountSubHeadId: $journalItem->account_sub_head_id,
                    debit: $journalItem->debit,
                    credit: $journalItem->credit,
                    openingAmount: $journalItem->opening_amount
                );
            }
            $journal->update([
                'approved_by_id' => $this->domain['user_id'] ?? null,
                'process'        => 'Approved',
                'approved_date'  => now()
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

    public function reconciliationItems(Request $request)
    {
        $params = $request->only(['start_date', 'branch_id','head_id']);
        $getItems = AccountJournalItemModel::getVoucherEntryReconciliationItems($params, $this->domain);
        if (count($getItems) > 0) {
            return response()->json([
                'status'  => ResponseAlias::HTTP_OK,
                'success' => true,
                'message' => 'Reconciliation items retrieved.',
                'data' => $getItems
            ], ResponseAlias::HTTP_OK);
        }else{
            return response()->json([
                'status'  => ResponseAlias::HTTP_NOT_FOUND,
                'success' => false,
                'message' => 'Reconciliation items not found.',
            ],ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function reconciliationItemsInlineUpdate(Request $request)
    {
        $input = $request->only(['journal_item_id','journal_id','amount']);

        return DB::transaction(function () use ($input) {

            $findJournalItem = AccountJournalItemModel::find($input['journal_item_id']);
            if (!$findJournalItem) {
                return response()->json([
                    'status'  => 404,
                    'success' => false,
                    'message' => 'Journal item not found.',
                ]);
            }

            // ---------------- Update child item ----------------
            $amount = abs($input['amount']); // always positive

            if ($findJournalItem->mode === 'credit') {
                $findJournalItem->update([
                    'amount' => -$amount,
                    'credit' => $amount,
                ]);
            } else {
                $findJournalItem->update([
                    'amount' => $amount,
                    'debit' => $amount,
                    'credit' => 0,
                ]);
            }

            // ---------------- Update parent item ----------------
            if ($findJournalItem->parent_id) {
                $sumChildAmount = AccountJournalItemModel::where('parent_id', $findJournalItem->parent_id)
                    ->sum(DB::raw('ABS(amount)')); // sum absolute values

                $findParentItem = AccountJournalItemModel::find($findJournalItem->parent_id);
                if (!$findParentItem) {
                    return response()->json([
                        'status'  => 404,
                        'success' => false,
                        'message' => 'Parent item not found.'
                    ]);
                }

                if ($findParentItem->mode === 'credit') {
                    $findParentItem->update([
                        'amount' => -$sumChildAmount,
                        'credit' => $sumChildAmount,
                        'debit' => 0,
                    ]);
                } else {
                    $findParentItem->update([
                        'amount' => $sumChildAmount,
                        'debit' => $sumChildAmount,
                        'credit' => 0,
                    ]);
                }
            }

            // ---------------- Update journal totals ----------------
            $findJournal = AccountJournalModel::find($findJournalItem->account_journal_id);
            if (!$findJournal) {
                return response()->json([
                    'status'  => 404,
                    'success' => false,
                    'message' => 'Journal not found.'
                ]);
            }

            $findJournal->update([
                'debit' => $sumChildAmount,
                'credit' => $sumChildAmount,
            ]);

            return response()->json([
                'status'  => ResponseAlias::HTTP_OK,
                'success' => true,
                'message' => 'Reconciliation items updated successfully.',
            ],ResponseAlias::HTTP_OK);
        });
    }

    public function reconciliationItemsApprove(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return response()->json([
                'status'  => ResponseAlias::HTTP_NOT_FOUND,
                'success' => false,
                'message' => 'No reconciliation items found.'
            ]);
        }

        $journals = AccountJournalModel::with('journalItems')->whereIn('id', $ids)->get();

        if ($journals->isEmpty()) {
            return response()->json([
                'status'  => 404,
                'success' => false,
                'message' => 'Journals not found.',
            ]);
        }

        DB::beginTransaction();

        try {
            foreach ($journals as $journal) {
                if ($journal->journalItems->isEmpty()) {
                    throw new \Exception("No journal items found for journal ID: {$journal->id}");
                }

                if (!$journal->approved_by_id) {
                    foreach ($journal->journalItems as $journalItem) {
                        // manage journal opening & closing quantity
                        AccountJournalModel::journalOpeningClosing($journal, $journalItem);

                        // for daily ledger maintain
                        DailyLedger::dailyLedgerManage(
                            configId: $journal->config_id,
                            accountHeadId: $journalItem->account_head_id,
                            accountSubHeadId: $journalItem->account_sub_head_id,
                            debit: $journalItem->debit,
                            credit: $journalItem->credit,
                            openingAmount: $journalItem->opening_amount
                        );
                    }

                    // update journal as approved
                    $journal->update([
                        'approved_by_id' => $this->domain['user_id'] ?? null,
                        'process' => 'Approved',
                        'approved_date' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => 'All selected journals approved successfully.',
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
