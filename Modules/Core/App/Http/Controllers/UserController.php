<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\UserRole;
use Modules\Core\App\Http\Requests\UserLoginRequest;
use Modules\Core\App\Http\Requests\UserRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\UserProfileModel;
use Modules\Core\App\Models\UserRoleGroupModel;
use Modules\Core\App\Models\UserRoleModel;

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
        $accessControlRole = UserModel::getAccessControlRoles($id,'access_control_role');
        $androidControlRole = UserModel::getAccessControlRoles($id,'android_control_role');
        $entity['access_control_roles'] = $accessControlRole;
        $entity['android_control_role'] = $androidControlRole;
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

        //user profile update
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

        // user role update
        $userRole = UserRoleModel::where('user_id', $id)->first();

        // Get all "id" values
        $accessControlRolesJson = null;
        if (isset($data['access_control_role'])) {
            $accessControlRoles = [];
            UserRoleGroupModel::where('user_id',$id)->where('role_type','access_control_role')->delete();
            foreach ($data['access_control_role'] as $group) {
                if ($group){
                    foreach ($group["actions"] as $action) {
                        $accessControlRoles[] = $action["id"];
                        //insert role group
                        UserRoleGroupModel::create([
                            'user_id' => $id,
                            'group_name' => $group["Group"],
                            'role_name' => $action["id"],
                            'role_label' => $action["label"],
                            'role_type' => 'access_control_role',
                        ]);
                    }
                }
            }
            $accessControlRolesJson = json_encode($accessControlRoles, JSON_PRETTY_PRINT);
        }

        $androidControlRolesJson = null;
        if (isset($data['android_control_role'])){
            $androidControlRoles = [];
            UserRoleGroupModel::where('user_id',$id)->where('role_type','android_control_role')->delete();
            foreach ($data['android_control_role'] as $group) {
                if ($group){
                    foreach ($group["actions"] as $action) {
                        $androidControlRoles[] = $action["id"];
                        // insert role group
                        UserRoleGroupModel::create([
                            'user_id' => $id,
                            'group_name' => $group["Group"],
                            'role_name' => $action["id"],
                            'role_label' => $action["label"],
                            'role_type' => 'android_control_role',
                        ]);
                    }
                }
            }
            $androidControlRolesJson = json_encode($androidControlRoles, JSON_PRETTY_PRINT);
        }

        if (!$userRole){
            UserRoleModel::create([
                'user_id' => $id,
                'access_control_role' => $accessControlRolesJson,
                'android_control_role' => $androidControlRolesJson,
            ]);
        }else{
            $userRole->update([
                'user_id' => $id,
                'access_control_role' => $accessControlRolesJson,
                'android_control_role' => $androidControlRolesJson,
            ]);
        }

        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }

    public function updateImage(Request $request,$id)
    {
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
        $response = new Response();
        $response->headers->set('Content-Type','application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
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
