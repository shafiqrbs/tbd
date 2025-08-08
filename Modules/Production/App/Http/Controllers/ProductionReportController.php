<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use TCPDF;
use Throwable;


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
        $entity = ProductionBatchModel::issueReportData($params, $domainConfigId, $productionConfigId);
        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'data' => $entity
        ], ResponseAlias::HTTP_OK);
    }

    public function issueGenerateXlsx(Request $request)
    {
        $params = $request->all();
        $productionConfigId = $this->domain['pro_config'];
        $domainConfigId = $this->domain['domain_id'];

        try {
            // Use the reusable method
            $processedData = $this->processProductionBatchData($params, $domainConfigId, $productionConfigId);


            $materials = $processedData['materials'];
            $productionMap = $processedData['productionMap'];
            $productionTotals = $processedData['productionTotals'];
            $batches = $processedData['batches'];

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Define colors matching frontend
            $colors = [
                'basic_info' => 'D6EAF8',
                'issue_production' => 'E8F8F5',
                'total_expense' => 'FADBD8',
                'current_stock' => 'FDF2E9',
                'success' => 'D5EDDA',
                'warning' => 'FFF3CD',
                'error' => 'F8D7DA',
                'warning_dark' => 'E2B51F',
                'highlighted' => 'E9ECEF'
            ];

            $defaultStyle = [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];

            $materialCols = ['Material Item', 'Unit'];
            $productionCount = count($productionMap);
            $totalExpenseCols = ['Issue', 'Expense', 'Less', 'More'];
            $currentStockCols = ['Stock', 'Remaining'];

            $row1 = 1;
            $row2 = 2;
            $row3 = 3;
            $row4 = 4;

            $prodStartCol = 3; // Change to column C instead of F due to removed CDE columns
            $prodEndCol = $prodStartCol + ($productionCount * 2) - 1;
            $expenseStartCol = $prodEndCol + 1;
            $expenseEndCol = $expenseStartCol + count($totalExpenseCols) - 1;
            $stockStartCol = $expenseEndCol + 1;
            $stockEndCol = $stockStartCol + count($currentStockCols) - 1;

// Merge Basic Information across 3 rows
            $sheet->mergeCells("A{$row1}:B{$row3}")->setCellValue("A{$row1}", 'Basic Information');
            $sheet->getStyle("A{$row1}:B{$row3}")->applyFromArray($defaultStyle)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['basic_info']);

            if ($productionCount > 0) {
                $start = Coordinate::stringFromColumnIndex($prodStartCol) . $row1;
                $end = Coordinate::stringFromColumnIndex($prodEndCol) . $row1;
                $sheet->mergeCells("{$start}:{$end}")->setCellValue($start, 'Issue Production Quantity');
                $sheet->getStyle("{$start}:{$end}")->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['issue_production']);
            }

            $expStart = Coordinate::stringFromColumnIndex($expenseStartCol) . $row1;
            $expEnd = Coordinate::stringFromColumnIndex($expenseEndCol) . $row1;
            $sheet->mergeCells("{$expStart}:{$expEnd}")->setCellValue($expStart, 'Total Expense Material');
            $sheet->getStyle("{$expStart}:{$expEnd}")->applyFromArray($defaultStyle)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['total_expense']);

            $stockStart = Coordinate::stringFromColumnIndex($stockStartCol) . $row1;
            $stockEnd = Coordinate::stringFromColumnIndex($stockEndCol) . $row1;
            $warehouseName = isset($batches[0]->warehouse_name) ? $batches[0]->warehouse_name : '';
            $headerText = $warehouseName ? "Current Stock ({$warehouseName})" : "Current Stock";

// Merge cells and set value
            $sheet->mergeCells("{$stockStart}:{$stockEnd}")
                ->setCellValue($stockStart, $headerText);
            $sheet->getStyle("{$stockStart}:{$stockEnd}")->applyFromArray($defaultStyle)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['current_stock']);

// Row 2 - Issue + Receive sub-columns
            $ci = $prodStartCol;
            foreach ($productionMap as $prodId => $prodName) {
                $cell1 = Coordinate::stringFromColumnIndex($ci++) . $row2;
                $cell2 = Coordinate::stringFromColumnIndex($ci++) . $row2;
                $sheet->setCellValue($cell1, 'Issue')->getStyle($cell1)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success']);
                $sheet->setCellValue($cell2, 'Receive')->getStyle($cell2)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning']);
            }

// Merge expense columns
            foreach ($totalExpenseCols as $i => $label) {
                $colIndex = $expenseStartCol + $i;
                $cellRange = Coordinate::stringFromColumnIndex($colIndex) . "{$row2}:" . Coordinate::stringFromColumnIndex($colIndex) . "{$row4}";
                $sheet->mergeCells($cellRange)->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $row2, $label);
                $sheet->getStyle($cellRange)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['highlighted']);
            }

// Merge stock columns
            foreach ($currentStockCols as $i => $label) {
                $colIndex = $stockStartCol + $i;
                $cellRange = Coordinate::stringFromColumnIndex($colIndex) . "{$row2}:" . Coordinate::stringFromColumnIndex($colIndex) . "{$row4}";
                $sheet->mergeCells($cellRange)->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $row2, $label);

                $colorType = $label === 'Stock' ? 'error' : 'warning_dark';
                $sheet->getStyle($cellRange)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors[$colorType]);
            }

