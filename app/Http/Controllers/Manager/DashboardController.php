<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\SalesQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportService;

class DashboardController extends Controller
{
    /**
     * Display the Manager dashboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(ReportService $reportService)
    {
        $user = Auth::user();
        $deptId = $user->department_id;

        $stats = [
            'team_members'    => User::where('department_id', $deptId)->count(),
            'team_tasks'      => Task::inDepartment($deptId)->count(),
            'pending_tasks'   => Task::inDepartment($deptId)->status(Task::STATUS_PENDING)->count(),
            'completed_tasks' => Task::inDepartment($deptId)->where('final_status', Task::FINAL_CLOSED)->count(),
            'delayed_tasks'   => Task::inDepartment($deptId)->overdue()->count(),
            'high_priority'   => Task::inDepartment($deptId)->priority(Task::PRIORITY_HIGH)->count(),
        ];

        $teamIds = User::where('department_id', $deptId)->pluck('id');
        $teamFollowUps = SalesQuery::whereIn('assigned_to', $teamIds)->whereNotNull('next_followup_date');
        $completedFollowUps = (clone $teamFollowUps)->whereIn('status', ['Confirmed', 'Converted'])->count();
        $totalFollowUps = (clone $teamFollowUps)->count();
        $stats += [
            'team_followups_today' => (clone $teamFollowUps)->where('status', 'Open')->whereDate('next_followup_date', today())->count(),
            'missed_followups' => (clone $teamFollowUps)->where('status', 'Open')->whereDate('next_followup_date', '<', today())->count(),
            'followup_completion_rate' => $totalFollowUps > 0 ? round(($completedFollowUps / $totalFollowUps) * 100, 2) : 0,
        ];

        $chartData = $reportService->getDashboardChartsData($deptId, null);

        $recentTasks = Task::inDepartment($deptId)
            ->with(['assignee', 'department'])
            ->latest()
            ->take(5)
            ->get();

        return view('manager.dashboard', compact('stats', 'recentTasks', 'chartData'));
    }
}
