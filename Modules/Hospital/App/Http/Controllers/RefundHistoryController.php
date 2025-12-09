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
use Modules\Hospital\App\Entities\InvoiceTransactionRefund;
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
use Modules\Hospital\App\Models\RefundModel;


class RefundHistoryController extends Controller
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
        $data = RefundModel::getHistory($request,$domain);
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
        $entity = RefundModel::getShowHistory($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }



    public function print($id)
    {
        $service = new JsonRequestResponse();
        $entity = RefundModel::showInvoiceData($id);
        return $service->returnJosnResponse($entity);
    }

    /**
     * Show the specified resource.
     */
    public function approve($id)
    {
        $userId = $this->domain['user_id'];
        $service = new JsonRequestResponse();
        $entity = RefundModel::find($id);
        $entity->update([
            'process' => 'Done',
            'approved_by_id' => $userId
        ]);
        $success = ['message' => 'success'];
        $data = $service->returnJosnResponse($success);
        return $data;
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
