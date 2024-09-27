<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Http\Requests\UserLoginRequest;
use Modules\Core\App\Http\Requests\UserRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserProfileModel;

class UserController extends Controller
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

        $data = UserModel::getRecords($request,$this->domain);
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
    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $data['domain_id'] = $this->domain['global_id'];
        $data['email_verified_at']= now();
        $data['password']= Hash::make($data['password']);
        $data['is_delete']= 0;

        $user = UserModel::create($data);
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($user);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $service = new JsonRequestResponse();
        $entity = UserModel::showUserDetails($id);
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
        $entity = UserModel::find($id);
        if (!$entity){
            $entity = 'Data not found';
        }
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, $id)
    {
        $data = $request->validated();
        $entity = UserModel::find($id);
        if (!empty($data['confirm_password'])) {
            $data['password'] = Hash::make($data['confirm_password']);
        }
        $entity->update($data);

        $userProfile = UserProfileModel::where('user_id', $id)->first();
        $data['user_id'] = $id;

        if ($request->file('profile_image')) {
            $profileImage = $this->processFileUpload($request->file('profile_image'), 'uploads/core/user/profile');
            if ($profileImage) {
                if ($userProfile && $userProfile->path) {
                    $target_location = 'uploads/core/user/profile/';
                    File::delete(public_path($target_location . $userProfile->path));
                }
                $data['path'] = $profileImage;
            }
        }

        if ($request->file('digital_signature')) {
            $digitalSign = $this->processFileUpload($request->file('digital_signature'), 'uploads/core/user/signature');
            if ($digitalSign) {
                if ($userProfile && $userProfile->signature_path) {
                    $target_location = 'uploads/core/user/signature/';
                    File::delete(public_path($target_location . $userProfile->signature_path));
                }
                $data['signature_path'] = $digitalSign;
            }
        }

        if (!$userProfile){
            UserProfileModel::create($data);
        }else{
            $userProfile->update($data);
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
        UserModel::find($id)->delete();
        $entity = ['message'=>'delete'];
        $data = $service->returnJosnResponse($entity);
        return $data;
    }

    public function localStorage(Request $request){
        $data = UserModel::getRecordsForLocalStorage($request,$this->domain);
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

    private function processFileUpload($file, $uploadDir)
    {
        if ($file) {
            $uploadDirPath = public_path($uploadDir);

            // Ensure that the directory exists
            if (!file_exists($uploadDirPath)) {
                mkdir($uploadDirPath, 0777, true); // Recursively create the directory with full permissions
            }

            // Generate a unique file name with timestamp
            $fileName = time() . '.' . $file->extension();

            // Move the uploaded file to the target location
            $file->move($uploadDirPath, $fileName);

            return $fileName;
        }

        return null;
    }

}
