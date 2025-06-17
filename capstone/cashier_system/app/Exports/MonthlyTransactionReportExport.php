<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyTransactionReportExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents, WithCustomStartCell
{
    protected $startDate;
    protected $endDate;
    protected $feeIds;
    protected $feeNames = [];
    protected $boldRows = [];

    public function startCell(): string
    {
        return 'A1'; // Move data starting point down by 1 row to keep row 1 for styling
    }

    public function __construct(string $startDate, string $endDate, array $feeIds = [])
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->feeIds = $feeIds;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Calculate total columns: 3 fixed (Date, OR Number, Total) + dynamic fee columns
                $feeCount = count($this->feeNames);
                $totalCols = 3 + $feeCount;

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15); // Date
                $sheet->getColumnDimension('B')->setWidth(25); // OR Number
                $sheet->getColumnDimension('C')->setWidth(20); // Total

                for ($i = 0; $i < $feeCount; $i++) {
                    $colLetter = Coordinate::stringFromColumnIndex(4 + $i); // starts at column D
                    $sheet->getColumnDimension($colLetter)->setWidth(15);   // Fees
                }

                // Center-align all cells with data (including headers)
                $lastCol = Coordinate::stringFromColumnIndex($totalCols);
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ]
                ]);

                // ✅ Freeze header row (1) and first 3 columns (A–C)
                $sheet->freezePane('D2'); // D2 = col D, row 2
            }
        ];
    }

    public function array(): array
    {
        $start = $this->startDate;
        $end = $this->endDate;

        $query = DB::table('fees')->whereNull('deleted_at');
        if (!empty($this->feeIds)) {
            $query->whereIn('id', $this->feeIds);
        }
        $this->feeNames = $query->orderBy('fee_name')->pluck('fee_name')->toArray();

        $transactions = DB::table('transactions as t')
            ->join('receipts as r', 't.id', '=', 'r.transaction_id')
            ->whereBetween('t.transaction_date', [$start, $end])
            ->where('t.amount_paid', '>', 0)
            ->select('t.id', 't.transaction_date', 'r.receipt_number', 't.total_amount')
            ->orderBy('t.transaction_date')
            ->get();

        $data = [];
        $this->boldRows = [];

        $header = array_merge(['DATE', 'OFFICIAL RECEIPT NUMBER', 'TOTAL COLLECTION'], $this->feeNames);
        $data[] = $header;

        $currentDate = null;
        $dailyGroup = [];

        foreach ($transactions as $txn) {
            $txnDate = Carbon::parse($txn->transaction_date)->toDateString();

            $fees = DB::table('customer_transaction_details as ctd')
                ->join('fees as f', 'ctd.fee_id', '=', 'f.id')
                ->where('ctd.transaction_id', $txn->id)
                ->select('f.fee_name', DB::raw('ctd.amount as total'))
                ->get()
                ->pluck('total', 'fee_name');

            if ($txnDate !== $currentDate) {
                if (!empty($dailyGroup)) {
                    $data = array_merge($data, $dailyGroup);

                    $totalRow = $this->dailyTotalRow($currentDate, $dailyGroup);
                    $data[] = $totalRow;
                    $this->boldRows[] = count($data); // bold total

                    $data[] = array_fill(0, count($header), ''); // blank row
                    $dailyGroup = [];
                }

                $currentDate = $txnDate;
            }

            $row = [
                count($dailyGroup) === 0 ? $txnDate : '',
                $txn->receipt_number,
                $txn->total_amount,
            ];

            foreach ($this->feeNames as $fee) {
                $row[] = $fees[$fee] ?? null;
            }

            $dailyGroup[] = $row;
        }

        if (!empty($dailyGroup)) {
            $data = array_merge($data, $dailyGroup);

            $totalRow = $this->dailyTotalRow($currentDate, $dailyGroup);
            $data[] = $totalRow;
            $this->boldRows[] = count($data); // bold total
        }

        return $data;
    }

    protected function dailyTotalRow($date, $rows)
    {
        $row = [
            $date,
            'TOTAL',
            number_format(collect($rows)->sum(fn ($r) => (float) $r[2]), 2, '.', ''), // ensure 0.00 format
        ];

        foreach ($this->feeNames as $i => $fee) {
            $sum = collect($rows)->sum(fn ($r) => (float) ($r[3 + $i] ?? 0));
            $row[] = number_format($sum, 2, '.', '');
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // 1. Bold only the TOTAL rows
        foreach ($this->boldRows as $row) {
            $styles[$row] = ['font' => ['bold' => true]];
        }

        // 2. Thin borders for all cells
        $styles["A1:{$highestColumn}{$highestRow}"] = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ];

        // 3. Thick bottom border for header row (row 1)
        $styles["A1:{$highestColumn}1"]['borders']['bottom'] = [
            'borderStyle' => Border::BORDER_THICK,
        ];

        // 4. Thick right border for column C (Total Collection)
        for ($row = 1; $row <= $highestRow; $row++) {
            $styles["C{$row}"]['borders']['right'] = [
                'borderStyle' => Border::BORDER_THICK,
            ];
        }

        // ✅ 5. Set default font to Times New Roman
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'font' => [
                'name' => 'Times New Roman',
            ]
        ]);

        return $styles;
    }

    public function columnFormats(): array
    {
        $formats = [];

        // 'C' is column 3: Total
        $formats['C'] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;

        // Starting from column D are the dynamic fee columns
        $columnIndex = 4; // D
        foreach ($this->feeNames as $fee) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex++);
            $formats[$columnLetter] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
        }

        return $formats;
    }

    public function headings(): array
    {
        return array_merge(['DATE', 'OFFICIAL RECEIPT NUMBER', 'TOTAL COLLECTION'], $this->feeNames);
    }

    public function title(): string
    {
        return 'Report ' . $this->startDate->format('M d') . ' - ' . $this->endDate->format('M d, Y');
    }

}
