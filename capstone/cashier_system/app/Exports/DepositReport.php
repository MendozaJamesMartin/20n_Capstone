<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DepositReport implements FromArray, WithEvents
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

                $monthHeader = $this->month->format('F Y');

                $sheet = $event->sheet->getDelegate();

                $dailyCollections = DB::table('transactions')
                    ->selectRaw('
                        DATE(transaction_date) as txn_date,
                        SUM(total_amount) as total
                    ')
                    ->whereBetween(
                        'transaction_date',
                        [$this->startDate, $this->endDate]
                    )
                    ->where('status', 'Completed')
                    ->groupBy(DB::raw('DATE(transaction_date)'))
                    ->orderBy('txn_date')
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Get previous day's transactions for each day in range
                |--------------------------------------------------------------------------
                |
                | Example:
                | May 1 report row = April 30 transactions
                | May 2 report row = May 1 transactions
                | ...
                | May 25 report row = May 24 transactions
                |
                */

                $reportDates = [];

                $currentDate = $this->startDate->copy();

                while (
                    $currentDate->lte(
                        $this->endDate->copy()->startOfDay()
                    )
                ) {

                    $transactionDate =
                        $currentDate
                        ->copy()
                        ->subDay();

                    $total = DB::table('transactions')
                        ->whereDate(
                            'transaction_date',
                            $transactionDate
                        )
                        ->where(
                            'status',
                            'Completed'
                        )
                        ->sum(
                            'total_amount'
                        );

                    if ($total > 0) {

                        $reportDates[] = [

                            'collection_date' =>
                            $currentDate->copy(),

                            'amount' =>
                            $total

                        ];
                    }

                    $currentDate->addDay();
                }

                /*
                |--------------------------------------------------------------------------
                | Report values
                |--------------------------------------------------------------------------
                */

                $reportStartDate =
                    $this->startDate
                    ->copy()
                    ->format('M d, Y');

                $reportEndDate =
                    $this->endDate
                    ->copy()
                    ->format('M d, Y');

                /*
                Total collections in dynamic rows
                */
                $overallTotal = $totalCollected = 0;

                /*
                Receipt range:
                Use actual transaction period
                (startDate-1 to endDate-1)
                because May 1 total = Apr 30 transactions
                */

                $receiptRangeStart =
                    $this->startDate
                    ->copy()
                    ->subDay();

                $receiptRangeEnd =
                    $this->endDate
                    ->copy()
                    ->subDay();

                $receiptNumbers = DB::table('receipts as r')
                    ->join(
                        'transactions as t',
                        'r.transaction_id',
                        '=',
                        't.id'
                    )
                    ->whereBetween(
                        't.transaction_date',
                        [
                            $receiptRangeStart,
                            $receiptRangeEnd
                        ]
                    )
                    ->where(
                        't.status',
                        'Completed'
                    )
                    ->where(
                        'r.status',
                        '!=',
                        'Cancelled'
                    )
                    ->orderBy(
                        'r.receipt_number'
                    )
                    ->pluck(
                        'r.receipt_number'
                    );

                $startReceipt =
                    $receiptNumbers->first()
                    ?? 'N/A';

                $lastReceipt =
                    $receiptNumbers->last()
                    ?? 'N/A';

                /*
                |--------------------------------------------------------------------------
                | Dynamic rows
                |--------------------------------------------------------------------------
                */

                $startDynamicRow = 13;
                $currentRow = $startDynamicRow;

                $totalCollected = 0;

                foreach ($reportDates as $day) {

                    $sheet->mergeCells(
                        "A{$currentRow}:B{$currentRow}"
                    );

                    $sheet->mergeCells(
                        "C{$currentRow}:D{$currentRow}"
                    );

                    $sheet->mergeCells(
                        "E{$currentRow}:F{$currentRow}"
                    );

                    $sheet->setCellValue(
                        "A{$currentRow}",
                        $day['collection_date']
                            ->format('M d, Y')
                    );

                    $sheet->setCellValue(
                        "C{$currentRow}",
                        '## ####-###'
                    );

                    $sheet->setCellValue(
                        "E{$currentRow}",
                        $day['amount']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Dynamic styling
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        "A{$currentRow}:F{$currentRow}"
                    )->applyFromArray([

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]

                    ]);

                    $sheet->getStyle(
                        "A{$currentRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "C{$currentRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "C{$currentRow}"
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ]
                    ]);

                    $sheet->getStyle(
                        "E{$currentRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );

                    $totalCollected += $day['amount'];
                    $overallTotal = $totalCollected;

                    $currentRow++;
                }

                /*
                |--------------------------------------------------------------------------
                | Push all hardcoded rows downward
                |--------------------------------------------------------------------------
                */

                $generatedRows =
                    count($reportDates);

                $offset =
                    max(
                        0,
                        $generatedRows - 2
                    );

                /*
                |--------------------------------------------------------------------------
                | Merge cells (Hard-coded formatting)
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:F2');
                $sheet->mergeCells('G2:J2');

                $sheet->mergeCells('E4:F4');
                $sheet->mergeCells('E5:F5');

                $sheet->mergeCells('I4:J4');
                $sheet->mergeCells('I6:J6');
                $sheet->mergeCells('I7:J7');
                $sheet->mergeCells('I8:J8');

                $sheet->mergeCells('A11:B12');
                $sheet->mergeCells('C11:D12');
                $sheet->mergeCells('E11:F12');


                $sheet->mergeCells(
                    'E' . (15 + $offset) .
                        ':F' . (15 + $offset)
                );
                $sheet->mergeCells(
                    'I' . (17 + $offset) .
                        ':J' . (17 + $offset)
                );
                $sheet->mergeCells(
                    'E' . (18 + $offset) .
                        ':F' . (18 + $offset)
                );
                $sheet->mergeCells(
                    'I' . (18 + $offset) .
                        ':J' . (18 + $offset)
                );
                $sheet->mergeCells(
                    'I' . (19 + $offset) .
                        ':J' . (19 + $offset)
                );

                $sheet->mergeCells(
                    'E' . (21 + $offset) .
                        ':F' . (21 + $offset)
                );
                $sheet->mergeCells(
                    'E' . (35 + $offset) .
                        ':G' . (35 + $offset)
                );
                $sheet->mergeCells(
                    'E' . (36 + $offset) .
                        ':G' . (36 + $offset)
                );

                /*
                |--------------------------------------------------------------------------
                | Row content (Hard-Coded Formatting)
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A1', 'ACCOUNT CURRENT');
                $sheet->setCellValue('G1', 'Period Covered:');
                $sheet->setCellValue('G2', $monthHeader);

                $sheet->setCellValue('A3', ' COLLECTIONS');
                $sheet->setCellValue('A4', ' Balance Beginning');
                $sheet->setCellValue('E4', $reportStartDate);
                $sheet->setCellValue('I4', '0.00');
                $sheet->setCellValue('E5', 'Date');

                $sheet->setCellValue('A6', ' Collections per this report');
                $sheet->setCellValue('I6', $overallTotal);

                $sheet->setCellValue('A8', ' Total…………………………………..');
                $sheet->setCellValue('I8', $overallTotal); //as it says

                $sheet->setCellValue('A9', ' DEPOSITS');
                $sheet->setCellValue('A10', ' To authorized government depository bank:');

                $sheet->setCellValue('A11', 'Date');
                $sheet->setCellValue('C11', 'Official Receipt No.');
                $sheet->setCellValue('E11', 'Amount');

                $sheet->setCellValue("B" . (15 + $offset), 'Total………………….');
                $sheet->setCellValue("E" . (15 + $offset), $overallTotal); //Total amount
                $sheet->setCellValue("A" . (17 + $offset), ' Total Deposits…………………………..');
                $sheet->setCellValue("I" . (17 + $offset), $overallTotal); //Total amount
                $sheet->setCellValue("A" . (18 + $offset), ' Balance, End');
                $sheet->setCellValue("E" . (18 + $offset), $reportEndDate); //End Date
                $sheet->setCellValue("I" . (18 + $offset), '0.00');
                $sheet->setCellValue("A" . (19 + $offset), ' TOTAL……………………………………');
                $sheet->setCellValue("I" . (19 + $offset), $overallTotal); //Total Sum of I17 and I18

                $sheet->setCellValue("E" . (21 + $offset), 'CERTIFICATION');

                $sheet->setCellValue("C" . (23 + $offset), '     I certify on my official oath that the above is a true statement of ');
                $sheet->setCellValue("C" . (24 + $offset), 'all collections  received  by me during  the period stated above  for ');

                $richText = new RichText();

                /*
                Beginning text
                */
                $richText->createText(
                    'which Official Receipt No. '
                );

                /*
                Receipt range (special style)
                */
                $receiptText = $richText->createTextRun(
                    "{$startReceipt} to {$lastReceipt}"
                );

                $receiptText->getFont()
                    ->setBold(true)
                    ->setItalic(true)
                    ->setUnderline(true);

                /*
                Remaining text
                */
                $richText->createText(
                    ' inclusive were'
                );

                $sheet->setCellValue(
                    "C" . (25 + $offset),
                    $richText
                );

                $sheet->setCellValue("C" . (26 + $offset), 'actually issued by me in the amounts shown thereon. I also certify');
                $sheet->setCellValue("C" . (27 + $offset), 'that I have not received money from whatever source without having');
                $sheet->setCellValue("C" . (28 + $offset), 'issued the necessary Official Receipt in acknowledgement thereof.');
                $sheet->setCellValue("C" . (29 + $offset), 'Collections received by sub-collectors are recorded above in lump-');
                $sheet->setCellValue("C" . (30 + $offset), 'sum  opposite their  respective collection report numbers.  I certify ');
                $sheet->setCellValue("C" . (31 + $offset), 'further  that  the balance  shown  above  agrees  with  the  balance ');
                $sheet->setCellValue("C" . (32 + $offset), 'appearing in my cash book.');

                $sheet->setCellValue("E" . (35 + $offset), $preparedBy);
                $sheet->setCellValue("E" . (36 + $offset), 'Collecting Officer');

                /*
                |--------------------------------------------------------------------------
                | Alignment and Font Styles
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER,
                    );

                $sheet->getStyle('G2')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER,
                    );

                $sheet->getStyle('E4')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('I4')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('E5')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('I6')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('I8')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT,
                    );

                $sheet->getStyle('A11')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER,
                    );

                $sheet->getStyle('C11')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER,
                    );

                $sheet->getStyle('E11')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER,
                    );

                $sheet->getStyle('I' . (17 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('I' . (18 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('I' . (19 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT,
                    );

                $sheet->getStyle('E' . (18 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('E' . (21 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('E' . (35 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('E' . (36 + $offset))
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER,
                    );

                $sheet->getStyle('A1:Z99')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'name' => 'Arial'

                    ]
                ]);

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ]
                ]);

                $sheet->getStyle('G2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'italic' => true,
                        'name' => 'Bookman Old Style'
                    ]
                ]);

                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                $sheet->getStyle('I8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11
                    ]
                ]);

                $sheet->getStyle('A9')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                $sheet->getStyle('E' . (15 + $offset))->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11
                    ]
                ]);

                $sheet->getStyle('I' . (19 + $offset))->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11
                    ]
                ]);

                $sheet->getStyle('E' . (21 + $offset))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                $sheet->getStyle('E' . (35 + $offset))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                $sheet->getStyle('C' . (23 + $offset) . ':H' . (32 + $offset))->applyFromArray([
                    'font' => [
                        'italic' => true,
                    ]
                ]);

                $sheet->getStyle('E' . (36 + $offset))->applyFromArray([
                    'font' => [
                        'italic' => true,
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | Border Styles
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:F2')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G1:J2')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A3:F3')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G3:J3')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A4:F8')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('E4:F4')
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G4:J4')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G5:J6')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G7:J8')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A9:J9')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A10:F10')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G10:J' . (17 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A11:B12')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('C11:D12')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('E11:F12')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('E' . (15 + $offset) . ':F' . (15 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A' . (15 + $offset) . ':F' . (19 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G' . (16 + $offset) . ':J' . (17 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G' . (18 + $offset) . ':J' . (18 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('E' . (18 + $offset) . ':F' . (18 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('G' . (19 + $offset) . ':J' . (19 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A' . (20 + $offset) . ':J' . (37 + $offset))
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' =>
                                Border::BORDER_THIN
                            ]
                        ]
                    ]);

                /*
                Dynamic amount rows (E13 onwards)
                */
                if($currentRow > $startDynamicRow){

                    $sheet->getStyle(
                        "E{$startDynamicRow}:E".($currentRow-1)
                    )->getNumberFormat()
                    ->setFormatCode(
                        '#,##0.00'
                    );
                }

                /*
                Static amount cells
                */
                $sheet->getStyle('I4')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('I6')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('I8')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle(
                    'E'.(15+$offset)
                )->getNumberFormat()
                ->setFormatCode('#,##0.00');

                $sheet->getStyle(
                    'I'.(17+$offset)
                )->getNumberFormat()
                ->setFormatCode('#,##0.00');

                $sheet->getStyle(
                    'I'.(18+$offset)
                )->getNumberFormat()
                ->setFormatCode('#,##0.00');

                $sheet->getStyle(
                    'I'.(19+$offset)
                )->getNumberFormat()
                ->setFormatCode('#,##0.00');

            }

        ];
    }

    public function title(): string
    {
        return $this->month->format('F Y');
    }
}
