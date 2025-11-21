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
use Modules\Hospital\App\Http\Requests\PrescriptionRequest;
use Modules\Hospital\App\Models\BillingModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\HospitalSalesModel;
use Modules\Hospital\App\Models\InvoiceContentDetailsModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\InvoicePathologicalReportModel;
use Modules\Hospital\App\Models\InvoiceTransactionModel;
use Modules\Hospital\App\Models\LabInvestigationModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PatientWaiverModel;
use Modules\Hospital\App\Models\PrescriptionModel;



class PatientWaiverController extends Controller
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
        $data = PatientWaiverModel::getRecords($request,$domain);
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
    public function invoice(Request $request){

        $domain = $this->domain;
        $data = PatientWaiverModel::getInvoices($request,$domain);
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
     * Remove the specified resource from storage.
     */
    public function process(Request $request , $id)
    {
        $mode = $request->get('mode');
        $service = new JsonRequestResponse();
        if($mode == 'room'){
            $entity = PatientWaiverModel::getInvoiceRoomParticular($id,$mode);
        }else{
            $entity = PatientWaiverModel::getInvoiceParticular($id,$mode);
        }
        $data = $service->returnJosnResponse($entity);
        return $data;

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->all();
        $input['config_id'] = $this->domain['hms_config'];
        $input['created_by_id'] = $this->domain['user_id'];
        $input['hms_invoice_id'] = InvoiceModel::findByIdOrUid($input['hms_invoice_id'])->id;
        $exist =  InvoiceParticularModel::checkExistingWaiver($input);
        $count = count($exist['new']);
        if($count > 0){
            $entity = PatientWaiverModel::create($input);
            PatientWaiverModel::insertInvoiceTransaction($entity,$input['hms_invoice_id'],$input['mode'],$exist['new']);
            $data = $service->returnJosnResponse($entity);
            return $data;
        }
        $data = $service->returnJosnResponse(null);
        return $data;
    }



    /**
     * Show the specified resource.
     *//**/
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = PatientWaiverModel::with('items')
            ->where('uid', $id)
            ->first();
        return $service->returnJosnResponse($entity);
    }



    /**
     * Update the specified resource in storage.
     */
    public function approve(Request $request, $id)
    {
        $data = $request->all();
        $date = now();
        $entity = PatientWaiverModel::where('uid',$id)->first();
        if(empty($entity->checked_by_id)){
            $data['checked_by_id']= $this->domain['user_id'];
            $data['checked_date'] = $date;
        }elseif($entity->checked_by_id and empty($entity->approved_by_id)){
            $data['approved_by_id']= $this->domain['user_id'];
            $data['approved_date'] = $date;
        }
        $entity->update($data);
        if($entity->approved_by_id){
            $transaction = InvoiceTransactionModel::where('patient_waiver_id', $entity->id);
            $transaction->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Done',
                'sub_total' => 0,
                'total' => 0,
                'amount' => 0,
            ]);
        }
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);

    }

    /**
     * Update the specified resource in storage.
     */
    public function print(Request $request, $id)
    {

        $entity = PatientWaiverModel::getWaiverDetails($id);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        $entity = PatientWaiverModel::where('uid',$id)->first();
        if($entity){
            $items = InvoiceParticularModel::where('patient_waiver_id', $entity->id)->get();
            foreach ($items as $item) {
                $item->update([
                    'patient_waiver_id'      => null,
                    'invoice_transaction_id' => null,
                    'price'                  => $item->estimate_price, // use its own field
                    'is_waiver'               => 0,
                ]);
            }
            $entity->delete();
            $data = ['message' => 'delete'];
        }else{
            $data = ['message' => 'invalid'];
        }
        return $service->returnJosnResponse($data);
    }

}
