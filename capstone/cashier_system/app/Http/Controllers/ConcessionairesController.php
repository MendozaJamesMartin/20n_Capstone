<?php

namespace App\Http\Controllers;

use App\Mail\ConcessionaireBillMail;
use App\Mail\PaymentReceiptMail;
use App\Models\Concessionaire;
use App\Models\ConcessionaireBill;
use App\Models\ReceiptBatch;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ConcessionairesController extends Controller 
{
    public function GetConcessionairesList() {
        $concessionaires = Concessionaire::all();
        return view('common.users.concessionaires-list', compact('concessionaires'));
    }

    public function AddNewConcessionaire(Request $request) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
            'name' => 'required|string|max:45',
            'contact' => 'required|string|email',
        ]);

        Concessionaire::create($validated);

        DB::commit();
        return redirect()->route('concessionaires.list')->with('success','concessionaire added successfully');
        
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("New Concessionaire unsuccessfully registered");
            return back()->with('error', 'Concessionaire registration failed');
        }
    }

    public function updateConcessionaire(Request $request, $concessionaires_id) {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|email',
            'status' => 'required|in:Active,Inactive'
        ]);
    
        $concessionaires = Concessionaire::findOrFail($concessionaires_id);

        $concessionaires->update([
            'name' => $validated['name'],
            'contact' => $validated['contact'],
            'status' => $validated['status'],
        ]);
    
        DB::commit();
        return redirect()->route('concessionaires.list')->with('success', 'Concessionaire updated successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire update failed");
            return back()->with('error', 'Concessionaire update failed');
        }
        
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
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {
                $concessionaires = DB::table('concessionaires')->where('status', 'Active')->get();
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
                $results = DB::select("CALL CreateConcessionaireBilling(?, ?, ?, ?)", [
                    $validated['concessionaire_id'],
                    $validated['utility_type'],
                    $validated['bill_amount'],
                    $validated['due_date']
                ]);

                DB::commit();

                $concessionaire = Concessionaire::find($validated['concessionaire_id']);
                $billId = $results[0]->concessionaire_bills;
                $bills = ConcessionaireBill::find($billId);

                $pdf = Pdf::loadView('pdfs.concessionaire-billing-pdf', [
                    'bills' => $bills,
                    'concessionaire' => $concessionaire,
                    'due_date' => $validated['due_date']
                ]);

                $pdfContent = $pdf->output(); // Save to attach to mail

                Mail::to($concessionaire->contact)->send(
                    new ConcessionaireBillMail($validated['bill_amount'], $validated['utility_type'], $validated['due_date'], $pdfContent)
                );

                return redirect()->back()->with('success', 'Billing created successfully!');
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire New Billing unsuccessful");
            return back()->with('error', 'Bill creation unsuccessful!');
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
                $billings = ConcessionaireBill::with('concessionaire')
                    ->where('concessionaire_bills.balance_due', '>', 0);
    
                if ($request->has('concessionaire_id') && $request->concessionaire_id) {
                    $billings->where('concessionaire_bills.concessionaire_id', $request->concessionaire_id);
                }
    
                $bills = $billings->get();

                Log::info("Receipt Batch checker");
                $currentBatch = ReceiptBatch::whereColumn('next_number', '<=', 'end_number')
                    ->orderBy('id')
                    ->first();
                
                return view('common.concessionaires.bills-payment', compact('concessionaires', 'bills'), ['hasActiveBatch' => $currentBatch !== null]);
                Log::info('display concessionaire billing');
            } elseif ($request->isMethod('post')) {
                Log::info('Processing concessionaire transaction request');
    
                // Validate input
                $validated = $request->validate([
                    'bill_id' => 'required|array',
                    'amount' => 'required|array',
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
                $results = DB::select("CALL ConcessionairePayBills(?, ?)", [$billIds, $amounts]);
                Log::info('Stored procedure executed successfully');
    
                Log::info('Select Last insert ID from Stored Procedure');
                $transactionId = $results[0]->transaction_id;

                DB::commit();

                Log::info("return");
                return redirect()->route('concessionaire.transaction.details', ['id' => $transactionId]);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire Payment unsuccessful");
            return back()->with('error', 'Bills payment not successful');
        }
    }
}
