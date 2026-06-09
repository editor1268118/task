<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsRepository
{
    /**
     * Get productivity metrics for a specific user.
     */
    public function getUserProductivityMetrics(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Task::assignedTo($user->id);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $total = (clone $query)->count();
        $completed = (clone $query)->where('final_status', Task::FINAL_CLOSED)->count();
        $pending = (clone $query)->status(Task::STATUS_PENDING)->count();
        $overdue = (clone $query)->overdue()->count();
        $active = (clone $query)->statusIn([Task::STATUS_IN_PROGRESS, Task::STATUS_ON_HOLD])->count();
        $cancelled = (clone $query)->status(Task::STATUS_CANCELLED)->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        return compact('total', 'completed', 'pending', 'overdue', 'active', 'cancelled', 'completionRate');
    }

    /**
     * Get workload metrics for all employees in a department (or all departments).
     */
    public function getWorkloadMetrics(?int $departmentId = null)
    {
        $query = User::withCount([
            'assignedTasks as active_tasks' => function ($q) {
                $q->statusNotIn([Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED]);
            },
            'assignedTasks as pending_tasks' => function ($q) {
                $q->status(Task::STATUS_PENDING);
            },
            'assignedTasks as overdue_tasks' => function ($q) {
                $q->overdue();
            }
        ])
        ->withSum(['assignedTasks' => function ($q) {
            $q->statusNotIn([Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED]);
        }], 'estimated_hours')
        ->withSum('assignedTasks', 'actual_hours');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->whereIn('status', ['active'])->orderByDesc('active_tasks')->get();
    }

    /**
     * Get department performance summary.
     */
    public function getDepartmentPerformance(?int $departmentId = null, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = Department::withCount([
            'tasks as total_tasks' => function($q) use ($startDate, $endDate) {
                if ($startDate) $q->whereDate('created_at', '>=', $startDate);
                if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            },
            'tasks as completed_tasks' => function($q) use ($startDate, $endDate) {
                $q->where('final_status', Task::FINAL_CLOSED);
                if ($startDate) $q->whereDate('created_at', '>=', $startDate);
                if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            },
            'tasks as overdue_tasks' => function($q) use ($startDate, $endDate) {
                $q->overdue();
                if ($startDate) $q->whereDate('created_at', '>=', $startDate);
                if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            }
        ]);

        if ($departmentId) {
            $query->where('id', $departmentId);
            return $query->first();
        }

        return $query->active()->get();
    }

    /**
     * Get monthly task trends (Created vs Completed) for charts.
     */
    public function getMonthlyTrends(int $months = 6, ?int $departmentId = null, ?int $userId = null): array
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            $createdQuery = Task::whereMonth('created_at', $date->month)
                               ->whereYear('created_at', $date->year);
                               
            $completedQuery = Task::whereMonth('updated_at', $date->month)
                                 ->whereYear('updated_at', $date->year)
                                 ->where('final_status', Task::FINAL_CLOSED);

            if ($departmentId) {
                $createdQuery->inDepartment($departmentId);
                $completedQuery->inDepartment($departmentId);
            }

            if ($userId) {
                $createdQuery->assignedTo($userId);
                $completedQuery->assignedTo($userId);
            }

            $trends[] = [
                'month' => $date->format('M Y'),
                'created' => $createdQuery->count(),
                'completed' => $completedQuery->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get tasks grouped by priority.
     */
    public function getPriorityDistribution(?int $departmentId = null, ?int $userId = null): array
    {
        $query = Task::select('priority', DB::raw('count(*) as count'));
        
        if ($departmentId) $query->inDepartment($departmentId);
        if ($userId) $query->assignedTo($userId);

        $results = $query->groupBy('priority')->pluck('count', 'priority')->toArray();

        return [
            'high' => $results['high'] ?? 0,
            'medium' => $results['medium'] ?? 0,
            'low' => $results['low'] ?? 0,
        ];
    }
}
