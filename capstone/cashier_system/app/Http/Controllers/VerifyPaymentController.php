<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyPaymentController extends Controller
{
    public function verify(Request $request) {
        // Basic validation
        $request->validate([
            'or_no' => 'required',
            'customer_name' => 'required',
        ]);

        // Step 1: Find transaction
        $transaction = DB::table('universal_transaction_history')
            ->where('receipt_number', $request->or_no)
            ->whereRaw('LOWER(customer_name) = ?', [strtolower($request->customer_name)])
            ->first();

        if (!$transaction) {
            return response()->json([
                'valid' => false,
                'reason' => 'NOT_FOUND'
            ]);
        }

        // Step 2: Validate status
        if ($transaction->receipt_status !== 'Issued') {
            return response()->json([
                'valid' => false,
                'reason' => 'NOT_ISSUED'
            ]);
        }

        // Step 3: Get receipt items
        $items = DB::table('customer_transaction_receipt')
            ->where('transaction_id', $transaction->transaction_id)
            ->get();

        // Step 4: Check payment status
        if ($items->isEmpty() || $items->first()->payment_status !== 'Completed') {
            return response()->json([
                'valid' => false,
                'reason' => 'NOT_PAID'
            ]);
        }

        // Step 5: Return success
        return response()->json([
            'valid' => true,
            'reason' => null,
            'data' => [
                'receipt_number' => $transaction->receipt_number,
                'customer_name' => $transaction->customer_name,
                'transaction_date' => $transaction->transaction_date,
                'items' => $items->map(function ($item) {
                    return [
                        'document' => (
                            $item->fee_label &&
                            strtolower(trim($item->fee_label)) !== 'none'
                        )
                            ? $item->fee_label . ' - ' . $item->fee_name
                            : $item->fee_name,
                        'amount' => $item->subtotal
                    ];
                })
            ]
        ]);
    }
    
}
