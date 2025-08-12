<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OwenIt\Auditing\Models\Audit;

class FixAuditValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audits:fix-values';

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
        $audits = Audit::all();
        $count = 0;

        foreach ($audits as $audit) {
            $changed = false;

            if (is_array($audit->old_values)) {
                $audit->old_values = json_encode($audit->old_values);
                $changed = true;
            }

            if (is_array($audit->new_values)) {
                $audit->new_values = json_encode($audit->new_values);
                $changed = true;
            }

            if ($changed) {
                $audit->save();
                $count++;
            }
        }

        $this->info("✅ Fixed {$count} audit records.");
    }
}
