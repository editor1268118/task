<?php

namespace App\Services;

use App\Models\OperationBoardColumnPreference;
use App\Models\CustomerReceipt;
use App\Models\Task;
use App\Models\User;
use App\Models\VendorPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MasterOperationsBoardService
{
    public const DEFAULT_COLUMNS = [
        'task_no',
        'task_type',
        'client_name',
        'client_contact',
        'assigned_employee',
        'created_date',
        'task_status',
        'last_updated',
        'sale_amount',
        'total_received',
        'pending_collection',
        'purchase_amount',
        'vendor_paid',
        'expected_profit',
        'finance_approval',
        'management_approval',
        'final_status',
        'last_activity_date',
        'last_activity_user',
    ];

    public const COLUMN_LABELS = [
        'task_no' => 'Task No',
        'task_type' => 'Task Type',
        'client_name' => 'Client Name',
        'client_contact' => 'Client Contact',
        'assigned_employee' => 'Assigned Employee',
        'created_date' => 'Created Date',
        'due_date' => 'Due Date',
        'priority' => 'Priority',
        'task_status' => 'Task Status',
        'business_status' => 'Business Status',
        'operational_status' => 'Operational Status',
        'current_department' => 'Current Department',
        'last_updated' => 'Last Updated',
        'sale_amount' => 'Sale Amount',
        'total_received' => 'Total Received',
        'pending_collection' => 'Pending Collection',
        'purchase_amount' => 'Purchase Amount',
        'vendor_paid' => 'Vendor Paid',
        'vendor_pending' => 'Vendor Pending',
        'expected_profit' => 'Expected Profit',
        'finance_approval' => 'Finance Approval',
        'management_approval' => 'Management Approval',
        'final_status' => 'Final Status',
        'last_activity_date' => 'Last Activity Date',
        'last_activity_user' => 'Last Activity User',
    ];

    public function baseQuery(User $user): Builder
    {
        $query = Task::query()
            ->with([
                'taskType',
                'taskStatus',
                'businessStatus',
                'assignee',
                'department',
                'booking',
                'activities.causer',
            ])
            ->withSum(['customerReceipts as received_total' => fn ($q) => $q->whereIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)], 'amount_received')
            ->withSum(['vendorPayments as vendor_paid_total' => fn ($q) => $q->whereIn('payment_status', VendorPayment::APPROVED_STATUSES)], 'amount_paid');

        if ($user->hasRole('manager')) {
            $query->where('department_id', $user->department_id);
        } elseif ($user->hasRole('finance')) {
            $query->financeRelevant();
        } elseif ($user->hasRole('employee')) {
            abort(403);
        }

        return $query;
    }

    public function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('task_no', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('client_contact', 'like', "%{$search}%")
                        ->orWhereHas('assignee', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('task_no'), fn ($q) => $q->where('task_no', 'like', '%' . $request->task_no . '%'))
            ->when($request->filled('client_name'), fn ($q) => $q->where('client_name', 'like', '%' . $request->client_name . '%'))
            ->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->filled('task_type_id'), fn ($q) => $q->where('task_type_id', $request->task_type_id))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('task_status'), fn ($q) => $q->status($request->task_status))
            ->when($request->filled('business_status_id'), fn ($q) => $q->where('business_status_id', $request->business_status_id))
            ->when($request->filled('operational_status'), fn ($q) => $q->where('operational_status', $request->operational_status))
            ->when($request->filled('financial_status'), fn ($q) => $q->where('financial_status', $request->financial_status))
            ->when($request->filled('current_department'), fn ($q) => $q->where('current_department', $request->current_department))
            ->when($request->filled('created_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->created_from))
            ->when($request->filled('created_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->created_to))
            ->when($request->filled('updated_from'), fn ($q) => $q->whereDate('updated_at', '>=', $request->updated_from))
            ->when($request->filled('updated_to'), fn ($q) => $q->whereDate('updated_at', '<=', $request->updated_to));
    }

    public function kpis(Builder $filteredQuery): array
    {
        $tasks = (clone $filteredQuery)->get();

        return [
            'total_tasks' => $tasks->count(),
            'active_tasks' => $tasks->where('final_status', '!=', Task::FINAL_CLOSED)->count(),
            'operationally_completed' => $tasks->where('operational_status', Task::OPERATIONAL_COMPLETED)->count(),
            'collection_pending_amount' => $tasks->sum(fn ($task) => $this->pendingCollection($task)),
            'vendor_pending_amount' => $tasks->sum(fn ($task) => $this->vendorPending($task)),
            'closed_tasks' => $tasks->where('final_status', Task::FINAL_CLOSED)->count(),
            'revenue' => $tasks->sum(fn ($task) => (float) ($task->booking?->sale_amount ?? 0)),
            'expected_profit' => $tasks->sum(fn ($task) => (float) ($task->booking?->expected_profit ?? 0)),
        ];
    }

    public function row(Task $task): array
    {
        $lastActivity = $task->activities->sortByDesc('created_at')->first();

        return [
            'task_no' => $task->task_no,
            'task_type' => $task->taskType?->name ?? '-',
            'client_name' => $task->client_name ?? $task->booking?->client_name ?? '-',
            'client_contact' => $task->client_contact ?? '-',
            'assigned_employee' => $task->assignee?->name ?? 'Unassigned',
            'created_date' => $task->created_at?->format('d M Y'),
            'due_date' => $task->due_date?->format('d M Y') ?? '-',
            'priority' => ucfirst($task->priority),
            'task_status' => $task->taskStatus?->name ?? ucfirst($task->status),
            'business_status' => $task->businessStatus?->name ?? '-',
            'operational_status' => str($task->operational_status ?? 'pending')->headline()->toString(),
            'current_department' => $task->current_department ?? '-',
            'last_updated' => $task->updated_at?->timezone(config('app.display_timezone'))->format('d M Y h:i A'),
            'sale_amount' => (float) ($task->booking?->sale_amount ?? 0),
            'total_received' => (float) ($task->received_total ?? 0),
            'pending_collection' => $this->pendingCollection($task),
            'purchase_amount' => (float) ($task->booking?->purchase_amount ?? 0),
            'vendor_paid' => (float) ($task->vendor_paid_total ?? 0),
            'vendor_pending' => $this->vendorPending($task),
            'expected_profit' => (float) ($task->booking?->expected_profit ?? 0),
            'finance_approval' => $task->finance_approved_at ? 'Approved' : 'Pending',
            'management_approval' => $task->management_approved_at ? 'Approved' : 'Pending',
            'final_status' => str($task->final_status ?? 'active')->headline()->toString(),
            'last_activity_date' => $lastActivity?->created_at?->timezone(config('app.display_timezone'))->format('d M Y h:i A') ?? '-',
            'last_activity_user' => $lastActivity?->causer?->name ?? '-',
            '_indicator' => $this->indicator($task),
            '_task_id' => $task->id,
        ];
    }

    public function rows(Collection $tasks): Collection
    {
        return $tasks->map(fn (Task $task) => $this->row($task));
    }

    public function selectedColumns(User $user, Request $request): array
    {
        $requested = $request->input('columns', []);
        if (is_string($requested)) {
            $requested = explode(',', $requested);
        }

        if ($requested) {
            return array_values(array_intersect(self::DEFAULT_COLUMNS, $requested));
        }

        $preference = OperationBoardColumnPreference::where('user_id', $user->id)->first();

        if ($preference?->columns) {
            return array_values(array_intersect(self::DEFAULT_COLUMNS, $preference->columns));
        }

        return self::DEFAULT_COLUMNS;
    }

    public function pendingCollection(Task $task): float
    {
        return max(0, (float) ($task->booking?->sale_amount ?? 0) - (float) ($task->received_total ?? 0));
    }

    public function vendorPending(Task $task): float
    {
        return max(0, (float) ($task->booking?->purchase_amount ?? 0) - (float) ($task->vendor_paid_total ?? 0));
    }

    private function indicator(Task $task): string
    {
        if ($task->status === Task::STATUS_CANCELLED) return 'gray';
        if ($task->isOverdue()) return 'red';
        if ($task->final_status === Task::FINAL_CLOSED) return 'green';
        if ($this->pendingCollection($task) > 0) return 'yellow';
        if ($this->vendorPending($task) > 0) return 'orange';
        if (!$task->finance_approved_at && $task->operational_status === Task::OPERATIONAL_COMPLETED) return 'blue';

        return 'light';
    }
}
