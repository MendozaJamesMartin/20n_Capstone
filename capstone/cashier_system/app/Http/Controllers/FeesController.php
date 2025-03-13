<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    public function GetFeesList() {
        $fees = Fee::all();
        return view('common.fees.list-of-fees', compact('fees'));
    }

    public function AddFees(Request $request) {
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
    
        return redirect()->route('FeesList')->with('success', 'fees added successfully!');
    }    

    public function UpdateFees(Request $request, $fees_id)
    {
        $validated = $request->validate([
            'fee_name' => 'required|string|max:100',
            'amount' => 'required|numeric'
        ]);
    
        $existingFee = Fee::whereRaw('LOWER(fee_name) = ?', [strtolower($validated['fee_name'])])
            ->where('id', '!=', $fees_id)
            ->first();
    
        if ($existingFee) {
            session()->flash('duplicate_item', 'Item "' . $validated['fee_name'] . '" already exists. Do you still want to proceed?');
            return back();
        }
    
        $fees = Fee::findOrFail($fees_id);
        $fees->update([
            'fee_name' => $validated['fee_name'],
            'amount' => $validated['amount'],
        ]);
    
        return redirect()->route('FeesList')->with('success', 'Item updated successfully!');
    }    

    public function deleteItem() {

    }
}
