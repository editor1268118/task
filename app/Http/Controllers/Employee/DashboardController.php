<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\SalesQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportService;

class DashboardController extends Controller
{
    /**
     * Display the Employee dashboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(ReportService $reportService)
    {
        $user = Auth::user();
        $myQueries = fn () => SalesQuery::where(function ($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id)
                ->orWhere('assigned_by', $user->id);
        });

        $stats = [
            'my_tasks'        => Task::assignedTo($user->id)->count(),
            'due_today'       => Task::assignedTo($user->id)->whereDate('due_date', today())->statusNotIn([Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED])->count(),
            'pending_tasks'   => Task::assignedTo($user->id)->status(Task::STATUS_PENDING)->count(),
            'completed_tasks' => Task::assignedTo($user->id)->where('final_status', Task::FINAL_CLOSED)->count(),
            'overdue_tasks'   => Task::assignedTo($user->id)->overdue()->count(),
            'my_followups_today' => $myQueries()->where('status', 'Open')->whereDate('next_followup_date', today())->count(),
            'upcoming_followups' => $myQueries()->where('status', 'Open')->whereDate('next_followup_date', '>', today())->count(),
            'missed_followups' => $myQueries()->where('status', 'Open')->whereDate('next_followup_date', '<', today())->count(),
        ];

        $chartData = $reportService->getDashboardChartsData(null, $user->id);

        $upcomingTasks = Task::assignedTo($user->id)
            ->statusNotIn([Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED])
            ->orderBy('due_date', 'asc')
            ->with(['assigner', 'department'])
            ->take(5)
            ->get();

        $recentActivities = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('employee.dashboard', compact('stats', 'chartData', 'upcomingTasks', 'recentActivities'));
    }
}
