<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckSLAs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:check';
    protected $description = 'Check for tasks that have breached their SLA and escalate them';

    public function handle()
    {
        $this->info('Checking for SLA breaches...');

        // In a real application, you'd fetch active escalation rules and match them.
        // For the foundation, we'll just query overdue SLAs.
        $breachedTasks = \App\Models\Task::whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->statusNotIn([\App\Models\Task::STATUS_COMPLETED, \App\Models\Task::STATUS_CLOSED, \App\Models\Task::STATUS_CANCELLED])
            ->get();

        if ($breachedTasks->isEmpty()) {
            $this->info('No SLA breaches found.');
            return Command::SUCCESS;
        }

        foreach ($breachedTasks as $task) {
            // Increment escalation level
            $newLevel = $task->escalation_level + 1;
            $task->update(['escalation_level' => $newLevel]);

            // Log the escalation
            \App\Models\EscalationLog::create([
                'task_id' => $task->id,
                'escalation_rule_id' => 1, // Placeholder for default rule
                'action_taken' => "Escalated to Level {$newLevel}",
                'remarks' => "SLA Due Date " . $task->sla_due_at->format('Y-m-d H:i:s') . " breached.",
            ]);

            $this->warn("Task {$task->task_no} escalated to level {$newLevel}");
            // TODO: Dispatch notifications based on rules
        }

        $this->info('SLA check completed.');
        return Command::SUCCESS;
    }
}
