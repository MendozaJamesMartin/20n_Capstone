<?php

namespace App\Http\Controllers;

use App\Models\Concessionaire;
use App\Models\ConcessionaireBill;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConcessionairesController extends Controller 
{
    public function GetConcessionairesList() {
        $concessionaires = Concessionaire::all();
        return view('common.users.concessionaires-list', compact('concessionaires'));
    }

    public function AddNewConcessionaire(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'contact' => 'required|numeric',
        ]);

        $concessionaires = Concessionaire::create($validated);
        return back();
    }

    public function GetBillingList(Request $request) {
        $utilityType = $request->input('utility_type');
        $paymentStatus = $request->input('status');
        $sortBy = $request->input('sort_by', 'id'); // Default sorting by due date
        $sortOrder = $request->input('sort_order', 'Desc'); // Default descending order

        $billings = ConcessionaireBill::with('concessionaire');

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

    public function BillsPayment(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {
                Log::info('Displaying concessionaire billing');
    
                // Fetch all concessionaires
                $concessionaires = Concessionaire::all();
    
                // Fetch only unpaid bills, filtered by concessionaire if selected
                $billsQuery = DB::table('concessionaire_bills')
                    ->join('concessionaires', 'concessionaire_bills.concessionaire_id', '=', 'concessionaires.id')
                    ->select(
                        'concessionaire_bills.id',
                        'concessionaire_bills.utility_type',
                        'concessionaire_bills.bill_amount',
                        'concessionaire_bills.balance_due',
                        'concessionaire_bills.due_date',
                        'concessionaires.name as concessionaire_name'
                    )
                    ->where('concessionaire_bills.balance_due', '>', 0);
    
                if ($request->has('concessionaire_id') && $request->concessionaire_id) {
                    $billsQuery->where('concessionaire_bills.concessionaire_id', $request->concessionaire_id);
                }
    
                $bills = $billsQuery->get();
    
                return view('common.concessionaires.bills-payment', compact('concessionaires', 'bills'));
                Log::info('display concessionaire billing');
            } elseif ($request->isMethod('post')) {
                Log::info('Processing concessionaire transaction request');
    
                // Validate input
                $validated = $request->validate([
                    'bill_id' => 'required|array',
                    'amount' => 'required|array',
                    'receipt_number' => 'required|string',
                ]);
                Log::info('Validation passed');
    
                // Filter out bills with zero or negative payments
                $filteredBills = [];
                foreach ($validated['bill_id'] as $billId) {
                    if (!empty($validated['amount'][$billId]) && $validated['amount'][$billId] > 0) {
                        $filteredBills[$billId] = $validated['amount'][$billId];
                    }
                }
                Log::info('Filtered valid payments', ['bills' => $filteredBills]);
    
                if (empty($filteredBills)) {
                    return back()->with('error', 'Please enter a valid payment amount for at least one bill.');
                }
    
                // Convert filtered bill IDs and amounts into comma-separated strings
                $billIds = implode(',', array_keys($filteredBills));
                $amounts = implode(',', array_values($filteredBills));
                Log::info('Formatted data for stored procedure', ['bill_ids' => $billIds, 'amounts' => $amounts]);
    
                // Call the stored procedure
                DB::statement("CALL ConcessionairePayBills(?, ?, ?)", [$billIds, $amounts, $validated['receipt_number']]);
                Log::info('Stored procedure executed successfully');
    
                DB::commit();
                return redirect()->route('receipts.list')->with('success', 'Bills paid successfully!');
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire Payment unsuccessful");
            return back();
        }
    }
}
