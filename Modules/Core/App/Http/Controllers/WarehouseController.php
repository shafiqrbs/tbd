<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\VendorRequest;
use Modules\Core\App\Http\Requests\WarehouseRequest;
use Modules\Core\App\Models\SettingModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Core\App\Models\WarehouseModel;

class WarehouseController extends Controller
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
    public function index(Request $request){

        $data = WarehouseModel::getRecords($request,$this->domain);
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
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        $service = new JsonRequestResponse();
        $input = $request->validated();

        // Include necessary fields
        $input['domain_id'] = $this->domain['global_id'];
        $input['status'] = true;
        $input['setting_type_id'] = 9;

        DB::beginTransaction(); // Begin a database transaction
        try {
            // Create setting record
            $setting = SettingModel::create($input);
            $input['setting_id'] = $setting->id;

            // Create warehouse record
            $warehouse = WarehouseModel::create($input);

            // Commit the transaction since everything is successful
            DB::commit();

            // Return the warehouse record as a JSON response
            return $service->returnJosnResponse($warehouse);

        } catch (\Exception $e) {
            // Rollback the transaction if any exception occurs
            DB::rollBack();

            // Log the error for debugging purposes
            Log::error('Warehouse creation failed: ' . $e->getMessage(), ['trace' => $e->getTrace()]);

            // Return an appropriate error response to the client
            return response()->json(['error' => 'Failed to create warehouse'], 500);
        }
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = VendorModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }


    /**
     * Show the specified resource.
     */
    public function details($id)
    {
        $service = new JsonRequestResponse();
        $entity = VendorModel::find($id);

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
        $entity = VendorModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorRequest $request, $id)
    {
        $data = $request->validated();

        if ($data['customer_id']){
            $customerExists = VendorModel::where('customer_id', $data['customer_id'])->first();

            if ($customerExists && $customerExists->id != $id) {
                throw ValidationException::withMessages([
                    'customer_id' => ['The customer ID is already in use.'],
                ]);
            }
        }

        $entity = VendorModel::find($id);
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
        VendorModel::find($id)->delete();

        $entity = ['message'=>'delete'];
        return $service->returnJosnResponse($entity);

    }


    public function localStorage(Request $request){

        $data = VendorModel::getRecordsForLocalStorage($request,$this->domain);
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
