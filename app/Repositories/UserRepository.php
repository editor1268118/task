<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new UserRepository instance.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmployeeId(string $employeeId)
    {
        return $this->model->where('employee_id', $employeeId)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByRole(string $roleName)
    {
        return $this->model->role($roleName)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByDepartment(int $departmentId)
    {
        return $this->model->where('department_id', $departmentId)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveUsers()
    {
        return $this->model->where('status', 'active')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function generateEmployeeId(): string
    {
        $lastUser = $this->model->withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastUser ? $lastUser->id + 1 : 1;

        return 'EMP' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
