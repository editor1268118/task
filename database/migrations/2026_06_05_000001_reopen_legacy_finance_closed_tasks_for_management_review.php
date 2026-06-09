<?php

use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $financeReviewStatusId = TaskStatus::where('slug', Task::STATUS_FINANCE_REVIEW_PENDING)->value('id');

        DB::table('tasks')
            ->where('operational_status', Task::OPERATIONAL_COMPLETED)
            ->where('final_status', Task::FINAL_CLOSED)
            ->whereNotNull('finance_approved_at')
            ->whereNull('management_approved_at')
            ->update([
                'final_status' => Task::FINAL_UNDER_REVIEW,
                'current_department' => Task::DEPARTMENT_MANAGEMENT,
                'task_status_id' => $financeReviewStatusId ?: DB::raw('task_status_id'),
                'completed_at' => null,
                'updated_at' => now(),
            ]);

        DB::table('tasks')
            ->where('operational_status', Task::OPERATIONAL_COMPLETED)
            ->where('final_status', '!=', Task::FINAL_CLOSED)
            ->whereNull('finance_approved_at')
            ->update([
                'current_department' => Task::DEPARTMENT_FINANCE,
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        // Data correction only. Do not re-close tasks without management approval.
    }
};
