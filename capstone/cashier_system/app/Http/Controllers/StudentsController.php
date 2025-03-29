<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    public function NewStudentTransaction(Request $request) {
        $user = Auth::user();

        // Ensure the logged-in user is a student
        $student = DB::table('students')->where('user_id', $user->id)->first();
    
        if (!$student) {
            return back()->with('error', 'Unauthorized access.');
        }
    
        if ($request->isMethod('get')) {
            // Fetch fees
            $fees = DB::table('fees')->get();
    
            return view('user.transaction-form', compact('student', 'fees'));
    
        } elseif ($request->isMethod('post')) {
    
            $validated = $request->validate([
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
    
            DB::statement("CALL StudentPayFees_Unpaid(?, ?, ?)", [
                $student->id,
                $feeIds,
                $quantities,
            ]);
    
            // Redirect with success message
            return back()->with('success', 'Transaction submitted successfully!');
        }
    }

    public function StudentTransactionHistory(Request $request)
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
        return view('user.transaction-history', compact('result'));
    }    

    public function StudentTransactionDetails($id) {
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
        return view('user.transaction-details', compact('TransactionDetails','receipt'));
    }

    public function StudentFeesList() {
        $fees = Fee::all();
        return view('user.student-fees', compact('fees'));
    }
}
