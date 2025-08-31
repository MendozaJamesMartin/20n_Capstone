<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Models\Fee;
use App\Models\Receipt;
use App\Models\ReceiptBatch;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentsController extends Controller
{
    public function GetPendingPaymentsList(Request $request)
    {
        $timeframe = $request->input('timeframe', 'all');
        $search = $request->input('search', '');

        $query = DB::table('unpaid_transactions_list')
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

    public function CustomerPayment(Request $request)
    {
        Log::info("Payment form accessed");
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {
                // Display the form for creating a transaction
                Log::info("List of Fees");
                $fees = Fee::all();

                Log::info("Receipt Batch checker");
                $currentBatch = ReceiptBatch::whereColumn('next_number', '<=', 'end_number')
                    ->orderBy('id')
                    ->first();

                return view('common.payments.customer-payment-form', compact('fees'), ['hasActiveBatch' => $currentBatch !== null]);
            } elseif ($request->isMethod('post')) {
                Log::info("Input Validation");
                $validated = $request->validate([
                    'customer_name' => 'required|string',
                    'contact'       => 'nullable|string|email',
                    'fee_ids'       => 'required|array',
                    'quantities'    => 'required|array',
                    'amounts'       => 'required|array',
                    'labels'        => 'required|array',
                ]);

                Log::info("Filtering items with 0 quantity");

                $feeIdsRaw    = $validated['fee_ids'];
                $quantitiesRaw = $validated['quantities'];
                $amountsRaw    = $validated['amounts'];
                $labelsRaw     = $validated['labels'];

                $finalFeeIds = [];
                $finalQuantities = [];
                $finalAmounts = [];
                $finalLabels = [];

                foreach ($feeIdsRaw as $i => $feeId) {
                    $qty = $quantitiesRaw[$i] ?? 0;
                    $amt = $amountsRaw[$i] ?? 0;
                    $lbl = $labelsRaw[$i] ?? '';

                    if (!empty($feeId) && $qty > 0) {
                        $finalFeeIds[]   = $feeId;
                        $finalQuantities[] = $qty;
                        $finalAmounts[]    = $amt;
                        $finalLabels[]     = $lbl;
                    }
                }

                if (empty($finalFeeIds)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                $feeIdsStr    = implode(',', $finalFeeIds);
                $quantitiesStr = implode(',', $finalQuantities);
                $amountsStr    = implode(',', $finalAmounts);
                $labelsStr     = implode(',', $finalLabels);

                // Fetch fee names for logging
                $feeNames = Fee::whereIn('id', $finalFeeIds)->pluck('fee_name', 'id')->toArray();

                $readableFees = [];
                $readableAmounts = [];
                foreach ($finalFeeIds as $idx => $id) {
                    $name = $feeNames[$id] ?? "Fee ID $id";
                    $readableFees[$name] = $finalQuantities[$idx];
                    $readableAmounts[$name] = $finalAmounts[$idx];
                }

                Log::info("Stored Procedure call");
                $results = DB::select("CALL CustomerPayment(?, ?, ?, ?, ?, ?)", [
                    $validated['customer_name'],
                    $validated['contact'],
                    $feeIdsStr,
                    $quantitiesStr,
                    $amountsStr,
                    $labelsStr
                ]);

                $transactionId = $results[0]->transaction_id;

                DB::commit();

                // Audit log
                AuditLogger::log(
                    event: 'payment_created',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transactionId,
                    oldValues: [],
                    newValues: [
                        'customer_name' => $validated['customer_name'],
                        'contact'       => $validated['contact'],
                        'fees'          => $readableFees,
                        'amounts'       => $readableAmounts,
                        'labels'        => $finalLabels
                    ],
                    tags: 'payment'
                );

                Log::info("return");
                return redirect()->route('customer.transaction.details', ['id' => $transactionId]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Payment form unsuccessful");
            return back()->with('error', 'Customer payment failed');
        }
    }

    public function selfServiceStudentPayment(Request $request)
    {
        Log::info("Payment form accessed");
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {

                Log::info("List of Fees");
                $fees = Fee::all();

                Log::info("Receipt Batch checker");
                $currentBatch = ReceiptBatch::whereColumn('next_number', '<=', 'end_number')
                    ->orderBy('id')
                    ->first();

                return view('students.payment-form-unpaid', compact('fees'), ['hasActiveBatch' => $currentBatch !== null]);
            } elseif ($request->isMethod('post')) {
                Log::info("Input Validation");
                $validated = $request->validate([
                    'customer_name' => 'required|string',
                    'contact' => 'nullable|string|email',
                    'quantities' => 'required|array',
                    'amounts' => 'required|array',
                ]);

                Log::info("Filtering Items with 0 quantity");
                // Filter out fees with zero quantity
                $fees = array_filter($validated['quantities'], function ($qty) {
                    return $qty > 0;
                });

                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                $feeIds = array_keys($fees);
                $quantities = array_values($fees);
                $feeNames = Fee::whereIn('id', $feeIds)->pluck('fee_name', 'id')->toArray();

                // Use same keys to extract amounts
                $amounts = array_map(function ($id) use ($validated) {
                    return $validated['amounts'][$id] ?? '0.00';
                }, $feeIds);

                $feeIdsStr = implode(',', $feeIds);
                $quantitiesStr = implode(',', $quantities);
                $amountsStr = implode(',', $amounts);

                $readableFees = [];
                foreach ($fees as $feeId => $qty) {
                    $name = $feeNames[$feeId] ?? "Fee ID $feeId";
                    $readableFees[$name] = $qty;  // store by name
                }

                $readableAmounts = [];
                foreach ($readableFees as $name => $qty) {
                    $amount = $validated['amounts'][array_search($name, $feeNames)] ?? '0.00'; // get amount by matching fee name key
                    $readableAmounts[$name] = $amount;
                }

                Log::info("Stored Procedure call");
                $results = DB::select("CALL CustomerPayment(?, ?, ?, ?, ?)", [
                    $validated['customer_name'],
                    $validated['contact'],
                    $feeIdsStr,
                    $quantitiesStr,
                    $amountsStr
                ]);

                $transactionId = $results[0]->transaction_id;
                $transaction_num = $results[0]->transaction_number;

                DB::commit();

                // Use your AuditLogger to log the payment creation
                AuditLogger::log(
                    event: 'payment_submitted_by_customer',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transactionId,
                    oldValues: [],
                    newValues: [
                        'customer_name' => $validated['customer_name'],
                        'contact'       => $validated['contact'],
                        'fees'          => $readableFees,     // ['Fee Name 1' => quantity, 'Fee Name 2' => quantity, ...]
                        'amounts'       => $readableAmounts,  // ['Fee Name 1' => amount, 'Fee Name 2' => amount, ...]
                    ],
                    tags: 'payment'
                );

                return redirect()->route('students.submitted', ['transaction_num' => $transaction_num]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Payment form unsuccessful");
            return back()->with('error', 'Student payment submission failed');
        }
    }

    public function updateUnpaidTransaction(Request $request, $transactionId)
    {
        Log::info("Accessed transaction update form for transaction ID: $transactionId");
        DB::beginTransaction();

        try {
            if ($request->isMethod('get')) {
                // Get all fees
                $fees = Fee::all();

                // Get transaction details
                $transactionDetails = DB::table('customer_transaction_receipt as ctr')
                    ->join('customers as c', 'ctr.customer_id', '=', 'c.id')
                    ->join('fees', 'fees.fee_name', '=', 'ctr.fee_name') // to get fee_id
                    ->where('ctr.transaction_id', $transactionId)
                    ->select(
                        'fees.id as fee_id',
                        'fees.amount',
                        'ctr.*',
                        'c.customer_name',
                        'c.contact'
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

                    // Get detailed fee information using fee_ids
                    $feeIds = $transactionDetails->pluck('fee_id')->unique();
                    $selectedFeeDetails = Fee::whereIn('id', $feeIds)->get();

                    // Extract customer info from the first record
                    $customerInfo = (object)[
                        'customer_name' => $transactionDetails[0]->customer_name,
                        'contact' => $transactionDetails[0]->contact,
                    ];

                    Log::info("Loaded transaction details for: {$customerInfo->customer_name}");
                } else {
                    Log::warning("No transaction details found for transaction ID $transactionId");
                }

                return view('common.payments.update-payment', compact(
                    'fees',
                    'selectedFees',
                    'selectedFeeDetails',
                    'transactionDetails',
                    'transactionId',
                    'customerInfo'
                ));
            } elseif ($request->isMethod('put')) {
                Log::info("Validating update form input");
                $validated = $request->validate([
                    'quantities' => 'required|array',
                    'amounts' => 'required|array',
                ]);

                Log::info("Filtering Items with 0 quantity");
                // Filter out fees with zero quantity
                $fees = array_filter($validated['quantities'], function ($qty) {
                    return $qty > 0;
                });

                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                $feeIds = array_keys($fees);
                $quantities = array_values($fees);
                $feeNames = Fee::whereIn('id', $feeIds)->pluck('fee_name', 'id')->toArray();

                // Use same keys to extract amounts
                $amounts = array_map(function ($id) use ($validated) {
                    return $validated['amounts'][$id] ?? '0.00';
                }, $feeIds);

                $feeIdsStr = implode(',', $feeIds);
                $quantitiesStr = implode(',', $quantities);
                $amountsStr = implode(',', $amounts);

                $readableFees = [];
                foreach ($fees as $feeId => $qty) {
                    $name = $feeNames[$feeId] ?? "Fee ID $feeId";
                    $readableFees[$name] = $qty;  // store by name
                }

                $readableAmounts = [];
                foreach ($readableFees as $name => $qty) {
                    $amount = $validated['amounts'][array_search($name, $feeNames)] ?? '0.00'; // get amount by matching fee name key
                    $readableAmounts[$name] = $amount;
                }

                Log::info("Calling stored procedure to update fees for transaction $transactionId");
                DB::statement("CALL UpdateUnpaidTransaction(?, ?, ?, ?)", [
                    $transactionId,
                    $feeIdsStr,
                    $quantitiesStr,
                    $amountsStr,
                ]);

                DB::commit();

                // Use your AuditLogger to log the payment creation
                AuditLogger::log(
                    event: 'payment_edited_by_cashier',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transactionId,
                    oldValues: [],
                    newValues: [
                        'fees'          => $readableFees,     // ['Fee Name 1' => quantity, 'Fee Name 2' => quantity, ...]
                        'amounts'       => $readableAmounts,  // ['Fee Name 1' => amount, 'Fee Name 2' => amount, ...]
                    ],
                    tags: 'payment'
                );

                return redirect()->route('customer.transaction.details', ['id' => $transactionId]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Transaction update failed: " . $e->getMessage());
            return back()->with('error', 'Transaction update failed.');
        }
    }

    public function disapproveTransaction($id)
    {
        Log::info("Begin Transaction disapproval");
        DB::beginTransaction();
        try {
            $transaction = DB::table('transactions')->where('id', $id)->first();
            // Safety check: only delete if transaction is unfinalized and completely unpaid
            if (
                !$transaction ||
                !is_null($transaction->transaction_date) || // already finalized
                $transaction->amount_paid != 0 ||
                $transaction->balance_due != $transaction->total_amount
            ) {
                return redirect()->route('payments.pending')->with('error', 'Only unfinalized and unpaid transactions can be disapproved.');
            }

            // Get customer_id via the transaction
            $customerId = DB::table('customer_transaction_details')
                ->where('transaction_id', $id)
                ->value('customer_id');

            // Capture old values for audit before deletion
            $oldValues = [
                'transaction' => (array) $transaction,
                'customer_id' => $customerId,
            ];

            // Delete from customer_transaction_details
            DB::table('customer_transaction_details')->where('transaction_id', $id)->delete();

            // Delete the transaction
            DB::table('transactions')->where('id', $id)->delete();

            // Check if customer has any remaining transactions
            $remaining = DB::table('customer_transaction_details')
                ->where('customer_id', $customerId)
                ->count();

            if ($remaining === 0) {
                DB::table('student_details')->where('customer_id', $customerId)->delete();
                DB::table('customers')->where('id', $customerId)->delete();
                $oldValues['customer_deleted'] = true;
            } else {
                $oldValues['customer_deleted'] = false;
            }

            // Log the disapproval and deletion
            AuditLogger::log(
                event: 'transaction_disapproved_and_deleted',
                auditableType: 'App\\Models\\Transaction',
                auditableId: $id,
                oldValues: $oldValues,
                newValues: [],
                tags: 'transaction'
            );

            DB::commit();

            return redirect()->route('payments.pending')->with('success', 'Transaction disapproved and deleted successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Failed to Delete Transaction");
            return redirect()->route('payments.pending')->with('error', 'Failed to delete transaction');
        }
    }
}
