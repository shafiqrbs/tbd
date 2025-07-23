<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Entities\AccountVoucher;
use Modules\Accounting\App\Http\Requests\AccountHeadRequest;
use Modules\Accounting\App\Models\AccountHeadDetailsModel;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountJournalItemModel;
use Modules\Accounting\App\Models\AccountVoucherModel;
use Modules\Accounting\App\Models\LedgerDetailsModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;


class AccountHeadController extends Controller
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

        $data = AccountHeadModel::getRecords($request,$this->domain);
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'total' => $data['count'],
            'data' => $data['entities']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountHeadRequest $request)
    {
        $data = $request->validated();
        $data['config_id'] = $this->domain['acc_config'];
        $entity = AccountHeadModel::create($data);
        AccountHeadDetailsModel::updateOrCreate([
            'account_head_id' => $entity->id,
            'config_id' => $this->domain['acc_config'],
        ]);
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Account head created successfully.',
            'data' => $entity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountHeadRequest $request, $id)
    {
        try {
            // Validate and get the validated data
            $validatedData = $request->validated();

            // Find the entity or fail
            $entity = AccountHeadModel::findOrFail($id);

            // Update the entity
            $updated = $entity->update($validatedData);

            if (!$updated) {
                throw new \RuntimeException('Failed to update account head');
            }
            AccountHeadDetailsModel::updateOrCreate([
                'account_head_id' => $entity->id,
                'config_id' => $this->domain['acc_config'],
            ]);

            // Reload the model to get any database-default values
            $entity->refresh();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Account head updated successfully.',
                'data' => $entity,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Account head not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update account head.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function generateAccountHead(EntityManager $em)
    {
        $config_id = $this->domain['acc_config'];
        $entity = $em->getRepository(AccountHead::class)->generateAccountHead($config_id);
        AccountingModel::initiateConfig($this->domain);
        $em->getRepository(AccountHead::class)->resetAccountLedgerHead($config_id);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

     /**
     * Store a newly created resource in storage.
     */
    public function resetAccountLedgerHead(Request $request,EntityManager $em)
    {
        $config_id = $this->domain['acc_config'];
        AccountingModel::initiateConfig($this->domain);
        AccountHeadModel::initialLedgerSetup($this->domain);
        $service = new JsonRequestResponse();
        $data = AccountHeadModel::getRecords($request,$this->domain);
        return $service->returnJosnResponse($data);

    }

     /**
     * Store a newly created resource in storage.
     */
    public function resetAccountHead(EntityManager $em)
    {
        $config_id = $this->domain['acc_config'];
        $entity = $em->getRepository(AccountHead::class)->generateAccountHead($config_id);
        AccountingModel::initiateConfig($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resetAccountVoucher(EntityManager $em)
    {

        AccountVoucherModel::resetVoucher($this->domain);
        AccountingModel::initiateConfig($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse('success');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = AccountHeadModel::with('accountHeadDetails')->find($id);
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
        $entity = AccountHeadModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        AccountHeadModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    public function LocalStorage(Request $request){
        $data = AccountHeadModel::getRecordsForLocalStorage($request,$this->domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function accountLedgerWiseJournal($id)
    {
        $getJournalItems = AccountJournalItemModel::getLedgerWiseJournalItems( ledgerId:$id,configId: $this->domain['acc_config'] );
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Ledger wise journal items retrieved.',
            'data' => $getJournalItems,
        ]);
    }

}
