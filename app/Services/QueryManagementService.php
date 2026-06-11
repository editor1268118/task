<?php

namespace App\Services;

use App\Models\QueryActivity;
use App\Models\QueryDiscussion;
use App\Models\QueryFollowup;
use App\Models\Customer;
use App\Models\SalesQuery;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryManagementService
{
    public function __construct(
        protected TaskService $taskService,
        protected SystemNotificationService $systemNotificationService
    )
    {
    }

    public function baseQuery(User $user): Builder
    {
        $query = SalesQuery::query()
            ->with(['assignedBy', 'assignedTo', 'convertedTask'])
            ->withCount('followups');

        if ($user->hasRole('employee')) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->hasRole('manager')) {
            $teamIds = User::where('department_id', $user->department_id)->pluck('id');
            $query->where(function ($q) use ($user, $teamIds) {
                $q->whereIn('assigned_to', $teamIds)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('assigned_by', $user->id);
            });
        } elseif ($user->hasRole('finance')) {
            abort(403);
        }

        return $query;
    }

    public function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('followup'), function ($q) use ($request) {
                if ($request->followup === 'pending') {
                    $q->whereNotNull('next_followup_date')
                        ->whereDate('next_followup_date', '>=', today())
                        ->whereIn('status', ['Open', 'Confirmed']);
                }

                if ($request->followup === 'overdue') {
                    $q->whereNotNull('next_followup_date')
                        ->whereDate('next_followup_date', '<', today())
                        ->whereIn('status', ['Open', 'Confirmed']);
                }
            })
            ->when($request->filled('quick'), function ($q) use ($request) {
                if ($request->quick === 'today') {
                    $q->whereDate('query_date', today());
                }
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('query_no', 'like', "%{$search}%")
                        ->orWhere('query_title', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('query_no'), fn ($q) => $q->where('query_no', 'like', '%' . $request->query_no . '%'))
            ->when($request->filled('service_type'), fn ($q) => $q->where('service_type', $request->service_type))
            ->when($request->filled('client_name'), fn ($q) => $q->where('client_name', 'like', '%' . $request->client_name . '%'))
            ->when($request->filled('company_name'), fn ($q) => $q->where('company_name', 'like', '%' . $request->company_name . '%'))
            ->when($request->filled('mobile'), fn ($q) => $q->where('mobile', 'like', '%' . $request->mobile . '%'))
            ->when($request->filled('assigned_by'), fn ($q) => $q->where('assigned_by', $request->assigned_by))
            ->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->source))
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->stage))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('travel_month'), fn ($q) => $q->where('travel_month', $request->travel_month))
            ->when($request->filled('destination'), fn ($q) => $q->where('destination', 'like', '%' . $request->destination . '%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('query_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('query_date', '<=', $request->date_to));
    }

    public function create(array $data, User $user): SalesQuery
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            $data['assigned_by'] = $data['assigned_by'] ?? $user->id;
            $data['query_date'] = $data['query_date'] ?? today();
            $data['travel_month'] = $this->travelMonth($data);
            $data['customer_id'] = $data['customer_id'] ?? $this->resolveCustomer($data, $user)->id;

            $query = SalesQuery::create($data);
            $this->log($query, $user, 'Query Created', $query->latest_remark);

            if ($query->assigned_to) {
                $this->log($query, $user, 'Assigned', 'Assigned to ' . ($query->assignedTo?->name ?? 'employee'));
                $this->systemNotificationService->queryAssigned($query->fresh(['assignedTo', 'assignedBy']), $user);
            }

            return $query->fresh(['assignedBy', 'assignedTo']);
        });
    }

    public function update(SalesQuery $query, array $data, User $user): SalesQuery
    {
        return DB::transaction(function () use ($query, $data, $user) {
            $old = $query->only(['assigned_to', 'stage', 'status']);

            $data['updated_by'] = $user->id;
            $data['travel_month'] = $this->travelMonth($data, $query);
            $data['customer_id'] = $data['customer_id'] ?? $this->resolveCustomer($data, $user, $query->customer)->id;
            $query->update($data);

            if (($old['assigned_to'] ?? null) !== $query->assigned_to) {
                $this->log($query, $user, 'Reassigned', 'Reassigned query', [
                    'old_assigned_to' => $old['assigned_to'],
                    'new_assigned_to' => $query->assigned_to,
                ]);
                $this->systemNotificationService->queryAssigned($query->fresh(['assignedTo', 'assignedBy']), $user);
            }

            if (($old['stage'] ?? null) !== $query->stage) {
                $this->log($query, $user, 'Stage Updated', $query->latest_remark, [
                    'old_stage' => $old['stage'],
                    'new_stage' => $query->stage,
                ]);
            }

            if (($old['status'] ?? null) !== $query->status) {
                $this->log($query, $user, 'Status Changed', $query->lost_reason ?: $query->latest_remark, [
                    'old_status' => $old['status'],
                    'new_status' => $query->status,
                ]);
                $this->systemNotificationService->queryStatusChanged($query->fresh(['assignedTo', 'assignedBy']), $user, $old['status'], $query->status);
            }

            return $query->fresh(['assignedBy', 'assignedTo']);
        });
    }

    public function addFollowup(SalesQuery $query, array $data, User $user): QueryFollowup
    {
        return DB::transaction(function () use ($query, $data, $user) {
            $followup = $query->followups()->create([
                'followup_date' => $data['followup_date'],
                'remarks' => $data['remarks'],
                'next_followup_date' => $data['next_followup_date'] ?? null,
                'created_by' => $user->id,
            ]);

            $query->update([
                'last_followup_date' => $followup->followup_date,
                'next_followup_date' => $followup->next_followup_date,
                'latest_remark' => $followup->remarks,
                'updated_by' => $user->id,
                'stage' => $query->stage === 'New Query' ? 'Follow Up' : $query->stage,
            ]);

            $this->log($query, $user, 'Follow-Up Added', $followup->remarks, [
                'followup_id' => $followup->id,
                'discussion_type' => $data['discussion_type'] ?? 'Follow-Up',
                'next_followup_date' => $followup->next_followup_date?->toDateString(),
            ]);

            $query->discussions()->create([
                'discussion_type' => $data['discussion_type'] ?? 'Follow-Up',
                'message' => $followup->remarks,
                'created_by' => $user->id,
            ]);

            return $followup;
        });
    }

    public function addDiscussion(SalesQuery $query, array $data, User $user): QueryDiscussion
    {
        return DB::transaction(function () use ($query, $data, $user) {
            $discussion = $query->discussions()->create([
                'discussion_type' => $data['discussion_type'],
                'message' => $data['message'],
                'mentioned_user_id' => $data['mentioned_user_id'] ?? null,
                'attachments' => null,
                'created_by' => $user->id,
            ]);

            $query->update([
                'latest_remark' => $discussion->message,
                'updated_by' => $user->id,
            ]);

            $this->log($query, $user, 'Discussion Added', $discussion->message, [
                'discussion_id' => $discussion->id,
                'discussion_type' => $discussion->discussion_type,
                'mentioned_user_id' => $discussion->mentioned_user_id,
            ]);

            if ($discussion->mentionedUser) {
                $this->systemNotificationService->send(
                    $discussion->mentionedUser,
                    'Mentioned on Query: ' . $query->query_no,
                    $user->name . ' mentioned you in a ' . $discussion->discussion_type . ' discussion.',
                    route('sales.queries.show', $query),
                    'query',
                    'discussion_mention',
                    'normal',
                    true,
                    ['query_id' => $query->id, 'discussion_id' => $discussion->id]
                );
            }

            return $discussion->fresh(['creator', 'mentionedUser']);
        });
    }

    public function updateDiscussion(QueryDiscussion $discussion, array $data, User $user): QueryDiscussion
    {
        return DB::transaction(function () use ($discussion, $data, $user) {
            $old = $discussion->only(['discussion_type', 'message', 'mentioned_user_id']);
            $discussion->update([
                'discussion_type' => $data['discussion_type'],
                'message' => $data['message'],
                'mentioned_user_id' => $data['mentioned_user_id'] ?? null,
            ]);

            $this->log($discussion->salesQuery, $user, 'Discussion Edited', $discussion->message, [
                'discussion_id' => $discussion->id,
                'old' => $old,
                'new' => $discussion->only(['discussion_type', 'message', 'mentioned_user_id']),
            ]);

            return $discussion->fresh(['creator', 'mentionedUser']);
        });
    }

    public function deleteDiscussion(QueryDiscussion $discussion, User $user): void
    {
        DB::transaction(function () use ($discussion, $user) {
            $query = $discussion->salesQuery;
            $this->log($query, $user, 'Discussion Deleted', $discussion->message, [
                'discussion_id' => $discussion->id,
                'discussion_type' => $discussion->discussion_type,
            ]);

            $discussion->delete();
        });
    }

    public function reassign(SalesQuery $query, int $newUserId, string $reason, User $user): SalesQuery
    {
        return DB::transaction(function () use ($query, $newUserId, $reason, $user) {
            $oldUserId = $query->assigned_to;
            $query->update([
                'assigned_to' => $newUserId,
                'assigned_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->log($query, $user, 'Reassigned', $reason, [
                'old_assigned_to' => $oldUserId,
                'new_assigned_to' => $newUserId,
                'assigned_by' => $user->id,
            ]);
            $this->systemNotificationService->queryAssigned($query->fresh(['assignedTo', 'assignedBy']), $user);

            return $query->fresh(['assignedBy', 'assignedTo']);
        });
    }

    public function convertToTask(SalesQuery $query, User $user): Task
    {
        if (!$query->canConvert()) {
            throw new \LogicException('Only confirmed, non-converted queries can be converted to tasks.');
        }

        return DB::transaction(function () use ($query, $user) {
            $taskType = $this->taskTypeForQuery($query);
            $task = $this->taskService->createTask([
                'title' => $query->effective_service_type . ' - ' . $query->client_name,
                'description' => implode("\n", array_filter([
                    'Converted from query ' . $query->query_no,
                    $query->destination ? 'Destination: ' . $query->destination : null,
                    $query->travel_date ? 'Travel Date: ' . $query->travel_date->format('d M Y') : null,
                    $query->number_of_pax ? 'Pax: ' . $query->number_of_pax : null,
                    $query->latest_remark ? 'Remarks: ' . $query->latest_remark : null,
                ])),
                'priority' => $this->taskPriority($query->priority),
                'department_id' => $query->assignedTo?->department_id,
                'assigned_to' => $query->assigned_to ?: $user->id,
                'start_date' => today(),
                'due_date' => $query->travel_date ?: today(),
                'estimated_hours' => null,
                'remarks' => $query->latest_remark,
                'task_type_id' => $taskType?->id,
                'customer_id' => $query->customer_id,
                'client_name' => $query->company_name ?: $query->client_name,
                'client_contact' => $query->mobile,
                'additional_info' => json_encode([
                    'query_no' => $query->query_no,
                    'company_name' => $query->company_name,
                    'destination' => $query->destination,
                    'travel_month' => $query->travel_month,
                    'number_of_pax' => $query->number_of_pax,
                ]),
            ], $user);

            $query->update([
                'status' => 'Converted',
                'converted_task_id' => $task->id,
                'updated_by' => $user->id,
            ]);

            $this->log($query, $user, 'Converted To Task', 'Converted to task ' . $task->task_no, [
                'task_id' => $task->id,
                'task_no' => $task->task_no,
            ]);

            $this->systemNotificationService->queryStatusChanged($query->fresh(['assignedTo', 'assignedBy']), $user, 'Confirmed', 'Converted');

            return $task;
        });
    }

    public function duplicates(array $data, ?SalesQuery $ignore = null): Collection
    {
        return SalesQuery::query()
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))
            ->where(function ($q) use ($data) {
                $q->when(!empty($data['mobile']), fn ($sub) => $sub->orWhere('mobile', $data['mobile']))
                    ->when(!empty($data['email']), fn ($sub) => $sub->orWhere('email', $data['email']))
                    ->when(!empty($data['company_name']), fn ($sub) => $sub->orWhere('company_name', $data['company_name']));
            })
            ->latest()
            ->limit(5)
            ->get();
    }

    public function dashboardStats(User $user): array
    {
        $query = $this->baseQuery($user);
        $total = (clone $query)->count();
        $converted = (clone $query)->where('status', 'Converted')->count();

        return [
            'total_queries' => $total,
            'todays_queries' => (clone $query)->whereDate('query_date', today())->count(),
            'pending_followups' => (clone $query)->whereDate('next_followup_date', '>=', today())->where('status', 'Open')->count(),
            'overdue_followups' => (clone $query)->whereDate('next_followup_date', '<', today())->where('status', 'Open')->count(),
            'confirmed_queries' => (clone $query)->where('status', 'Confirmed')->count(),
            'lost_queries' => (clone $query)->where('status', 'Lost')->count(),
            'converted_queries' => $converted,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0,
            'by_service_type' => (clone $query)->select('service_type')->selectRaw('count(*) as count')->groupBy('service_type')->pluck('count', 'service_type'),
            'by_source' => (clone $query)->select('source')->selectRaw('count(*) as count')->groupBy('source')->pluck('count', 'source'),
            'by_employee' => (clone $query)->with('assignedTo')->get()->groupBy(fn ($q) => $q->assignedTo?->name ?? 'Unassigned')->map->count(),
        ];
    }

    public function log(SalesQuery $query, ?User $user, string $action, ?string $remarks = null, array $properties = []): QueryActivity
    {
        return QueryActivity::create([
            'query_id' => $query->id,
            'activity_at' => now(),
            'user_id' => $user?->id,
            'action' => $action,
            'remarks' => $remarks,
            'properties' => $properties ?: null,
        ]);
    }

    private function travelMonth(array $data, ?SalesQuery $query = null): ?string
    {
        if (!empty($data['travel_month'])) {
            return $data['travel_month'];
        }

        $travelDate = $data['travel_date'] ?? $query?->travel_date;
        return $travelDate ? date('Y-m', strtotime((string) $travelDate)) : null;
    }

    private function taskTypeForQuery(SalesQuery $query): ?TaskType
    {
        $map = [
            'Hotel Booking' => 'hotel',
            'Tour Package' => 'tour-package',
            'Flight Booking' => 'flight',
            'Train Ticket' => 'train',
            'Bus Booking' => 'bus',
            'VISA' => 'visa',
            'Cruise' => 'cruise',
        ];

        return TaskType::where('slug', $map[$query->service_type] ?? Str::slug($query->service_type))->first()
            ?: TaskType::active()->first();
    }

    private function taskPriority(string $priority): string
    {
        return match ($priority) {
            'Urgent', 'High' => Task::PRIORITY_HIGH,
            'Low' => Task::PRIORITY_LOW,
            default => Task::PRIORITY_MEDIUM,
        };
    }

    private function resolveCustomer(array $data, User $user, ?Customer $existing = null): Customer
    {
        $customer = $existing;

        if (!$customer) {
            $customer = Customer::query()
                ->when(!empty($data['mobile']), fn ($q) => $q->orWhere('mobile', $data['mobile']))
                ->when(!empty($data['email']), fn ($q) => $q->orWhere('email', $data['email']))
                ->when(!empty($data['company_name']), fn ($q) => $q->orWhere('company_name', $data['company_name']))
                ->first();
        }

        $payload = [
            'customer_type' => !empty($data['company_name']) ? 'B2B' : ($customer?->customer_type ?? 'B2C'),
            'company_name' => $data['company_name'] ?? $customer?->company_name,
            'contact_person' => $data['client_name'] ?? $customer?->contact_person,
            'mobile' => $data['mobile'] ?? $customer?->mobile,
            'alternate_mobile' => $data['alternate_mobile'] ?? $customer?->alternate_mobile,
            'email' => $data['email'] ?? $customer?->email,
            'status' => $customer?->status ?? 'Active',
            'created_by' => $customer?->created_by ?? $user->id,
        ];

        if ($customer) {
            $customer->update(array_filter($payload, fn ($value) => $value !== null));
            return $customer->fresh();
        }

        return Customer::create($payload);
    }
}
