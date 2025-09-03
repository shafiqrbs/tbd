<?php

namespace Modules\Hospital\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AppsApi\App\Services\JsonRequestResponse;

use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Http\Requests\ParticularRequest;
use Modules\Hospital\App\Models\ParticularDetailsModel;
use Modules\Hospital\App\Models\ParticularInvestigationModeModel;
use Modules\Hospital\App\Models\ParticularModel;
use Modules\Hospital\App\Models\ParticularTypeMasterModel;
use Modules\Hospital\App\Models\ParticularTypeModel;



class InvestigationController extends Controller
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
        $data = ParticularInvestigationModeModel::getRecords($request, $this->domain);
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
        $service = new JsonRequestResponse();
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $entity = ParticularModel::with(['particularDetails','particularDetails.patientMode','particularDetails.paymentMode','particularDetails.genderMode','particularDetails.roomNo','particularDetails.cabinMode'])->find($id);
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
