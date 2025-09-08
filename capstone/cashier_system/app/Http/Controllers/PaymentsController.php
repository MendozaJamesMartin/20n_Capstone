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
use Illuminate\Support\Facades\Auth;
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
        $sortBy = $request->input('sort_by', 'transaction_number'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending

        // Validate sorting parameters
        $validSortColumns = ['transaction_date', 'full_name', 'total_amount'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'transaction_number'; // Fallback to default sorting column
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
                    'labels.*'      => 'required|string|min:1',
                ]);

                // filter valid rows
                $finalFeeIds = $finalQuantities = $finalAmounts = $finalLabels = [];
                foreach ($validated['fee_ids'] as $i => $feeId) {
                    $qty = $validated['quantities'][$i] ?? 0;
                    $amt = $validated['amounts'][$i] ?? 0;
                    $lbl = $validated['labels'][$i] ?? '';

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

                // convert to comma-separated
                $feeIdsStr     = implode(',', $finalFeeIds);
                $quantitiesStr = implode(',', $finalQuantities);
                $amountsStr    = implode(',', $finalAmounts);
                $labelsStr     = implode(',', $finalLabels);

                // call unified proc
                $results = DB::select("CALL UpsertTransaction(?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    0, // always new when cashier creates
                    $validated['customer_name'],
                    $validated['contact'],
                    $feeIdsStr,
                    $quantitiesStr,
                    $amountsStr,
                    $labelsStr,
                    'cashier',
                    Auth::id()
                ]);

                $transactionId = $results[0]->transaction_id;

                DB::commit();

                // Prepare readable logs
                $feeNames = Fee::whereIn('id', $finalFeeIds)->pluck('fee_name', 'id')->toArray();

                $readableFees = [];
                $readableAmounts = [];

                foreach ($finalFeeIds as $i => $feeId) {
                    $name   = $feeNames[$feeId] ?? "Fee ID $feeId";
                    $label  = $finalLabels[$i] ?? '';
                    $key    = $label ? "{$name} ({$label})" : $name;

                    $qty    = $finalQuantities[$i] ?? 1;
                    $amount = $finalAmounts[$i] ?? '0.00';

                    $readableFees[$key]    = $qty;
                    $readableAmounts[$key] = $amount;
                }

                // Log
                AuditLogger::log(
                    event: 'payment_created',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transactionId,
                    oldValues: [],
                    newValues: [
                        'customer_name' => $validated['customer_name'],
                        'contact'       => $validated['contact'],
                        'fees'          => $readableFees,     // ['Fee Name' => qty]
                        'amounts'       => $readableAmounts,  // ['Fee Name' => amount]
                        'labels'        => $labelsStr,
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
                $validated = $request->validate([
                    'customer_name' => 'required|string',
                    'contact'       => 'nullable|string|email',
                    'quantities'    => 'required|array',
                    'amounts'       => 'required|array',
                ]);

                // filter fees > 0
                $fees = array_filter($validated['quantities'], fn($qty) => $qty > 0);

                if (empty($fees)) {
                    return back()->with('error', 'Please select at least one fee.');
                }

                $feeIds = array_keys($fees);
                $quantities = array_values($fees);
                $amounts = array_map(fn($id) => $validated['amounts'][$id] ?? '0.00', $feeIds);

                $feeIdsStr     = implode(',', $feeIds);
                $quantitiesStr = implode(',', $quantities);
                $amountsStr    = implode(',', $amounts);
                $labelsStr     = implode(',', array_fill(0, count($feeIds), '')); // always empty

                $results = DB::select("CALL UpsertTransaction(?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    0,
                    $validated['customer_name'],
                    $validated['contact'],
                    $feeIdsStr,
                    $quantitiesStr,
                    $amountsStr,
                    $labelsStr,
                    'customer',
                    null
                ]);

                $transactionId = $results[0]->transaction_id;
                $transactionNum = $results[0]->transaction_number;

                DB::commit();

                // $fees = [feeId => qty]
                $feeNames = Fee::whereIn('id', array_keys($fees))->pluck('fee_name', 'id')->toArray();

                $readableFees = [];
                $readableAmounts = [];

                foreach ($fees as $feeId => $qty) {
                    $name   = $feeNames[$feeId] ?? "Fee ID $feeId";
                    $amount = $validated['amounts'][$feeId] ?? '0.00';

                    $readableFees[$name]    = $qty;
                    $readableAmounts[$name] = $amount;
                }

                // Log
                AuditLogger::log(
                    event: 'payment_created',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transactionId,
                    oldValues: [],
                    newValues: [
                        'customer_name' => $validated['customer_name'],
                        'contact'       => $validated['contact'],
                        'fees'          => $readableFees,     // ['Fee Name' => qty]
                        'amounts'       => $readableAmounts,  // ['Fee Name' => amount]
                    ],
                    tags: 'payment'
                );

                return redirect()->route('students.submitted', ['transaction_num' => $transactionNum]);
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
                //Get all fees
                $fees = Fee::all();

                $transactionDetails = DB::table('customer_transaction_receipt as ctr')
                    ->join('customers as c', 'ctr.customer_id', '=', 'c.id')
                    ->join('fees', 'fees.fee_name', '=', 'ctr.fee_name')
                    ->where('ctr.transaction_id', $transactionId)
                    ->select('fees.id as fee_id', 'fees.amount', 'fees.is_variable', 'ctr.*', 'c.customer_name', 'c.contact')
                    ->get();

                $customerInfo = $transactionDetails->isNotEmpty()
                    ? (object)[
                        'customer_name' => $transactionDetails[0]->customer_name,
                        'contact' => $transactionDetails[0]->contact,
                    ]
                    : null;

                return view('common.payments.update-payment', compact(
                    'fees',
                    'transactionDetails',
                    'transactionId',
                    'customerInfo'
                ));
            } elseif ($request->isMethod('put')) {
                $validated = $request->validate([
                    'fee_ids'   => 'required|array',
                    'quantities'=> 'required|array',
                    'amounts'   => 'required|array',
                    'labels'    => 'required|array',
                    'labels.*'  => 'required|string|min:1',
                ]);

                $feeIds = $quantities = $amounts = $labels = [];
                foreach ($validated['fee_ids'] as $i => $feeId) {
                    $qty = $validated['quantities'][$i] ?? 0;
                    $amt = $validated['amounts'][$i] ?? null;
                    $lbl = $validated['labels'][$i] ?? '';

                    if ($qty > 0 && $amt !== null) {
                        $feeIds[] = $feeId;
                        $quantities[] = $qty;
                        $amounts[] = $amt;
                        $labels[] = $lbl;
                    }
                }

                if (empty($feeIds)) {
                    return back()->with('error', 'Please select at least one valid fee.');
                }

                $feeIdsStr     = implode(',', $feeIds);
                $quantitiesStr = implode(',', $quantities);
                $amountsStr    = implode(',', $amounts);
                $labelsStr     = implode(',', $labels);

                $results = DB::select("CALL UpsertTransaction(?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $transactionId, // update existing
                    $request->customer_name,
                    $request->contact,
                    $feeIdsStr,
                    $quantitiesStr,
                    $amountsStr,
                    $labelsStr,
                    'cashier',
                    Auth::id()
                ]);

                DB::commit();

                // Prepare readable logs
                $feeNames = Fee::whereIn('id', $feeIds)->pluck('fee_name', 'id')->toArray();

                $readableFees = [];
                $readableAmounts = [];

                foreach ($feeIds as $i => $feeId) {
                    $name   = $feeNames[$feeId] ?? "Fee ID $feeId";
                    $label  = $labels[$i] ?? '';   // use $labels, not $finalLabels
                    $key    = $label ? "{$name} ({$label})" : $name;

                    $qty    = $quantities[$i] ?? 1;       // use $quantities
                    $amount = $amounts[$i] ?? '0.00';     // use $amounts

                    $readableFees[$key]    = $qty;
                    $readableAmounts[$key] = $amount;
                }

                // Log
                AuditLogger::log(
                    event: 'payment_updated',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transactionId,
                    oldValues: [],
                    newValues: [
                        'fees'          => $readableFees,     // ['Fee Name' => qty]
                        'amounts'       => $readableAmounts,  // ['Fee Name' => amount]
                        'labels'        => $labelsStr,
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
            Log::info("Safety Check");
            if (
                !$transaction ||
                !is_null($transaction->transaction_date) || // already finalized
                $transaction->amount_paid != 0 ||
                $transaction->balance_due != $transaction->total_amount
            ) {
                return redirect()->route('payments.pending')->with('error', 'Only unfinalized and unpaid transactions can be disapproved.');
            }

            Log::info("Get customer id");
            // Get customer_id via the transaction
            $customerId = DB::table('customer_transaction_details')
                ->where('transaction_id', $id)
                ->value('customer_id');

            Log::info("Capture old values for audit before deletion");
            $oldValues = [
                'transaction' => (array) $transaction,
                'customer_id' => $customerId,
            ];

            Log::info("Delete from customer_transaction_details");
            
            DB::table('customer_transaction_details')->where('transaction_id', $id)->delete();

            // Delete the transaction
            DB::table('transactions')->where('id', $id)->delete();

            Log::info("Check if customer has any remaining transactions");
            
            $remaining = DB::table('customer_transaction_details')
                ->where('customer_id', $customerId)
                ->count();

            if ($remaining === 0) {
                DB::table('customers')->where('id', $customerId)->delete();
                $oldValues['customer_deleted'] = true;
            } else {
                $oldValues['customer_deleted'] = false;
            }

            DB::commit();
            
            Log::info("Audit Logging");

            $oldValues = [
                'transaction' => [
                    'id'     => $transaction->id,
                    'number' => $transaction->transaction_number,
                    'total'  => $transaction->total_amount,
                ]
            ];

            // Log the disapproval and deletion
            AuditLogger::log(
                event: 'transaction_disapproved_and_deleted',
                auditableType: 'App\\Models\\Transaction',
                auditableId: $id,
                oldValues: $oldValues,
                newValues: [],
                tags: 'transaction'
            );

            return redirect()->route('payments.pending')->with('success', 'Transaction disapproved and deleted successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Failed to Delete Transaction");
            return redirect()->route('payments.pending')->with('error', 'Failed to delete transaction');
        }
    }
}
