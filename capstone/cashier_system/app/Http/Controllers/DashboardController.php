<?php

namespace App\Http\Controllers;

use App\Models\ConcessionaireBill;
use App\Models\Receipt;
use App\Models\ReceiptBatch;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::now();

        // Revenue today (exclude cancelled)
        $todaysRevenue = Transaction::whereDate('transaction_date', Carbon::today())
            ->whereHas('receipt', fn($q) => $q->where('status', '!=', 'Cancelled'))
            ->sum('total_amount');

        // Unpaid transactions (still unpaid, exclude cancelled)
        $unpaidCount = Transaction::where('status', '=', 'Pending')
            ->count();

        // Paid transactions (today, exclude cancelled)
        $paidCount = Receipt::whereDate('printed_at', Carbon::today())
            ->where('status', '!=', 'Cancelled')
            ->count();

        // Bills due soon
        $billsDue = ConcessionaireBill::where('status', '!=', 'Fully Paid')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->count();

        // Recent payments (exclude cancelled)
        $recentPayments = DB::table('universal_transaction_history')
            ->where('balance_due', '0')
            ->where('receipt_status', '!=', 'Cancelled') // assuming you expose this in the history view
            ->orderBy('receipt_print_date', 'desc')
            ->limit(5)
            ->get();

        // Current batch
        $currentBatch = ReceiptBatch::whereNotNull('next_number')
            ->whereColumn('next_number', '<=', 'end_number')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('common.admin-dashboard', compact(
            'todaysRevenue',
            'unpaidCount',
            'paidCount',
            'billsDue',
            'recentPayments',
            'currentBatch'
        ));
    }

    public function analytics()
    {
        // Revenue overview (exclude cancelled)
        $totalRevenue = DB::table('transactions')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', '!=', 'Cancelled')
            ->sum('transactions.amount_paid');

        $monthlyRevenue = DB::table('transactions')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', '!=', 'Cancelled')
            ->whereBetween('transactions.transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('transactions.amount_paid');

        $unpaidRevenue = DB::table('transactions')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', '!=', 'Cancelled')
            ->sum('transactions.balance_due');

        // Monthly trend (exclude cancelled)
        $monthlyTrend = DB::table('transactions')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->selectRaw("DATE_FORMAT(transactions.transaction_date, '%Y-%m') as month, SUM(transactions.amount_paid) as total")
            ->where('receipts.status', '!=', 'Cancelled')
            ->where('transactions.transaction_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $chartLabels = $monthlyTrend->pluck('month');
        $chartData = $monthlyTrend->pluck('total');

        // Fees (exclude cancelled)
        $allFees = DB::table('customer_transaction_details')
            ->join('fees', 'customer_transaction_details.fee_id', '=', 'fees.id')
            ->join('transactions', 'customer_transaction_details.transaction_id', '=', 'transactions.id')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', '!=', 'Cancelled')
            ->select(
                'fees.fee_name',
                DB::raw('SUM(customer_transaction_details.amount * customer_transaction_details.quantity) as total')
            )
            ->groupBy('fees.fee_name')
            ->orderByDesc('total')
            ->get();

        $fees = DB::table('fees')->whereNull('deleted_at')->orderBy('fee_name')->get();

        // Receipt batches
        $receiptBatches = DB::table('receipt_batches')
            ->select(
                'id',
                'start_number',
                'end_number',
                'next_number',
                DB::raw('(next_number - start_number) as used_count'),
                DB::raw('GREATEST(0, end_number - next_number + 1) as remaining_count'),
                'exhausted_at',
                'created_at'
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReceiptsIssued = $receiptBatches->sum('used_count');
        $totalReceiptsRemaining = $receiptBatches->sum('remaining_count');

        // Top fees + group others
        $topFees = $allFees->take(10);
        $othersTotal = $allFees->skip(10)->sum('total');
        if ($othersTotal > 0) {
            $topFees->push((object)[
                'fee_name' => 'Others',
                'total' => $othersTotal
            ]);
        }

        // Bills (exclude cancelled receipts)

        // Get all transactions with fees named 'Water' or 'Electricity'
        $waterPayments = DB::table('customer_transaction_details')
            ->join('fees', 'customer_transaction_details.fee_id', '=', 'fees.id')
            ->join('transactions', 'customer_transaction_details.transaction_id', '=', 'transactions.id')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', '!=', 'Cancelled')
            ->where('fees.fee_name', 'like', '%Water%')
            ->sum(DB::raw('customer_transaction_details.amount * customer_transaction_details.quantity'));

        $electricityPayments = DB::table('customer_transaction_details')
            ->join('fees', 'customer_transaction_details.fee_id', '=', 'fees.id')
            ->join('transactions', 'customer_transaction_details.transaction_id', '=', 'transactions.id')
            ->join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', '!=', 'Cancelled')
            ->where('fees.fee_name', 'like', '%Electricity%')
            ->sum(DB::raw('customer_transaction_details.amount * customer_transaction_details.quantity'));

        // Overdue bills based on concessionaire_bills table
        $overdueWaterAmount = DB::table('concessionaire_bills')
            ->where('utility_type', 'Water')
            ->where('status', '!=', 'Fully Paid')
            ->where('due_date', '<', Carbon::today())
            ->sum('total_due');

        $overdueElectricityAmount = DB::table('concessionaire_bills')
            ->where('utility_type', 'Electricity')
            ->where('status', '!=', 'Fully Paid')
            ->where('due_date', '<', Carbon::today())
            ->sum('total_due');

        $totalOverdueAmount = $overdueWaterAmount + $overdueElectricityAmount;
        $totalBillingPayments = $waterPayments + $electricityPayments;

        // Cancelled receipts stats
        $cancelledReceipts = Receipt::where('status', 'Cancelled')->count();
        $cancelledRevenue = Transaction::join('receipts', 'transactions.id', '=', 'receipts.transaction_id')
            ->where('receipts.status', 'Cancelled')
            ->sum('transactions.amount_paid');

        return view('common.analytics', compact(
            'totalRevenue',
            'monthlyRevenue',
            'unpaidRevenue',
            'chartLabels',
            'chartData',
            'topFees',
            'fees',
            'waterPayments',
            'electricityPayments',
            'overdueWaterAmount',
            'overdueElectricityAmount',
            'totalOverdueAmount',
            'totalBillingPayments',
            'receiptBatches',
            'totalReceiptsIssued',
            'totalReceiptsRemaining',
            'cancelledReceipts',
            'cancelledRevenue'
        ));
    }

}
