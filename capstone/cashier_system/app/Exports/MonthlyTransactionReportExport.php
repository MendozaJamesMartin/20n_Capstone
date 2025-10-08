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
    protected $includeCustomers;
    protected $includeConcessionaires;
    protected $includeElectricity;
    protected $includeWater;
    protected $boldRows = [];

    public function __construct(
        string $startDate,
        string $endDate,
        array $feeIds = [],
        bool $includeCustomers = true,
        bool $includeConcessionaires = true,
        bool $includeElectricity = true,
        bool $includeWater = true
    ) {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->feeIds = $feeIds;
        $this->includeCustomers = $includeCustomers;
        $this->includeConcessionaires = $includeConcessionaires;
        $this->includeElectricity = $includeElectricity;
        $this->includeWater = $includeWater;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function array(): array
    {
        $start = $this->startDate;
        $end = $this->endDate;
        $data = [];
        $this->boldRows = [];

        $data[] = ['DATE', 'OFFICIAL RECEIPT NUMBER', 'CUSTOMER NAME', 'FEES', 'COLLECTION'];

        $transactions = DB::table('transactions as t')
            ->join('receipts as r', 't.id', '=', 'r.transaction_id')
            ->whereBetween('t.transaction_date', [$start, $end])
            ->when(!$this->includeCustomers, function ($q) {
                $q->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('customer_transaction_details as ctd')
                        ->whereColumn('ctd.transaction_id', 't.id');
                });
            })
            ->when(!$this->includeConcessionaires, function ($q) {
                $q->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('concessionaire_transaction_details as ctd')
                        ->whereColumn('ctd.transaction_id', 't.id');
                });
            })
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
                $isConcessionaire = DB::table('concessionaire_transaction_details')->where('transaction_id', $txn->id)->exists();

                if ($isConcessionaire) {
                    $utilities = DB::table('concessionaire_transaction_details as ctd')
                        ->join('concessionaire_bills as cb', 'ctd.bill_id', '=', 'cb.id')
                        ->where('ctd.transaction_id', $txn->id)
                        ->pluck('cb.utility_type')
                        ->unique();

                    // Skip if filtered out
                    if (
                        (!$this->includeElectricity && $utilities->contains('Electricity')) ||
                        (!$this->includeWater && $utilities->contains('Water'))
                    ) {
                        continue;
                    }

                    $fees = $utilities->implode(', ');
                    $customerName = DB::table('concessionaire_bills as cb')
                        ->join('concessionaires as c', 'cb.concessionaire_id', '=', 'c.id')
                        ->whereIn('cb.id', function ($q) use ($txn) {
                            $q->select('bill_id')
                                ->from('concessionaire_transaction_details')
                                ->where('transaction_id', $txn->id);
                        })
                        ->value('c.name');
                } else {
                    if (!$this->includeCustomers) continue;

                    $customerName = DB::table('customers as c')
                        ->join('customer_transaction_details as ctd', 'c.id', '=', 'ctd.customer_id')
                        ->where('ctd.transaction_id', $txn->id)
                        ->value('c.customer_name');

                    $fees = DB::table('customer_transaction_details as ctd')
                        ->join('fees as f', 'ctd.fee_id', '=', 'f.id')
                        ->where('ctd.transaction_id', $txn->id)
                        ->when(!empty($this->feeIds), fn($q) => $q->whereIn('f.id', $this->feeIds))
                        ->select('ctd.fee_label', 'f.fee_name')
                        ->get()
                        ->map(fn($f) => "{$f->fee_label}-{$f->fee_name}")
                        ->implode(', ');
                }

                $row = [$txnDate, $txn->receipt_number, $customerName, $fees, $txn->total_amount];
            }

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

        // Add one blank row
        $data[] = ['', '', '', '', ''];

        // Add filters summary (single merged row)
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

        $included = [];
        if ($this->includeCustomers) $included[] = 'Customers';
        if ($this->includeConcessionaires) $included[] = 'Concessionaires';

        $utilities = [];
        if ($this->includeElectricity) $utilities[] = 'Electricity';
        if ($this->includeWater) $utilities[] = 'Water';

        $excludedFees = 'none';
        if (!empty($this->feeIds)) {
            $excludedFees = 'none (all selected)';
        } else {
            $excludedFees = 'none';
        }

        return "Filters Applied: Date Range: {$dateRange}; Included: " . implode(', ', $included) .
            "; Utilities: " . implode(', ', $utilities) .
            "; Excluded Fees: {$excludedFees}";
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

        // Filters Applied row
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

                // Merge "Filters Applied" row
                $sheet->mergeCells("A{$highestRow}:E{$highestRow}");

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(50);
                $sheet->getColumnDimension('E')->setWidth(20);

                // Freeze header
                $sheet->freezePane('A2');
            }
        ];
    }

    public function title(): string
    {
        return 'Report ' . $this->startDate->format('M d') . ' - ' . $this->endDate->format('M d, Y');
    }
}
