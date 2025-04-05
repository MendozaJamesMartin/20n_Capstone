<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptsController extends Controller
{
    public function GetReceiptList (Request $request) {
        $timeframe = $request->input('timeframe', 'all');

        $query = DB::table('receipts as r')
        ->join('transactions as t', 't.id', '=', 'r.transaction_id')
        ->leftJoin('students as s', function ($join) {
            $join->on('t.entity_id', '=', 's.id')
                 ->where('t.entity_type', '=', 'student');
        })
        ->leftJoin('concessionaires as c', function ($join) {
            $join->on('t.entity_id', '=', 'c.id')
                 ->where('t.entity_type', '=', 'concessionaire');
        })
        ->select(
            'r.id',
            'r.receipt_number',
            'r.transaction_id',
            't.entity_type',
            DB::raw("CASE 
                WHEN t.entity_type = 'student' THEN CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name, s.suffix)
                WHEN t.entity_type = 'concessionaire' THEN c.name
                ELSE 'Unknown'
            END AS entity_name"),
            'r.printed_at',
            't.total_amount'
        );

        // Apply timeframe filter
        if ($timeframe === 'today') {
            $query->where('receipts.printed_at', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('receipts.printed_at', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('receipts.printed_at', '>=', Carbon::now()->subMonth());
        }

        // Apply sorting method
        $sortBy = $request->input('sort_by', 'printed_at'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending
        // Apply sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        $result = $query->paginate(10);
        
        return view('common.receipts.receipts-list', compact('result'));
    }

    public function GetStudentReceiptDetails($id) {
        $ReceiptDetails = DB::table('receipts as r')
        ->join('transactions as t', 't.id', '=', 'r.transaction_id')
        ->join('student_transaction_details as std', 't.id', '=', 'std.transaction_id')
        ->join('students as s', 'std.student_id', '=', 's.id')
        ->join('fees as f', 'std.fee_id', '=', 'f.id')
        ->select(
            'r.id',
            'r.receipt_number',
            'r.transaction_id',
            'std.student_id',
            's.first_name',
            's.middle_name',
            's.last_name',
            's.suffix',
            'r.printed_at',
            'std.fee_id',
            'f.fee_name',
            'f.amount',
            'std.quantity',
            'std.amount as subtotal',
            't.total_amount'
        )
        ->where('r.id', $id)
        ->get();

        // Return the result to a view to display the details
        return view('common.receipts.receipts-details-student', compact('ReceiptDetails'));
    }

    public function GetConcessionaireReceiptDetails($id) {
        $ReceiptDetails = DB::table('receipts as r')
        ->join('transactions as t', 't.id', '=', 'r.transaction_id')
        ->join('concessionaire_transaction_details as ctd', 't.id', '=', 'ctd.transaction_id')
        ->join('concessionaire_bills as cb', 'ctd.bill_id', '=', 'cb.id')
        ->join('concessionaires as c', 'cb.concessionaire_id', '=', 'c.id')
        ->select(
            'r.id',
            'r.receipt_number',
            'r.transaction_id',
            't.entity_id',
            'c.name',
            'r.printed_at',
            'ctd.bill_id',
            'ctd.amount_paid',
            'cb.utility_type',
            't.total_amount'
        )
        ->where('r.id', $id)
        ->get();

        // Return the result to a view to display the details
        return view('common.receipts.receipts-details-concessionaire', compact('ReceiptDetails'));
    }

}
