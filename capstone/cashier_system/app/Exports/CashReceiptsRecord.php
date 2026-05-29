<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashReceiptsRecord implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents, WithCustomStartCell
{
    protected $startDate;
    protected $endDate;
    protected $feeIds;
    protected $dailyTotalRows = [];
    protected $finalTotalRow = null;

    public function __construct(string $startDate, string $endDate, array $feeIds = [])
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->feeIds = $feeIds;
    }

    public function startCell(): string
    {
        return 'A13';
    }

    public function array(): array
    {
        $data = [];

        $runningTotal = 0;
        $grandTotal = 0;

        $transactions = DB::table('transactions as t')
            ->join(
                'receipts as r',
                't.id',
                '=',
                'r.transaction_id'
            )
            ->whereBetween(
                't.transaction_date',
                [
                    $this->startDate,
                    $this->endDate
                ]
            )
            ->select(
                't.id',
                't.transaction_date',
                't.total_amount',
                't.status as transaction_status',
                'r.receipt_number',
                'r.status as receipt_status'
            )
            ->orderBy('t.transaction_date')
            ->orderBy('r.receipt_number')
            ->get();

        $grouped = $transactions->groupBy(function ($t) {
            return Carbon::parse(
                $t->transaction_date
            )->format('Y-m-d');
        });

        foreach ($grouped as $date => $dayTransactions) {

            $currentDate = Carbon::parse($date);

            $dailyRows = [];
            $dailyTotal = 0;
            $runningTotal = 0;

            foreach ($dayTransactions as $index => $txn) {

                $isCancelled =
                    $txn->receipt_status === 'Cancelled'
                    ||
                    $txn->transaction_status === 'Cancelled';

                $payor = 'CANCELLED';
                $fees = 'CANCELLED';
                $amount = 'CANCELLED';

                if (!$isCancelled) {

                    $payor = DB::table('customers')
                        ->whereIn(
                            'id',
                            function ($q) use ($txn) {
                                $q->select('customer_id')
                                    ->from(
                                        'customer_transaction_details'
                                    )
                                    ->where(
                                        'transaction_id',
                                        $txn->id
                                    );
                            }
                        )
                        ->value('customer_name');

                    if (!$payor) {

                        $payor = DB::table(
                            'concessionaires'
                        )
                            ->whereIn(
                                'id',
                                function ($q) use ($txn) {

                                    $q->select(
                                        'customer_id'
                                    )
                                        ->from(
                                            'customer_transaction_details'
                                        )
                                        ->where(
                                            'transaction_id',
                                            $txn->id
                                        );
                                }
                            )
                            ->value('name');
                    }

                    $payor = strtoupper(
                        $payor ?? ''
                    );

                    $fees = DB::table(
                        'customer_transaction_details as ctd'
                    )
                        ->join(
                            'fees as f',
                            'ctd.fee_id',
                            '=',
                            'f.id'
                        )
                        ->where(
                            'ctd.transaction_id',
                            $txn->id
                        )
                        ->select(
                            'ctd.fee_label',
                            'f.fee_name',
                            'ctd.quantity'
                        )
                        ->get()
                        ->map(function ($f) {

                            $label =
                                trim(
                                    strtolower(
                                        $f->fee_label
                                    )
                                );

                            $labelText =
                                ($label === '' || $label === 'none')
                                ? ''
                                : $f->fee_label . '-';

                            return
                                "{$labelText}{$f->fee_name}";
                        })
                        ->implode(', ');

                    $amount = $txn->total_amount;

                    $dailyTotal += $amount;
                    $runningTotal += $amount;
                    $grandTotal += $amount;
                }

                $showDate = '';

                if (
                    $index === 0
                    ||
                    $index === count($dayTransactions) - 1
                ) {
                    $showDate =
                        $currentDate->format('d/m/Y');
                }

                $dailyRows[] = [

                    $showDate,
                    $txn->receipt_number,
                    $payor,
                    '',
                    '',
                    $fees,
                    $amount,
                    '',
                    $runningTotal
                ];
            }

            $currentDate = Carbon::parse($date);

            $data = array_merge(
                $data,
                $dailyRows
            );

            /*
            |--------------------------------------------------------------------------
            | Daily total row
            |--------------------------------------------------------------------------
            |
            | No deposit date anymore.
            | Just show total collections for the transaction date.
            |
            */

            $data[] = [

                'Date',
                '##-###',
                '####-####-##',
                '',
                '',
                '',
                '',
                $dailyTotal,
                ''

            ];

            $this->dailyTotalRows[] =
                count($data);
        }

        $data[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '-'
        ];

        /*
        Final total row
        */

        $data[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            $grandTotal,
            $grandTotal,
            '-'
        ];

        $this->finalTotalRow = count($data);

        return $data;
    }

    protected function dailyTotalRow($date, $rows)
    {
        $total = collect($rows)->sum(fn($r) => is_numeric($r[4]) ? (float) $r[4] : 0);
        return [$date, 'TOTAL', '', '', number_format($total, 2, '.', '')];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $highestRow = $sheet->getHighestRow();

        // Main table styling (centered)
        $styles["A12:I{$highestRow}"] = [
            'font' => ['name' => 'Calibri'],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ];

        $styles["A13:A{$highestRow}"] = [
            'font' => ['bold' => true],
        ];

        $styles["H13:H{$highestRow}"] = [
            'font' => ['bold' => true],
        ];

        return $styles;
    }

    public function columnFormats(): array
    {
        return [

            'B' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $user = Auth::user();

                $parts = explode(' ', trim($user->name));

                $firstName = $parts[0] ?? '';
                $lastName = count($parts) > 1 ? end($parts) : '';
                $middleInitial = '';

                $start = $this->startDate->format('F d, Y');
                $end = $this->endDate->format('F d, Y');
                $range = "{$start} to {$end}";

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
                | HEADER SECTION (Rows 1–7)
                |--------------------------------------------------------------------------
                */

                // Row 1
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue(
                    'A1',
                    'CASH RECEIPTS RECORD'
                );

                // Row 2
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue(
                    'A2',
                    'Polytechnic University of the Philippines'
                );

                // Row 5
                $sheet->mergeCells('A5:B5');
                $sheet->setCellValue(
                    'A5',
                    'Entity Name:'
                );

                $sheet->setCellValue(
                    'C5',
                    'PUPTaguig'
                );

                $sheet->setCellValue(
                    'G5',
                    'Sheet No.:'
                );

                // Row 6
                $sheet->mergeCells('A6:B6');
                $sheet->setCellValue(
                    'A6',
                    'Fund Cluster:'
                );

                $sheet->setCellValue(
                    'G6',
                    'Year:'
                );

                $sheet->setCellValue(
                    'H6',
                    now()->year
                );

                /*
                |--------------------------------------------------------------------------
                | ROW 8
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A8:C8');
                $sheet->mergeCells('D8:E8');
                $sheet->mergeCells('F8:G8');
                $sheet->mergeCells('H8:I8');

                $sheet->setCellValue('A8', $preparedBy);

                $sheet->setCellValue(
                    'F8',
                    'Collecting Officer'
                );

                $sheet->setCellValue(
                    'H8',
                    'Taguig Campus'
                );

                /*
                |--------------------------------------------------------------------------
                | ROW 9
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A9:C9');
                $sheet->mergeCells('D9:E9');
                $sheet->mergeCells('F9:G9');
                $sheet->mergeCells('H9:I9');

                $sheet->setCellValue(
                    'A9',
                    'Accountable Officer'
                );

                $sheet->setCellValue(
                    'F9',
                    'Official Designation'
                );

                $sheet->setCellValue(
                    'H9',
                    'Station'
                );

                /*
                |--------------------------------------------------------------------------
                | TABLE HEADER (Rows 10–11)
                |--------------------------------------------------------------------------
                */

                // Vertical merged headers
                $sheet->mergeCells('A10:A11');
                $sheet->mergeCells('B10:B11');
                $sheet->mergeCells('C10:C11');

                $sheet->mergeCells('F10:F11');
                $sheet->mergeCells('G10:G11');
                $sheet->mergeCells('H10:H11');
                $sheet->mergeCells('I10:I11');

                // Horizontal merged header
                $sheet->mergeCells('D10:E10');

                /*
                |--------------------------------------------------------------------------
                | Header text
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A10',
                    'Date'
                );

                $sheet->setCellValue(
                    'B10',
                    'Reference No./OR No./DS'
                );

                $sheet->setCellValue(
                    'C10',
                    'Name of Payor'
                );

                $sheet->setCellValue(
                    'D10',
                    'UACS Code'
                );

                $sheet->setCellValue(
                    'D11',
                    'MFO/PAP'
                );

                $sheet->setCellValue(
                    'E11',
                    'Object Code'
                );

                $sheet->setCellValue(
                    'F10',
                    'Nature of Collection'
                );

                $sheet->setCellValue(
                    'G10',
                    'Collection'
                );

                $sheet->setCellValue(
                    'H10',
                    'Deposit'
                );

                $sheet->setCellValue(
                    'I10',
                    'Undeposited Collection'
                );

                /*
                |--------------------------------------------------------------------------
                | Styling
                |--------------------------------------------------------------------------
                */

                // Title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Institution
                $sheet->getStyle('A2:I6')->applyFromArray([
                    'font' => [
                        'bold' => false,
                        'italic' => true,
                        'size' => 11
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $sheet->getStyle('H6')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                /*
                |--------------------------------------------------------------------------
                | Row 8 styling
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A8:I8')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'underline' => true
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Row 9 styling
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A9:I9')
                    ->applyFromArray([
                        'font' => [
                            'italic' => true,
                            'bold' => false
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Header styling (Rows 10–11)
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A10:I11')
                    ->applyFromArray([

                        'font' => [
                            'bold' => true
                        ],

                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                            ]
                        ]
                    ]);

                $sheet->getStyle('A5')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('A6')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('G5')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('G6')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                /*
                |--------------------------------------------------------------------------
                | Make D10:E10 italic only
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('D10:E10')
                    ->applyFromArray([

                        'font' => [
                            'italic' => true,
                            'bold' => false
                        ]

                    ]);

                $highestRow = $sheet->getHighestRow();

                /*
                Payor + Nature of Collection
                Left aligned
                */

                $sheet->getStyle("C13:C{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("F13:F{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                /*
                Money columns
                Right aligned
                */

                $sheet->getStyle("G13:G{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("H13:H{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("I13:I{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                /*
                |--------------------------------------------------------------------------
                | Column widths A:I
                |--------------------------------------------------------------------------
                */

                $boxes = [
                    'A8:I9',
                    'A8:C9',
                    'D8:E9',
                    'F8:G9',
                    'H8:I9',
                    'A10:I11'
                ];

                foreach ($boxes as $range) {

                    $sheet->getStyle($range)
                        ->applyFromArray([
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => Border::BORDER_MEDIUM
                                ]
                            ]
                        ]);
                }

                $sheet->getColumnDimension('A')->setWidth(11);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(24);
                $sheet->getColumnDimension('D')->setWidth(6);
                $sheet->getColumnDimension('E')->setWidth(6);
                $sheet->getColumnDimension('F')->setWidth(26);
                $sheet->getColumnDimension('G')->setWidth(11);
                $sheet->getColumnDimension('H')->setWidth(11);
                $sheet->getColumnDimension('I')->setWidth(13);

                $highestRow = $sheet->getHighestRow();

                /*
                |--------------------------------------------------------------------------
                | Daily subtotal rows
                |--------------------------------------------------------------------------
                */

                foreach ($this->dailyTotalRows as $row) {

                    $actualRow = $row + 12;

                    $sheet->getStyle("A{$actualRow}:I{$actualRow}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true
                            ]
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Final total row
                |--------------------------------------------------------------------------
                */

                if ($this->finalTotalRow) {

                    $actualRow = $this->finalTotalRow + 12;

                    $sheet->getStyle("A{$actualRow}:I{$actualRow}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'italic' => true
                            ]
                        ]);

                    $highestRow = $actualRow;
                }

                /*
                |--------------------------------------------------------------------------
                | Footer / Signature section
                |--------------------------------------------------------------------------
                */

                /*
                empty row after total
                */
                $currentRow = $highestRow + 2;


                /*
                Merged title row
                */
                $sheet->mergeCells("A{$currentRow}:I{$currentRow}");

                $sheet->setCellValue(
                    "A{$currentRow}",
                    "CERTIFICATION"
                );

                $sheet->getStyle("A{$currentRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER
                        ]
                    ]);

                $currentRow += 2;

                /*
                4 text rows
                */

                $footerText = [

                    'I hereby certify on my official oath that the foregoing is a correct and complete',
                    'record of all collections and deposits had by me in my capacity as Collecting Officer',
                    'of Polytechnic University of the Philippines-Taguig Campus during the period from',
                    "{$range}, inclusives, as indicated in the corresponding columns."

                ];

                foreach ($footerText as $text) {
                    $sheet->mergeCells("A{$currentRow}:I{$currentRow}");

                    $sheet->setCellValue(
                        "A{$currentRow}",
                        $text
                    );

                    $sheet->getStyle("A{$currentRow}")
                        ->applyFromArray([
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER
                            ]
                        ]);

                    $currentRow++;
                }

                /*
                Empty row
                */

                $currentRow++;

                /*
                Prepared by signature
                */

                $sheet->mergeCells(
                    "F{$currentRow}:G{$currentRow}"
                );

                $sheet->setCellValue(
                    "F{$currentRow}",
                    $preparedBy
                );

                $sheet->getStyle(
                    "F{$currentRow}:G{$currentRow}"
                )
                    ->applyFromArray([

                        'font' => [
                            'italic' => true
                        ],

                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN
                            ]
                        ],

                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER
                        ]
                    ]);

                $currentRow++;

                /*
                Title below signature
                */

                $sheet->mergeCells(
                    "F{$currentRow}:G{$currentRow}"
                );

                $sheet->setCellValue(
                    "F{$currentRow}",
                    "Collecting Officer"
                );
                $sheet->getStyle("F{$currentRow}")
                    ->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER
                        ]
                    ]);

                $currentRow += 2;

                /*
                Date line
                */

                $sheet->mergeCells(
                    "F{$currentRow}:G{$currentRow}"
                );

                $sheet->setCellValue(
                    "F{$currentRow}",
                    now()->format('F d, Y')
                );

                $sheet->getStyle(
                    "F{$currentRow}:G{$currentRow}"
                )
                    ->applyFromArray([

                        'font' => [
                            'italic' => true
                        ],

                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN
                            ]
                        ],

                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER
                        ]
                    ]);

                $currentRow++;

                /*
                date label
                */

                $sheet->mergeCells(
                    "F{$currentRow}:G{$currentRow}"
                );

                $sheet->setCellValue(
                    "F{$currentRow}",
                    "Date"
                );

                $sheet->getStyle(
                    "F{$currentRow}:G{$currentRow}"
                )
                    ->applyFromArray([

                        'font' => [
                            'italic' => true
                        ],

                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER
                        ]
                    ]);

                // Freeze below report headers
                $sheet->freezePane('A12');
            }
        ];
    }

    public function title(): string
    {
        return 'CashReceiptsRecord ' . $this->startDate->format('M d') . ' - ' . $this->endDate->format('M d, Y');
    }
}
