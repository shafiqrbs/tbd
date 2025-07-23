<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\App\Models\UserModel;
use Modules\Inventory\App\Entities\StockItem;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionItem;
use Modules\Production\App\Http\Requests\RecipeItemsRequest;
use Modules\Production\App\Models\ProductionBatchModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionValueAdded;
use Modules\Production\App\Models\SettingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductionReportController extends Controller
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
    public function issueReport(Request $request)
    {
        $params = $request->all();
        $productionConfigId = $this->domain['pro_config'];
        $domainConfigId = $this->domain['domain_id'];
        $entity = ProductionBatchModel::issueReportData($params, $domainConfigId,$productionConfigId);
        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'data' => $entity
        ],ResponseAlias::HTTP_OK);
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
