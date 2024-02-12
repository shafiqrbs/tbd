<?php

namespace Modules\AppsApi\App\Http\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\CacheClearable;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Filters\CustomerFilter;
use Modules\Core\App\Services\CustomerService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Repositories\CustomerRepository;


class GlobalApiController extends Controller
{

    public function index(Request $request,EntityManagerInterface $em){


        $entities = $em->getRepository(Customer::class)->listWithSearch($request->query());
        try{
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => '200',
                'data' => $entities
            ]));
            $response->setStatusCode(Response::HTTP_FOUND);
            return $response;
        }catch(\Exception $ex){
            return \response([
                'message'=>$ex->getMessage()
            ]);
        }
    }

    public function splash(Request $request,EntityManagerInterface $em){

        $service = new JsonRequestResponse();
        $service->clearCaches('Customer');
        $entities = $em->getRepository(Customer::class)->listWithSearch($request->query());
        $data = $service->returnJosnResponse($entities);
        return $data;

    }

    public function customer(Request $request,EntityManagerInterface $em){
        $entities = $em->getRepository(Customer::class)->listWithSearch($request->query());
        try{
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => '200',
                'data' => $entities
            ]));
            $response->setStatusCode(Response::HTTP_FOUND);
            return $response;
        }catch(\Exception $ex){
            return \response([
                'message'=>$ex->getMessage()
            ]);
        }
    }

    public function domain(){
        try{
            if ($this->access == 200){
                $allCustomers = Domain::getFindAll();
                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent(json_encode([
                    'message' => 'success',
                    'status' => '200',
                    'data' => $allCustomers
                ]));
                $response->setStatusCode(Response::HTTP_FOUND);
                return $response;
            }
        }catch(Exception $ex){
            return \response([
                'message'=>$ex->getMessage()
            ]);
        }
    }

    public function medicineBrand(){
        try{
            if ($this->access == 200){
                $allCustomers = MedicineBrand::getFindAll();
                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent(json_encode([
                    'message' => 'success',
                    'status' => '200',
                    'data' => $allCustomers
                ]));
                $response->setStatusCode(Response::HTTP_FOUND);
                return $response;
            }
        }catch(Exception $ex){
            return \response([
                'message'=>$ex->getMessage()
            ]);
        }
    }

    public function signIn(Request $request){
        try{
            if ($this->access == 200){
                $email = $request->post('email');
                $password = $request->post('password');
                $validation = true;

                if (empty($email)){
                    $validation = false;
                    return new JsonResponse(['status'=>404, 'message'=>'Email field must be filled']);
                }else{
                    if (empty($password)){
                        $validation = false;
                        return new JsonResponse(['status'=>404, 'message'=>'Password field must be filled']);
                    }else{
                        $userExists = DB::table('users')->where('email',$email)->where('enabled',1)->first();
                        if ($userExists){
                            $verify = password_verify($password, $userExists->password);
                            if (!$verify){
                                $validation = false;
                                return new JsonResponse(['status'=>404, 'message'=>'You enter wrong password']);
                            }
                        }else{
                            $validation = false;
                            return new JsonResponse(['status'=>404, 'message'=>'Invalid credentials']);
                        }
                    }
                }

                if ($validation){
                    $arrayData[]=[
                        'id'=>$userExists->id,
                        'username'=>$userExists->username,
                        'email'=>$userExists->email,
                        'roles'=>$userExists->roles,
                        'last_login'=>$userExists->last_login,
                    ];
                    return new JsonResponse([
                        'status'=>200,
                        'message'=>'Sign in successful',
                        'user_data'=>$arrayData
                    ]);
                }
            }elseif ($this->access == 404){
                return new JsonResponse(['status'=>404, 'message'=>$this->access]);
            }else{
                return new JsonResponse(['status'=>405, 'message'=>'Api key does not match']);
            }
        }catch(Exception $ex){
            return \response([
                'message'=>$ex->getMessage()
            ]);
        }
    }
}
