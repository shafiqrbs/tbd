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
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionElement;
use Modules\Production\App\Entities\ProductionItem;
use Modules\Production\App\Http\Requests\ProductionRecipeRequest;
use Modules\Production\App\Models\ProductionConfig;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionItems;
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
    /*public function store(Request $request, EntityManager $em)
    {
        $data = $request->all();
        $em->getRepository(ProductionElement::class)->insertProductionElement($data,$this->domain);
    }*/

    public function store(ProductionRecipeRequest $request)
    {
        DB::beginTransaction();

        try {

            $input = $request->validated();

            $materialId        = $input['inv_stock_id'] ?? null;
            $productionItemId  = $input['item_id'] ?? null;
            $quantity          = $input['quantity'] ?? 0;
            $wastagePercent    = $input['percent'] ?? null;

            $productionItem = ProductionItems::find($productionItemId);
            $material       = StockItemModel::find($materialId);
            $config         = ProductionConfig::find($this->domain['pro_config']);

            if (!$productionItem || !$material) {
                return response()->json(['error' => 'Invalid data'], 422);
            }

            $price = $material->average_price ?? $material->purchase_price;

            $exist = ProductionElements::where('production_item_id', $productionItemId)
                ->where('material_id', $materialId)
                ->first();

            if (!$exist) {

                $element = new ProductionElements([
                    'production_item_id' => $productionItem->id,
                    'material_id'        => $material->id,
                    'quantity'           => $quantity,
                    'purchase_price'      => $price,
                    'price'               => $price,
                    'sub_total'           => $price * $quantity,
                    'config_id'           => $config->id,
                    'status'              => 1,
                ]);

                // Apply wastage rules
                if ($wastagePercent) {
                    $this->applyWastage($element, $wastagePercent);
                } elseif ($productionItem->waste_percent) {
                    $this->applyWastage($element, $productionItem->waste_percent);
                }

                $element->save();

            } else {

                $exist->quantity        = $quantity;
                $exist->price           = $price;
                $exist->purchase_price  = $price;
                $exist->sub_total       = $price * $quantity;

                if ($wastagePercent) {
                    $this->applyWastage($exist, $wastagePercent);
                }

                $exist->save();
                $element = $exist;
            }

            DB::commit();

            return response()->json([
                'message' => 'Success',
                'data'    => $element
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'error' => 'Failed to save',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    private function applyWastage($element, $percent)
    {
        $wasteQty = ($element->quantity * $percent) / 100;
        $element->wastage_percent = $percent;
        $element->wastage_quantity = $wasteQty;
        $element->wastage_amount = $wasteQty * $element->price;
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
