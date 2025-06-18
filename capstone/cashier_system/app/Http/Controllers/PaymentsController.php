<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Models\Fee;
use App\Models\Receipt;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentsController extends Controller
{
    public function GetPendingPaymentsList(Request $request) {
        $timeframe = $request->input('timeframe', 'all');
        $search = $request->input('search', '');

        $query = DB::table('student_unpaid_transactions_list')
            ->select();

        // Apply timeframe filter
        if ($timeframe === 'today') {
            $query->where('student_unpaid_transactions_list.transaction_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('student_unpaid_transactions_list.transaction_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('student_unpaid_transactions_list.transaction_date', '>=', Carbon::now()->subMonth());
        }

        // Apply sorting method
        $sortBy = $request->input('sort_by', 'transaction_date'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending

        // Validate sorting parameters
        $validSortColumns = ['transaction_date', 'full_name', 'total_amount'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'transaction_date'; // Fallback to default sorting column
        }

        // Apply sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $result = $query->paginate(10)->appends(request()->except('page'));

        // Return the view with sorted and filtered results
        return view('common.payments.pending-payments', compact('result'));
    }

    public function StudentPayment(Request $request) {
        Log::info("Payment form accessed");
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {
                // Display the form for creating a transaction
                $fees = Fee::all();
                Log::info("List of Fees");
                return view('common.payments.student-payment-form', compact('fees'));
            } elseif ($request->isMethod('post')) {
                Log::info("Input Validation");
                $validated = $request->validate([
                    'student_id' => 'required|string',
                    'first_name' => 'required|string',
                    'middle_name' => 'required|string',
                    'last_name' => 'required|string',
                    'suffix' => 'nullable|string',
                    'email' => 'required|string|email',
                    'quantities' => 'required|array',
                ]);

                Log::info("Filtering Items with 0 quantity");
                // Filter out fees with zero quantity
                $fees = array_filter($validated['quantities'], function ($qty) {
                    return $qty > 0;
                });

                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                Log::info("Extract Fee ID and Quantities as array");
                // Extract Fee IDs & Quantities as comma-separated strings
                $feeIds = implode(',', array_keys($fees));
                $quantities = implode(',', array_values($fees));

                Log::info("Stored Procedure call StudentPayment");
                $results = DB::select("CALL StudentPayment(?, ?, ?, ?, ?, ?, ?, ?)", [
                    $validated['student_id'],
                    $validated['first_name'],
                    $validated['middle_name'],
                    $validated['last_name'],
                    $validated['suffix'],
                    $validated['email'],
                    $feeIds,
                    $quantities
                ]);
                
                $transactionId = $results[0]->transaction_id;

                DB::commit();

                Log::info("return");
                return redirect()->route('customer.transaction.details', ['id' => $transactionId]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Payment form unsuccessful");
            return back()->with('error', 'Student payment failed');
        }
    }

    public function OutsiderPayment(Request $request) {
        Log::info("Payment form accessed");
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {
                // Display the form for creating a transaction
                $fees = Fee::all();
                Log::info("List of Fees");
                return view('common.payments.outsider-payment-form', compact('fees'));
            } elseif ($request->isMethod('post')) {
                Log::info("Input Validation");
                $validated = $request->validate([
                    'name' => 'required|string',
                    'contact' => 'required|string|email',
                    'quantities' => 'required|array',
                ]);

                Log::info("Filtering Items with 0 quantity");
                // Filter out fees with zero quantity
                $fees = array_filter($validated['quantities'], function ($qty) {
                    return $qty > 0;
                });

                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                Log::info("Extract Fee ID and Quantities as array");
                // Extract Fee IDs & Quantities as comma-separated strings
                $feeIds = implode(',', array_keys($fees));
                $quantities = implode(',', array_values($fees));

                Log::info("Stored Procedure call");
                $results = DB::select("CALL OutsiderPayment(?, ?, ?, ?)", [
                    $validated['name'],
                    $validated['contact'],
                    $feeIds,
                    $quantities
                ]);

                $transactionId = $results[0]->transaction_id;

                DB::commit();

                Log::info("return");
                return redirect()->route('customer.transaction.details', ['id' => $transactionId]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Payment form unsuccessful");
            return back()->with('error', 'Outsider customer payment failed');
        }
    }

    public function selfServiceStudentPayment(Request $request) {
        Log::info("Payment form accessed");
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {
                // Display the form for creating a transaction
                $fees = Fee::all();
                Log::info("List of Fees");
                return view('students.payment-form-unpaid', compact('fees'));
            } elseif ($request->isMethod('post')) {
                Log::info("Input Validation");
                $validated = $request->validate([
                    'student_id' => 'required|string',
                    'first_name' => 'required|string',
                    'middle_name' => 'required|string',
                    'last_name' => 'required|string',
                    'suffix' => 'nullable|string',
                    'email' => 'required|string|email',
                    'quantities' => 'required|array',
                ]);

                Log::info("Filtering Items with 0 quantity");
                // Filter out fees with zero quantity
                $fees = array_filter($validated['quantities'], function ($qty) {
                    return $qty > 0;
                });

                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                Log::info("Extract Fee ID and Quantities as array");
                // Extract Fee IDs & Quantities as comma-separated strings
                $feeIds = implode(',', array_keys($fees));
                $quantities = implode(',', array_values($fees));

                Log::info("Stored Procedure call");
                $results = DB::select("CALL StudentPayment(?, ?, ?, ?, ?, ?, ?, ?)", [
                    $validated['student_id'],
                    $validated['first_name'],
                    $validated['middle_name'],
                    $validated['last_name'],
                    $validated['suffix'],
                    $validated['email'],
                    $feeIds,
                    $quantities
                ]);
                
                $transactionId = $results[0]->transaction_id;

                DB::commit();
                return redirect()->route('students.submitted', ['transactionId' => $transactionId]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Payment form unsuccessful");
            return back()->with('error', 'Student payment submission failed');
        }
    }
    
    public function updateUnpaidTransaction (Request $request, $transactionId) {
        Log::info("Accessed transaction update form for transaction ID: $transactionId");
        DB::beginTransaction();
    
        try {
            if ($request->isMethod('get')) {
                // Get all fees
                $fees = Fee::all();
    
                $transactionDetails = DB::table('customer_transaction_receipt as ctr')
                    ->join('customers as c', 'ctr.customer_id', '=', 'c.id')
                    ->leftJoin('student_details as s', 'c.id', '=', 's.customer_id')
                    ->join('fees', 'fees.fee_name', '=', 'ctr.fee_name') // to get fee_id
                    ->where('ctr.transaction_id', $transactionId)
                    ->select(
                        'fees.id as fee_id',
                        'fees.amount',
                        'ctr.*',
                        's.first_name',
                        's.middle_name',
                        's.last_name',
                        's.suffix',
                        's.student_id',
                        's.email'
                    )
                    ->get();

                // Initialize defaults
                $selectedFees = collect();
                $selectedFeeDetails = collect();
                $customerInfo = null;

                if ($transactionDetails->isNotEmpty()) {
                    // Map selected fee IDs and their quantities
                    $selectedFees = $transactionDetails->mapWithKeys(function ($item) {
                        return [$item->fee_id => $item->quantity];
                    });

                    // Get detailed fee information using fee_ids (more reliable than names)
                    $feeIds = $transactionDetails->pluck('fee_id')->unique();
                    $selectedFeeDetails = Fee::whereIn('id', $feeIds)->get();

                    // Extract customer info from the first record
                    $customerInfo = (object)[
                        'first_name' => $transactionDetails[0]->first_name,
                        'last_name' => $transactionDetails[0]->last_name,
                    ];

                    Log::info("Loaded transaction details for: {$customerInfo->first_name} {$customerInfo->last_name}");
                } else {
                    Log::warning("No transaction details found for transaction ID $transactionId");
                }
                return view('common.payments.update-payment', compact('fees', 'selectedFees', 'selectedFeeDetails', 'transactionDetails', 'transactionId', 'customerInfo'));
            }
    
            elseif ($request->isMethod('put')) {
                Log::info("Validating update form input");
                $validated = $request->validate([
                    'quantities' => 'required|array',
                ]);
    
                Log::info("Filtering Items with 0 quantity");
                // Filter out fees with zero quantity
                $fees = array_filter($validated['quantities'], function ($qty) {
                    return $qty > 0;
                });
    
                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }
    
                Log::info("Building fee ID and quantity strings");
                $feeIds = implode(',', array_keys($fees));
                $quantities = implode(',', array_values($fees));
    
                Log::info("Calling stored procedure to update fees for transaction $transactionId");
                DB::statement("CALL UpdateUnpaidTransaction(?, ?, ?)", [
                    $transactionId,
                    $feeIds,
                    $quantities
                ]);
    
                DB::commit();
                return redirect()->route('customer.transaction.details', ['id' => $transactionId]);
            }
    
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Transaction update failed: " . $e->getMessage());
            return back()->with('error', 'Transaction update failed.');
        }
    }
    
}