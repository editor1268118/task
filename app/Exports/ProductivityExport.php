<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductivityExport implements FromCollection, WithHeadings
{
    protected $metrics;
    protected $userName;

    public function __construct(array $metrics, string $userName)
    {
        $this->metrics = $metrics;
        $this->userName = $userName;
    }

    public function collection()
    {
        return collect([
            [
                'Employee Name' => $this->userName,
                'Total Assigned' => $this->metrics['total'],
                'Completed' => $this->metrics['completed'],
                'Pending' => $this->metrics['pending'],
                'Overdue' => $this->metrics['overdue'],
                'Active' => $this->metrics['active'],
                'Cancelled' => $this->metrics['cancelled'],
                'Completion Rate %' => $this->metrics['completionRate'],
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Total Assigned',
            'Completed',
            'Pending',
            'Overdue',
            'Active',
            'Cancelled',
            'Completion Rate %',
        ];
    }
}
