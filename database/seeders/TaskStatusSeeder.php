<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['name' => 'Pending', 'slug' => 'pending', 'color' => '#ffc107'],
            ['name' => 'Assigned', 'slug' => 'assigned', 'color' => '#17a2b8'],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => '#0d6efd'],
            ['name' => 'Completion Pending', 'slug' => 'completion_pending', 'color' => '#6f42c1'],
            ['name' => 'Forms Submitted', 'slug' => 'forms_submitted', 'color' => '#17a2b8'],
            ['name' => 'Operationally Completed', 'slug' => 'operationally_completed', 'color' => '#0dcaf0'],
            ['name' => 'Collection Pending', 'slug' => 'collection_pending', 'color' => '#fd7e14'],
            ['name' => 'Vendor Payment Pending', 'slug' => 'vendor_payment_pending', 'color' => '#ffc107'],
            ['name' => 'Finance Review Pending', 'slug' => 'finance_review_pending', 'color' => '#6f42c1'],
            ['name' => 'Closed', 'slug' => 'closed', 'color' => '#198754'],
            ['name' => 'Under Review', 'slug' => 'under_review', 'color' => '#fd7e14'],
            ['name' => 'Approved', 'slug' => 'approved', 'color' => '#20c997'],
            ['name' => 'Completed', 'slug' => 'completed', 'color' => '#198754'],
            ['name' => 'Cancelled', 'slug' => 'cancelled', 'color' => '#dc3545'],
            ['name' => 'Rejected', 'slug' => 'rejected', 'color' => '#dc3545'],
            ['name' => 'Escalated', 'slug' => 'escalated', 'color' => '#e83e8c'],
            ['name' => 'On Hold', 'slug' => 'on_hold', 'color' => '#6c757d'],
        ];

        foreach ($statuses as $status) {
            \App\Models\TaskStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}
