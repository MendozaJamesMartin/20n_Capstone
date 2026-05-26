<?php

namespace App\Http\Controllers;

use App\Exports\AccountabilityReport;
use App\Exports\MonthlyTransactionReportExport;
use App\Exports\CashReceiptsRecord;
use App\Exports\DepositReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function showReportsPage() {
        return view('common.reports.reports');
    }

    public function viewReport(Request $request)
    {
        return response()->json([
            'message' =>
                'Report preview is not yet implemented. Please download the Excel file instead.'
        ]);
    }

    public function exportReport(Request $request) {
        $reportType = $request->input('report_type');

        switch($reportType)
        {
            case 'transactions':

                return $this->exportTransactions($request);

            case 'accountability':

                return $this->exportAccountabilityReport($request);

            case 'collections':

                return back()->with(
                    'error',
                    'Report not implemented'
                );

            case 'cash_receipts':

                return $this->exportCashReceiptsRecord($request);

            case 'deposits':

                return $this->exportDepositReport($request);
        }
    }

    private function exportTransactions(Request $request) {
        $request->validate([
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date'
        ]);

        $start=$request->start_date;
        $end=$request->end_date;

        return Excel::download(
            new MonthlyTransactionReportExport(
                $start,
                $end,
                []
            ),
            "MonthlyTransactionsReport_{$start}_to_{$end}.xlsx"
        );
    }

    public function exportCashReceiptsRecord(Request $request) {
        $request->validate([
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date'
        ]);

        $start=$request->start_date;
        $end=$request->end_date;

        return Excel::download(
            new CashReceiptsRecord(
                $start,
                $end,
                []
            ),
            "CashReceiptsRecord_{$start}_to_{$end}.xlsx"
        );
    }

    public function exportAccountabilityReport(Request $request) {
        $request->validate([
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date'
        ]);

        $start=$request->start_date;
        $end=$request->end_date;

        return Excel::download(
            new AccountabilityReport(
                $start,
                $end,
            ),
            "Accountability_{$start}_to_{$end}.xlsx"
        );
    }
    
    public function exportDepositReport(Request $request) {
        $request->validate([
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date'
        ]);

        $start=$request->start_date;
        $end=$request->end_date;

        return Excel::download(
            new DepositReport(
                $start,
                $end,
            ),
            "Deposit_{$start}_to_{$end}.xlsx"
        );
    }

}
