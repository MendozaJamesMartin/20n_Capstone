<?php

namespace App\Http\Controllers;

use App\Models\Concessionaire;
use App\Models\ConcessionaireBill;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\StudentTransactionDetail;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionsController extends Controller
{
    public function GetTransactionsHistory(Request $request)
    {
        $timeframe = $request->input('timeframe', 'all');
        $search = $request->input('search', '');

        $query = DB::table('universal_transaction_history')
            ->select();

        // Apply transaction date filter
        if ($timeframe === 'today') {
            $query->where('transactions.transaction_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('transactions.transaction_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('transactions.transaction_date', '>=', Carbon::now()->subMonth());
        }

        // Apply date of receipt printing filter
        if ($timeframe === 'today') {
            $query->where('receipts.receipt_print_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('receipts.receipt_print_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('receipts.receipt_print_date', '>=', Carbon::now()->subMonth());
        }

        // Apply entity_type filter if provided
        if ($request->has('customer_type') && !empty($request->input('customer_type'))) {
            $query->where('customer_type', $request->input('customer_type'));
        }

        // Apply sorting method
        $sortBy = $request->input('sort_by', 'receipt_print_date'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending

        // Validate sorting parameters
        $validSortColumns = ['transaction_date', 'customer_name', 'total_amount', 'receipt_print_date'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'receipt_print_date'; // Fallback to default sorting column
        }

        // Apply sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $result = $query->paginate(10);

        // Return the view with sorted and filtered results
        return view('common.transactions.transactions-history', compact('result'));
    }

    public function GetCustomerReceipt ($id) {
        $TransactionDetails = DB::table('full_customer_transaction_details')
            ->where('transaction_id', $id)
            ->get();

        return view('common.transactions.customer-details', compact('TransactionDetails'));
    }

    public function GetConcessionaireReceipt ($id) {
        $TransactionDetails = DB::table('full_concessionaire_transaction_details')
            ->where('transaction_id', $id)
            ->get();

        return view('common.transactions.concessionaire-details', compact('TransactionDetails'));
    }
}
