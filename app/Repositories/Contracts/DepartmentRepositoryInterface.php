<?php

namespace App\Repositories\Contracts;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get active departments.
     */
    public function getActiveDepartments();

    /**
     * Find department by code.
     */
    public function findByCode(string $code);
}
