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
    public function index() {

        $today = Carbon::now();

        // Total revenue today
        $todaysRevenue = Transaction::whereDate('transaction_date', Carbon::today())
            ->sum('total_amount');

        // Unpaid transactions
        $unpaidCount = Transaction::where('amount_paid', '0')->count();

        // Paid transactions (this month)
        $paidCount = Receipt::whereDate('printed_at', Carbon::today())
            ->count();

        // Bills due soon (within 7 days, assuming `due_date`)
        $billsDue = ConcessionaireBill::where('status', '!=', 'Fully Paid')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->count();

        // Recent payments
        $recentPayments = DB::table('universal_transaction_history')
            ->where('balance_due', '0')
            ->orderBy('receipt_print_date', 'desc')
            ->limit(5)
            ->get();

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

    public function analytics() {
        // Revenue Overview
        $totalRevenue = DB::table('transactions')->sum('amount_paid');
        $monthlyRevenue = DB::table('transactions')
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount_paid');
        $unpaidRevenue = DB::table('transactions')->sum('balance_due');

        // Monthly Revenue Trend (Last 6 Months)
        $monthlyTrend = DB::table('transactions')
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(amount_paid) as total")
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $chartLabels = $monthlyTrend->pluck('month');
        $chartData = $monthlyTrend->pluck('total');

        // Get all fees summed and ordered
        $allFees = DB::table('customer_transaction_details')
            ->join('fees', 'customer_transaction_details.fee_id', '=', 'fees.id')
            ->select(
                'fees.fee_name',
                DB::raw('SUM(customer_transaction_details.amount * customer_transaction_details.quantity) as total')
            )
            ->groupBy('fees.fee_name')
            ->orderByDesc('total')
            ->get();

        $fees = DB::table('fees')->whereNull('deleted_at')->orderBy('fee_name')->get();

        // Take top 10 fees and group the rest under "Others"
        $topFees = $allFees->take(10);
        $othersTotal = $allFees->skip(10)->sum('total');

        if ($othersTotal > 0) {
            $topFees->push((object)[
                'fee_name' => 'Others',
                'total' => $othersTotal
            ]);
        }

        $waterPayments = DB::table('view_water_bills')
            ->join('concessionaire_transaction_details', 'view_water_bills.bill_id', '=', 'concessionaire_transaction_details.bill_id')
            ->where('view_water_bills.utility_type', 'Water')
            ->sum('concessionaire_transaction_details.amount_paid');

        $overdueWaterAmount = DB::table('view_water_bills')
            ->where('utility_type', 'Water')
            ->where('status', '!=', 'Fully Paid')
            ->where('due_date', '<', Carbon::today())
            ->sum('total_amount_due');

        $electricityPayments = DB::table('view_electricity_bills')
            ->join('concessionaire_transaction_details', 'view_electricity_bills.bill_id', '=', 'concessionaire_transaction_details.bill_id')
            ->where('view_electricity_bills.utility_type', 'Electricity')
            ->sum('concessionaire_transaction_details.amount_paid');

        $overdueElectricityAmount = DB::table('view_electricity_bills')
            ->where('utility_type', 'Electricity')
            ->where('status', '!=', 'Fully Paid')
            ->where('due_date', '<', Carbon::today())
            ->sum('total_amount_due');

        // ========================
        // Totals
        // ========================
        $totalOverdueAmount = $overdueWaterAmount + $overdueElectricityAmount;
        $totalBillingPayments = $waterPayments + $electricityPayments;

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
            'totalBillingPayments'
        ));
    }

}
