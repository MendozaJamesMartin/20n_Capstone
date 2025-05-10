<?php

namespace App\Http\Controllers;

use App\Models\ConcessionaireBill;
use App\Models\Receipt;
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
            ->where('balance_due', '0')
            ->sum('total_amount');

        // Unpaid transactions
        $unpaidCount = Transaction::where('amount_paid', '0')->count();

        // Paid transactions (this month)
        $paidCount = Receipt::whereDate('printed_at', Carbon::today())
            ->count();

        // Bills due soon (within 7 days, assuming `due_date`)
        $billsDue = ConcessionaireBill::where('balance_due', '>', '0')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->count();

        // Recent payments
        $recentPayments = DB::table('universal_transaction_history')
            ->where('balance_due', '0')
            ->orderBy('receipt_print_date', 'desc')
            ->limit(5)
            ->get();

        // Unpaid transactions
        $pendingPayments = ConcessionaireBill::where('balance_due', '>', '0')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->orderBy('due_date', 'desc')
            ->limit(5)
            ->get();

        return view('common.admin-dashboard', compact(
            'todaysRevenue',
            'unpaidCount',
            'paidCount',
            'billsDue',
            'recentPayments',
            'pendingPayments'
        ));
    }
}
