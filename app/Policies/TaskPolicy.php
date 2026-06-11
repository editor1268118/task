<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true; // Controller handles filtering logic
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('finance')) {
            return $task->isFinanceRelevant();
        }

        if ($user->hasRole('manager')) {
            // Manager can view tasks in their department
            return $user->department_id === $task->department_id;
        }

        if ($user->hasRole('employee')) {
            // Employee can only view tasks assigned to them
            return $user->id === $task->assigned_to;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasRole('super-admin') || $user->can('create-tasks');
    }

    /**
     * Determine whether the user can update the model (Full edit: title, description, assignment).
     */
    public function update(User $user, Task $task)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->department_id === $task->department_id;
        }

        if ($user->hasRole('employee')) {
            return $user->id === $task->assigned_to;
        }

        return false;
    }

    /**
     * Determine whether the user can update the status of the task.
     */
    public function updateStatus(User $user, Task $task)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('finance')) {
            return $task->isFinanceRelevant();
        }

        if ($user->hasRole('manager')) {
            return $user->department_id === $task->department_id;
        }

        if ($user->hasRole('employee')) {
            return $user->id === $task->assigned_to;
        }

        return false;
    }

    /**
     * Determine whether the user can add comments to the task.
     */
    public function comment(User $user, Task $task)
    {
        return $this->view($user, $task);
    }

    /**
     * Determine whether the user can upload attachments to the task.
     */
    public function attach(User $user, Task $task)
    {
        return $this->updateStatus($user, $task); // Same rules as status update
    }

    /**
     * Determine whether the user can start the completion workflow.
     */
    public function startCompletion(User $user, Task $task)
    {
        return $this->updateStatus($user, $task);
    }

    /**
     * Determine whether the user can force-complete without workflow.
     */
    public function forceComplete(User $user, Task $task)
    {
        return $user->hasRole('super-admin');
    }

    public function approveFinance(User $user, Task $task)
    {
        return $task->final_status !== Task::FINAL_CLOSED
            && !$task->finance_approved_at
            && $task->isFinanceRelevant()
            && $user->hasRole('super-admin');
    }

    public function approveManagement(User $user, Task $task)
    {
        return $task->final_status !== Task::FINAL_CLOSED
            && $task->finance_approved_at
            && $user->hasAnyRole(['super-admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Managers and Employees cannot delete tasks
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task)
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task)
    {
        return $user->hasRole('super-admin');
    }
}
