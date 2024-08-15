<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\App\Http\Requests\TransactionModeRequest;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Utility\App\Models\SettingModel;


class TransactionModeController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $entityId = $request->header('X-Api-User');
        if ($entityId && !empty($entityId)){
            $entityData = UserModel::getUserData($entityId);
            $this->domain = $entityData;
        }
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request){

        $data = TransactionModeModel::getRecords($request,$this->domain);
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

    public function store(TransactionModeRequest $request)
    {
        $data = $request->validated();
        $data['status'] = true;
        $data['config_id'] = $this->domain['acc_config'];
        $data['is_selected'] = $data['is_selected'] ?? false;
        $data['authorised'] = $this->getSettingName($data['authorised_mode_id']);
        $data['account_type'] = $this->getSettingName($data['account_mode_id']);
        $data['path'] = $this->processFileUpload($request, 'uploads/accounting/transaction-mode/');

        $entity = TransactionModeModel::create($data);

        if ($data['is_selected']) {
            $this->updateIsSelected($entity->id);
        }

        return (new JsonRequestResponse())->returnJosnResponse($entity);
    }

    private function getSettingName($id)
    {
        $setting = SettingModel::find($id);
        return $setting ? $setting->name : null;
    }

    private function processFileUpload($request, $uploadDir)
    {
        if ($request->file('path')) {
            $imageName = time() . '.' . $request->path->extension();
            $request->path->move(public_path($uploadDir), $imageName);
            return $imageName;
        }

        return null;
    }

    private function updateIsSelected($id)
    {
        $getData = TransactionModeModel::where('config_id', $this->domain['acc_config'])
                                                ->where('id', '<>', $id)
                                                ->get();
        if (sizeof($getData) > 0){
            foreach ($getData as $data) {
                $updateEntity = TransactionModeModel::find($data->id);
                $updateEntity->update(['is_selected' => false]);
            }
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = TransactionModeModel::find($id);

        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionModeRequest $request, $id)
    {
        $data = $request->validated();

        $entity = TransactionModeModel::find($id);
        $data['config_id'] = $this->domain['acc_config'];
        $data['is_selected'] = $data['is_selected'] ?? false;
        $data['authorised'] = $this->getSettingName($data['authorised_mode_id']);
        $data['account_type'] = $this->getSettingName($data['account_mode_id']);
        $path = $this->processFileUpload($request, 'uploads/accounting/transaction-mode/');
        if ($path){
            if ($entity->path){
                $target_location = 'uploads/accounting/transaction-mode/';
                File::delete(public_path().'/'.$target_location.$entity->path);
            }
            $data['path'] = $path;
        }
        $entity->update($data);
        if ($data['is_selected']) {
            $this->updateIsSelected($entity->id);
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
        DomainModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public function transactionMode(Request $request)
    {
        $entity = TransactionModeModel::getTransactionsModeData($this->domain);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    public function LocalStorage(Request $request){

        $data = TransactionModeModel::getRecordsForLocalStorage($request,$this->domain);
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
