<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositsController extends Controller
{
    
    public function manage() {
        $deposits = Deposit::orderBy('deposit_date', 'desc')->get();
        return view('common.reports.deposit', compact('deposits'));
    }

    public function addDeposit(Request $request) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'deposit_date' => 'required|date',
                'reference_number' => 'required|string|unique:deposits,reference_number',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'amount' => 'required|numeric'
            ]);

            Deposit::create([
                'deposit_date' => $validated['deposit_date'],
                'reference_number' => $validated['reference_number'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'amount' => $validated['amount'],
            ]);

            DB::commit();
            return redirect()->route('deposits.manage')->with('success', 'Deposit Recorded Successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Transaction add failed: " . $e->getMessage());
            return back()->with('error', 'Failed to Record Deposit');
        }
    }

    public function editDeposit(Request $request, $id) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'deposit_date' => 'required|date',
                'reference_number' => 'required|string',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'amount' => 'required|numeric'
            ]);

            $deposit = Deposit::findOrFail($id);

            $deposit->update([
                'deposit_date' => $validated['deposit_date'],
                'reference_number' => $validated['reference_number'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'amount' => $validated['amount'],
            ]);

            DB::commit();
            return redirect()->route('deposits.manage')->with('success', 'Deposit Record Updated Successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Transaction update failed: " . $e->getMessage());
            return back()->with('error', 'Failed to Update Deposit Record');
        }
    }

    public function deleteDeposit($id) {
        $deposit = Deposit::findOrFail($id);
        $deposit->delete();

        DB::commit();
        return redirect()->route('deposits.manage')->with('success', 'Deposit Record Deleted Successfully!');
    }

}
