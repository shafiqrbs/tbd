<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionItem;
use Modules\Production\App\Http\Requests\RecipeItemsRequest;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionValueAdded;
use Modules\Production\App\Models\SettingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                ProductionValueAdded::firstOrCreate([
                    'production_item_id' => $pro_item_id,
                    'value_added_id' => $value['id'],
                ]);
            }
        }

        $getValueAdded = ProductionValueAdded::getValueAddedWithInputGenerate($pro_item_id);
        $getProductionItem = ProductionItems::find($pro_item_id);
        $getStockItem = StockItemModel::find($getProductionItem->item_id);

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => Response::HTTP_OK,
            'message' => 'success',
            'data' => [
                'field' => $getValueAdded,
                'item' => $getProductionItem,
                'stock_item' => $getStockItem
            ],
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

    public function inlineUpdateElementStatus(Request $request)
    {
        $response = new Response();

        $getElement = ProductionElements::find($request->get('id'));

        if (!$getElement){
            $response->setContent(json_encode([
                'message' => 'Element not found',
                'status' => Response::HTTP_NOT_FOUND
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }
        $getElement->update([
            'status' => $request->get('status')==true?1:0
        ]);

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => Response::HTTP_OK,
            'message' => 'success',
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function updateProcess(Request $request)
    {
        $response = new Response();

        $getProductionItems = ProductionItems::find($request->get('pro_item_id'));

        if (!$getProductionItems){
            $response->setContent(json_encode([
                'message' => 'Production item not found',
                'status' => Response::HTTP_NOT_FOUND
            ]));
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }
        $getProductionItems->update([
            'process' => $request->get('process')
        ]);

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'status' => Response::HTTP_OK,
            'message' => 'success',
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function dropdown()
    {
        $data = ProductionItems::dropdown($this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $data
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public function finishGoodsXlsxGenerate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Fetch data from the database
            $finishGoods = ProductionItems::getFinishGoodsDownload($this->domain);

            // Handle empty data case
            if (empty($finishGoods)) {
                return response([
                    'error' => 'No finish goods available for download.',
                    'result' => false,
                    'status' => 404,
                ]);
            }

            // Define headers and fields
            $headers = ['ProductionItemId','ProductType', 'ProductName', 'CategoryName', 'UnitName', 'WasteAmount','TotalQuantity','WastePercent','MaterialQuantity','MaterialAmount','ValueAddedAmount','SubTotal','IssueQuantity'];
            $fields = ['id', 'product_type_slug','product_name', 'category_name', 'unit_name', 'waste_amount','quantity','waste_percent','material_quantity','material_amount','value_added_amount','sub_total'];

            // Write headers to the first row
            $sheet->fromArray($headers, null, 'A1');

            // Make headers bold
            $headerStyleArray = [
                'font' => ['bold' => true],
            ];
            // Dynamically set style for all header cells
            $columnRange = 'A1:' . chr(64 + count($headers)) . '1'; // e.g., A1:E1
            $sheet->getStyle($columnRange)->applyFromArray($headerStyleArray);

            // Set the data rows
            $rowIndex = 2;
            foreach ($finishGoods as $row) {
                $colIndex = 'A'; // Start from column A
                foreach ($fields as $field) {
                    if (is_array($row) && array_key_exists($field, $row)) {
                        $sheet->setCellValue($colIndex . $rowIndex, $row[$field]);
                    } elseif (is_object($row) && property_exists($row, $field)) {
                        $sheet->setCellValue($colIndex . $rowIndex, $row->{$field});
                    } else {
                        $sheet->setCellValue($colIndex . $rowIndex, '');
                    }
                    $colIndex++;
                }
                $rowIndex++;
            }

            // Auto-size columns
            foreach (range('A', chr(64 + count($headers))) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Save the file
            $directory = storage_path('exports');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true); // Create directory if it doesn't exist
            }
            $fileName = 'finish-goods-data.xlsx';
            $tempFilePath = $directory . '/' . $fileName;
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFilePath);

            // Return file path as response
            return response([
                'filename' => $fileName,
                'file_url' => url("storage/exports/{$fileName}"),
                'result' => true,
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            // Handle errors gracefully
            return response([
                'error' => $e->getMessage(),
                'result' => false,
                'status' => 500,
            ]);
        }
    }

    public function finishGoodsDownload()
    {
        $fileName = 'finish-goods-data.xlsx';
        $filePath = storage_path('exports/'.$fileName);
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
