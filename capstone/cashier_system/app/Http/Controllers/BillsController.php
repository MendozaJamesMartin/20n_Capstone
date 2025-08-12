<?php

namespace App\Http\Controllers;

use App\Models\Concessionaire;
use App\Models\ReceiptBatch;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillsController extends Controller
{
        public function GetBillingList(Request $request) {
        // Subquery to get latest electricity bill per concessionaire
        $electricityBills = DB::table('view_electricity_bills as eb1')->get();

        // Subquery to get latest water bill per concessionaire
        $waterBills = DB::table('view_water_bills as wb1')->get();

        return view('common.concessionaires.concessionaire-bills', compact('electricityBills', 'waterBills'));
    }

    public function CreateNewBilling(Request $request)
    {
        Log::info("CreateNewBilling method");
        DB::beginTransaction();
        try {
            if ($request->isMethod('get')) {

                Log::info("Get method to view UI");

                $concessionaires = DB::table('concessionaires')->where('status', 'Active')->get();
                return view('common.concessionaires.billing-create', compact('concessionaires'));

                // Check if the request is a POST (form submission)
            } elseif ($request->isMethod('post')) {

                Log::info("Input Validation 1");
                $validated = $request->validate([
                    'concessionaire_id' => 'required|exists:concessionaires,id',
                    'utility_type' => 'required|in:Water,Electricity',
                    'billing_period' => 'required|integer|between:1,12',
                    'due_date' => 'required|date',
                ]);

                Log::info("Billing Period Validation ");
                $billingMonth = $validated['billing_period'];
                $currentYear = now()->year;

                $billingPeriodDate = \Carbon\Carbon::create($currentYear, $billingMonth, 1)->format('Y-m-d');

                Log::info("Billing Period in Text format validated to correct format");

                Log::info("Validate Utility Type");
                if ($validated['utility_type'] === 'Water') {
                    $request->validate([
                        'current_charges' => 'required|numeric|min:0',
                    ]);

                    Log::info("Procedure call CreateWaterBill with values: {$validated['concessionaire_id']}, {$billingPeriodDate}, {$validated['due_date']}, {$request->input('current_charges')}");
                    DB::statement('CALL CreateWaterBill(?, ?, ?, ?)', [
                        $validated['concessionaire_id'],
                        $billingPeriodDate,
                        $validated['due_date'],
                        $request->input('current_charges')
                    ]);
                    Log::info("Successful procedure call");

                } elseif ($validated['utility_type'] === 'Electricity') {

                    // Step 1: Check if this concessionaire has any previous electricity bill
                    Log::info("Check if concessionaire first time bill");
                    $isFirstBill = DB::table('concessionaire_bills')
                        ->where('concessionaire_id', $validated['concessionaire_id'])
                        ->where('utility_type', 'Electricity')
                        ->count() === 0;
                    Log::info("Check finished");

                    // Step 2: Build validation rules dynamically
                    Log::info("Building Validation rules");
                    $electricityRules = [
                        'bill_start_date' => 'required|date',
                        'bill_end_date' => 'required|date|after_or_equal:bill_start_date',
                        'current_reading' => 'required|numeric|min:0',
                        'cost_per_kwh' => 'required|numeric|min:0',
                        'university_total_kwh' => 'required|numeric|min:0',
                        'university_total_bill' => 'required|numeric|min:0',
                        'previous_reading' => $isFirstBill ? 'required|numeric|min:0' : 'nullable|numeric|min:0'
                    ];

                    Log::info("Finished building validation rules");

                    // Step 3: Validate
                    $request->validate($electricityRules);

                    // Step 4: Decide what to pass to the procedure
                    $previousReading = $isFirstBill ? $request->input('previous_reading') : null;

                    Log::info("Procedure call CreateElectricityBill");
                    DB::statement('CALL CreateElectricityBill(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                        $validated['concessionaire_id'],
                        $billingPeriodDate,
                        $validated['due_date'],
                        $request->input('bill_start_date'),
                        $request->input('bill_end_date'),
                        $request->input('current_reading'),
                        $request->input('university_total_kwh'),
                        $request->input('university_total_bill'),
                        $request->input('cost_per_kwh'),
                        $previousReading
                    ]);
                    Log::info("Procedure call success");
                }

                Log::info("Commit");

                DB::commit();

                // Audit log
                AuditLogger::log(
                    event: 'billing_created',
                    auditableType: 'App\\Models\\ConcessionaireBill',
                    auditableId: $validated['concessionaire_id'],
                    oldValues: [],
                    newValues: $validated,
                    tags: $validated['utility_type']
                );

                return redirect()->back()->with('success', 'Billing created successfully!');
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire New Billing unsuccessful");
            return back()->with('error', 'Bill creation unsuccessful!');
        }
    }

    public function electricityBillingStatement($id) {
        $bill = DB::table('view_electricity_bills')->where('bill_id', $id)->first();
        if (!$bill) abort(404);

        $pdf = Pdf::loadView('pdfs.electricity-bill-pdf', compact('bill'));
        return $pdf->stream("Electricity_Bill_{$id}.pdf");
    }

    public function waterBillingStatement($id) {
        $bill = DB::table('view_water_bills')->where('bill_id', $id)->first();
        if (!$bill) abort(404);

        $pdf = Pdf::loadView('pdfs.water-bill-pdf', compact('bill'));
        return $pdf->stream("Water_Bill_{$id}.pdf");
    }

    public function BillsPayment(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info("Open Bills Payment Window");
            if ($request->isMethod('get')) {
                $concessionaires = Concessionaire::all();

                $currentBatch = ReceiptBatch::whereColumn('next_number', '<=', 'end_number')
                    ->orderBy('id')
                    ->first();

                return view('common.concessionaires.bills-payment', [
                    'concessionaires' => $concessionaires,
                    'hasActiveBatch' => $currentBatch !== null
                ]);
            }

            Log::info("Validate");
            // Handle POST
            $validated = $request->validate([
                'concessionaire_id' => 'required|exists:concessionaires,id',
                'utility_type' => 'required|array|min:1|max:2',
                'utility_type.*' => 'required|in:Water,Electricity|distinct',
                'amount_paid' => 'required|array|min:1|max:2',
                'amount_paid.*' => 'required|numeric|min:0.01'
            ]);

            Log::info("Checking if unpaid bills exist");
            // Check if any unpaid bills exist for each utility type
            foreach ($validated['utility_type'] as $index => $type) {
                $hasUnpaid = DB::table('concessionaire_bills')
                    ->where('concessionaire_id', $validated['concessionaire_id'])
                    ->where('utility_type', $type)
                    ->whereIn('status', ['Unpaid', 'Partially Paid'])
                    ->exists();

                if (!$hasUnpaid) {
                    return back()->with('error', "🚫 All bills for $type are already fully paid.");
                }
            }

            Log::info("Convert input into arrays");
            // Convert arrays to CSV for stored procedure
            $utilityCSV = implode(',', $validated['utility_type']);
            $amountCSV = implode(',', $validated['amount_paid']);

            Log::info("Utility CSV: $utilityCSV, Amount CSV: $amountCSV");

            Log::info("Procedure call");
            // Call the stored procedure
            $results = DB::select("CALL ConcessionaireBillsPayment(?, ?, ?)", [
                $validated['concessionaire_id'],
                $utilityCSV,
                $amountCSV
            ]);
            Log::info("Procedure Success");

            if (empty($results)) {
                DB::rollBack();
                return back()->with('error', 'Procedure did not return any result.');
            }

            $transactionId = $results[0]->transaction_id ?? null;
            $messages = $results[0]->message ?? '';

            DB::commit();
            Log::info("Commit");

            // Audit log
            AuditLogger::log(
                event: 'bills_payment',
                auditableType: 'App\\Models\\Transaction',
                auditableId: $transactionId,
                oldValues: [],
                newValues: $validated,
                tags: 'payment'
            );

            return redirect()->route('concessionaire.transaction.details', ['id' => $transactionId])
                            ->with('success', $messages);
            
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire Payment unsuccessful");
            return back()->with('error', 'Bills payment not successful');
        }
    }

}
