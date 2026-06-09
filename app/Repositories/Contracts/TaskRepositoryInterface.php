<?php

namespace App\Repositories\Contracts;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get tasks assigned to a user.
     */
    public function getTasksAssignedTo(int $userId, int $perPage = 15);

    /**
     * Get tasks assigned by a user.
     */
    public function getTasksAssignedBy(int $userId, int $perPage = 15);

    /**
     * Get tasks by status.
     */
    public function getTasksByStatus(string $status, int $perPage = 15);

    /**
     * Get tasks by department.
     */
    public function getTasksByDepartment(int $departmentId, int $perPage = 15);

    /**
     * Get overdue tasks.
     */
    public function getOverdueTasks(int $perPage = 15);

    /**
     * Filter tasks by criteria.
     */
    public function filterTasks(array $filters, int $perPage = 15);

    /**
     * Generate next task number.
     */
    public function generateTaskNumber(): string;
}
