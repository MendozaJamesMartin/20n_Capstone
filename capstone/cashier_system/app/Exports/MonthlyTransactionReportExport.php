<?php

namespace App\Exports;

use Carbon\Carbon;
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

class MonthlyTransactionReportExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents, WithCustomStartCell
{
    protected $startDate;
    protected $endDate;
    protected $feeIds;
    protected $boldRows = [];

    public function __construct(string $startDate, string $endDate, array $feeIds = [])
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->feeIds = $feeIds;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function array(): array
    {
        $data = [];
        $this->boldRows = [];

        $data[] = ['DATE', 'OFFICIAL RECEIPT NUMBER', 'PAYER NAME', 'FEES', 'COLLECTION'];

        $transactions = DB::table('transactions as t')
            ->join('receipts as r', 't.id', '=', 'r.transaction_id')
            ->whereBetween('t.transaction_date', [$this->startDate, $this->endDate])
            ->select('t.id', 't.transaction_date', 't.total_amount', 't.status as transaction_status', 'r.receipt_number', 'r.status as receipt_status')
            ->orderBy('t.transaction_date')
            ->orderBy('r.receipt_number')
            ->get();

        $currentDate = null;
        $dailyGroup = [];

        foreach ($transactions as $txn) {
            $txnDate = Carbon::parse($txn->transaction_date)->format('Y-m-d');
            $isCancelled = $txn->receipt_status === 'Cancelled' || $txn->transaction_status === 'Cancelled';

            if ($isCancelled) {
                $row = [$txnDate, $txn->receipt_number, 'CANCELLED', 'CANCELLED', 'CANCELLED'];
            } else {
                // Determine payer name (customer or concessionaire)
                $payerName = DB::table('customers')
                    ->whereIn('id', function ($q) use ($txn) {
                        $q->select('customer_id')
                            ->from('customer_transaction_details')
                            ->where('transaction_id', $txn->id);
                    })
                    ->value('customer_name');

                if (!$payerName) {
                    $payerName = DB::table('concessionaires')
                        ->whereIn('id', function ($q) use ($txn) {
                            $q->select('customer_id')
                                ->from('customer_transaction_details')
                                ->where('transaction_id', $txn->id);
                        })
                        ->value('name');
                }

                // Fetch fees for this transaction
                $fees = DB::table('customer_transaction_details as ctd')
                    ->join('fees as f', 'ctd.fee_id', '=', 'f.id')
                    ->where('ctd.transaction_id', $txn->id)
                    ->when(!empty($this->feeIds), fn($q) => $q->whereIn('f.id', $this->feeIds))
                    ->select('ctd.fee_label', 'f.fee_name')
                    ->get()
                    ->map(fn($f) => "{$f->fee_label}-{$f->fee_name}")
                    ->implode(', ');

                $row = [$txnDate, $txn->receipt_number, $payerName, $fees, $txn->total_amount];
            }

            // Group by day
            if ($txnDate !== $currentDate && $currentDate !== null) {
                $data = array_merge($data, $dailyGroup);
                $data[] = $this->dailyTotalRow($currentDate, $dailyGroup);
                $this->boldRows[] = count($data);
                $data[] = ['', '', '', '', ''];
                $dailyGroup = [];
            }

            $dailyGroup[] = $row;
            $currentDate = $txnDate;
        }

        if (!empty($dailyGroup)) {
            $data = array_merge($data, $dailyGroup);
            $data[] = $this->dailyTotalRow($currentDate, $dailyGroup);
            $this->boldRows[] = count($data);
        }

        $data[] = ['', '', '', '', ''];
        $data[] = [$this->buildFiltersSummary()];

        return $data;
    }

    protected function dailyTotalRow($date, $rows)
    {
        $total = collect($rows)->sum(fn($r) => is_numeric($r[4]) ? (float) $r[4] : 0);
        return [$date, 'TOTAL', '', '', number_format($total, 2, '.', '')];
    }

    protected function buildFiltersSummary(): string
    {
        $dateRange = $this->startDate->format('M j, Y') . ' – ' . $this->endDate->format('M j, Y');
        $feesText = empty($this->feeIds) ? 'All fees included' : 'Filtered by specific fees';

        return "Filters Applied: Date Range: {$dateRange}; {$feesText}";
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $highestRow = $sheet->getHighestRow();

        foreach ($this->boldRows as $row) {
            $styles[$row] = ['font' => ['bold' => true]];
        }

        $styles["A1:E{$highestRow}"] = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'font' => ['name' => 'Times New Roman'],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $styles[$highestRow] = [
            'font' => ['italic' => true, 'bold' => true, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        return $styles;
    }

    public function columnFormats(): array
    {
        return ['E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                $sheet->mergeCells("A{$highestRow}:E{$highestRow}");

                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(50);
                $sheet->getColumnDimension('E')->setWidth(20);

                $sheet->freezePane('A2');
            }
        ];
    }

    public function title(): string
    {
        return 'Report ' . $this->startDate->format('M d') . ' - ' . $this->endDate->format('M d, Y');
    }
}
