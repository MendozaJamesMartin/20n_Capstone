<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyTransactionReportExport;
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
use Maatwebsite\Excel\Facades\Excel;

class TransactionsController extends Controller
{
    public function GetTransactionsHistory(Request $request) {

        $timeframe = $request->input('timeframe', 'all');
        $search = $request->input('search', '');
        $show = $request->input('show', 'active'); // active or cancelled

        $query = DB::table('universal_transaction_history')->select();

        if ($show === 'cancelled') {
            $query->where('receipt_status', '=', 'Cancelled');
        } else {
            $query->where('receipt_status', '!=', 'Cancelled');
        }

        // Apply transaction date filter
        if ($timeframe === 'today') {
            $query->where('universal_transaction_history.transaction_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('universal_transaction_history.transaction_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('universal_transaction_history.transaction_date', '>=', Carbon::now()->subMonth());
        }

        // Apply receipt print date filter
        if ($timeframe === 'today') {
            $query->where('universal_transaction_history.receipt_print_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('universal_transaction_history.receipt_print_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('universal_transaction_history.receipt_print_date', '>=', Carbon::now()->subMonth());
        }

        // Apply entity_type filter if provided
        if ($request->has('customer_type') && !empty($request->input('customer_type'))) {
            $query->where('customer_type', $request->input('customer_type'));
        }

        // Apply sorting method
        $sortBy = $request->input('sort_by', 'receipt_print_date');
        $sortOrder = $request->input('sort_order', 'DESC');
        $validSortColumns = ['transaction_date', 'customer_type', 'customer_name', 'total_amount', 'receipt_print_date'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'receipt_print_date';
        }

        $query->orderBy($sortBy, $sortOrder);

        $result = $query->paginate(10)->appends(request()->except('page'));

        return view('common.transactions.transactions-history', compact('result', 'show'));
    }

    public function GetCustomerTransactionDetails ($id) {
        $TransactionDetails = DB::table('customer_transaction_receipt')
            ->where('transaction_id', $id)
            ->get();

        $currentBatch = ReceiptBatch::whereColumn('next_number', '<=', 'end_number')
            ->orderBy('id')
            ->first();

        return view('common.transactions.customer-details', compact('TransactionDetails'), ['hasActiveBatch' => $currentBatch !== null]);
    }

    public function GetConcessionaireTransactionDetails ($id) {
        $TransactionDetails = DB::table('concessionaire_transaction_receipt')
            ->where('transaction_id', $id)
            ->get();

        $currentBatch = ReceiptBatch::whereColumn('next_number', '<=', 'end_number')
            ->orderBy('id')
            ->first();

        return view('common.transactions.concessionaire-details', compact('TransactionDetails'), ['hasActiveBatch' => $currentBatch !== null]);
    }

    public function customerReceiptPDF ($id) {
        $TransactionDetails = DB::table('customer_transaction_receipt')
        ->where('transaction_id', $id)
        ->get();

        $Cashier = Auth::user();

        if ($TransactionDetails->isEmpty()) {
            abort(404, 'Transaction not found');
        }

        $pdf = Pdf::loadView('pdfs.customer-receipt-pdf', [
            'TransactionDetails' => $TransactionDetails,
            'Cashier' => $Cashier,
            'printMode' => false,
        ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

        // Stream the PDF so it opens in the browser (not downloaded unless user chooses to)
        return $pdf->stream("Receipt_{$id}.pdf");
    }

    public function concessionaireReceiptPDF ($id) {
        $TransactionDetails = DB::table('concessionaire_transaction_receipt')
        ->where('transaction_id', $id)
        ->get();

        $Cashier = Auth::user();

        if ($TransactionDetails->isEmpty()) {
            abort(404, 'Transaction not found');
        }

        $pdf = Pdf::loadView('pdfs.concessionaire-receipt-pdf', [
            'TransactionDetails' => $TransactionDetails,
            'Cashier' => $Cashier,
            'printMode' => false,
        ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

        // Stream the PDF so it opens in the browser (not downloaded unless user chooses to)
        return $pdf->stream("Receipt_{$id}.pdf");
    }

    public function finalizeTransaction ($transactionId) {
        Log::info("Finalize Transaction with ID: $transactionId");
        DB::beginTransaction();
        try {
            // Determine transaction type (customer or concessionaire)
            $isConcessionaire = DB::table('concessionaire_transaction_details')
                ->where('transaction_id', $transactionId)
                ->exists();

            $viewName = $isConcessionaire
                ? 'for-print.concessionaire-print'
                : 'for-print.customer-print';

            $receiptView = $isConcessionaire
                ? 'concessionaire_transaction_receipt'
                : 'customer_transaction_receipt';

            Log::info("Using view: $receiptView");

            $TransactionDetails = DB::table($receiptView)
                ->where('transaction_id', $transactionId)
                ->get();

            if ($TransactionDetails->isEmpty()) {
                abort(404, 'Transaction not found');
            }

            $Cashier = Auth::user();

            // Fetch the updated transaction before finalization for audit old/new values if needed
            $transactionBeforeFinalize = DB::table('transactions')->where('id', $transactionId)->first();

            Log::info("Calling FinalizeTransaction SP");
            DB::statement("CALL FinalizeTransaction(?)", [$transactionId]);
            Log::info("Calling FinalizeTransaction SP Success");

            // Fetch the updated transaction after finalization for audit old/new values if needed
            $transactionAfterFinalize = DB::table('transactions')->where('id', $transactionId)->first();

            if (!$transactionAfterFinalize) {
                abort(404, 'Transaction not found after finalization.');
            }

            // Log the finalize event
            AuditLogger::log(
                event: 'transaction_finalized',
                auditableType: 'App\\Models\\Transaction',
                auditableId: $transactionId,
                oldValues: [                    
                    'transaction_date' => $transactionBeforeFinalize->transaction_date,
                    'amount_paid' => $transactionBeforeFinalize->amount_paid,
                    'balance_due' => $transactionBeforeFinalize->balance_due,
                    'status' => 'pending',
                ],  // If you have previous state, you can include here
                newValues: [
                    'transaction_date' => $transactionAfterFinalize->transaction_date,
                    'amount_paid' => $transactionAfterFinalize->amount_paid,
                    'balance_due' => $transactionAfterFinalize->balance_due,
                    'status' => 'finalized',
                ],
                tags: 'transaction'
            );

            DB::commit();

            Log::info("generate PDF");
            $pdf = Pdf::loadView($viewName, [
                'TransactionDetails' => $TransactionDetails,
                'Cashier' => $Cashier,
                'printMode' => true,
            ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

            return $pdf->stream("Receipt_{$transactionId}.pdf");
            
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Failed to finalize transaction : " . $e->getMessage());
            return back()->with('error', 'Transaction failed.');
        }
    }

    public function cancelReceipt($id) {
        
        DB::beginTransaction();

        $receiptBeforeCancel = Receipt::where('transaction_id', $id)->firstOrFail();

        if ($receiptBeforeCancel) {
            try {
                
                DB::statement("CALL CancelTransaction(?)", [$id]);

                $receiptAfterCancel = Receipt::where('transaction_id', $id)->firstOrFail();

                // Log the finalize event
                AuditLogger::log(
                    event: 'receipt_cancelled',
                    auditableType: 'App\\Models\\Receipt',
                    auditableId: $id,
                    oldValues: [                    
                        'status' => $receiptBeforeCancel->status
                    ],  // If you have previous state, you can include here
                    newValues: [
                        'status' => $receiptAfterCancel->status
                    ],
                    tags: 'transaction'
                );

                DB::commit();

                return back()->with('success', 'Receipt has been cancelled.');

            } catch (QueryException $e) {
                DB::rollBack();
                Log::error("Failed to cancel receipt : " . $e->getMessage());
                return back()->with('error', 'Receipt cancellation failed.');
            }
        } else {
            return back()->with('error', 'Receipt not found.');
        }

    }

    public function exportMonthlyReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'fee_ids' => 'array',
            'fee_ids.*' => 'integer|exists:fees,id',
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $feeIds = $request->input('fee_ids', []);

        return Excel::download(
            new MonthlyTransactionReportExport($start, $end, $feeIds),
            "Report_{$start}_to_{$end}.xlsx"
        );
    }
    
}
