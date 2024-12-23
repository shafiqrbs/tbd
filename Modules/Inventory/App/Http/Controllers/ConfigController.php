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
use Modules\Inventory\App\Models\SettingModel;

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
        $inv_product_type = SettingModel::where('parent_slug', 'product-type')->where('config_id', $id)
            ->select('id', 'slug', 'name', 'status')
            ->get()
            ->toArray();
//        dump($inv_product_type,$id);

        if ($inv_product_type) {
            foreach ($inv_product_type as $value) {
                switch ($value['slug']) {
                    case 'raw-materials':
                        $entity['raw_materials'] = $value['status'];
                        break;
                    case 'stockable':
                        $entity['stockable'] = $value['status'];
                        break;
                    case 'post-production':
                        $entity['post_production'] = $value['status'];
                        break;
                    case 'mid-production':
                        $entity['mid_production'] = $value['status'];
                        break;
                    case 'pre-production':
                        $entity['pre_production'] = $value['status'];
                        break;
                }
            }
        }
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function getConfigById($id)
    {
        $service = new JsonRequestResponse();
        $dataModel = ConfigModel::with('domain','currency','businessModel')->where('domain_id',$id)->first();
        $entity = ConfigModel::with('domain','currency','businessModel')->find($dataModel->id);
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

        $productSlugs = [
            'raw_materials' => 'raw-materials',
            'stockable' => 'stockable',
            'post_production' => 'post-production',
            'mid_production' => 'mid-production',
            'pre_production' => 'pre-production'
        ];

        foreach ($productSlugs as $requestKey => $slug) {
            if ($request->has($requestKey)) {
                // Fetch the latest model instance
                $productType = SettingModel::where('parent_slug', 'product-type')
                    ->where('config_id', $this->domain['config_id'])
                    ->where('slug', $slug)
                    ->first();

                if (!$productType) {
                    \Log::error("Record not found for slug: {$slug}");
                    continue; // Skip to the next slug
                }

                $newStatus = $request->get($requestKey);

                // Only update if the new status is different
                if ($productType->status !== $newStatus) {
                    $productType->update(['status' => $newStatus]);

                    if ($productType->wasChanged('status')) {
                        \Log::info("Slug {$slug} updated successfully.");
                    } else {
                        \Log::error("Failed to update slug: {$slug}");
                    }
                } else {
                    \Log::info("No changes needed for slug: {$slug}. Existing status is the same.");
                }
            }
        }

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($data);
    }


}
