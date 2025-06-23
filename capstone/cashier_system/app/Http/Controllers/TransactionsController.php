<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyTransactionReportExport;
use App\Mail\PaymentReceiptMail;
use App\Models\Concessionaire;
use App\Models\ConcessionaireBill;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\StudentTransactionDetail;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

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
            $query->where('universal_transaction_history.transaction_date', '>=', Carbon::now()->subDay());
        } elseif ($timeframe === 'this_week') {
            $query->where('universal_transaction_history.transaction_date', '>=', Carbon::now()->subWeek());
        } elseif ($timeframe === 'this_month') {
            $query->where('universal_transaction_history.transaction_date', '>=', Carbon::now()->subMonth());
        }

        // Apply date of receipt printing filter
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
        $sortBy = $request->input('sort_by', 'receipt_print_date'); // Default: sort by transaction date
        $sortOrder = $request->input('sort_order', 'DESC'); // Default: descending

        // Validate sorting parameters
        $validSortColumns = ['transaction_date', 'customer_type', 'customer_name', 'total_amount', 'receipt_print_date'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'receipt_print_date'; // Fallback to default sorting column
        }

        // Apply sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $result = $query->paginate(10)->appends(request()->except('page'));

        // Return the view with sorted and filtered results
        return view('common.transactions.transactions-history', compact('result'));
    }

    public function GetCustomerTransactionDetails ($id) {
        $TransactionDetails = DB::table('customer_transaction_receipt')
            ->where('transaction_id', $id)
            ->get();

        return view('common.transactions.customer-details', compact('TransactionDetails'));
    }

    public function GetConcessionaireTransactionDetails ($id) {
        $TransactionDetails = DB::table('concessionaire_transaction_receipt')
            ->where('transaction_id', $id)
            ->get();

        return view('common.transactions.concessionaire-details', compact('TransactionDetails'));
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
            // Now finalize the transaction
                Log::info("Retrieve customer_transaction_receipt");

                $TransactionDetails = DB::table('customer_transaction_receipt')
                    ->where('transaction_id', $transactionId)
                    ->get();

                $Cashier = Auth::user();

                Log::info("Transaction ID: {$transactionId}");
                Log::info("Transaction Details Retrieved: " . json_encode($TransactionDetails));
                
                if ($TransactionDetails->isEmpty()) {
                    abort(404, 'Transaction not found');
                }

                Log::info("generate PDF");
                $pdf = Pdf::loadView('for-print.customer-print', [
                    'TransactionDetails' => $TransactionDetails,
                    'Cashier' => $Cashier,
                    'printMode' => true,
                ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

                Log::info("Stored Procedure call FinalizeTransaction");

                DB::statement("CALL FinalizeTransaction(?)", [
                    $transactionId,
                ]);

                DB::commit();

                return $pdf->stream("Receipt_{$transactionId}.pdf");
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Failed to finalize transaction : " . $e->getMessage());
            return back()->with('error', 'Transaction failed.');
        }
    }

    public function finalizeConcessionaireTransaction ($transactionId) {
        Log::info("Finalize Concessionaire Transaction with ID: $transactionId");
        DB::beginTransaction();
        try {
                Log::info("Retrieve concessionaire_transaction_receipt");

                $TransactionDetails = DB::table('concessionaire_transaction_receipt')
                    ->where('transaction_id', $transactionId)
                    ->get();

                $Cashier = Auth::user();

                $concessionaire = $TransactionDetails[0]->concessionaire_name;

                Log::info("Transaction ID: {$transactionId}");
                Log::info("Concessionaire: {$concessionaire}");
                Log::info("Transaction Details Retrieved: " . json_encode($TransactionDetails));

                Log::info("generate PDF");
                $pdf = Pdf::loadView('for-print.concessionaire-print', [
                    'TransactionDetails' => $TransactionDetails,
                    'Cashier' => $Cashier,
                ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

                DB::commit();
                
                return $pdf->stream("Receipt_{$transactionId}.pdf");
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Failed to finalize transaction : " . $e->getMessage());
            return back()->with('error', 'Transaction failed.');
        }
    }

    public function saveReceiptNumber (Request $request) {
        DB::beginTransaction();
        try {
            $request->validate([
                'transaction_id' => 'required|exists:transactions,id',
                'receipt_number' => 'required|string|max:50|unique:receipts,receipt_number',
            ]);

            // Call the stored procedure to save receipt number
            $result = DB::select("CALL SaveReceiptNumber(?, ?)", [
                $request->transaction_id,
                $request->receipt_number,
            ]);

            DB::commit();

            $transactionId = $result[0]->transaction_id;
            $Cashier = Auth::user();

            // Retrieve transaction details
            $TransactionDetails = DB::table('customer_transaction_receipt')
                ->where('transaction_id', $transactionId)
                ->get();

            if ($TransactionDetails->isEmpty()) {
                throw new \Exception("Transaction details not found.");
            }

            // Extract values for email
            $email = $TransactionDetails[0]->contact_info ?? null;
            $receiptNumber = $TransactionDetails[0]->receipt_number ?? $request->receipt_number;
            $totalAmount = $TransactionDetails[0]->totalAmount ?? 0;

            Log::info('Generating PDF');
            // Generate the PDF
            $pdf = Pdf::loadView('pdfs.customer-receipt-pdf', [
                'TransactionDetails' => $TransactionDetails,    
                'Cashier' => $Cashier,
                'printMode' => false,
            ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

            Log::info('Sending email to: ' . $email);
            if ($email) {
                // Send the email with the PDF attached
                Mail::to($email)->send(
                    new PaymentReceiptMail($totalAmount, $receiptNumber, $TransactionDetails, $pdf->output())
                );
            }

            return response()->json(['success' => true]);

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Failed to save receipt number or send email: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

public function saveConcessionaireReceipt (Request $request) {
        Log::info("Save Concessionaire Receipt");
        DB::beginTransaction();
        try {
            $request->validate([
                'transaction_id' => 'required|exists:transactions,id',
                'receipt_number' => 'required|string|max:50|unique:receipts,receipt_number',
            ]);

            Log::info("Call Save Receipt Number Procedure");
            // Call the stored procedure to save receipt number
            $result = DB::select("CALL SaveReceiptNumber(?, ?)", [
                $request->transaction_id,
                $request->receipt_number,
            ]);

            DB::commit();

            $transactionId = $result[0]->transaction_id;
            $Cashier = Auth::user();

            // Retrieve transaction details
            $TransactionDetails = DB::table('concessionaire_transaction_receipt')
                ->where('transaction_id', $transactionId)
                ->get();

            if ($TransactionDetails->isEmpty()) {
                throw new \Exception("Transaction details not found.");
            }

            // Extract values for email
            $email = $TransactionDetails[0]->concessionaire_contact ?? null;
            $receiptNumber = $TransactionDetails[0]->receipt_number ?? $request->receipt_number;
            $totalAmount = $TransactionDetails[0]->total_amount ?? 0;

            Log::info("Transaction Details Retrieved: " . json_encode($TransactionDetails));

            Log::info('Generating PDF');
            // Generate the PDF
            $pdf = Pdf::loadView('pdfs.concessionaire-receipt-pdf', [
                'TransactionDetails' => $TransactionDetails,
                'Cashier' => $Cashier
            ])->setPaper([0, 0, 294.84, 612.36], 'portrait');

            Log::info('Sending email to: ' . $email);
            if ($email) {
                // Send the email with the PDF attached
                Mail::to($email)->send(
                    new PaymentReceiptMail($totalAmount, $receiptNumber, $TransactionDetails, $pdf->output())
                );
            }

            Log::info("Return");
            return response()->json([
                'success' => true,
                'redirect_url' => route('concessionaire.transaction.details', ['id' => $transactionId])
            ]);

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Failed to save receipt number or send email: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
