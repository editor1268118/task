<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskCommentAddedNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        protected CustomerCrmService $customerCrmService,
        protected SystemNotificationService $systemNotificationService
    )
    {
    }

    /**
     * Create a new task and notify the assignee.
     */
    public function createTask(array $data, User $creator): Task
    {
        return DB::transaction(function () use ($data, $creator) {
            $data['assigned_by'] = $creator->id;
            $data = $this->applyCustomerDefaults($data);
            
            // Auto-calculate department based on assigned user if not provided
            if (empty($data['department_id']) && !empty($data['assigned_to'])) {
                $assignee = User::find($data['assigned_to']);
                $data['department_id'] = $assignee?->department_id;
            }

            // Assigned tasks should enter the active workflow as Assigned, not plain Pending.
            $initialStatusSlug = !empty($data['assigned_to']) ? Task::STATUS_ASSIGNED : Task::STATUS_PENDING;
            $initialStatus = \App\Models\TaskStatus::where('slug', $initialStatusSlug)->first();
            if ($initialStatus) {
                $data['task_status_id'] = $initialStatus->id;
            }
            $data['current_department'] = $data['current_department'] ?? Task::DEPARTMENT_SALES;

            $task = Task::create($data);
            $this->customerCrmService->syncCustomerForTask($task->fresh(['customer', 'booking']), $creator);

            // Notify Assignee
            if ($task->assignee && $task->assigned_to !== $creator->id) {
                $this->systemNotificationService->taskCreated($task, $creator);
            }

            return $task;
        });
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, array $data, User $updater): Task
    {
        $oldAssigneeId = $task->assigned_to;
        $data = $this->applyCustomerDefaults($data);

        if (array_key_exists('department_id', $data) && empty($data['department_id']) && !empty($data['assigned_to'])) {
            $assignee = User::find($data['assigned_to']);
            $data['department_id'] = $assignee?->department_id;
        }
        
        $task->update($data);
        $this->customerCrmService->syncCustomerForTask($task->fresh(['customer', 'booking']), $updater);

        // Notify new assignee if task was reassigned
        if (isset($data['assigned_to']) && $data['assigned_to'] !== $oldAssigneeId) {
            if ($task->assignee && $task->assigned_to !== $updater->id) {
                $this->systemNotificationService->taskAssigned($task, $updater);
            }
        }

        return $task;
    }

    /**
     * Update task status safely and trigger notifications.
     */
    public function updateStatus(Task $task, string $newStatus, User $updater, ?int $completionPercentage = null): Task
    {
        $oldStatus = $task->status;
        
        if ($oldStatus === $newStatus) {
            return $task;
        }

        // ─── Completion Workflow Guard ──────────────────────────────
        // If an employee/manager tries to directly set "completed" on a task
        // that has a completion workflow, block it (super-admin can bypass).
        if (in_array($newStatus, [
            Task::STATUS_COMPLETED,
            Task::STATUS_CLOSED,
            Task::STATUS_OPERATIONALLY_COMPLETED,
            Task::STATUS_COLLECTION_PENDING,
            Task::STATUS_VENDOR_PAYMENT_PENDING,
            Task::STATUS_FINANCE_REVIEW_PENDING,
        ], true)) {
            // Cannot directly complete — must use the completion wizard
            throw new \LogicException(
                'Tasks can only be closed by finance approval after operational and balance checks pass.'
            );
        }

        if ($task->operational_status === Task::OPERATIONAL_COMPLETED
            && $task->final_status !== Task::FINAL_CLOSED
            && in_array($newStatus, [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_FOLLOW_UP, Task::STATUS_ON_HOLD], true)
        ) {
            throw new \LogicException(
                'Operational work is complete. Use the financial workflow while settlement remains open.'
            );
        }

        $data = [];
        $taskStatus = \App\Models\TaskStatus::where('slug', $newStatus)->first();
        $businessStatus = null;

        if ($taskStatus) {
            $data['task_status_id'] = $taskStatus->id;
        } else {
            $businessStatus = \App\Models\BusinessStatus::where('slug', $newStatus)->first();
            if ($businessStatus) {
                $data['business_status_id'] = $businessStatus->id;
            } else {
                throw new \LogicException("Status '$newStatus' not found in either Task or Business statuses.");
            }
        }

        if (in_array($newStatus, [Task::STATUS_IN_PROGRESS, Task::STATUS_FOLLOW_UP], true)) {
            $data['current_department'] = Task::DEPARTMENT_SALES;
        }

        if ($newStatus === Task::STATUS_COMPLETED) {
            $data['completion_percentage'] = 100;
            $data['completed_at']          = now();
        } elseif ($completionPercentage !== null) {
            $data['completion_percentage'] = $completionPercentage;
        }

        $task->update($data);

        // Create Status Log
        \App\Models\TaskStatusLog::create([
            'task_id' => $task->id,
            'employee_id' => $updater->id,
            'task_status_id' => $taskStatus ? $taskStatus->id : $task->task_status_id,
            'business_status_id' => $businessStatus ? $businessStatus->id : $task->business_status_id,
            'remarks' => "Status changed from {$oldStatus} to {$newStatus}",
        ]);

        $this->systemNotificationService->taskStatusChanged($task, $updater, $oldStatus, $newStatus);

        return $task;
    }

    private function applyCustomerDefaults(array $data): array
    {
        if (!empty($data['customer_id'])) {
            $customer = \App\Models\Customer::find($data['customer_id']);
            if ($customer) {
                $data['client_name'] = !empty($data['client_name']) ? $data['client_name'] : ($customer->company_name ?: $customer->contact_person);
                $data['client_contact'] = !empty($data['client_contact']) ? $data['client_contact'] : $customer->mobile;
            }
        }

        return $data;
    }

    /**
     * Add a comment to a task.
     */
    public function addComment(Task $task, string $commentText, User $user): TaskComment
    {
        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $commentText,
        ]);

        // Notify relevant parties
        $notifiables = collect();
        if ($task->assigner && $task->assigned_by !== $user->id) {
            $notifiables->push($task->assigner);
        }
        if ($task->assignee && $task->assigned_to !== $user->id) {
            $notifiables->push($task->assignee);
        }

        foreach ($notifiables->unique('id') as $notifiable) {
            $notifiable->notify(new TaskCommentAddedNotification($task, $comment));
        }

        return $comment;
    }

    /**
     * Handle task attachment upload.
     */
    public function uploadAttachment(Task $task, $file, User $uploader): TaskAttachment
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        
        // Ensure unique filename
        $fileName = time() . '_' . str_replace(' ', '_', $originalName);
        $path = $file->storeAs('task-attachments/' . $task->task_no, $fileName, 'public');

        return $task->attachments()->create([
            'uploaded_by' => $uploader->id,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $size,
        ]);
    }
}
