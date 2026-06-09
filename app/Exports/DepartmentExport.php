<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartmentExport implements FromCollection, WithHeadings
{
    protected $departments;

    public function __construct($departments)
    {
        $this->departments = $departments;
    }

    public function collection()
    {
        return $this->departments->map(function($dept) {
            $rate = $dept->total_tasks > 0 ? round(($dept->completed_tasks / $dept->total_tasks) * 100, 1) : 0;
            return [
                'Department' => $dept->name,
                'Total Tasks' => $dept->total_tasks,
                'Completed' => $dept->completed_tasks,
                'Overdue' => $dept->overdue_tasks,
                'Completion Rate %' => $rate,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Department',
            'Total Tasks',
            'Completed',
            'Overdue',
            'Completion Rate %',
        ];
    }
}
