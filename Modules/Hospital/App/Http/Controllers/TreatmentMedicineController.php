<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;

use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Http\Requests\InvestigationReportRequest;
use Modules\Hospital\App\Http\Requests\ParticularRequest;
use Modules\Hospital\App\Http\Requests\TreatmentMedicineRequest;
use Modules\Hospital\App\Models\MedicineDetailsModel;
use Modules\Hospital\App\Models\TreatmentMedicineModel;
use Modules\Hospital\App\Models\ParticularDetailsModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularTypeMasterModel;
use Modules\Hospital\App\Models\ParticularTypeModel;



class TreatmentMedicineController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function index(Request $request)
    {
        $data = ParticularModel::getTreatmentMedicine($this->domain,$request);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($data);
        return $data;
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(TreatmentMedicineRequest $request)
    {

        $config = $this->domain['hms_config'];
        $input = $request->validated();
        $input['config_id'] = $config;
        $medicineId = $input['medicine_id'];
        $medicine = MedicineDetailsModel::find($medicineId);
        $dosage_id = $medicine->medicineStock->medicine_dosage_id ?? null;
        $medicine_bymeal_id = $medicine->medicineStock->medicine_bymeal_id ?? null;
        $input['medicine_dosage_id'] = $dosage_id;
        $input['medicine_bymeal_id'] = $medicine_bymeal_id;
        $entity = TreatmentMedicineModel::create($input);
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $entity = ParticularModel::with(['treatmentMedicineFormat','treatmentMedicineFormat.medicineDosage','treatmentMedicineFormat.medicineBymeal'])->find($id);
        if (!$entity) {
            $entity = 'Data not found';
        }
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource for edit.
     */
    public function edit($id)
    {
        $entity = TreatmentMedicineModel::find($id);
        $status = $entity ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
        return response()->json([
            'message' => 'success',
            'status' => $status,
            'data' => $entity ?? []
        ], Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvestigationReportRequest $request, $id)
    {

        $input = $request->validated();
        $entity = TreatmentMedicineModel::find($id);
        $entity->update($input);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        TreatmentMedicineModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
