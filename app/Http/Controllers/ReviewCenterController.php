<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\ApprovalLog;
use App\Models\TaskStatusLog;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewCenterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $pendingReviews = Task::with(['assignee', 'department', 'booking', 'financeApprover'])
            ->where('final_status', Task::FINAL_UNDER_REVIEW)
            ->where('current_department', Task::DEPARTMENT_MANAGEMENT)
            ->when($user->hasRole('manager'), function($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('department_id', $user->department_id)
                        ->orWhere('current_department', Task::DEPARTMENT_MANAGEMENT);
                });
            })
            ->latest()
            ->get();

        // 2. My Approved/Rejected logs
        $myLogs = ApprovalLog::with(['task', 'step'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        return view('reviews.index', compact('pendingReviews', 'myLogs'));
    }

    public function action(Request $request, Task $task)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,request_correction',
            'comment' => 'nullable|string'
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $task, $user) {
            // Log the approval
            ApprovalLog::create([
                'task_id' => $task->id,
                'approval_step_id' => 1, // Placeholder until dynamic step tracking is fully mapped
                'user_id' => $user->id,
                'status' => $request->action,
                'comment' => $request->comment,
            ]);

            $statusSlug = $request->action === 'request_correction'
                ? Task::STATUS_COMPLETION_PENDING
                : Task::STATUS_FINANCE_REVIEW_PENDING;

            $taskStatus = TaskStatus::where('slug', $statusSlug)->first();

            if ($taskStatus) {
                $task->update([
                    'task_status_id' => $taskStatus->id,
                    'final_status' => $request->action === 'approve' ? Task::FINAL_CLOSED : Task::FINAL_ACTIVE,
                    'management_approved_at' => $request->action === 'approve' ? now() : $task->management_approved_at,
                    'management_approved_by' => $request->action === 'approve' ? $user->id : $task->management_approved_by,
                ]);
                
                TaskStatusLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $user->id,
                    'task_status_id' => $taskStatus->id,
                    'business_status_id' => $task->business_status_id,
                    'remarks' => "Review action: {$request->action} - {$request->comment}"
                ]);
            }
        });

        return redirect()->back()->with('success', 'Review action submitted successfully.');
    }
}
