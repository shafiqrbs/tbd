<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Production\App\Entities\ProductionItem;
use Modules\Production\App\Http\Requests\RecipeItemsRequest;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionValueAdded;
use Modules\Production\App\Models\SettingModel;

class ProductionRecipeItemsController extends Controller
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
        $data = ProductionItems::getRecords($request, $this->domain);
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

    public function restore(Request $request, EntityManager $em)
    {
        $pro_config =  $this->domain['pro_config'];
        $inv_config =  $this->domain['config_id'];
        $entities = $em->getRepository(StockItem::class)->getProductionItems($inv_config);
        $response = new Response();
        foreach ($entities as $entity) {
            $em->getRepository(ProductionItem::class)->insertUpdate($pro_config, $entity['id']);
        }
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK
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
    public function store(RecipeItemsRequest $request)
    {
        $response = new Response();
        $pro_item_id = $request->validated()['pro_item_id'];
        $findItems = ProductionItems::find($pro_item_id);

        if (!$findItems){
            $response->setContent(json_encode([
                'message' => 'Production Item not found',
                'status' => Response::HTTP_NOT_FOUND
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }

        $getMeasurementInputGenerate = SettingModel::getMeasurementInput($this->domain['pro_config']);

        if (sizeof($getMeasurementInputGenerate) > 0) {
            foreach ($getMeasurementInputGenerate as $value) {
                $valueAddedExists = ProductionValueAdded::where([['production_item_id', '=', $pro_item_id],['value_added_id', '=', $value['id']]])->first();
                if (!$valueAddedExists){
                    ProductionValueAdded::firstOrCreate([
                        'production_item_id' => $pro_item_id,
                        'value_added_id' => $value['id'],
                    ]);
                }
            }
        }

        $getValueAdded = ProductionValueAdded::getValueAddedWithInputGenerate($pro_item_id);

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => Response::HTTP_OK,
            'message' => 'success',
            'data' => $getValueAdded,
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
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
        //
    }

    public function inlineUpdateValueAdded(Request $request)
    {
        $response = new Response();
        $getValueAdded = ProductionValueAdded::find($request->get('value_added_id'));
        if (!$getValueAdded){
            $response->setContent(json_encode([
                'message' => 'Value added not found',
                'status' => Response::HTTP_NOT_FOUND
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }
        $getValueAdded->update([
            'amount'=> $request->get('amount'),
        ]);

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => Response::HTTP_OK,
            'message' => 'success',
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
}
