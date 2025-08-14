<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use TCPDF;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportService
{
    private const EXPORT_DIR = 'exports';

    /**
     * Generate report file and return output file details.
     *
     * @param string $type     // e.g. ledger, invoice, customer-summary
     * @param string $format   // pdf | xlsx
     * @param array  $data     // structured dataset
     * @param array  $options  // extra options (headers, filename, etc.)
     * @return array{filename: string, file_url: string, file_path: string}
     */
    public function generateReport(string $type, string $format, array $data, array $options = []): array
    {
        $filename = $options['filename'] ?? "{$type}-report.{$format}";

        $path = $this->ensureExportPath() . '/' . $filename;

        match (strtolower($format)) {
            'pdf'  => $this->generatePdf($data, $path, $options),
            'xlsx' => $this->generateXlsx($data, $path, $options),
            default => throw new \InvalidArgumentException('Invalid export format.')
        };

        return [
            'status' => 200,
            'filename'  => $filename,
            'file_url'  => asset('storage/' . self::EXPORT_DIR . '/' . $filename),
            'file_path' => $path,
        ];
    }

    /**
     * Download a file from exports folder.
     */
    public function download(string $fileName): BinaryFileResponse
    {
        $fullPath = public_path('storage/' . self::EXPORT_DIR . '/' . $fileName);

        if (!File::exists($fullPath)) {
            abort(404, 'File does not exist.');
        }

        return response()->download($fullPath, $fileName)->deleteFileAfterSend(true);
    }

    private function ensureExportPath(): string
    {
        $path = storage_path('app/public/' . self::EXPORT_DIR);

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return $path;
    }

    /**
     * Generate PDF to a given path from generic dataset.
     */
    private function generatePdf(array $data, string $filePath, array $options = []): void
    {
        $headers = $options['headers'] ?? array_keys($data[0] ?? []);
        $titleLines = $options['titles'] ?? [];

        $pdf = new \TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
        $pdf->SetTitle('Ledger Report');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->AddPage();

        // Render title lines
        foreach ($titleLines as $line) {
            $text = $line['text'] ?? '';
            $align = strtoupper($line['align'] ?? 'C');
            $fontSize = $line['font_size'] ?? 10;
            $style = ($line['bold'] ?? false) ? 'B' : '';

            if (!empty($line['fill']) && is_array($line['fill'])) {
                $pdf->SetFillColor(...$line['fill']);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', $style, $fontSize);
                $pdf->Cell(0, 8, $text, 0, 1, $align, true);
            } else {
                $pdf->SetFont('helvetica', $style, $fontSize);
                $pdf->Cell(0, 8, $text, 0, 1, $align);
            }
        }

        $pdf->Ln(2);

        // Define column widths
        $pageWidth = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $usableWidth = $pageWidth - $margins['left'] - $margins['right'];
        $numCols = count($headers);
        $columnWidths = [];

        foreach ($headers as $header) {
            $colKey = strtolower(trim($header));
            $columnWidths[] = ($colKey === 'ledger name') ? 40 : ($usableWidth - 40) / ($numCols - 1);
        }

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(227, 242, 253);
        foreach ($headers as $i => $header) {
            $pdf->Cell($columnWidths[$i], 8, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 9);

        foreach ($data as $row) {
            // 🔶 Handle special styled rows (Previous Opening Balance row)
            if (isset($row['__colspan']) && isset($row['__style'])) {
                $style = $row['__style'];
                $label = $row['Debit'] ?? 'Previous Opening Balance';
                $value = $row['Closing'] ?? '';
                $colspan = intval($row['__colspan']);

                $labelWidth = array_sum(array_slice($columnWidths, 0, $colspan));
                $valueWidth = $columnWidths[$colspan];

                $pdf->SetFillColor(...($style['background'] ?? [255, 255, 153]));
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', $style['bold'] ? 'B' : '', 10);

                $pdf->Cell($labelWidth, 10, $label, 1, 0, 'L', true);
                $pdf->Cell($valueWidth, 10, $value, 1, 1, 'R', true);
                continue;
            }

            // 🔁 MULTICELL ROW WITH WRAPPING
            $xStart = $pdf->GetX();
            $yStart = $pdf->GetY();
            $rowHeight = 0;

            // First pass: calculate individual heights
            $cellHeights = [];
            foreach ($headers as $i => $key) {
                $text = (string) ($row[$key] ?? '-');
                $cellHeight = $pdf->getStringHeight($columnWidths[$i], $text, false, true, '', 1);
                $cellHeights[] = $cellHeight;
            }

            $rowHeight = max($cellHeights);

            // Second pass: render cells properly
            $pdf->SetXY($margins['left'], $yStart);

            foreach ($headers as $i => $key) {
                $text = (string) ($row[$key] ?? '-');
                $align = $this->detectCellAlignment($key);

                $pdf->MultiCell(
                    $columnWidths[$i],
                    $rowHeight,
                    $text,
                    1,
                    $align,
                    false,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $rowHeight,
                    'M'
                );
            }

            $pdf->Ln($rowHeight);
        }

        $pdf->Output($filePath, 'F');
    }
    // without opening balance pdf generate , normal all column & row show
    /*private function generatePdf(array $data, string $filePath, array $options = []): void
    {
        $headers = $options['headers'] ?? array_keys($data[0] ?? []);
        $titleLines = $options['titles'] ?? [];

        $pdf = new \TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
        $pdf->SetTitle('Export Report');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->AddPage();

        // Render all provided title lines
        foreach ($titleLines as $line) {
            $text = $line['text'] ?? '';
            $align = strtoupper($line['align'] ?? 'C');
            $fontSize = $line['font_size'] ?? 10;
            $style = ($line['bold'] ?? false) ? 'B' : '';

            if (!empty($line['fill']) && is_array($line['fill'])) {
                $pdf->SetFillColor(...$line['fill']);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', $style, $fontSize);
                $pdf->Cell(0, 8, $text, 0, 1, $align, true);
            } else {
                $pdf->SetFont('helvetica', $style, $fontSize);
                $pdf->Cell(0, 8, $text, 0, 1, $align);
            }
        }

        $pdf->Ln(2); // spacing

        // Setup table headers
        $numColumns = count($headers);
        $pageWidth = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $colWidth = ($pageWidth - $margins['left'] - $margins['right']) / $numColumns;

        $pdf->SetX($margins['left']);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(227, 242, 253);

        foreach ($headers as $header) {
            $pdf->Cell($colWidth, 9, (string) $header, 1, 0, 'C', true);
        }

        $pdf->Ln();

        // Data rows
        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $row) {
            $pdf->SetX($margins['left']);

            // Special styled row? (First: prev opening)
            if (isset($row['__colspan']) && isset($row['__style'])) {
                $style = $row['__style'];
                $label = $row['Debit'] ?? 'Previous Opening Balance';
                $value = $row['Closing'] ?? '';

                $pdf->SetFillColor(...($style['background'] ?? [240, 240, 240]));
                $pdf->SetFont('helvetica', $style['bold'] ? 'B' : '', 10);
                $pdf->SetTextColor(0, 0, 0);

                $labelWidth = $colWidth * intval($row['__colspan']);
                $valueWidth = $colWidth;

                // Left cell with label
                $pdf->Cell($labelWidth, 8, $label, 1, 0, $style['align'] ?? 'L', true);

                // Right cell with value
                $pdf->Cell($valueWidth, 8, $value, 1, 0, 'R', true);

                $pdf->Ln();
                continue;
            }

            // for others row
            foreach ($headers as $key) {
                $text = (string) ($row[$key] ?? '-');

                if (mb_strlen($text) > 50) {
                    $text = mb_strimwidth($text, 0, 50, '…');
                }

                $align = $this->detectCellAlignment($key);
                $pdf->Cell($colWidth, 8, $text, 1, 0, $align);
            }


            $pdf->Ln();
        }

        $pdf->Output($filePath, 'F');
    }*/


    /**
     * Determine cell alignment based on the header name or field key.
     *
     * @param string $key
     * @return string One of: 'L', 'C', 'R'
     */
    private function detectCellAlignment(string $key): string
    {
        $key = strtolower(trim($key));

        // Right align financial/numeric fields
        $rightFields = ['debit', 'credit', 'amount', 'opening', 'closing', 'balance', 'total'];
        foreach ($rightFields as $keyword) {
            if (str_contains($key, $keyword)) {
                return 'R';
            }
        }

        // Left align longer strings or names
        $leftFields = ['name', 'description', 'particulars', 'ledger', 'remark', 'narration'];
        foreach ($leftFields as $keyword) {
            if (str_contains($key, $keyword)) {
                return 'L';
            }
        }

        // Default: center align for IDs, dates etc.
        return 'C';
    }

    /**
     * Generate Excel XLSX from generic dataset.
     */
    private function generateXlsx(array $data, string $filePath, array $options = []): void
    {
        $headers = $options['headers'] ?? array_keys($data[0] ?? []);
        $titles  = $options['titles'] ?? [];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheet = new Worksheet($spreadsheet, 'Export');
        $spreadsheet->addSheet($sheet, 0);
        $spreadsheet->setActiveSheetIndex(0);

        $columnCount = count($headers);
        $columnLetters = $this->getColumnLetters($columnCount);
        $currentRow = 1;

        // 📌 Title section (optional)
        foreach ($titles as $title) {
            $text = (string) ($title['text'] ?? '');
            if (trim($text) === '') continue;

            $align = strtoupper($title['align'] ?? 'C');
            $bold = $title['bold'] ?? false;
            $size = $title['font_size'] ?? 12;

            $range = "{$columnLetters[0]}{$currentRow}:{$columnLetters[$columnCount - 1]}{$currentRow}";
            $sheet->mergeCells($range);
            $sheet->setCellValue("A{$currentRow}", $text);

            $styleArray = [
                'font' => [
                    'bold' => $bold,
                    'size' => $size,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => match ($align) {
                        'L' => Alignment::HORIZONTAL_LEFT,
                        'R' => Alignment::HORIZONTAL_RIGHT,
                        default => Alignment::HORIZONTAL_CENTER,
                    }
                ]
            ];
            $sheet->getStyle("A{$currentRow}")->applyFromArray($styleArray);
            $currentRow++;
        }

        $currentRow += 1; // Add empty row spacing

        // 🔹 Style definitions
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ]
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ]
        ];

        $dataStyle = [
            'font' => ['size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ]
            ]
        ];

        // 🔹 Render headers
        foreach ($headers as $index => $header) {
            $cell = "{$columnLetters[$index]}{$currentRow}";
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
            $sheet->getColumnDimension($columnLetters[$index])->setAutoSize(true);
        }

        $currentRow++;

        foreach ($data as $row) {
//            for opening balance row
            if (isset($row['__colspan']) && isset($row['__style'])) {
                $colspan = intval($row['__colspan']);
                $style = $row['__style'];
                $label = $row['Debit'] ?? 'Previous Opening Balance';
                $value = $row['Closing'] ?? '';

                $labelCell = "{$columnLetters[0]}{$currentRow}";
                $valueCell = "{$columnLetters[$colspan]}{$currentRow}";

                // Merge merged label
                $sheet->mergeCells("{$labelCell}:{$columnLetters[$colspan - 1]}{$currentRow}");
                $sheet->setCellValue($labelCell, $label);
                $sheet->setCellValue($valueCell, $value);

                $sheet->getStyle($labelCell)->applyFromArray([
                    'font' => ['size' => 10, 'bold' => $style['bold']],
                    'alignment' => [
                        'horizontal' => match ($style['align'] ?? 'L') {
                            'R' => Alignment::HORIZONTAL_RIGHT,
                            'C' => Alignment::HORIZONTAL_CENTER,
                            default => Alignment::HORIZONTAL_LEFT,
                        },
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => sprintf('%02X%02X%02X', ...$style['background'])],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);

                $sheet->getStyle($valueCell)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);

                $currentRow++;
                continue;
            }

            // for others row
            foreach ($headers as $index => $key) {
                $cell = "{$columnLetters[$index]}{$currentRow}";
                $value = $row[$key] ?? '';

                $align = match ($this->detectCellAlignment($key)) {
                    'R' => Alignment::HORIZONTAL_RIGHT,
                    'L' => Alignment::HORIZONTAL_LEFT,
                    default => Alignment::HORIZONTAL_CENTER,
                };

                $sheet->setCellValue($cell, $value);

                $sheet->getStyle($cell)->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => [
                        'horizontal' => $align,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
            }

            $currentRow++;
        }


        (new Xlsx($spreadsheet))->save($filePath);
    }


    /**
     * Helper to get Excel column letters A-Z, AA-AZ, ... for dynamic columns
     *
     * @param int $count
     * @return array<int, string>
     */
    private function getColumnLetters(int $count): array
    {
        $letters = [];
        for ($i = 0; $i < $count; $i++) {
            $letters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        }

        return $letters;
    }

    /**
     * Suggested X offset helper for centering tables in PDF.
     */
    private function calculateCenteredXOffset(TCPDF $pdf, array $colWidths): float
    {
        $totalWidth = array_sum($colWidths);
        $printableWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];

        return $pdf->getMargins()['left'] + ($printableWidth - $totalWidth) / 2;
    }
}
