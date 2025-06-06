<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Models\Fee;
use App\Models\Receipt;
use App\Models\Transaction;
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

        // Apply entity_type filter if provided
        if ($request->has('customer_type') && !empty($request->input('customer_type'))) {
            $query->where('customer_type', $request->input('customer_type'));
        }

        // Apply sorting method
        $sortBy = $request->input('sort_by', 'transaction_date'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending

        // Validate sorting parameters
        $validSortColumns = ['transaction_date', 'customer_name', 'total_amount'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'transaction_date'; // Fallback to default sorting column
        }

        // Apply sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $result = $query->paginate(10);

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
                    'receipt_number' => 'required|string',
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
                
                // Now finalize the transaction
                DB::statement("CALL FinalizeTransaction(?, ?)", [
                    $transactionId,
                    $validated['receipt_number']
                ]);

                DB::commit();

                $transaction = Transaction::find($transactionId);
                // Save the payment, then send the email
                Mail::to($validated['email'])->send(
                    new PaymentReceiptMail($transaction->total_amount, $validated['receipt_number'])
                );

                return redirect()->route('customer.receipt', ['id' => $transactionId])->with('auto_print', true);;
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
                    'receipt_number' => 'required|string',
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

                // Now finalize the transaction
                DB::statement("CALL FinalizeTransaction(?, ?)", [
                    $transactionId,
                    $validated['receipt_number']
                ]);

                DB::commit();

                $transaction = Transaction::find($transactionId);
                // Save the payment, then send the email
                Mail::to($validated['contact'])->send(
                    new PaymentReceiptMail($transaction->total_amount, $validated['receipt_number'])
                );

                return redirect()->route('customer.receipt', ['id' => $transactionId])->with('auto_print', true);
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
    
                // Get current fee selections (map: fee_id => quantity)
                $selectedFees = DB::table('customer_transaction_details')
                    ->where('transaction_id', $transactionId)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->fee_id => $item->quantity];
                    });
    
                Log::info("Loaded fees and existing transaction data");
    
                return view('common.payments.update-payment', compact('fees', 'selectedFees', 'transactionId'));
            }
    
            elseif ($request->isMethod('put')) {
                Log::info("Validating update form input");
                $validated = $request->validate([
                    'quantities' => 'required|array',
                    'receipt_number' => 'required|string',
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
    
                Log::info("Calling FinalizeTransaction for payment finalization");
                DB::statement("CALL FinalizeTransaction(?, ?)", [
                    $transactionId,
                    $validated['receipt_number']
                ]);
    
                DB::commit();
                return redirect()->route('customer.receipt', ['id' => $transactionId])->with('auto_print', true);
            }
    
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Transaction update failed: " . $e->getMessage());
            return back()->with('error', 'Transaction update failed.');
        }
    }
    
}