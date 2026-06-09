<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    /**
     * Create a new TaskRepository instance.
     */
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksAssignedTo(int $userId, int $perPage = 15)
    {
        return $this->model
            ->assignedTo($userId)
            ->with(['assigner', 'department'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksAssignedBy(int $userId, int $perPage = 15)
    {
        return $this->model
            ->assignedBy($userId)
            ->with(['assignee', 'department'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksByStatus(string $status, int $perPage = 15)
    {
        return $this->model
            ->status($status)
            ->with(['assigner', 'assignee', 'department'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksByDepartment(int $departmentId, int $perPage = 15)
    {
        return $this->model
            ->inDepartment($departmentId)
            ->with(['assigner', 'assignee'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getOverdueTasks(int $perPage = 15)
    {
        return $this->model
            ->overdue()
            ->with(['assigner', 'assignee', 'department'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function filterTasks(array $filters, int $perPage = 15)
    {
        $query = $this->model->with(['assigner', 'assignee', 'department']);

        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->priority($filters['priority']);
        }

        if (!empty($filters['department_id'])) {
            $query->inDepartment($filters['department_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->assignedTo($filters['assigned_to']);
        }

        if (!empty($filters['assigned_by'])) {
            $query->assignedBy($filters['assigned_by']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('task_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTaskNumber(): string
    {
        return Task::generateTaskNumber();
    }
}
