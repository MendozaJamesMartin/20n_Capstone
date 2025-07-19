<?php

namespace App\Http\Controllers;

use App\Models\ReceiptBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptsController extends Controller
{
    public function manage() {
        
        $batches = ReceiptBatch::orderBy('created_at', 'desc')->get();

        foreach ($batches as $batch) {
            $batch->used_count;       
            $batch->remaining_count;   
            $batch->is_active = $batch->next_number <= $batch->end_number;
        }

        $currentBatch = $batches->where('is_active', true)->sortBy('id')->first();

        return view('common.receipts.manage-receipts', [
            'batches' => $batches,
            'currentBatch' => $currentBatch,
        ]);
    }

    public function addBatch(Request $request) {
        $validated = $request->validate([
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|gt:start_number',
        ]);

        $start = $validated['start_number'];
        $end = $validated['end_number'];

        // 1. Check if this range overlaps with any existing receipts (used numbers)
        $conflictInReceipts = DB::table('receipts')
            ->whereBetween('receipt_number', [$start, $end])
            ->exists();

        if ($conflictInReceipts) {
            return back()->with('error', 'Some receipt numbers in this range have already been used.');
        }

        // 2. Check if this range overlaps with any existing batches (even unused ones)
        $conflictInBatches = DB::table('receipt_batches')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_number', [$start, $end])
                    ->orWhereBetween('end_number', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_number', '<=', $start)
                            ->where('end_number', '>=', $end);
                    });
            })
            ->exists();

        if ($conflictInBatches) {
            return back()->with('error', 'This receipt number range overlaps with an existing batch.');
        }

        // All checks passed, create batch
        ReceiptBatch::create([
            'start_number' => $start,
            'end_number' => $end,
            'next_number' => $start,
        ]);

        return redirect()->route('receipts.manage')->with('success', 'Receipt batch added.');
    }

}
