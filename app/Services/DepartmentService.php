<?php

namespace App\Services;

use App\Repositories\Contracts\DepartmentRepositoryInterface;

class DepartmentService
{
    /**
     * @var DepartmentRepositoryInterface
     */
    protected DepartmentRepositoryInterface $departmentRepository;

    /**
     * Create a new DepartmentService instance.
     */
    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * Get all departments (paginated).
     */
    public function getAllDepartments(int $perPage = 15)
    {
        return $this->departmentRepository->paginate($perPage);
    }

    /**
     * Get active departments (for dropdowns).
     */
    public function getActiveDepartments()
    {
        return $this->departmentRepository->getActiveDepartments();
    }

    /**
     * Create a new department.
     */
    public function createDepartment(array $data)
    {
        return $this->departmentRepository->create($data);
    }

    /**
     * Update a department.
     */
    public function updateDepartment(int $id, array $data): bool
    {
        return $this->departmentRepository->update($id, $data);
    }

    /**
     * Delete a department (soft delete).
     */
    public function deleteDepartment(int $id): bool
    {
        return $this->departmentRepository->delete($id);
    }

    /**
     * Find a department by ID.
     */
    public function findDepartment(int $id)
    {
        return $this->departmentRepository->findOrFail($id);
    }
}
