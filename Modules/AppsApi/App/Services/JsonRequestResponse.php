<?php


namespace Modules\AppsApi\App\Services;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class JsonRequestResponse
{
    public function returnJosnResponse($data = [])
    {
        try{
            $response = new Response();
            $response->headers->set('Content-Type','application/json');
            $response->setContent(json_encode([
                'message' => 'success',
                'status' => Response::HTTP_OK,
                'data' => $data
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }catch(\Exception $ex){
            return \response([
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function clearCaches($prefix)
    {
        // Increase loop if you need, the loop will stop when key not found
        for ($i=1; $i < 1000; $i++) {
            $key = $prefix . $i;
            if (Cache::has($key)) {
                Cache::forget($key);
            } else {
                break;
            }
        }
    }
}
