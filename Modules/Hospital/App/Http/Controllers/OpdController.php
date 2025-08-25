<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\CustomerRequest;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Entities\Prescription;
use Modules\Hospital\App\Http\Requests\OPDRequest;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PrescriptionModel;



class OpdController extends Controller
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
       // $data = CustomerModel::getRecords($this->domain,$request);
        $domain = $this->domain;
        $data = InvoiceModel::getRecords($request,$domain);
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
     * Display a listing of the resource.
     */
    public function getVisitingRooms(Request $request){
        $domain = $this->domain;
        $data = InvoiceModel::getVisitingRooms($domain);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(OPDRequest $request)
    {

        $service = new JsonRequestResponse();
        $input = $request->validated();
        DB::beginTransaction();
        try {

            $input['domain_id'] = $this->domain['global_id'];
            $entity = PatientModel::create($input);
            $invConfig = $this->domain['inv_config'];
            $hmsConfig = $this->domain['hms_config'];
            $config = HospitalConfigModel::find($hmsConfig);
            if($entity){
                OPDModel::insertHmsInvoice($invConfig,$config, $entity,$input);
            }
            $accountingConfig = AccountingModel::where('id', $this->domain['acc_config'])->first();
            $ledgerExist = AccountHeadModel::where('customer_id', $entity->id)->where('config_id', $this->domain['acc_config'])->where('parent_id', $config->account_customer_id)->first();
            if (empty($ledgerExist)) {
               AccountHeadModel::insertCustomerLedger($accountingConfig, $entity);
            }
            DB::commit();
            $data = $service->returnJosnResponse($entity);
            return $data;
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }

    }

    /**
     * Show the specified resource.
     *//**/
    public function show($id)
    {
        $entity = CustomerModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function sendPrescription(Request $request,$id)
    {
        $service = new JsonRequestResponse();
        $userId = $request->header('X-Api-User');


        $patientPaymentMode = ParticularModel::getDoctorNurseLabUser($userId,'doctor');
        $doctorId = $patientPaymentMode ? $patientPaymentMode->id : null;
        $entity = PrescriptionModel::updateOrCreate(
            ['hms_invoice_id' => $id],
            [
                'created_by_id' => $userId ,
                'doctor_id' => $doctorId
            ]
        );
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



    /**
     * Show the specified resource.
     */
    public function prescription(Request $request , $id)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();
        DB::beginTransaction();
        try {

            $input['domain_id'] = $this->domain['global_id'];
            $entity = PatientModel::create($input);
            $invConfig = $this->domain['inv_config'];
            $hmsConfig = $this->domain['hms_config'];
            $config = HospitalConfigModel::find($hmsConfig);
            if($entity){
                OPDModel::insertHmsInvoice($invConfig,$config, $entity,$input);
            }
            $accountingConfig = AccountingModel::where('id', $this->domain['acc_config'])->first();
            $ledgerExist = AccountHeadModel::where('customer_id', $entity->id)->where('config_id', $this->domain['acc_config'])->where('parent_id', $config->account_customer_id)->first();
            if (empty($ledgerExist)) {
                AccountHeadModel::insertCustomerLedger($accountingConfig, $entity);
            }
            DB::commit();
            $data = $service->returnJosnResponse($entity);
            return $data;
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Optionally log the exception for debugging purposes
            \Log::error('Error storing domain and related data: ' . $e->getMessage());

            // Return an error response
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'An error occurred while saving the domain and related data.',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }



    /**
     * Show the specified resource.
     */
    public function details($id)
    {
        $service = new JsonRequestResponse();
        $entity = CustomerModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function ledger($id)
    {
        $service = new JsonRequestResponse();
        $entity = CustomerModel::find($id);
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
        $entity = CustomerModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, $id)
    {


        $data = $request->validated();
        $entity = CustomerModel::find($id);
        $data['customer_unique_id'] = "{$entity['domain_id']}@{$data['mobile']}-{$data['name']}";
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
        CustomerModel::find($id)->delete();

        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);
    }


    /**
     * Display a listing of the resource.
     */
    public function localStorage(Request $request,EntityManagerInterface $em){
        $data = CustomerModel::getRecordsForLocalStorage($this->domain,$request);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


}
