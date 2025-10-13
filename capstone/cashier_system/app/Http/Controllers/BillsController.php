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
        // Latest Electricity Bill per Concessionaire
        $electricityBills = DB::table('view_electricity_bills as eb1')
            ->whereRaw('eb1.bill_date = (
                SELECT MAX(eb2.bill_date)
                FROM view_electricity_bills eb2
                WHERE eb2.concessionaire_name = eb1.concessionaire_name
            )')
            ->orderBy('eb1.concessionaire_name')
            ->get();

        // Latest Water Bill per Concessionaire
        $waterBills = DB::table('view_water_bills as wb1')
            ->whereRaw('wb1.bill_date = (
                SELECT MAX(wb2.bill_date)
                FROM view_water_bills wb2
                WHERE wb2.concessionaire_name = wb1.concessionaire_name
            )')
            ->orderBy('wb1.concessionaire_name')
            ->get();

        return view('common.concessionaires.concessionaire-bills', compact('electricityBills', 'waterBills'));
    }

    public function CreateNewBilling(Request $request)
    {
        Log::info("CreateNewBilling method");

        DB::beginTransaction();

        try {
            if ($request->isMethod('get')) {
                Log::info("Get method to view UI");

                // Pass all concessionaires for search suggestions
                $concessionaires = Concessionaire::select('name')->orderBy('name')->get();

                return view('common.concessionaires.billing-create', compact('concessionaires'));
            }

            elseif ($request->isMethod('post')) {
                Log::info("POST method triggered");

                $currentYear = now()->year;
                $startOfYear = now()->startOfYear()->toDateString();
                $endOfYear   = now()->endOfYear()->toDateString();

                $validated = $request->validate([
                    'concessionaire_name' => 'required|string|max:100',
                    'utility_type' => 'required|in:Water,Electricity',
                    'billing_period' => 'required|integer|between:1,12',
                    'due_date' => "required|date|after_or_equal:$startOfYear|before_or_equal:$endOfYear",
                ]);

                $billingMonth = $validated['billing_period'];
                $billingPeriodDate = \Carbon\Carbon::create($currentYear, $billingMonth, 1)->format('Y-m-d');

                if ($validated['utility_type'] === 'Water') {
                    $request->validate([
                        'current_charges' => 'required|numeric|min:0',
                        'water_previous_unpaid' => 'required|numeric|min:0',
                    ]);

                    $results = DB::select('CALL CreateWaterBill(?, ?, ?, ?, ?)', [
                        $validated['concessionaire_name'],
                        $billingPeriodDate,
                        $validated['due_date'],
                        $request->input('current_charges'),
                        $request->input('water_previous_unpaid')
                    ]);

                } elseif ($validated['utility_type'] === 'Electricity') {

                    $rules = [
                        'bill_start_date' => "required|date|after_or_equal:$startOfYear|before_or_equal:$endOfYear",
                        'bill_end_date' => "required|date|after_or_equal:bill_start_date|before_or_equal:$endOfYear",
                        'current_reading' => 'required|numeric|min:0',
                        'cost_per_kwh' => 'required|numeric|min:0',
                        'university_total_kwh' => 'required|numeric|min:0',
                        'university_total_bill' => 'required|numeric|min:0',
                        'previous_reading' => 'required|numeric|min:0',
                        'electricity_previous_unpaid' => 'required|numeric|min:0'
                    ];
                    $request->validate($rules);

                    $results = DB::select('CALL CreateElectricityBill(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                        $validated['concessionaire_name'],
                        $billingPeriodDate,
                        $validated['due_date'],
                        $request->input('bill_start_date'),
                        $request->input('bill_end_date'),
                        $request->input('current_reading'),
                        $request->input('university_total_kwh'),
                        $request->input('university_total_bill'),
                        $request->input('cost_per_kwh'),
                        $request->input('previous_reading'),
                        $request->input('electricity_previous_unpaid')
                    ]);
                }

                $billId = $results[0]->bill_id;

                DB::commit();

                AuditLogger::log(
                    event: 'billing_created',
                    auditableType: 'App\\Models\\ConcessionaireBill',
                    auditableId: $billId,
                    oldValues: [],
                    newValues: $validated,
                    tags: $validated['utility_type']
                );

                return redirect()->back()->with('success', 'Billing created successfully!');
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Concessionaire Billing failed: " . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'code' => $e->getCode(),
                'errorInfo' => $e->errorInfo,
            ]);
            return back()->with('error', 'Bill creation unsuccessful. Please review your input.');
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
    
}
