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
use Modules\Hospital\App\Models\EpharmaModel;
use Modules\Hospital\App\Models\HospitalConfigModel;
use Modules\Hospital\App\Models\HospitalSalesModel;
use Modules\Hospital\App\Models\InvoiceModel;
use Modules\Hospital\App\Models\InvoiceParticularModel;
use Modules\Hospital\App\Models\OPDModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularModeModel;
use Modules\Hospital\App\Models\PatientModel;
use Modules\Hospital\App\Models\PharmacyModel;
use Modules\Hospital\App\Models\PrescriptionModel;



class PharmacyController extends Controller
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
        $data = PharmacyModel::getRecords($request,$domain);
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
        $entity = EpharmaModel::getShow($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = new JsonRequestResponse();
        $entity = PrescriptionModel::getShow($id);
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $domain = $this->domain;
        $data = $request->all();
        $entity = PrescriptionModel::find($id);
        $data['json_content'] = json_encode($data);
        $userId = $request->header('X-Api-User');
        $data['prescribe_doctor_id'] = $userId;
        $entity->update($data);
        $return = PrescriptionModel::getShow($entity->id);
        HospitalSalesModel::insertMedicineDelivery($domain,$entity->id);
    //    InvoiceParticularModel::insertUpdate($return,$data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($return);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        CustomerModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
