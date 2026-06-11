<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Models\Booking;
use App\Models\CustomerReceipt;
use App\Models\SalesQuery;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Services\ReportService;
use Spatie\Permission\PermissionRegistrar;

class DashboardController extends Controller
{
    /**
     * Display the Super Admin dashboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(ReportService $reportService)
    {
        $bookings = Booking::withSum(['receipts as received_total' => fn ($query) => $query->whereIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)], 'amount_received')
            ->withSum(['vendorPayments as paid_total' => fn ($query) => $query->whereIn('payment_status', VendorPayment::APPROVED_STATUSES)], 'amount_paid')
            ->whereHas('task')
            ->get();

        $stats = [
            'total_users'     => User::count(),
            'total_tasks'     => Task::count(),
            'pending_tasks'   => Task::status(Task::STATUS_PENDING)->count(),
            'completed_tasks' => Task::where('final_status', Task::FINAL_CLOSED)->count(),
            'overdue_tasks'   => Task::overdue()->count(),
            'departments'     => Department::active()->count(),
            'pending_collections' => $bookings->sum(fn ($booking) => max(0, (float) $booking->sale_amount - (float) ($booking->received_total ?? 0))),
            'vendor_pending_payments' => $bookings->sum(fn ($booking) => max(0, (float) $booking->purchase_amount - (float) ($booking->paid_total ?? 0))),
            'operationally_completed_tasks' => Task::where('operational_status', Task::OPERATIONAL_COMPLETED)->count(),
            'financially_pending_tasks' => Task::where('final_status', '!=', Task::FINAL_CLOSED)->where('operational_status', Task::OPERATIONAL_COMPLETED)->count(),
            'fully_closed_tasks' => Task::where('final_status', Task::FINAL_CLOSED)->count(),
            'total_followups' => SalesQuery::whereNotNull('next_followup_date')->count(),
            'completed_followups' => SalesQuery::whereIn('status', ['Confirmed', 'Converted'])->count(),
            'missed_followups' => SalesQuery::whereDate('next_followup_date', '<', today())->where('status', 'Open')->count(),
            'conversion_tracking' => Task::where('business_status_id', \App\Models\BusinessStatus::where('slug', 'booking_confirmed')->value('id'))->count(),
        ];

        $departmentTasks = Department::withCount('tasks')->having('tasks_count', '>', 0)->get();
        $chartData = $reportService->getDashboardChartsData();

        return view('admin.dashboard', compact('stats', 'departmentTasks', 'chartData'));
    }

    public function clearCache()
    {
        Cache::flush();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Artisan::call('optimize:clear');

        return back()->with('success', 'Application cache, config cache, route cache, view cache, and permission cache were cleared.');
    }
}