// Row 3 - Production Totals
            $ci = $prodStartCol;
            foreach ($productionMap as $prodId => $prodName) {
                $issueCell = Coordinate::stringFromColumnIndex($ci++) . $row3;
                $receiveCell = Coordinate::stringFromColumnIndex($ci++) . $row3;

                $issueQty = $productionTotals[$prodId]['issue_quantity'];
                $receiveQty = $productionTotals[$prodId]['receive_quantity'];

                $sheet->setCellValue($issueCell, is_int($issueQty) ? $issueQty : number_format($issueQty, 2))
                    ->getStyle($issueCell)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success']);

                $sheet->setCellValue($receiveCell, is_int($receiveQty) ? $receiveQty : number_format($receiveQty, 2))
                    ->getStyle($receiveCell)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning']);
            }

// Row 4 - Basic Info
            foreach ($materialCols as $i => $label) {
                $cell = Coordinate::stringFromColumnIndex($i + 1) . $row4;
                $sheet->setCellValue($cell, $label)->getStyle($cell)->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['highlighted']);
            }

// Row 4 - Production headers
            $ci = $prodStartCol;
            foreach ($productionMap as $prodName) {
                $startCell = Coordinate::stringFromColumnIndex($ci) . $row4;
                $endCell = Coordinate::stringFromColumnIndex($ci + 1) . $row4;
                $sheet->mergeCells("{$startCell}:{$endCell}")->setCellValue($startCell, $prodName);
                $sheet->getStyle("{$startCell}:{$endCell}")->applyFromArray($defaultStyle)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['highlighted']);
                $ci += 2;
            }

