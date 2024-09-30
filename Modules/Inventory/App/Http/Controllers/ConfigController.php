<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\VendorRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Inventory\App\Http\Requests\ConfigRequest;
use Modules\Inventory\App\Models\ConfigModel;

class ConfigController extends Controller
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
     * Show the form for editing the specified resource.
     */
    public function getConfig()
    {
        $id = $this->domain['config_id'];
        $service = new JsonRequestResponse();
        $entity = ConfigModel::with('domain','currency','businessModel')->find($id);
        if (!$entity){
            $entity = 'Data not found';
        }

        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateConfig(Request $request,$id)
    {
        $data = $request->all();

        $entity = ConfigModel::where('domain_id',$id)->first();
        if ($request->file('logo')) {
            $path = public_path('uploads/inventory/logo/');
            File::delete($path.$entity->path);

            if(!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $imageName = $this->domain['config_id'].time().'.'.$request->logo->extension();
            $request->logo->move($path, $imageName);
            $data['path'] = $imageName;
        }

        $entity->update($data);

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }


}
