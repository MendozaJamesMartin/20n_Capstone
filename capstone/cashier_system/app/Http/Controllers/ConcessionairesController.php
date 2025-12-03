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
    public function GetConcessionairesList(Request $request) {

        $status = $request->query('status', 'active'); // default to active

        if ($status === 'deleted') {
            $concessionaires = Concessionaire::onlyTrashed()->get();
        } else {
            $concessionaires = Concessionaire::all();
        }
        
        return view('common.users.concessionaires-list', compact('concessionaires', 'status'));
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
        ]);
    
        $concessionaires = Concessionaire::findOrFail($concessionaires_id);

        $concessionaires->update([
            'name' => $validated['name'],
            'contact' => $validated['contact'],
        ]);
    
        DB::commit();
        return redirect()->route('concessionaires.list')->with('success', 'Concessionaire updated successfully!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::info("Concessionaire update failed");
            return back()->with('error', 'Concessionaire update failed');
        }
        
    }

    public function deleteConcessionaire($id) {
        $concessionaire = Concessionaire::findOrFail($id);

        // Get latest bill
        $latestBill = DB::table('concessionaire_bills')
            ->where('concessionaire_id', $id)
            ->orderBy('id', 'DESC')
            ->first();

        // If they have any bills at all
        if ($latestBill) {
            $hasRemainingBalance = $latestBill->amount_paid < $latestBill->total_due;

            if ($hasRemainingBalance) {
                return redirect()->route('concessionaires.list')
                    ->with('error', 'Cannot delete concessionaire with unpaid balance.');
            }
        }

        // Safe to delete
        $concessionaire->delete();
        return redirect()->route('concessionaires.list')->with('success', 'Concessionaire removed successfully!');
    }

    public function restoreConcessionaire($id) {
        $concessionaire = Concessionaire::withTrashed()->findOrFail($id);
        $concessionaire->restore(); // Triggers audit
        return redirect()->route('concessionaires.list')->with('success', 'Concessionaire restored successfully!');
    }

}
