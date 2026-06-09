<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_tasks' => Task::where('final_status', '!=', Task::FINAL_CLOSED)->count(),
            'operationally_completed_tasks' => Task::where('operational_status', Task::OPERATIONAL_COMPLETED)->count(),
            'collection_pending_tasks' => Task::where(function ($query) {
                $query->where('final_status', Task::FINAL_UNDER_COLLECTION)
                    ->orWhere('financial_status', Task::FINANCIAL_PENDING_BALANCE)
                    ->orWhereHas('taskStatus', fn ($sub) => $sub->where('slug', Task::STATUS_COLLECTION_PENDING));
            })->count(),
            'vendor_pending_tasks' => Task::where(function ($query) {
                $query->where('financial_status', Task::FINANCIAL_VENDOR_PENDING)
                    ->orWhereHas('taskStatus', fn ($sub) => $sub->where('slug', Task::STATUS_VENDOR_PAYMENT_PENDING));
            })->count(),
            'management_review_tasks' => Task::where('current_department', Task::DEPARTMENT_MANAGEMENT)
                ->where('final_status', Task::FINAL_UNDER_REVIEW)
                ->count(),
            'fully_closed_tasks' => Task::where('final_status', Task::FINAL_CLOSED)->count(),
            'revenue' => Booking::sum('sale_amount'),
            'expected_profit' => Booking::sum('expected_profit'),
        ];

        $reviewTasks = Task::with(['booking', 'financeApprover'])
            ->where('current_department', Task::DEPARTMENT_MANAGEMENT)
            ->where('final_status', Task::FINAL_UNDER_REVIEW)
            ->latest('finance_approved_at')
            ->take(10)
            ->get();

        return view('management.dashboard', compact('stats', 'reviewTasks'));
    }
}
