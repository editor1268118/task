<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('operational_status')->default('pending')->after('completed_at');
            $table->string('financial_status')->default('unpaid')->after('operational_status');
            $table->string('final_status')->default('active')->after('financial_status');
            $table->timestamp('finance_approved_at')->nullable()->after('final_status');
            $table->foreignId('finance_approved_by')->nullable()->after('finance_approved_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['operational_status', 'financial_status', 'final_status'], 'tasks_lifecycle_status_idx');
        });

        foreach ([
            ['name' => 'Operationally Completed', 'slug' => 'operationally_completed', 'color' => '#0dcaf0'],
            ['name' => 'Collection Pending', 'slug' => 'collection_pending', 'color' => '#fd7e14'],
            ['name' => 'Vendor Payment Pending', 'slug' => 'vendor_payment_pending', 'color' => '#ffc107'],
            ['name' => 'Finance Review Pending', 'slug' => 'finance_review_pending', 'color' => '#6f42c1'],
            ['name' => 'Closed', 'slug' => 'closed', 'color' => '#198754'],
        ] as $status) {
            DB::table('task_statuses')->updateOrInsert(
                ['slug' => $status['slug']],
                array_merge($status, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_lifecycle_status_idx');
            $table->dropForeign(['finance_approved_by']);
            $table->dropColumn([
                'operational_status',
                'financial_status',
                'final_status',
                'finance_approved_at',
                'finance_approved_by',
            ]);
        });
    }
};
