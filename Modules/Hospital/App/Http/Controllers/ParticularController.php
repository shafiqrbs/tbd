<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;

use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Http\Requests\ParticularInlineRequest;
use Modules\Hospital\App\Http\Requests\ParticularRequest;
use Modules\Hospital\App\Models\ParticularDetailsModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularTypeMasterModel;
use Modules\Hospital\App\Models\ParticularTypeModel;



class ParticularController extends Controller
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
        $userGroup = (isset($request['user_group']) and $request['user_group']) ? $request['user_group']:'';
        if($userGroup){
           ParticularModel::getDoctorNurseStaff($request, $this->domain);
        }
        $data = ParticularModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
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
    public function store(ParticularRequest $request)
    {
        $config = $this->domain['hms_config'];
        $input = $request->validated();
        $input['config_id'] = $config;
        $input['display_name'] = $input['name'];
        $masterId = (isset($input['particular_type_master_id']) and $input['particular_type_master_id']) ? $input['particular_type_master_id']:'';
        $masterType = ParticularTypeMasterModel::find($masterId);
        $type = ParticularTypeModel::where([
            ['config_id', $config],
            ['particular_master_type_id', $masterId],
        ])->first();
        $input['particular_type_id'] = $type->id;
        $entity = ParticularModel::create($input);
        if($masterType->slug == 'bed'){
             ParticularDetailsModel::insertBed($entity,$input);
        }
        if($masterType->slug == 'doctor'){
            ParticularDetailsModel::insertDoctor($entity,$input);
        }
        if($masterType->slug == 'cabin'){
            ParticularDetailsModel::insertCabin($entity,$input);
        }
        if($masterType->slug == 'treatment-template' and $input['treatment_mode_id']){
            ParticularDetailsModel::updateOrCreate(
                [
                    'particular_id' => $entity->id,
                ],
                [
                    'treatment_mode_id'   => $input['treatment_mode_id'],
                ]
            );
        }
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $entity = ParticularModel::with(['particularDetails','particularDetails.patientMode','particularDetails.paymentMode','particularDetails.genderMode','particularDetails.roomNo','particularDetails.cabinMode','investigationReportFormat','treatmentMedicineFormat'])->find($id);
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
        $entity = ParticularModel::find($id);
        $status = $entity ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
        return response()->json([
            'message' => 'success',
            'status' => $status,
            'data' => $entity ?? []
        ], Response::HTTP_OK);

    }


     /**
     * Show the specified resource for edit.
     */
    public function particularInlineUpdate(ParticularInlineRequest $request, $id)
    {
        $input = $request->validated();

        $entity = ParticularModel::find($id);

        $data = array();
        $name = (isset($input['name']) and $input['name']) ? $input['name']:'';
        $price = (isset($input['price']) and $input['price']) ? $input['price']:0;
        if($price){$data['price'] = $price;}
        if($name){$data['name'] = $name;}
        if(!empty($data)){ $entity->update($data);}
        $unit = (isset($input['unit_id']) and $input['unit_id']) ? $input['unit_id']:null;
        $opd_room_id = (isset($input['opd_referred']) and $input['opd_referred']) ? $input['opd_referred']:0;
        $is_available = (isset($input['is_available']) and $input['is_available']) ? $input['is_available']:0;

        $details = [];

        ParticularDetailsModel::updateOrCreate(
            ['particular_id'    => $id]
        );
        $findParticular = ParticularModel::with('particularDetails')->findOrFail($id);

        // Update only changed fields
        if (array_key_exists('name', $input)) {
            $findParticular->name = $input['name'];
            $findParticular->display_name = $input['name'];
        }
        if (array_key_exists('opd_referred', $input)) {
            $findParticular->opd_referred = $findParticular->opd_referred ? 0:1;
        }
        if (array_key_exists('is_available', $input)) {
            $findParticular->is_available = $findParticular->is_available ? 0:1;
        }
        if (array_key_exists('status', $input)) {
            $findParticular->status = $findParticular->status ? 0:1;
        }
        $findParticular->save();

        if ($findParticular->particularDetails) {
            $updateDetails = [];

            if (array_key_exists('opd_room_id', $input)) {
                $updateDetails['room_id'] = $input['opd_room_id'];
            }

            if (array_key_exists('unit_id', $input)) {
                $updateDetails['unit_id'] = $input['unit_id'];
            }

            if (!empty($updateDetails)) {
                $findParticular->particularDetails->update($updateDetails);
            }
        }

        $findParticular->load('particularDetails'); // ensure fresh data

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully',
            'data'    => $findParticular,
        ]);
    }

    public function updateOrdering(Request $request)
    {
        foreach ($request->order as $row) {
            ParticularModel::where('id', $row['id'])
                ->update(['ordering' => $row['ordering']]);
        }
        return response()->json(['success' => true]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ParticularRequest $request, $id)
    {
        $config = $this->domain['hms_config'];
        $input = $request->validated();
        $entity = ParticularModel::find($id);
        $input['display_name'] = $input['name'];
        $masterId = (isset($input['particular_type_master_id']) and $input['particular_type_master_id']) ? $input['particular_type_master_id']:'';
        $masterType = ParticularTypeMasterModel::find($masterId);
        $type = ParticularTypeModel::where([
            ['config_id', $config],
            ['particular_master_type_id', $masterId],
        ])->first();
        $input['particular_type_id'] = $type->id;
        $entity->update($input);
        if($masterType->slug == 'bed'){
            ParticularDetailsModel::insertBed($entity,$input);
        }
        if($masterType->slug == 'doctor'){
            ParticularDetailsModel::insertDoctor($entity,$input);
        }
        if($masterType->slug == 'cabin'){
            ParticularDetailsModel::insertCabin($entity,$input);
        }
        if($masterType->slug == 'treatment-template' and $input['treatment_mode_id']){
            ParticularDetailsModel::updateOrCreate(
                [
                    'particular_id' => $entity->id,
                ],
                [
                    'treatment_mode_id'   => $input['treatment_mode_id'],
                ]
            );
        }
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        ParticularModel::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }

}
