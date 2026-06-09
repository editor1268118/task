<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WorkloadExport implements FromCollection, WithHeadings
{
    protected $workloads;

    public function __construct($workloads)
    {
        $this->workloads = $workloads;
    }

    public function collection()
    {
        return $this->workloads->map(function($emp) {
            return [
                'Employee Name' => $emp->name,
                'Department' => $emp->department?->name ?? 'N/A',
                'Active Tasks' => $emp->active_tasks,
                'Pending Tasks' => $emp->pending_tasks,
                'Overdue Tasks' => $emp->overdue_tasks,
                'Est. Hours' => $emp->assigned_tasks_sum_estimated_hours ?? 0,
                'Status' => $emp->is_overloaded ? 'OVERLOADED' : 'Healthy',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Department',
            'Active Tasks',
            'Pending Tasks',
            'Overdue Tasks',
            'Estimated Hours',
            'Status',
        ];
    }
}
