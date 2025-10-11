<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
use Modules\Hospital\App\Http\Requests\ReferredRequest;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoicePatientReferredModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PrescriptionModel;
use function Symfony\Component\TypeInfo\null;


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
        $domain = $this->domain;
        $data = InvoiceModel::getRecords($request,$domain);
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message'   => 'success',
            'status'    => Response::HTTP_OK,
            'total'     => $data['count'],
            'data'      => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Display a listing of the resource.
     */

    public function getVisitingRooms(Request $request){
        $domain = $this->domain;
        $data = InvoiceModel::getOpdRooms($domain);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function referred(ReferredRequest $request,$id)
    {
        $input = $request->validated();
        $entity = InvoiceModel::find($id);
        $opd_room_id= (isset($input['opd_room_id']) && $input['opd_room_id']) ? $input['opd_room_id']:null;
        if ($entity && isset($input['referred_mode'])) {
            $input['hms_invoice_id'] = $id;
            $input['created_by_id'] =  $this->domain['user_id'];
            if($opd_room_id){
                $prescription = PrescriptionModel::where('hms_invoice_id', $id)->first();
                $input['json_content'] =  $prescription['json_content'];
                $input['prescription_id'] =  $prescription['id'];
            }
            InvoicePatientReferredModel::updateOrCreate(
                [
                    'hms_invoice_id' => $id, // condition to check existing record
                ],
                $input // fields to update if exists OR create if not
            );
            if($input['referred_mode'] == "room"){
                $entity->forceFill([
                    'referred_mode' => $input['referred_mode'], 'room_id' => $opd_room_id,'process' => 'in-progress',
                ])->save();
            }else{
                $entity->forceFill([
                    'referred_mode' => $input['referred_mode'], 'room_id' => $opd_room_id,'process' => 'ipd',
                ])->save();
            }

        }
        $service = new JsonRequestResponse();
        $entity = InvoiceModel::getShow($id);
        $data = $service->returnJosnResponse($entity);
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
            $dob = (isset($input['dob']) and $input['dob']) ? $input['dob'] : null;
            if($dob =="invalid" || $dob == null){
                $dob = null;
            }else{
                $dob = new \DateTime($input['dob']);
            }
            $exist = PatientModel::where(['name' => $input['name']],['mobile' => $input['mobile']])->first();
            if($exist){
                $entity = $exist;
            }else{
                $customerId = (isset($input['customer_id']) and $input['customer_id']) ? $input['customer_id'] : '';
                $entity = PatientModel::updateOrCreate(
                    ['id' => $customerId ?? null],
                    [
                        'domain_id' => $this->domain['global_id'],
                        'name'      => $input['name'] ?? null,
                        'mobile'    => $input['mobile'] ?? null,
                        'address'    => $input['address'] ?? null,
                        'dob'    => $dob ?? null,
                        'upazilla_id'    => $input['upazilla_id'] ?? null,
                        'gender'    => $input['gender'] ?? null,
                        'identity_mode'    => $input['identity_mode'] ?? null,
                        'health_id'    => $input['health_id'] ?? null,
                        'nid'    => $input['identity'] ?? null,
                    ]
                );
            }
            $invConfig = $this->domain['inv_config'];
            $hmsConfig = $this->domain['hms_config'];
            $config = HospitalConfigModel::find($hmsConfig);
            if($entity){
                $invoiceId = OPDModel::insertHmsInvoice($invConfig,$config, $entity,$input);
            }
            $accountingConfig = AccountingModel::where('id', $this->domain['acc_config'])->first();
            $ledgerExist = AccountHeadModel::where('customer_id', $entity->id)->first();
            if (empty($ledgerExist)) {
               AccountHeadModel::insertCustomerLedger($accountingConfig, $entity);
            }
            DB::commit();
            $getNextOpdRoom = InvoiceModel::getNextOpdRoom($this->domain);
            $invoice = InvoiceModel::getShow($invoiceId);
            $invoice['opd_selected_room'] = $getNextOpdRoom['name'];
            $invoice['opd_selected_room_id']=$getNextOpdRoom['id'];
            $service = new JsonRequestResponse();
            return $service->returnJosnResponse($invoice);
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
        $entity = InvoiceModel::getShow($id);
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
        $doctor = ParticularModel::getDoctorNurseLabUser($userId,'doctor');
        $doctorId = $doctor ? $doctor->id : null;
        $entity = PrescriptionModel::updateOrCreate(
            ['hms_invoice_id' => $id],
            [
                'created_by_id' => $userId ,
                'process' => "new",
                'doctor_id' => $doctorId
            ]
        );
        $invoiceInfo = InvoiceModel::getInvoiceBasicInfo($id);
        if($invoiceInfo->payment_mode_slug == 'opd'){
            InvoiceModel::where('id', $id)->update([
                'process' => 'in-progress'
            ]);
        }
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = OPDModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OPDRequest $request, $id)
    {
        $data = $request->validated();
        $entity = InvoiceModel::find($id);
        $patient = PatientModel::find($entity->customer_id);
        $entity->update($data);
        $patient->update($data);
        $invoice = InvoiceModel::getShow($id);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($patient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        OPDModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);
    }


}
