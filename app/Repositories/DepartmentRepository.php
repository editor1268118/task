<?php

namespace App\Repositories;

use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    /**
     * Create a new DepartmentRepository instance.
     */
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveDepartments()
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }
}
