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
use Modules\Hospital\App\Models\PrescriptionModel;



class BillingController extends Controller
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
        $data = BillingModel::getRecords($request,$domain);
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
    public function findBill(Request $request){

        $domain = $this->domain;
        $data = BillingModel::getFinalBillRecords($request,$domain);
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
    public function admission(Request $request){

        $domain = $this->domain;
        $data = BillingModel::getRecords($request,$domain);
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
        $entity = InvoiceModel::findByIdOrUid($id);
        if($entity->process == 'billing'){
            $entity = BillingModel::getAdmissionBilling($id);
        }else{
            $entity = BillingModel::getShow($id);
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function transaction($id,$reportId)
    {
        $service = new JsonRequestResponse();
        $invoiceParticular = InvoiceTransactionModel::with(['items','createdDoctorInfo'])->find($reportId);
        $data = $service->returnJosnResponse($invoiceParticular);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $domain = $this->domain;
        $data = $request->all();
        $entity = InvoiceModel::findByIdOrUid($id);
        if($entity->process == "billing"){
            $transactionId = InvoiceTransactionModel::insertAdmissionInvoiceTransaction($domain,$entity,$data);
        }else{
           $transactionId =  InvoiceTransactionModel::insertInvoiceTransaction($domain,$entity,$data);
        }
        $amount = InvoiceTransactionModel::where('hms_invoice_id', $entity->id)->where('process','Done')->sum('amount');
        $total = InvoiceParticularModel::where('hms_invoice_id', $entity->id)->where('status',true)->sum('sub_total');
        InvoiceParticularModel::getCountBedRoom($entity->id);
        $entity->update(['sub_total' => $total , 'total' => $total, 'amount' => $amount]);
        $invoice = InvoiceTransactionModel::showInvoiceData($transactionId);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($invoice);

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

     /**
     * Remove the specified resource from storage.
     */
    public function print($id)
    {
        $service = new JsonRequestResponse();
        $entity = InvoiceTransactionModel::showInvoiceData($id);
        return $service->returnJosnResponse($entity);
    }

    /**
     * Show the specified resource.
     *//**/
    public function finalBillDetails($id)
    {
        $entity = InvoiceModel::findByIdOrUid($id);
        $service = new JsonRequestResponse();
        $amount = InvoiceTransactionModel::where('hms_invoice_id', $entity->id)->where('process','Done')->sum('amount');
        $total = InvoiceParticularModel::where('hms_invoice_id', $entity->id)->where('status',true)->sum('sub_total');
        InvoiceParticularModel::getCountBedRoom($entity->id);
        $entity->update(['sub_total' => $total , 'total' => $total, 'amount' => $amount]);
        $entity = BillingModel::getFinalBillShow($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

}
