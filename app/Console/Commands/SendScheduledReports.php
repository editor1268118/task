<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Task;
use App\Mail\DailyTaskSummaryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-daily';
    protected $description = 'Send daily task summary reports to Super Admins and Managers';

    public function handle()
    {
        $this->info('Starting to send daily reports...');

        // Only send to active super-admins and managers
        $users = User::active()->whereHas('roles', function($q) {
            $q->whereIn('name', ['super-admin', 'manager']);
        })->get();

        foreach ($users as $user) {
            // Compute metrics relevant to the user's scope
            $query = Task::query();

            if ($user->hasRole('manager')) {
                $query->inDepartment($user->department_id);
            }

            $metrics = [
                'active' => (clone $query)->statusIn([Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_ON_HOLD])->count(),
                'pending' => (clone $query)->status(Task::STATUS_PENDING)->count(),
                'completed_today' => (clone $query)->where('final_status', Task::FINAL_CLOSED)->whereDate('completed_at', Carbon::today())->count(),
                'overdue' => (clone $query)->overdue()->count(),
            ];

            Mail::to($user->email)->send(new DailyTaskSummaryMail($user, $metrics));
        }

        $this->info('Daily reports sent successfully.');
        return Command::SUCCESS;
    }
}
