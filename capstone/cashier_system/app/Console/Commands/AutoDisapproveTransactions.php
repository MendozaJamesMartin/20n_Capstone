<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoDisapproveTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-disapprove-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("🕒 Auto-disapproval started...");

        DB::beginTransaction();

        try {
            $threshold = Carbon::now()->subDays(2);

            // Fetch unfinalized, unpaid transactions older than 2 days
            $transactions = DB::table('transactions')
                ->whereNull('transaction_date') // not finalized
                ->where('amount_paid', 0)
                ->whereColumn('balance_due', 'total_amount')
                ->where('created_at', '<', $threshold)
                ->get();

            if ($transactions->isEmpty()) {
                Log::info("✅ No eligible transactions found.");
                return Command::SUCCESS;
            }

            foreach ($transactions as $transaction) {
                Log::info("Disapproving Transaction ID {$transaction->id}");

                // Get customer_id
                $customerId = DB::table('customer_transaction_details')
                    ->where('transaction_id', $transaction->id)
                    ->value('customer_id');

                // Capture audit data
                $oldValues = [
                    'transaction' => [
                        'id'     => $transaction->id,
                        'number' => $transaction->transaction_number,
                        'total'  => $transaction->total_amount,
                    ],
                    'customer_id' => $customerId,
                ];

                // Delete details and transaction
                DB::table('customer_transaction_details')->where('transaction_id', $transaction->id)->delete();
                DB::table('transactions')->where('id', $transaction->id)->delete();

                // If the customer has no more transactions, delete the customer
                $remaining = DB::table('customer_transaction_details')
                    ->where('customer_id', $customerId)
                    ->count();

                if ($remaining === 0) {
                    DB::table('customers')->where('id', $customerId)->delete();
                    $oldValues['customer_deleted'] = true;
                } else {
                    $oldValues['customer_deleted'] = false;
                }

                // Log the audit
                AuditLogger::log(
                    event: 'transaction_auto_disapproved_and_deleted',
                    auditableType: 'App\\Models\\Transaction',
                    auditableId: $transaction->id,
                    oldValues: $oldValues,
                    newValues: [],
                    tags: 'transaction,auto'
                );
            }

            DB::commit();

            Log::info("✅ Auto-disapproval completed successfully.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Auto-disapproval failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
