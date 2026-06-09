<?php

namespace App\Services;

use App\Repositories\AnalyticsRepository;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Collection;

class KPIEngine
{
    /**
     * Calculate Delay %
     * Formula: (Overdue Tasks / Total Uncompleted Tasks) * 100
     */
    public function calculateDelayPercentage(int $overdueCount, int $totalPendingCount): float
    {
        if ($totalPendingCount === 0) return 0;
        return round(($overdueCount / $totalPendingCount) * 100, 2);
    }

    /**
     * Calculate Average Completion Time (in hours)
     * For completed tasks, DiffInHours between created_at and updated_at (when status changed to completed)
     */
    public function calculateAverageCompletionTime($tasksQuery): float
    {
        // Must clone query to avoid modifying original
        $completedTasks = (clone $tasksQuery)->where('final_status', \App\Models\Task::FINAL_CLOSED)->get();
        
        if ($completedTasks->isEmpty()) return 0;

        $totalHours = 0;
        foreach ($completedTasks as $task) {
            $totalHours += $task->created_at->diffInHours($task->updated_at);
        }

        return round($totalHours / $completedTasks->count(), 1);
    }

    /**
     * Determine if an employee is overloaded.
     * Criteria: > 5 active tasks OR active tasks total estimated hours > 40.
     */
    public function isEmployeeOverloaded($userWorkload): bool
    {
        if ($userWorkload->active_tasks > 5) return true;
        if ($userWorkload->assigned_tasks_sum_estimated_hours > 40) return true;
        
        return false;
    }
}
