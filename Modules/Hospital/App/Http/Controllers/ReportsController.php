<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\ReportModel;
use Modules\Inventory\App\Models\CategoryModel;

class ReportsController extends Controller
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
    public function index(Request $request,EntityManagerInterface $em){
        $data = CustomerModel::getRecords($this->domain,$request);
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
    public function dailySummary(Request $request){

        $data = ReportModel::getInvoiceSummary($this->domain,$request);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function monthlyPatientModeSummary(Request $request){

        $data = ReportModel::getSummary($this->domain,$request);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function patientCollection(Request $request){

        $data = ReportModel::getPatientCollections($this->domain,$request);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function patientTicket(Request $request){

        $data = ReportModel::getPatientTickets($this->domain,$request);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function summaryInvestigation(Request $request){

        $data = ReportModel::serviceBaseInvestigation($this->domain,$request);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function summaryGroupInvestigation(Request $request){

        $data = ReportModel::serviceBaseGroupInvestigation($this->domain,$request);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

}
