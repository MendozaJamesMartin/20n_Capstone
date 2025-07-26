<?php

namespace App\Http\Controllers;

use App\Models\Concessionaire;
use App\Models\ReceiptBatch;
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
                    $request->validate([
                        'bill_start_date' => 'required|date',
                        'bill_end_date' => 'required|date|after_or_equal:bill_start_date',
                        'current_reading' => 'required|numeric|min:0',
                        'concessionaire_kwh' => 'required|numeric|min:0',
                        'cost_per_kwh' => 'required|numeric|min:0',
                        'university_total_kwh' => 'required|numeric|min:0',
                        'university_total_bill' => 'required|numeric|min:0',
                    ]);

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
                        $request->input('concessionaire_kwh')
                    ]);
                    Log::info("Procedure call success");
                }

                Log::info("Commit");

                DB::commit();
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

            // Handle POST
            $validated = $request->validate([
                'concessionaire_id' => 'required|exists:concessionaires,id',
                'utility_type' => 'required|in:Water,Electricity',
                'amount_paid' => 'required|numeric|min:0.01'
            ]);

            // Check if there are any unpaid or partially paid bills
            $hasUnpaid = DB::table('concessionaire_bills')
                ->where('concessionaire_id', $validated['concessionaire_id'])
                ->where('utility_type', $validated['utility_type'])
                ->whereIn('status', ['Unpaid', 'Partially Paid'])
                ->exists();

            if (!$hasUnpaid) {
                return back()->with('error', '🚫 All bills for this concessionaire and utility type are already fully paid.');
                DB::rollBack();
            }

            // Call the stored procedure
            $results = DB::select("CALL ConcessionaireBillsPayment(?, ?, ?)", [
                $validated['concessionaire_id'],
                $validated['utility_type'],
                $validated['amount_paid']
            ]);

            $transactionId = $results[0]->transaction_id ?? null;

            DB::commit();

            return redirect()->route('concessionaire.transaction.details', ['id' => $transactionId])
                            ->with('success', $results[0]->message ?? 'Payment processed.');
            
        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire Payment unsuccessful");
            return back()->with('error', 'Bills payment not successful');
        }
    }
}
