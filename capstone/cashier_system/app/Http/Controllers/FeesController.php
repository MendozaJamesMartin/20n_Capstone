<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeesController extends Controller
{
    public function GetFeesList() {
        $fees = Fee::all();
        return view('common.fees.list-of-fees', compact('fees'));
    }

    public function deletedFeesList() {
        $fees = Fee::onlyTrashed()->get();
        return view('common.fees.list-deleted', compact('fees'));
    }

    public function AddFees(Request $request) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
            'fees.*.fee_name' => 'required|string|max:100',
            'fees.*.amount' => 'required|numeric',
        ]);
    
        foreach ($validated['fees'] as $fees) {
            $existingItem = Fee::whereRaw('LOWER(fee_name) = ?', [strtolower($fees['fee_name'])])->first();
    
            if ($existingItem) {
                session()->flash('duplicate_item', 'Item "' . $fees['fee_name'] . '" already exists. Do you still want to proceed?');
                return back();
            }
    
            Fee::create($fees);
        }
        DB::commit();
        return redirect()->route('fees.list')->with('success', 'fees added successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Adding new fees unsuccessful");
            return back()->with('error', 'Failed to add fees');
        }
    }    

    public function UpdateFees(Request $request, $fees_id) {

        DB::beginTransaction();
        try {
            $validated = $request->validate([
            'fee_name' => 'required|string|max:100',
            'amount' => 'required|numeric'
        ]);
    
        $existingFee = Fee::whereRaw('LOWER(fee_name) = ?', [strtolower($validated['fee_name'])])
            ->where('id', '!=', $fees_id)
            ->first();
    
        if ($existingFee) {
            session()->flash('duplicate_item', 'Item "' . $validated['fee_name'] . '" already exists. Do you still want to proceed?');
            return back()->with('error', 'Failed to update fee');
        }
    
        $fees = Fee::findOrFail($fees_id);
        $fees->update([
            'fee_name' => $validated['fee_name'],
            'amount' => $validated['amount'],
        ]);
        
        DB::commit();
        return redirect()->route('fees.list')->with('success', 'Fee updated successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Adding new fees unsuccessful");
            return back()->with('error', 'Failed to update fees');
        }
    }    

    public function deleteFees($id) {
        $fees = Fee::find($id)->delete();
        return redirect()->route('fees.list')->with('success', 'Item deleted successfully!');
    }

    public function restoreFees($id) {
        $fees = Fee::withTrashed()->find($id)->restore();
        return redirect()->route('fees.list')->with('success', 'Item restored successfully!');
    }
}