// Data rows
            $row = 5;
            foreach ($materials as $mat) {
                $line = [
                    $mat['name'],
                    $mat['uom'],
                ];

                $totalIssue = $totalNeeded = $totalLess = $totalMore = 0.0;

                $productionData = [];
                foreach ($productionMap as $prodId => $_) {
                    $prod = $mat['productions'][$prodId] ?? [
                        'raw_issue_quantity' => 0,
                        'needed_quantity' => 0,
                        'less_quantity' => 0,
                        'more_quantity' => 0
                    ];

                    $issue = $prod['raw_issue_quantity'] ?? 0;
                    $need = $prod['needed_quantity'] ?? 0;
                    $less = $prod['less_quantity'] ?? 0;
                    $more = $prod['more_quantity'] ?? 0;

                    $productionData[] = $issue ?: '-';
                    $productionData[] = $need ?: '-';

                    $totalIssue += $issue;
                    $totalNeeded += $need;
                    $totalLess += $less;
                    $totalMore += $more;
                }

                $line = array_merge($line, $productionData);

                $line[] = $totalIssue ?: '-';
                $line[] = $totalNeeded ?: '-';
                $line[] = $totalLess ?: '-';
                $line[] = $totalMore ?: '-';

                $line[] = $mat['total_stock'];
                $remainingStock = $mat['total_stock'] - $totalNeeded;
                $line[] = $remainingStock < 0 ? '-' . abs($remainingStock) : $remainingStock;

                // Insert cell-by-cell
                $colIndex = 1;
                foreach ($line as $value) {
                    $cell = Coordinate::stringFromColumnIndex($colIndex++) . $row;
                    $sheet->setCellValue($cell, $value);
                }

                // Style basic info
                for ($i = 0; $i < 2; $i++) {
                    $cell = Coordinate::stringFromColumnIndex($i + 1) . $row;
                    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // Style production cells
                $prodCol = $prodStartCol;
                foreach ($productionMap as $prodId => $_) {
                    $issueCell = Coordinate::stringFromColumnIndex($prodCol++) . $row;
                    $receiveCell = Coordinate::stringFromColumnIndex($prodCol++) . $row;

                    $sheet->getStyle($issueCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success']);
                    $sheet->getStyle($receiveCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning']);

                    $sheet->getStyle($issueCell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle($receiveCell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // Style expense
                for ($i = 0; $i < 4; $i++) {
                    $cell = Coordinate::stringFromColumnIndex($expenseStartCol + $i) . $row;
                    $sheet->getStyle($cell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['highlighted']);
                    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // Style stock status
                $stockCell = Coordinate::stringFromColumnIndex($stockStartCol) . $row;
                $remainingCell = Coordinate::stringFromColumnIndex($stockStartCol + 1) . $row;

                $sheet->getStyle($stockCell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['error']);
                $sheet->getStyle($remainingCell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning_dark']);

                $sheet->getStyle($stockCell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($remainingCell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $row++;
            }

// Auto-size columns
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->getRowDimension(1)->setRowHeight(25);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->getRowDimension(3)->setRowHeight(20);
            $sheet->getRowDimension(4)->setRowHeight(20);

            // Save File
            $dir = storage_path('exports');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $filename = 'production-issue-report' . '.xlsx';
            $path = "{$dir}/{$filename}";
            (new Xlsx($spreadsheet))->save($path);

            return response()->json([
                'filename' => $filename,
                'file_url' => url("storage/exports/{$filename}"),
                'status' => 200,
                'result' => true,
            ]);

        } catch (Exception $e) {
            // Handle errors gracefully
            return response([
                'error' => $e->getMessage(),
                'result' => false,
                'status' => 500,
            ]);
        }
    }

    public function issueGeneratePdf(Request $request)
    {
        $params = $request->all();
        $productionConfigId = $this->domain['pro_config'];
        $domainConfigId = $this->domain['domain_id'];

        try {
            // Use the reusable method
            $processedData = $this->processProductionBatchData($params, $domainConfigId, $productionConfigId);

            $materials = $processedData['materials'];
            $productionMap = $processedData['productionMap'];
            $productionTotals = $processedData['productionTotals'];
            $batches = $processedData['batches'];

            // TCPDF setup
            $pdf = new TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
            $pdf->SetTitle('Production Issue Report');
            $pdf->SetMargins(5, 10, 5);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 10, 'Production Issue Report', 0, 1, 'C');
            $pdf->Ln(5);

// Setup variables
            $productionCount = count($productionMap);
            $headerHeight = 8;

            $materialWidth = 60;
            $unitWidth = 12;
            $sumWidth = 20;

            $totalBasicFixed = $materialWidth + $unitWidth;
            $totalSummaryFixed = $sumWidth * 4;
            $totalStockFixed = $sumWidth * 2;

            $pageUsableWidth = 410;

            $remainingWidthForProducts = $pageUsableWidth - ($totalBasicFixed + $totalSummaryFixed + $totalStockFixed);
            $minProdWidth = 20;
            $prodWidth = max($minProdWidth, floor($remainingWidthForProducts / max(1, $productionCount)));
            $totalProdWidth = $prodWidth * $productionCount;

// COLORS
            $colors = [
                'basic' => [169, 208, 142],
                'prod_header' => [217, 225, 242],
                'expense' => [252, 213, 180],
                'stock' => [255, 235, 156],
                'issue' => [198, 224, 180],
                'receive' => [255, 242, 204],
                'red' => [248, 215, 218],
                'white' => [255, 255, 255],
            ];

// DYNAMIC WAREHOUSE NAME
            $warehouseName = isset($batches[0]->warehouse_name) ? $batches[0]->warehouse_name : '';
            $stockHeader = $warehouseName ? "Current Stock ({$warehouseName})" : "Current Stock";

// Starting Y
            $startY = $pdf->GetY();

// ========= Row 1: Section Headers =========
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetXY(5, $startY);
            $pdf->SetFillColor(...$colors['basic']);
            $pdf->MultiCell($totalBasicFixed, $headerHeight * 3, 'Basic Information', 1, 'C', true, 0);
            $pdf->SetFillColor(...$colors['prod_header']);
            $pdf->MultiCell($totalProdWidth, $headerHeight * 2, 'Issue Production Quantity', 1, 'C', true, 0);
            $pdf->SetFillColor(...$colors['expense']);
            $pdf->MultiCell($totalSummaryFixed, $headerHeight, 'Total Expense Material', 1, 'C', true, 0);
            $pdf->SetFillColor(...$colors['stock']);
            $pdf->MultiCell($totalStockFixed, $headerHeight, $stockHeader, 1, 'C', true, 1);

// ========= Row 2: Issue / Receive =========
            $currentX = 5 + $totalBasicFixed;
            $pdf->SetY($startY + $headerHeight);
            foreach ($productionMap as $prodId => $_) {
                $pdf->SetX($currentX);
                $pdf->SetFillColor(...$colors['issue']);
                $pdf->Cell($prodWidth / 2, $headerHeight, 'Issue', 1, 0, 'C', true);
                $pdf->SetFillColor(...$colors['receive']);
                $pdf->Cell($prodWidth / 2, $headerHeight, 'Receive', 1, 0, 'C', true);
                $currentX += $prodWidth;
            }

// Vertical headers: Expense + Stock
            $currentX = 5 + $totalBasicFixed + $totalProdWidth;
            $pdf->SetY($startY + $headerHeight);
            foreach (['Issue', 'Expense', 'Less', 'More'] as $label) {
                $pdf->SetXY($currentX, $startY + $headerHeight);
                $pdf->SetFillColor(...$colors['expense']);
                $pdf->MultiCell($sumWidth, $headerHeight * 3, $label, 1, 'C', true, 0);
                $currentX += $sumWidth;
            }
            foreach (['Stock', 'Remaining'] as $label) {
                $pdf->SetXY($currentX, $startY + $headerHeight);
                $pdf->SetFillColor(...$colors['stock']);
                $pdf->MultiCell($sumWidth, $headerHeight * 3, $label, 1, 'C', true, 0);
                $currentX += $sumWidth;
            }
            $pdf->Ln();

// ========= Row 3: Production Totals (Issue / Receive) =========
            $currentX = 5 + $totalBasicFixed;
            $pdf->SetY($startY + $headerHeight * 2);
            foreach ($productionMap as $prodId => $_) {
                $issueQty = $productionTotals[$prodId]['issue_quantity'];
                $receiveQty = $productionTotals[$prodId]['receive_quantity'];
                $pdf->SetX($currentX);
                $pdf->SetFillColor(...$colors['issue']);
                $pdf->Cell($prodWidth / 2, $headerHeight, $issueQty ? number_format($issueQty, 2) : '-', 1, 0, 'C', true);
                $pdf->SetFillColor(...$colors['receive']);
                $pdf->Cell($prodWidth / 2, $headerHeight, $receiveQty ? number_format($receiveQty, 2) : '-', 1, 0, 'C', true);
                $currentX += $prodWidth;
            }
            $pdf->Ln();

// ========= Row 4: Column Headers =========
            $pdf->SetY($startY + $headerHeight * 3);
            $pdf->SetX(5);
            $basicHeaders = ['Material Item', 'Unit'];
            $basicWidths = [$materialWidth, $unitWidth];
            foreach ($basicHeaders as $i => $label) {
                $pdf->SetFillColor(...$colors['basic']);
                $pdf->Cell($basicWidths[$i], $headerHeight, $label, 1, 0, 'C', true);
            }
            foreach ($productionMap as $prodName) {
                $maxChars = floor($prodWidth / 1.5);
                $name = strlen($prodName) > $maxChars ? substr($prodName, 0, $maxChars - 3) . '...' : $prodName;
                $pdf->SetFillColor(...$colors['prod_header']);
                $pdf->Cell($prodWidth, $headerHeight, $name, 1, 0, 'C', true);
            }
            $pdf->Ln();

// ========= Data Rows =========
            $pdf->SetFont('helvetica', '', 7);
            foreach ($materials as $mat) {
                $pdf->SetX(5);
                $pdf->SetFillColor(...$colors['white']);
                $pdf->Cell($materialWidth, 8, substr($mat['name'], 0, 100), 1, 0, 'L', true);
                $pdf->Cell($unitWidth, 8, $mat['uom'], 1, 0, 'C', true);

                $totalIssue = $totalNeed = $totalLess = $totalMore = 0;

                foreach ($productionMap as $prodId => $_) {
                    $prod = $mat['productions'][$prodId] ?? [
                        'raw_issue_quantity' => 0,
                        'needed_quantity' => 0,
                        'less_quantity' => 0,
                        'more_quantity' => 0
                    ];
                    $i = $prod['raw_issue_quantity'] ?? 0;
                    $n = $prod['needed_quantity'] ?? 0;
                    $totalIssue += $i;
                    $totalNeed += $n;
                    $totalLess += $prod['less_quantity'] ?? 0;
                    $totalMore += $prod['more_quantity'] ?? 0;

                    $pdf->SetFillColor(...$colors['issue']);
                    $pdf->Cell($prodWidth / 2, 8, $i ? number_format($i, 2) : '-', 1, 0, 'R', true);
                    $pdf->SetFillColor(...$colors['receive']);
                    $pdf->Cell($prodWidth / 2, 8, $n ? number_format($n, 2) : '-', 1, 0, 'R', true);
                }

                // Expense + Stock columns
                $pdf->SetFillColor(...$colors['white']);
                $pdf->Cell($sumWidth, 8, number_format($totalIssue, 2), 1, 0, 'R', true);
                $pdf->Cell($sumWidth, 8, number_format($totalNeed, 2), 1, 0, 'R', true);
                $pdf->Cell($sumWidth, 8, number_format($totalLess, 2), 1, 0, 'R', true);
                $pdf->Cell($sumWidth, 8, number_format($totalMore, 2), 1, 0, 'R', true);

                $pdf->Cell($sumWidth, 8, number_format($mat['total_stock'], 2), 1, 0, 'R', true);

                $remaining = $mat['total_stock'] - $totalNeed;
                if ($remaining < 0) {
                    $pdf->SetFillColor(...$colors['red']);
                } else {
                    $pdf->SetFillColor(...$colors['white']);
                }
                $pdf->Cell($sumWidth, 8, number_format($remaining, 2), 1, 1, 'R', true);
            }


            // Save
            $dir = storage_path('exports');
            if (!File::exists($dir)) File::makeDirectory($dir);
            $fileName = 'production-issue-report.pdf';
            $pdf->Output("{$dir}/{$fileName}", 'F');

            return response()->json([
                'filename' => $fileName,
                'file_url' => url("storage/exports/{$fileName}"),
                'status' => 200,
                'result' => true
            ]);
        } catch (Exception $e) {
            return response([
                'error' => $e->getMessage(),
                'result' => false,
                'status' => 500,
            ]);
        }
    }


    /**
     * Process production batch data and build structured arrays for materials and production data
     *
     * @param array $params Request parameters
     * @param int $domainConfigId Domain configuration ID
     * @param int $productionConfigId Production configuration ID
     * @return array Returns structured data with materials, productionMap, and productionTotals
     * @throws Exception When no data is found or processing fails
     */
    private function processProductionBatchData($params, $domainConfigId, $productionConfigId)
    {
        $batches = ProductionBatchModel::issueReportData($params, $domainConfigId, $productionConfigId);

        if ($batches->isEmpty()) {
            throw new Exception('No data to export.');
        }

        // Build data structure
        $materials = [];
        $productionMap = [];
        $productionTotals = []; // To store issue and receive totals per production item

        foreach ($batches as $batch) {
            foreach ($batch->batchItems as $item) {
                $prodId = $item->production_item_id;
                $productionMap[$prodId] = $item->name;

                // Initialize production totals if not exists
                if (!isset($productionTotals[$prodId])) {
                    $productionTotals[$prodId] = [
                        'issue_quantity' => 0,
                        'receive_quantity' => 0
                    ];
                }

                // Add to production totals
                $productionTotals[$prodId]['issue_quantity'] += $item->issue_quantity ?? 0;
                $productionTotals[$prodId]['receive_quantity'] += $item->receive_quantity ?? 0;

                foreach ($item->productionExpenses as $exp) {
                    $matId = $exp->material_id;

                    if (!isset($materials[$matId])) {
                        $materials[$matId] = [
                            'name' => $exp->name,
                            'uom' => $exp->uom ?? 'KG',
                            'opening' => $exp->opening_quantity ?? 0,
                            'branch_stock' => $exp->narayangonj_stock ?? 0,
                            'total_stock' => ($exp->stock_quantity ?? 0),
                            'productions' => []
                        ];
                    }

                    if (!isset($materials[$matId]['productions'][$prodId])) {
                        $materials[$matId]['productions'][$prodId] = [
                            'raw_issue_quantity' => 0,
                            'needed_quantity' => 0,
                            'less_quantity' => 0,
                            'more_quantity' => 0
                        ];
                    }

                    $materials[$matId]['productions'][$prodId]['raw_issue_quantity'] += $exp->raw_issue_quantity ?? 0;
                    $materials[$matId]['productions'][$prodId]['needed_quantity'] += $exp->needed_quantity ?? 0;
                    $materials[$matId]['productions'][$prodId]['less_quantity'] += $exp->less_quantity ?? 0;
                    $materials[$matId]['productions'][$prodId]['more_quantity'] += $exp->more_quantity ?? 0;
                }
            }
        }

        return [
            'materials' => $materials,
            'productionMap' => $productionMap,
            'productionTotals' => $productionTotals,
            'batches' => $batches // Include original batches in case needed
        ];
    }

    public function issueReportDownload($type)
    {
        $fileName = '';
        if ($type == 'xlsx') {
            $fileName = 'production-issue-report.xlsx';
        } elseif ($type == 'pdf') {
            $fileName = 'production-issue-report.pdf';
        }
        $filePath = storage_path('exports/' . $fileName);
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }


    public function matrixWarehouse(Request $request)
    {
        $params = $request->only('start_date', 'end_date', 'warehouse_id', 'year', 'month');
        $productionConfigId = $this->domain['pro_config'];
        $domainConfigId = $this->domain['domain_id'];

        $entity = ProductionBatchModel::warehouseMatrixReportData($params, $domainConfigId, $productionConfigId);
        return response()->json([
            'message' => 'success',
            'status' => ResponseAlias::HTTP_OK,
            'data' => $entity
        ], ResponseAlias::HTTP_OK);
    }

    public function matrixWarehouseXlsx(Request $request)
    {
        $params = $request->only('warehouse_id', 'month', 'year');
        $productionConfigId = $this->domain['pro_config'];
        $domainConfigId = $this->domain['domain_id'];

        try {
            // Use the reusable method to get processed data
            $processedData = ProductionBatchModel::warehouseMatrixReportData($params, $domainConfigId, $productionConfigId);

            $warehouseName = $processedData['warehouse_name'] ?? 'Warehouse';

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();

            // Define colors matching frontend
            $colors = [
                'header_bg' => 'E3F2FD',           // Light blue for headers
                'success_bg' => 'D5EDDA',          // Light green for production qty
                'warning_bg' => 'FFF3CD',          // Light yellow for production amount
                'error_bg' => 'F8D7DA',            // Light red for closing stock
                'warning_dark_bg' => 'E2B51F',     // Dark yellow for closing amount
                'basic_info' => 'F8F9FA',          // Light gray for basic info
                'date_header' => '1976D2'          // Blue for date headers
            ];

            $defaultStyle = [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];

            $dataStyle = [
                'font' => ['bold' => false, 'size' => 9],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];

            // Remove default sheet
            $spreadsheet->removeSheetByIndex(0);

            $dateWiseData = $processedData['date_wise_data'] ?? [];

            $sheetIndex = 0;

            foreach ($dateWiseData as $dateKey => $dateData) {
                $date = $dateData['date']; // DD-MM-YYYY format
                $materials = $dateData['data'] ?? [];
                $productionItemsOnDate = $dateData['production_items_on_date'] ?? [];

                // Convert date-specific production items to an indexed array for iteration
                $productionItemsForCurrentDateArray = [];
                foreach ($productionItemsOnDate as $key => $item) {
                    // Ensure $item is treated as an object or array based on how JSON is decoded
                    $productionItemsForCurrentDateArray[] = [
                        'production_item_id' => $key,
                        'item_name' => is_object($item) ? ($item->item_name ?? 'Unknown Item') : ($item['item_name'] ?? 'Unknown Item')
                    ];
                }

                // Create new worksheet for each date
                // Changed sheet name to use $dateKey (YYYY-MM-DD) for guaranteed uniqueness
                $sheetName = "Report - {$dateKey}";
                Log::info("XLSX Generation: Attempting to create sheet: {$sheetName}"); // Log each sheet creation attempt

                $sheet = new Worksheet($spreadsheet, $sheetName);
                $spreadsheet->addSheet($sheet, $sheetIndex);
                $sheetIndex++;

                // Calculate column positions dynamically for THIS sheet
                $productionUsageCols = count($productionItemsForCurrentDateArray) > 0 ? count($productionItemsForCurrentDateArray) * 2 : 2;

                // Row 1: First header row (matching frontend structure)
                $row1 = 1;
                $row2 = 2;

                // Headers with rowSpan=2 (these span both header rows)
                $singleRowHeaders = [
                    'A' => 'S.L',
                    'B' => 'Date', // Keep Date column in the sheet
                    'C' => 'Product Name',
                    'D' => 'Unit',
                    'E' => 'Present Day Rate',
                    'F' => 'Opening Stock',
                    'G' => "In {$warehouseName}",
                    'H' => 'Total In',
                    'I' => 'IN Amount (TK)'
                ];

                // Set single row headers (rowSpan=2)
                foreach ($singleRowHeaders as $col => $value) {
                    $sheet->setCellValue($col . $row1, $value);
                    $sheet->mergeCells($col . $row1 . ':' . $col . $row2);
                    $sheet->getStyle($col . $row1 . ':' . $col . $row2)->applyFromArray($defaultStyle);
                    $sheet->getStyle($col . $row1 . ':' . $col . $row2)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['header_bg']);
                }

                // Production Usage header (colSpan across all production columns)
                $prodStartCol = 'J';
                $prodEndColIndex = 9 + $productionUsageCols; // J is column 10, so 9 + productionUsageCols
                $prodEndCol = Coordinate::stringFromColumnIndex($prodEndColIndex);
                $sheet->setCellValue($prodStartCol . $row1, 'Production Usage');
                $sheet->mergeCells($prodStartCol . $row1 . ':' . $prodEndCol . $row1);
                $sheet->getStyle($prodStartCol . $row1 . ':' . $prodEndCol . $row1)->applyFromArray($defaultStyle);
                $sheet->getStyle($prodStartCol . $row1 . ':' . $prodEndCol . $row1)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success_bg']);

                // Remaining single headers (rowSpan=2)
                $remainingStartColIndex = $prodEndColIndex + 1;
                $remainingHeaders = ['Out', 'Out Amount', 'Closing Stock', 'Closing Amount'];
                for ($i = 0; $i < count($remainingHeaders); $i++) {
                    $colIndex = $remainingStartColIndex + $i;
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->setCellValue($col . $row1, $remainingHeaders[$i]);
                    $sheet->mergeCells($col . $row1 . ':' . $col . $row2);
                    $sheet->getStyle($col . $row1 . ':' . $col . $row2)->applyFromArray($defaultStyle);
                    // Apply specific colors for closing columns
                    $bgColor = $colors['header_bg'];
                    if ($remainingHeaders[$i] === 'Closing Stock') {
                        $bgColor = $colors['error_bg'];
                    } elseif ($remainingHeaders[$i] === 'Closing Amount') {
                        $bgColor = $colors['warning_dark_bg'];
                    }
                    $sheet->getStyle($col . $row1 . ':' . $col . $row2)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
                }

                // Row 2: Second header row for production items (dynamic based on productionItemsForCurrentDateArray)
                $colIndex = 10; // Start from column J
                if (count($productionItemsForCurrentDateArray) > 0) {
                    foreach ($productionItemsForCurrentDateArray as $item) {
                        $qtyCol = Coordinate::stringFromColumnIndex($colIndex);
                        $amountCol = Coordinate::stringFromColumnIndex($colIndex + 1);
                        $itemName = $item['item_name'] ?? 'Production Item';

                        // Quantity column
                        $sheet->setCellValue($qtyCol . $row2, $itemName . ' Qty');
                        $sheet->getStyle($qtyCol . $row2)->applyFromArray($defaultStyle);
                        $sheet->getStyle($qtyCol . $row2)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success_bg']);

                        // Amount column
                        $sheet->setCellValue($amountCol . $row2, $itemName . ' Amount');
                        $sheet->getStyle($amountCol . $row2)->applyFromArray($defaultStyle);
                        $sheet->getStyle($amountCol . $row2)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning_bg']);
                        $colIndex += 2;
                    }
                } else {
                    // Default production columns when no production items for this date
                    $sheet->setCellValue('J' . $row2, 'Production Qty');
                    $sheet->getStyle('J' . $row2)->applyFromArray($defaultStyle);
                    $sheet->getStyle('J' . $row2)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success_bg']);
                    $sheet->setCellValue('K' . $row2, 'Production Amount');
                    $sheet->getStyle('K' . $row2)->applyFromArray($defaultStyle);
                    $sheet->getStyle('K' . $row2)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning_bg']);
                }

                // Data rows
                $dataRow = 3;
                foreach ($materials as $index => $material) {
                    $colIndex = 1;

                    // Basic info columns (A to I)
                    $basicData = [
                        $material['sl'] ?? ($index + 1),          // A: S.L
                        $date,                                     // B: Date
                        $material['product_name_english'] ?? '',   // C: Product Name
                        $material['unit'] ?? '',                   // D: Unit
                        $material['present_day_rate'] ?? 0,        // E: Present Day Rate
                        $material['opening_stock'] ?? 0,           // F: Opening Stock
                        $material['in_warehouse'] ?? 0,            // G: In Warehouse
                        $material['total_in'] ?? 0,                // H: Total In
                        $material['in_amount'] ?? 0                // I: IN Amount
                    ];

                    foreach ($basicData as $value) {
                        $cell = Coordinate::stringFromColumnIndex($colIndex) . $dataRow;
                        $sheet->setCellValue($cell, $value);
                        $sheet->getStyle($cell)->applyFromArray($dataStyle);
                        // Special formatting for text columns
                        if ($colIndex == 2 || $colIndex == 3) { // Date and Product Name
                            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        $colIndex++;
                    }

                    // Production usage columns (J onwards) (dynamic based on productionItemsForCurrentDateArray)
                    if (count($productionItemsForCurrentDateArray) > 0) {
                        foreach ($productionItemsForCurrentDateArray as $item) {
                            $productionItemId = $item['production_item_id'];
                            $usage = $material['production_usage'][$productionItemId] ?? null;

                            // Quantity column
                            $qtyCell = Coordinate::stringFromColumnIndex($colIndex) . $dataRow;
                            $qtyValue = $usage ? ($usage['quantity'] ?? '-') : '-';
                            $sheet->setCellValue($qtyCell, $qtyValue);
                            $sheet->getStyle($qtyCell)->applyFromArray($dataStyle);
                            $sheet->getStyle($qtyCell)->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success_bg']);
                            $colIndex++;

                            // Amount column
                            $amountCell = Coordinate::stringFromColumnIndex($colIndex) . $dataRow;
                            $amountValue = $usage ? ($usage['amount'] ?? '-') : '-';
                            $sheet->setCellValue($amountCell, $amountValue);
                            $sheet->getStyle($amountCell)->applyFromArray($dataStyle);
                            $sheet->getStyle($amountCell)->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning_bg']);
                            $colIndex++;
                        }
                    } else {
                        // Default production columns when no production items for this date
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $dataRow, '-');
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndex) . $dataRow)->applyFromArray($dataStyle);
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndex) . $dataRow)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['success_bg']);
                        $colIndex++;
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $dataRow, '-');
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndex) . $dataRow)->applyFromArray($dataStyle);
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndex) . $dataRow)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning_bg']);
                        $colIndex++;
                    }

                    // Remaining columns (Out, Out Amount, Closing Stock, Closing Amount)
                    $remainingData = [
                        $material['out'] ?? 0,                    // Out
                        $material['out_amount'] ?? 0,            // Out Amount
                        $material['closing_stock'] ?? 0,         // Closing Stock
                        $material['closing_stock_amount'] ?? 0   // Closing Amount
                    ];

                    for ($i = 0; $i < count($remainingData); $i++) {
                        $cell = Coordinate::stringFromColumnIndex($colIndex) . $dataRow;
                        $value = $remainingData[$i];
                        $sheet->setCellValue($cell, $value);
                        $sheet->getStyle($cell)->applyFromArray($dataStyle);

                        // Special styling for closing stock and amount
                        if ($i == 2) { // Closing Stock
                            $sheet->getStyle($cell)->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['error_bg']);
                            // Red text for negative values
                            if ($value < 0) {
                                $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FF0000');
                                $sheet->getStyle($cell)->getFont()->setBold(true);
                            }
                        } elseif ($i == 3) { // Closing Amount
                            $sheet->getStyle($cell)->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors['warning_dark_bg']);
                            // Red text for negative values
                            if ($value < 0) {
                                $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FF0000');
                                $sheet->getStyle($cell)->getFont()->setBold(true);
                            }
                        }
                        $colIndex++;
                    }
                    $dataRow++;
                }

                // Auto-size columns
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // Freeze panes (freeze first 2 rows and up to column E - Present Day Rate)
                $sheet->freezePane('F3');
            }

            // Set active sheet to first one
            if ($spreadsheet->getSheetCount() > 0) {
                $spreadsheet->setActiveSheetIndex(0);
            }

            // Save File
            $dir = storage_path('exports');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $filename = 'production-matrix-report' . '.xlsx';
            $path = "{$dir}/{$filename}";
            (new Xlsx($spreadsheet))->save($path);

            return response()->json([
                'filename' => $filename,
                'file_url' => url("storage/exports/{$filename}"),
                'status' => 200,
                'result' => true,
            ]);
        } catch (Exception $e) {
            // Handle errors gracefully
            Log::error("XLSX Generation Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response([
                'error' => $e->getMessage(),
                'result' => false,
                'status' => 500,
            ]);
        }
    }

    public function matrixWarehousePdf(Request $request)
    {
        $params = $request->only('warehouse_id', 'month', 'year');
        $productionConfigId = $this->domain['pro_config'];
        $domainConfigId = $this->domain['domain_id'];

        try {
            $processedData = ProductionBatchModel::warehouseMatrixReportData($params, $domainConfigId, $productionConfigId);

            $warehouseName = $processedData['warehouse_name'] ?? 'Warehouse';
            $dateWiseData = $processedData['date_wise_data'] ?? [];

            $pdf = new TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);


            $pdf->SetTitle('Warehouse Matrix Report');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(5);
            $pdf->SetAutoPageBreak(true, 15);

            foreach ($dateWiseData as $dateKey => $dateData) { // Use $dateKey for sheet naming consistency
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 10, "Matrix Report — {$dateData['date_formatted']} — {$warehouseName}", 0, 1, 'C'); // Use formatted date for title

                $materials = $dateData['data'] ?? [];
                $productionItemsOnDate = $dateData['production_items_on_date'] ?? [];

                // Dynamically build productionItemsArray for the current date
                $productionItemsForCurrentDateArray = [];
                foreach ($productionItemsOnDate as $key => $item) {
                    $productionItemsForCurrentDateArray[] = [
                        'production_item_id' => $key,
                        'item_name' => is_object($item) ? ($item->item_name ?? 'Item') : ($item['item_name'] ?? 'Item'),
                    ];
                }

                // Build headers dynamically for the current date
                $headers = ['S.L', 'Date', 'Product Name', 'Unit', 'Rate', 'Opening', 'In', 'Total In', 'In Amount'];
                foreach ($productionItemsForCurrentDateArray as $item) {
                    $headers[] = $item['item_name'] . ' Qty';
                    $headers[] = $item['item_name'] . ' Amount';
                }
                $headers = array_merge($headers, ['Out', 'Out Amount', 'Closing', 'Closing Amount']);

                $totalColumns = count($headers);
                $pageWidth = 420 - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
                $colWidth = floor($pageWidth / $totalColumns);
                $colWidths = array_fill(0, $totalColumns, $colWidth); // Initialize with equal widths

                // Adjust specific column widths if needed (e.g., for Product Name)
                // Example: if 'Product Name' is at index 2 (0-indexed)
                $productNameColIndex = array_search('Product Name', $headers);
                if ($productNameColIndex !== false) {
                    $colWidths[$productNameColIndex] = $colWidth * 1.5; // Make Product Name column wider
                    // Recalculate other widths or adjust remaining columns
                    $remainingWidth = $pageWidth - $colWidths[$productNameColIndex];
                    $remainingCols = $totalColumns - 1;
                    $newColWidth = floor($remainingWidth / $remainingCols);
                    for ($i = 0; $i < $totalColumns; $i++) {
                        if ($i !== $productNameColIndex) {
                            $colWidths[$i] = $newColWidth;
                        }
                    }
                }


                $cellHeight = 14;
                $fontSize = 7;

                // === Render Header with vertical center ===
                $pdf->SetFont('helvetica', 'B', $fontSize);
                $pdf->SetFillColor(227, 242, 253); // header_bg color
                $xStart = $pdf->GetX();
                $yStart = $pdf->GetY();

                for ($i = 0; $i < $totalColumns; $i++) {
                    $cellW = $colWidths[$i];
                    $title = $headers[$i];
                    $lineCount = $pdf->getNumLines($title, $cellW);
                    $lineHeight = $pdf->getCellHeightRatio() * $fontSize * 0.3528;
                    $contentHeight = $lineCount * $lineHeight;
                    $offsetY = $yStart + (($cellHeight - $contentHeight) / 2);

                    // Apply specific header colors
                    $fillColor = [227, 242, 253]; // Default header_bg
                    if (strpos($title, 'Qty') !== false) {
                        $fillColor = [213, 237, 218]; // success_bg
                    } elseif (strpos($title, 'Amount') !== false) {
                        $fillColor = [255, 243, 205]; // warning_bg
                    } elseif ($title === 'Closing') {
                        $fillColor = [248, 215, 218]; // error_bg
                    } elseif ($title === 'Closing Amount') {
                        $fillColor = [226, 181, 31]; // warning_dark_bg
                    }
                    $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);

                    $pdf->Rect($xStart, $yStart, $cellW, $cellHeight, 'DF');
                    $pdf->SetXY($xStart, $offsetY);
                    $pdf->MultiCell($cellW, $cellHeight, $title, 0, 'C', false, 0);
                    $xStart += $cellW;
                }
                $currentY = $yStart + $cellHeight;
                $pdf->SetY($currentY);

                // === Render Data Rows ===
                $pdf->SetFont('helvetica', '', $fontSize);
                $rowNo = 1;
                foreach ($materials as $material) {
                    $row = [];
                    // Basic data
                    $row[] = $rowNo++;
                    $row[] = $dateData['date']; // Use DD-MM-YYYY date for data row
                    $row[] = $material['product_name_english'] ?? '-';
                    $row[] = $material['unit'] ?? '-';
                    $row[] = $material['present_day_rate'] ?? 0;
                    $row[] = $material['opening_stock'] ?? 0;
                    $row[] = $material['in_warehouse'] ?? 0;
                    $row[] = $material['total_in'] ?? 0;
                    $row[] = $material['in_amount'] ?? 0;

                    // Production usage data (dynamic based on productionItemsForCurrentDateArray)
                    foreach ($productionItemsForCurrentDateArray as $item) {
                        $id = $item['production_item_id'];
                        $usage = $material['production_usage'][$id] ?? ['quantity' => '-', 'amount' => '-'];
                        $row[] = $usage['quantity'] ?? '-';
                        $row[] = $usage['amount'] ?? '-';
                    }

                    $row[] = $material['out'] ?? 0;
                    $row[] = $material['out_amount'] ?? 0;
                    $row[] = $material['closing_stock'] ?? 0;
                    $row[] = $material['closing_stock_amount'] ?? 0;

                    // Draw Row
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    for ($i = 0; $i < count($row); $i++) {
                        $value = $row[$i];
                        $cellW = $colWidths[$i];
                        $headerText = $headers[$i]; // Get header text for current column

                        $isSL = $headerText === 'S.L';
                        $align = is_numeric($value) && !$isSL ? 'R' : 'L'; // Align numbers right, others left

                        $pdf->SetXY($x, $y);
                        $display = $isSL ? (int)$value : (is_numeric($value) ? number_format($value, 2) : (string)$value);

                        $lineCount = $pdf->getNumLines($display, $cellW);
                        $lineHeight = $pdf->getCellHeightRatio() * $fontSize * 0.3528;
                        $contentHeight = $lineCount * $lineHeight;
                        $offsetY = $y + (($cellHeight - $contentHeight) / 2);

                        // Background color for closing values and production usage
                        $fillColor = [255, 255, 255]; // Default white
                        $textColor = [0, 0, 0]; // Default black
                        $isBold = false;

                        if (strpos($headerText, 'Qty') !== false) {
                            $fillColor = [213, 237, 218]; // success_bg
                        } elseif (strpos($headerText, 'Amount') !== false) {
                            $fillColor = [255, 243, 205]; // warning_bg
                        } elseif ($headerText === 'Closing') {
                            $fillColor = [248, 215, 218]; // error_bg
                            if (is_numeric($value) && floatval($value) < 0) {
                                $textColor = [255, 0, 0]; // Red for negative
                                $isBold = true;
                            }
                        } elseif ($headerText === 'Closing Amount') {
                            $fillColor = [226, 181, 31]; // warning_dark_bg
                            if (is_numeric($value) && floatval($value) < 0) {
                                $textColor = [255, 0, 0]; // Red for negative
                                $isBold = true;
                            }
                        }

                        $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                        $pdf->SetFont('helvetica', $isBold ? 'B' : '', $fontSize);

                        $pdf->Rect($x, $y, $cellW, $cellHeight, 'DF');
                        $pdf->SetXY($x, $offsetY);
                        $pdf->MultiCell($cellW, $cellHeight, $display, 0, $align, false, 0);
                        $x += $cellW;
                    }
                    // Reset font and text color for next row
                    $pdf->SetFont('helvetica', '', $fontSize);
                    $pdf->SetTextColor(0, 0, 0);

                    // Move to next row
                    $pdf->SetY($y + $cellHeight);
                }
            }

            // Save PDF
            $dir = storage_path('exports');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $fileName = 'production-matrix-report.pdf';
            $filePath = "{$dir}/{$fileName}";
            $pdf->Output($filePath, 'F');

            return response()->json([
                'filename' => $fileName,
                'file_url' => url("storage/exports/{$fileName}"),
                'status' => 200,
                'result' => true,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500,
                'result' => false,
            ]);
        }
    }

    public function matrixReportDownload($type)
    {
        $fileName = '';
        if ($type == 'xlsx') {
            $fileName = 'production-matrix-report.xlsx';
        } elseif ($type == 'pdf') {
            $fileName = 'production-matrix-report.pdf';
        }
        $filePath = storage_path('exports/' . $fileName);
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }


}
