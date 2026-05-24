<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountabilityReport implements FromArray, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $month;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();

        $this->month = Carbon::parse($startDate);
    }

    public function array(): array
    {
        return [
            [''], // row1
            [''], // row2
            [''], // row3
            [''], // row4
            [''], // row5
            [''], // row6
            [''], // row7
            [''], // row8
        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function ($event) {

                $user = Auth::user();

                $parts = explode(' ', trim($user->name));

                $firstName = $parts[0] ?? '';
                $lastName = count($parts) > 1 ? end($parts) : '';

                $middleInitial = '';
                if (count($parts) > 2) {
                    $middleInitial = strtoupper(substr($parts[1], 0, 1)) . '.';
                }

                $preparedBy =
                    trim(
                        "{$firstName} {$middleInitial} {$lastName}"
                    );

                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | Get receipt usage in date range
                |--------------------------------------------------------------------------
                */

                $receiptsInRange = DB::table('receipts')
                    ->whereBetween(
                        'created_at',
                        [$this->startDate, $this->endDate]
                    )
                    ->where('status', 'Issued')
                    ->orderBy('receipt_number')
                    ->pluck('receipt_number');

                $firstUsedInRange = $receiptsInRange->first();
                $lastUsedInRange = $receiptsInRange->last();

                /*
                |--------------------------------------------------------------------------
                | Find relevant batches
                |--------------------------------------------------------------------------
                |
                | Include:
                | 1. Current batch being used
                | 2. Next unused batches
                |
                */

                $batches = DB::table('receipt_batches')
                    ->where(function ($q) {

                        $q->whereNull('exhausted_at')
                            ->orWhereBetween(
                                'exhausted_at',
                                [$this->startDate, $this->endDate]
                            );
                    })
                    ->orderBy('start_number')
                    ->limit(8)
                    ->get();

                $currentRow = 15;

                $totalD = 0;
                $totalG = 0;
                $totalJ = 0;
                $totalM = 0;

                foreach ($batches as $batch) {

                    $batchStart = $batch->start_number;
                    $batchEnd = $batch->end_number;

                    /*
                    Receipts used in this batch within date range
                    */

                    $batchReceipts = $receiptsInRange
                        ->filter(function ($number)
                        use ($batchStart, $batchEnd) {

                            return
                                $number >= $batchStart
                                &&
                                $number <= $batchEnd;
                        })
                        ->values();

                    $usedCount = $batchReceipts->count();

                    $firstUsed = $batchReceipts->first();
                    $lastUsed = $batchReceipts->last();

                    /*
                    Determine if unused future batch
                    */

                    if (!$firstUsed) {

                        $firstUsed = $batchStart;
                        $lastUsed = $batchEnd;
                    }

                    /*
                    D = available during date range
                    */

                    $availableCount =
                        $batchEnd
                        -
                        $firstUsed
                        +
                        1;

                    /*
                    M = remaining after range
                    */

                    $remainingCount =
                        max(
                            0,
                            $batchEnd - $lastUsed
                        );

                    $nextAvailable =
                        $lastUsed + 1;

                    /*
                    Write row
                    */

                    $sheet->setCellValue(
                        "D{$currentRow}",
                        $availableCount
                    );

                    $sheet->setCellValue(
                        "E{$currentRow}", $firstUsed
                    );

                    $sheet->setCellValue(
                        "F{$currentRow}", $batchEnd
                    );

                    /*
                    G intentionally empty
                    */

                    $sheet->setCellValue(
                        "J{$currentRow}", $usedCount
                    );

                    $sheet->setCellValue(
                        "K{$currentRow}", $firstUsed
                    );

                    $sheet->setCellValue(
                        "L{$currentRow}", $lastUsed
                    );

                    $sheet->setCellValue(
                        "M{$currentRow}", $remainingCount
                    );

                    $sheet->setCellValue(
                        "N{$currentRow}", $nextAvailable
                    );

                    $sheet->setCellValue(
                        "O{$currentRow}", $batchEnd
                    );

                    /*
                    Totals
                    */

                    $totalD += $availableCount;
                    $totalJ += $usedCount;
                    $totalM += $remainingCount;

                    $currentRow++;
                }

                /*
                |--------------------------------------------------------------------------
                | Row 23 totals
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'D23',
                    $totalD
                );

                $sheet->setCellValue(
                    'G23',
                    $totalG
                );

                $sheet->setCellValue(
                    'J23',
                    $totalJ
                );

                $sheet->setCellValue(
                    'M23',
                    $totalM
                );

                /*
                |--------------------------------------------------------------------------
                | Merge cells
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:O1');
                $sheet->mergeCells('A3:O3');
                $sheet->mergeCells('A4:O4');
                $sheet->mergeCells('A7:O7');

                $sheet->mergeCells('A9:C9');
                $sheet->mergeCells('D9:F9');
                $sheet->mergeCells('G9:I9');
                $sheet->mergeCells('J9:L9');
                $sheet->mergeCells('M9:O9');

                $sheet->mergeCells('D10:F10');
                $sheet->mergeCells('G10:I10');
                $sheet->mergeCells('J10:L10');
                $sheet->mergeCells('M10:O10');

                $sheet->mergeCells('A26:O26');
                $sheet->mergeCells('A27:O27');

                $sheet->mergeCells('A29:B29');
                $sheet->mergeCells('B31:D31');
                $sheet->mergeCells('B32:D32');
                $sheet->mergeCells('J31:L31');
                $sheet->mergeCells('J32:L32');

                /*
                |--------------------------------------------------------------------------
                | Row content
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A1',
                    'REPORT OF ACCOUNTABILITY FOR ACCOUNTABLE FORMS'
                );

                $sheet->setCellValue(
                    'A3',
                    'POLYTECHNIC UNIVERSITY OF THE PHILIPPINES'
                );

                $sheet->setCellValue(
                    'A4',
                    'TAGUIG CAMPUS'
                );

                $sheet->setCellValue(
                    'A9',
                    'Accountable Forms'
                );

                $sheet->setCellValue(
                    'D9',
                    'Beginning Balance'
                );

                $sheet->setCellValue(
                    'G9',
                    'Receipt'
                );

                $sheet->setCellValue(
                    'J9',
                    'Issuance'
                );

                $sheet->setCellValue(
                    'M9',
                    'Ending Balance'
                );

                $sheet->setCellValue(
                    'C10',
                    'Face'
                );

                $sheet->setCellValue(
                    'D10',
                    'Inclusive Serial Nos.'
                );

                $sheet->setCellValue(
                    'G10',
                    'Inclusive Serial Nos.'
                );

                $sheet->setCellValue(
                    'J10',
                    'Inclusive Serial Nos.'
                );

                $sheet->setCellValue(
                    'M10',
                    'Inclusive Serial Nos.'
                );

                $sheet->setCellValue(
                    'A11',
                    'Name of Form'
                );

                $sheet->setCellValue(
                    'B11',
                    'Number'
                );

                $sheet->setCellValue(
                    'C11',
                    ' Value'
                );

                $sheet->setCellValue(
                    'D11',
                    'Qty.'
                );

                $sheet->setCellValue(
                    'E11',
                    'From'
                );

                $sheet->setCellValue(
                    'F11',
                    'To'
                );

                $sheet->setCellValue(
                    'G11',
                    'Qty.'
                );

                $sheet->setCellValue(
                    'H11',
                    'From'
                );

                $sheet->setCellValue(
                    'I11',
                    'To'
                );

                $sheet->setCellValue(
                    'J11',
                    'Qty.'
                );

                $sheet->setCellValue(
                    'K11',
                    'From'
                );

                $sheet->setCellValue(
                    'L11',
                    'To'
                );

                $sheet->setCellValue(
                    'M11',
                    'Qty.'
                );

                $sheet->setCellValue(
                    'N11',
                    'From'
                );

                $sheet->setCellValue(
                    'O11',
                    'To'
                );

                $sheet->setCellValue(
                    'A12',
                    'A. WITH FACE VALUE'
                );

                $sheet->setCellValue(
                    'A14',
                    'B. WITHOUT FACE VALUE'
                );

                $sheet->setCellValue(
                    'A23',
                    'TOTAL'
                );

                $sheet->setCellValue(
                    'A24',
                    'CERTIFICATION'
                );

                $sheet->setCellValue(
                    'A26',
                    '                                  I hereby certify that the foregoing is a true statement of all accountable forms received, issued and transferred by me during the period'
                );

                $sheet->setCellValue(
                    'A27',
                    '              above stated and that the beginning and ending balances are correct.'
                );

                $sheet->setCellValue(
                    'A29',
                    'Prepared & Certified Correct:'
                );

                $sheet->setCellValue(
                    'B31',
                    $preparedBy
                );

                $sheet->setCellValue(
                    'B32',
                    'Collecting  Officer'
                );

                $sheet->setCellValue(
                    'J31',
                    'Name'
                );

                $sheet->setCellValue(
                    'J32',
                    'Dir., PUP Taguig Campus'
                );

                /*
                |--------------------------------------------------------------------------
                | Row 7 mixed formatting
                |--------------------------------------------------------------------------
                */

                $richText = new RichText();

                // "Month of" → italic only
                $monthOf = $richText->createTextRun(
                    'Month of '
                );

                $monthOf->getFont()
                    ->setItalic(true);

                // "January 2026" → bold italic underline
                $datePart = $richText->createTextRun(
                    $this->month->format('F Y')
                );

                $datePart->getFont()
                    ->setBold(true)
                    ->setItalic(true)
                    ->setUnderline(true);

                $sheet->setCellValue(
                    'A7',
                    $richText
                );

                /*
                |--------------------------------------------------------------------------
                | Alignment
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:O11')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle('A12:A23')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_LEFT
                    );

                $sheet->getStyle('A15:O24')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle('A26:A27')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_LEFT
                    );

                $sheet->getStyle('A29:O33')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | Header styles
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14
                    ]
                ]);

                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);

                $sheet->getStyle('A9:O9')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'italic' => true,
                        'size' => 12
                    ]
                ]);

                $sheet->getStyle('A12:A24')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);

                $sheet->getStyle('D12:D23')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);


                $sheet->getStyle('G12:G23')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);

                $sheet->getStyle('J12:J23')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);


                $sheet->getStyle('M12:M23')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);

                $sheet->getStyle('B12:O23')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                /*
                |--------------------------------------------------------------------------
                | Outer border
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:O8')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_MEDIUM
                            ]
                        ]
                    ]);

                $sheet->getStyle('A9:O23')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_MEDIUM
                            ],
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A24:O33')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_MEDIUM
                            ],
                        ]
                    ]);

                $sheet->getStyle('B31:D31')
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ],
                        ]
                    ]);

                $sheet->getStyle('J31:L31')
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ],
                        ]
                    ]);

                $sheet->getColumnDimension('A')->setWidth(28);
            }

        ];
    }

    public function title(): string
    {
        return $this->month->format('F Y');
    }
}
