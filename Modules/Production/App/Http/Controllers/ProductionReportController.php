<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
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

        } catch (\Exception $e) {
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
            $pdf = new \TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
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
        } catch (\Exception $e) {
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
     * @throws \Exception When no data is found or processing fails
     */
    private function processProductionBatchData($params, $domainConfigId, $productionConfigId)
    {
        $batches = ProductionBatchModel::issueReportData($params, $domainConfigId, $productionConfigId);

        if ($batches->isEmpty()) {
            throw new \Exception('No data to export.');
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
        }elseif ($type == 'pdf') {
            $fileName = 'production-issue-report.pdf';
        }
        $filePath = storage_path('exports/'.$fileName);
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
