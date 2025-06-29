<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Core\App\Models\RequestLog;

class CleanRequestLogs extends Command
{
    protected $signature = 'logs:clean-requests {--days=30}';
    protected $description = 'Clean old request logs';

    public function handle()
    {
        $days = $this->option('days');
        $deleted = RequestLog::where('created_at', '<', Carbon::now()->subDays($days))->delete();

        $this->info("Deleted {$deleted} old request logs older than {$days} days.");
    }
}
