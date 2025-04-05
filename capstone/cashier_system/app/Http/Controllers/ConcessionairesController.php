<?php

namespace App\Http\Controllers;

use App\Models\Concessionaire;
use App\Models\ConcessionaireBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConcessionairesController extends Controller 
{
    public function GetConcessionairesList() {
        $concessionaires = Concessionaire::all();
        return view('common.concessionaires.concessionaires-list', compact('concessionaires'));
    }

    public function AddNewConcessionaire(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'contact' => 'required|numeric',
        ]);

        $concessionaires = Concessionaire::create($validated);
        return back();
    }

    public function GetConcessionaireBillingList(Request $request) {
        $utilityType = $request->input('utility_type');
        $paymentStatus = $request->input('status');
        $sortBy = $request->input('sort_by', 'id'); // Default sorting by due date
        $sortOrder = $request->input('sort_order', 'Desc'); // Default descending order

        $billings = ConcessionaireBill::with('concessionaire')->where('balance_due', '=', '0');

        // Apply utility_type filter if provided
        if (!empty($utilityType)) {
            $billings->where('utility_type', $utilityType);
        }

        // Apply payment status filter if provided
        if (!empty($paymentStatus)) {
            $billings->where('status', $paymentStatus);
        }

        // Apply sorting
        $billings->orderBy($sortBy, $sortOrder);

        // Get the results (now applying filters and sorting correctly)
        $result = $billings->paginate(10); // Use pagination for better performance

        return view('common.concessionaires.concessionaire-bills', compact('result', 'utilityType', 'paymentStatus', 'sortBy', 'sortOrder'));
    }

    public function CreateNewBilling(Request $request)
    {
        if ($request->isMethod('get')) {
            $concessionaires = DB::table('concessionaires')->get();
            return view('common.concessionaires.billing-create', compact('concessionaires'));

            // Check if the request is a POST (form submission)
        } elseif ($request->isMethod('post')) {
            $validated = $request->validate([
                'concessionaire_id' => 'required|exists:concessionaires,id',
                'utility_type' => 'required|in:Water,Electricity',
                'bill_amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
            ]);

            // Call stored procedure
            DB::statement("CALL CreateConcessionaireBilling(?, ?, ?, ?)", [
                $validated['concessionaire_id'],
                $validated['utility_type'],
                $validated['bill_amount'],
                $validated['due_date']
            ]);

            return redirect()->back()->with('success', 'Billing created successfully!');
        }
    }
}
