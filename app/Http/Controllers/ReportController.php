<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use App\Services\KPIEngine;
use App\Repositories\AnalyticsRepository;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\ProductivityExport;

class ReportController extends Controller
{
    protected $reportService;
    protected $analytics;
    protected $kpi;

    public function __construct(ReportService $reportService, AnalyticsRepository $analytics, KPIEngine $kpi)
    {
        $this->reportService = $reportService;
        $this->analytics = $analytics;
        $this->kpi = $kpi;
    }

    /**
     * Productivity Report (Super Admin & Manager & Employee)
     */
    public function productivity(Request $request)
    {
        $user = Auth::user();
        $targetUser = $user;

        // If Admin or Manager, they can filter by Employee
        if ($user->hasRole(['super-admin', 'manager']) && $request->has('user_id')) {
            $targetUser = User::findOrFail($request->user_id);
            // Managers can only view users in their department
            if ($user->hasRole('manager') && $targetUser->department_id !== $user->department_id) {
                abort(403);
            }
        }

        list($startDate, $endDate) = $this->reportService->parseDateRange($request);

        $metrics = $this->analytics->getUserProductivityMetrics($targetUser, $startDate, $endDate);
        
        // Allowed users for filter
        if ($user->hasRole('super-admin')) {
            $employees = User::active()->get();
        } elseif ($user->hasRole('manager')) {
            $employees = User::active()->where('department_id', $user->department_id)->get();
        } else {
            $employees = collect([$user]);
        }

        // Export logic
        if ($request->has('export')) {
            return $this->exportProductivity($targetUser, $metrics, $request->export);
        }

        return view('reports.productivity', compact('metrics', 'targetUser', 'employees'));
    }

    /**
     * Department Performance Report (Super Admin & Manager)
     */
    public function department(Request $request)
    {
        $user = Auth::user();
        
        // Prevent Employees
        if ($user->hasRole('employee')) abort(403);

        list($startDate, $endDate) = $this->reportService->parseDateRange($request);

        $targetDeptId = null;
        if ($user->hasRole('manager')) {
            $targetDeptId = $user->department_id;
        } elseif ($request->has('department_id') && $request->department_id != '') {
            $targetDeptId = $request->department_id;
        }

        if ($targetDeptId) {
            $departments = collect([$this->analytics->getDepartmentPerformance($targetDeptId, $startDate, $endDate)]);
        } else {
            $departments = $this->analytics->getDepartmentPerformance(null, $startDate, $endDate);
        }

        $allDepartments = Department::active()->get();

        if ($request->has('export')) {
            return $this->exportDepartment($departments, $request->export);
        }

        return view('reports.department', compact('departments', 'allDepartments'));
    }

    /**
     * Workload Monitoring (Super Admin & Manager)
     */
    public function workload(Request $request)
    {
        $user = Auth::user();
        if ($user->hasRole('employee')) abort(403);

        $targetDeptId = $user->hasRole('manager') ? $user->department_id : $request->department_id;

        $workloads = $this->analytics->getWorkloadMetrics($targetDeptId);

        // Add Smart Warning Flag
        $workloads->map(function($emp) {
            $emp->is_overloaded = $this->kpi->isEmployeeOverloaded($emp);
            return $emp;
        });

        $departments = Department::active()->get();

        if ($request->has('export')) {
            return $this->exportWorkload($workloads, $request->export);
        }

        return view('reports.workload', compact('workloads', 'departments'));
    }

    /**
     * System Audit Log (Super Admin Only)
     */
    public function audit(Request $request)
    {
        if (!Auth::user()->hasRole('super-admin')) abort(403);

        $logs = \Spatie\Activitylog\Models\Activity::with('causer')
                    ->latest()
                    ->paginate(50);

        return view('reports.audit', compact('logs'));
    }

    // ─── Export Logic ──────────────────────────────────────────────────

    private function exportProductivity($targetUser, $metrics, $format)
    {
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.productivity_pdf', compact('targetUser', 'metrics'));
            return $pdf->download('productivity-report-'.$targetUser->employee_id.'.pdf');
        } elseif ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ProductivityExport($metrics, $targetUser->name), 'productivity-report.xlsx');
        } elseif ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ProductivityExport($metrics, $targetUser->name), 'productivity-report.csv');
        }
    }

    private function exportDepartment($departments, $format)
    {
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.department_pdf', compact('departments'));
            return $pdf->download('department-performance.pdf');
        } elseif ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DepartmentExport($departments), 'department-report.xlsx');
        } elseif ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DepartmentExport($departments), 'department-report.csv');
        }
    }

    private function exportWorkload($workloads, $format)
    {
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.workload_pdf', compact('workloads'));
            return $pdf->download('workload-report.pdf');
        } elseif ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WorkloadExport($workloads), 'workload-report.xlsx');
        } elseif ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WorkloadExport($workloads), 'workload-report.csv');
        }
    }
}
