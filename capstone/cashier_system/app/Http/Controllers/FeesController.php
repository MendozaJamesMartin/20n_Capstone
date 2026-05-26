<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeesController extends Controller
{
    public function feesList(Request $request) {
        $status = $request->query('status', 'active'); // default to active

        if ($status === 'deleted') {
            $fees = Fee::onlyTrashed()
                ->orderBy('fee_name', 'asc')
                ->get();
        } else {
            $fees = Fee::orderBy('fee_name', 'asc')->get();
        }

        /*
        Get ENUM values from fees.classification
        */
        $column = DB::select("
            SHOW COLUMNS
            FROM fees
            LIKE 'classification'
        ");

        preg_match(
            "/^enum\(\'(.*)\'\)$/",
            $column[0]->Type,
            $matches
        );

        $classifications =
            explode(
                "','",
                $matches[1]
            );

        return view('common.fees.list-of-fees', compact('fees', 'status', 'classifications'));
    }

    public function AddFees(Request $request) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
            'fees.*.fee_name' => 'required|string|max:100',
            'fees.*.amount' => 'required|numeric',
            'fees.*.classification' => 'required|string'
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
            'amount' => 'required|numeric',
            'classification' => 'required|string'
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
            'classification' => $validated['classification']
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
        $fee = Fee::findOrFail($id);
        $fee->delete(); // Triggers audit
        return redirect()->route('fees.list')->with('success', 'Item deleted successfully!');
    }

    public function restoreFees($id) {
        $fee = Fee::withTrashed()->findOrFail($id);
        $fee->restore(); // Triggers audit
        return redirect()->route('fees.list')->with('success', 'Item restored successfully!');
    }
}
