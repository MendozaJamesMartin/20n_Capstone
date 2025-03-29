<?php

namespace App\Http\Controllers;

use App\Models\Concessionaire;
use App\Models\ConcessionaireBill;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\StudentTransactionDetail;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    public function GetTransactionsList(Request $request)
    {
        $timeframe = $request->input('timeframe', 'all');
        $search = $request->input('search', '');
        
        $query = DB::table('transactions')
            ->leftJoin('students', function ($join) {
                $join->on('transactions.entity_id', '=', 'students.id')
                    ->where('transactions.entity_type', '=', 'student');
            })
            ->leftJoin('concessionaires', function ($join) {
                $join->on('transactions.entity_id', '=', 'concessionaires.id')
                     ->where('transactions.entity_type', '=', 'concessionaire');
            })
            ->select(
                'transactions.*',
                DB::raw("CASE 
                    WHEN transactions.entity_type = 'student' THEN CONCAT_WS(' ', students.first_name, students.middle_name, students.last_name, students.suffix)
                    WHEN transactions.entity_type = 'concessionaire' THEN concessionaires.name
                    ELSE 'Unknown'
                END AS entity_name")
            );
            
        // Apply timeframe filter
        if ($timeframe === 'today') {
            $query->where('transactions.transaction_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('transactions.transaction_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('transactions.transaction_date', '>=', Carbon::now()->subMonth());
        }

        // Apply entity_type filter if provided
        if ($request->has('entity_type') && !empty($request->input('entity_type'))) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // Apply sorting method
        $sortBy = $request->input('sort_by', 'transaction_date'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending

        // Validate sorting parameters
        $validSortColumns = ['transaction_date', 'entity_name', 'total_amount'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'transaction_date'; // Fallback to default sorting column
        }

        // Apply sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $result = $query->paginate(10);

        // Return the view with sorted and filtered results
        return view('common.transactions.transactions-list', compact('result'));
    }    

    public function GetStudentTransactionDetails($id) {
        $TransactionDetails = DB::table('students as s')
        ->join('student_transaction_details as std', 's.id', '=', 'std.student_id')
        ->join('transactions as t', 'std.transaction_id', '=', 't.id')
        ->join('fees as f', 'std.fee_id', '=', 'f.id')
        ->select(
            't.id',
            't.entity_id',
            's.first_name',
            's.middle_name',
            's.last_name',
            's.suffix',
            't.transaction_date',
            'std.fee_id',
            'f.fee_name',
            'f.amount',
            'std.quantity',
            'std.amount as subtotal',
            't.total_amount',
            't.amount_paid',
            't.balance_due'
        )
        ->where('t.id', $id)
        ->get();

        // Return the result to a view to display the details
        $receipt = Receipt::where('transaction_id', $id)->first();
        return view('common.transactions.stud-trans-details', compact('TransactionDetails','receipt'));
    }

    public function GetConcessionaireTransactionDetails($id) {
        $TransactionDetails = DB::table('transactions as t')
        ->join('concessionaire_transaction_details as ctd', 't.id', '=', 'ctd.transaction_id')
        ->join('concessionaire_bills as cb', 'ctd.bill_id', '=', 'cb.id')
        ->join('concessionaires as c', 'cb.concessionaire_id', '=', 'c.id')
        ->select(
            't.id',
            't.entity_id',
            'c.name',
            't.transaction_date',
            'ctd.bill_id',
            'ctd.amount_paid',
            'cb.utility_type',
            't.total_amount'
        )
        ->where('t.id', $id)
        ->get();

        // Return the result to a view to display the details
        return view('common.transactions.conc-trans-details', compact('TransactionDetails'));
    }

    public function InsertNewStudentTransaction(Request $request) {
        if ($request->isMethod('get')) {
            // Display the form for creating a transaction
            $students = DB::table('students')->select('id', 'first_name', 'middle_name', 'last_name', 'suffix')->get();
            $fees = DB::table('fees')->get();
            return view('common.forms.transaction-form-student', compact('students','fees'));
        } elseif ($request->isMethod('post')) {

            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'quantities' => 'required|array',
            ]);
    
            // Filter out fees with zero quantity
            $fees = array_filter($validated['quantities'], function ($qty) {
                return $qty > 0;
            });
    
            if (empty($fees)) {
                return back()->with('error', 'Please select at least one fee.');
            }
    
            // Extract Fee IDs & Quantities as comma-separated strings
            $feeIds = implode(',', array_keys($fees));
            $quantities = implode(',', array_values($fees));
    
            DB::statement("CALL StudentPayment(?, ?, ?)", [
                $validated['student_id'],
                $feeIds,
                $quantities,
            ]);    

        //have to fetch the transaction id first before redirecting
        return redirect()->route('TransactionsList')->with('success', 'Transaction marked as paid successfully, and email notification sent!');
        }
    }

    public function InsertNewConcessionaireTransaction(Request $request)
    {
        if ($request->isMethod('get')) {
            // Fetch all concessionaires
            $concessionaires = Concessionaire::all();
            $bills = DB::table('concessionaire_bills')->where('balance_due', '>', '0')->get();
            return view('common.forms.transaction-form-concessionaire', compact('concessionaires', 'bills'));
        } 
        
        elseif ($request->isMethod('post')) {
            // Validate the input
            $validated = $request->validate([
                'concessionaire_id' => 'required|exists:concessionaires,id',
                'bill_id' => 'required|array',
                'amount' => 'required|array',
            ]);

            // Filter out bills with zero or negative payments
            $bills = array_filter($validated['bill_payments'], function ($amount) {
                return $amount > 0;
            });

            if (empty($bills)) {
                return back()->with('error', 'Please enter payment for at least one bill.');
            }

            // Extract Bill IDs & Payment Amounts as comma-separated strings
            $billIds = implode(',', array_keys($bills));
            $paymentAmounts = implode(',', array_values($bills));

            // Call Stored Procedure
            DB::statement("CALL ConcessionairePayMultipleBills(?, ?, ?)", [
                $validated['concessionaire_id'],
                $billIds,
                $paymentAmounts,
            ]);

            // Redirect with success message
            return redirect()->route('ConcessionaireTransactionDetails')->with('success', 'Bills paid successfully!');
        }
    }

    public function PayStudentTransaction(Request $request)
    {
        $transactionId = $request->input('id');

        // Call stored procedure to mark transaction as paid
        DB::statement("CALL PayStudentTransaction(?)", [$transactionId]);

        return back()->with(['Success' => 'Transaction Paid Successfully!']);
    }

    public function GenerateReceipt($id) {
        // Call the stored procedure
        DB::statement('CALL GenerateReceipt(?)', [$id]);
    
        // Fetch the newly generated receipt
        $receipt = DB::table('receipts')->where('transaction_id', $id)->first();
    
        // Redirect back with a success message
        return redirect()->back()->with(['success' => 'Receipt generated successfully!', 'receipt' => $receipt]);
    }

}