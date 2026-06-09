<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by email.
     */
    public function findByEmail(string $email);

    /**
     * Find a user by employee ID.
     */
    public function findByEmployeeId(string $employeeId);

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName);

    /**
     * Get users by department.
     */
    public function getUsersByDepartment(int $departmentId);

    /**
     * Get active users.
     */
    public function getActiveUsers();

    /**
     * Generate next employee ID.
     */
    public function generateEmployeeId(): string;
}
