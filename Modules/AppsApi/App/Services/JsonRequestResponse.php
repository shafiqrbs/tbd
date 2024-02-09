<?php


namespace Modules\AppsApi\App\Services;


use Illuminate\Http\Response;

class JsonRequestResponse
{
    public function returnJosnResponse($data = [])
    {

        try{
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => '200',
                'data' => $data
            ]));
            $response->setStatusCode(Response::HTTP_FOUND);
            return $response;
        }catch(\Exception $ex){
            return \response([
                'message'=>$ex->getMessage()
            ]);
        }
    }
}
