<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Production\App\Entities\ProductionElement;
use Modules\Production\App\Entities\ProductionItem;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionValueAdded;
use Modules\Production\App\Models\SettingModel;

class ProductionRecipeController extends Controller
{
    protected $domain;
    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = ProductionElements::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('production::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, EntityManager $em)
    {
        $data = $request->all();
        $em->getRepository(ProductionElement::class)->insertProductionElement($data,$this->domain);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('production::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('production::edit');
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = new JsonRequestResponse();
        ProductionElements::find($id)->delete();
        $entity = ['message' => 'delete'];
        return $service->returnJosnResponse($entity);
    }
}
