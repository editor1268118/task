<?php

namespace App\Services;

use App\Models\SalesQuery;
use App\Models\Task;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SystemNotificationService
{
    public function send(
        iterable|User|null $users,
        string $title,
        string $message,
        ?string $url = null,
        string $module = 'system',
        string $action = 'updated',
        string $priority = 'normal',
        bool $mail = true,
        array $meta = []
    ): void {
        $notifiables = $this->normalizeUsers($users);

        if ($notifiables->isEmpty()) {
            return;
        }

        try {
            Notification::send($notifiables, new SystemAlertNotification(
                $title,
                $message,
                $url,
                $module,
                $action,
                $priority,
                $mail,
                $meta
            ));

            activity()
                ->withProperties([
                    'module' => $module,
                    'action' => $action,
                    'priority' => $priority,
                    'recipients' => $notifiables->pluck('id')->all(),
                    'url' => $url,
                ])
                ->log('System notification dispatched');
        } catch (\Throwable $exception) {
            Log::warning('System notification dispatch failed', [
                'module' => $module,
                'action' => $action,
                'recipients' => $notifiables->pluck('id')->all(),
                'error' => $exception->getMessage(),
            ]);

            activity()
                ->withProperties([
                    'module' => $module,
                    'action' => $action,
                    'recipients' => $notifiables->pluck('id')->all(),
                    'error' => $exception->getMessage(),
                ])
                ->log('System notification dispatch failed');
        }
    }

    public function taskAssigned(Task $task, User $actor): void
    {
        $this->send(
            $task->assignee,
            'New Task Assigned: ' . $task->task_no,
            $actor->name . ' assigned you the task "' . $task->title . '".',
            route('tasks.show', $task),
            'task',
            'assigned',
            $task->priority === Task::PRIORITY_HIGH ? 'high' : 'normal',
            true,
            ['task_id' => $task->id, 'task_no' => $task->task_no]
        );
    }

    public function taskCreated(Task $task, User $actor): void
    {
        $recipients = collect([$task->assignee])
            ->filter(fn ($user) => $user && $user->id !== $actor->id);

        if ($recipients->isEmpty()) {
            return;
        }

        $this->send(
            $recipients,
            'Task Created: ' . $task->task_no,
            $actor->name . ' created task "' . $task->title . '" and assigned it to you.',
            route('tasks.show', $task),
            'task',
            'created',
            $task->priority === Task::PRIORITY_HIGH ? 'high' : 'normal',
            true,
            ['task_id' => $task->id, 'task_no' => $task->task_no]
        );
    }

    public function taskStatusChanged(Task $task, User $actor, string $oldStatus, string $newStatus): void
    {
        $recipients = collect([$task->assigner, $task->assignee])->filter(fn ($user) => $user && $user->id !== $actor->id);

        $this->send(
            $recipients,
            'Task Status Updated: ' . $task->task_no,
            $actor->name . ' changed status from ' . $this->label($oldStatus) . ' to ' . $this->label($newStatus) . '.',
            route('tasks.show', $task),
            'task',
            'status_changed',
            'normal',
            true,
            ['task_id' => $task->id, 'task_no' => $task->task_no, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    public function queryAssigned(SalesQuery $query, User $actor): void
    {
        $this->send(
            $query->assignedTo,
            'Query Assigned: ' . $query->query_no,
            $actor->name . ' assigned you a ' . $query->effective_service_type . ' query for ' . $query->client_name . '.',
            route('sales.queries.show', $query),
            'query',
            'assigned',
            $query->priority === 'Urgent' ? 'high' : 'normal',
            true,
            ['query_id' => $query->id, 'query_no' => $query->query_no]
        );
    }

    public function queryStatusChanged(SalesQuery $query, User $actor, string $oldStatus, string $newStatus): void
    {
        $recipients = collect([$query->assignedTo, $query->assignedBy])->filter(fn ($user) => $user && $user->id !== $actor->id);

        $this->send(
            $recipients,
            'Query Status Updated: ' . $query->query_no,
            $actor->name . ' changed query status from ' . $oldStatus . ' to ' . $newStatus . '.',
            route('sales.queries.show', $query),
            'query',
            'status_changed',
            in_array($newStatus, ['Confirmed', 'Converted'], true) ? 'high' : 'normal',
            true,
            ['query_id' => $query->id, 'query_no' => $query->query_no, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    public function financeEvent(Task $task, User $actor, string $title, string $message, string $action, string $priority = 'normal'): void
    {
        $financeUsers = User::role(['super-admin', 'finance'])->active()->get();
        $taskUsers = collect([$task->assigner, $task->assignee])->filter();

        $this->send(
            $financeUsers->merge($taskUsers),
            $title,
            $message,
            route('tasks.show', $task),
            'finance',
            $action,
            $priority,
            true,
            ['task_id' => $task->id, 'task_no' => $task->task_no, 'actor_id' => $actor->id]
        );
    }

    private function normalizeUsers(iterable|User|null $users): Collection
    {
        if ($users instanceof User) {
            return collect([$users]);
        }

        if ($users instanceof EloquentCollection || $users instanceof Collection) {
            return $users->filter()->unique('id')->values();
        }

        if (is_iterable($users)) {
            return collect($users)->filter()->unique('id')->values();
        }

        return collect();
    }

    private function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }
}
