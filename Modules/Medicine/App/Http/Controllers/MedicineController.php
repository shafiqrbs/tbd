<?php

namespace Modules\Medicine\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Models\ProductModel;
use Modules\Inventory\App\Models\CategoryModel;
use Modules\Medicine\App\Http\Requests\MedicineInlineRequest;
use Modules\Medicine\App\Models\MedicineDetailsModel;
use Modules\Medicine\App\Models\MedicineModel;

class MedicineController extends Controller
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

        $data = MedicineModel::getRecords($request,$this->domain,);
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

    public  function particularModuleDropdown(Request $request): JsonResponse
    {
        //dd($this->domain);

        //  $data = CustomerModel::getRecords($this->domain,$request);
        $data = CategoryModel::getRecords($request,$this->domain);
        $response = response()->json([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ], 200);
        return $response;

    }

    /**
     * Show the specified resource for edit.
     */
    public function medicineInlineUpdate(MedicineInlineRequest $request, $id)
    {
        $input = $request->validated();

        $entity = MedicineModel::find($id);

        $data = array();
        $company = (isset($input['company']) and $input['company']) ? $input['company']:'';
        $name = (isset($input['name']) and $input['name']) ? $input['name']:'';
        $generic = (isset($input['generic']) and $input['generic']) ? $input['generic']:'';
        $opd_quantity = (isset($input['opd_quantity']) and $input['opd_quantity']) ? $input['opd_quantity']:0;

        // Update only changed fields
        if (array_key_exists('name', $input)) {
            $entity->name = $name;
        }
        $entity->save();

        $medicineDetails = MedicineDetailsModel::where('product_id',$id)->first();
        if ($medicineDetails) {
            $updateDetails = [];

            if (array_key_exists('opd_quantity', $input)) {
                $updateDetails['opd_quantity'] = $input['opd_quantity'];
            }

            if (array_key_exists('company', $input)) {
                $updateDetails['company'] = $company;
            }

            if (array_key_exists('generic', $input)) {
                $updateDetails['generic'] = $generic;
            }

            if (!empty($updateDetails)) {
                $medicineDetails->update($updateDetails);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Updated Successfully',
            'data'    => $medicineDetails,
        ]);
    }

}
