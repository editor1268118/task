<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_status_id')->nullable()->constrained('task_statuses')->nullOnDelete();
            $table->foreignId('business_status_id')->nullable()->constrained('business_statuses')->nullOnDelete();
        });

        // Run Seeder to populate statuses first
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TaskStatusSeeder']);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'BusinessStatusSeeder']);

        // Data migration
        $tasks = \Illuminate\Support\Facades\DB::table('tasks')->get();
        foreach ($tasks as $task) {
            $taskStatus = \Illuminate\Support\Facades\DB::table('task_statuses')
                ->where('slug', $task->status)
                ->first();

            if ($taskStatus) {
                \Illuminate\Support\Facades\DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['task_status_id' => $taskStatus->id]);
            }
        }

        // Drop old column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('priority');
            $table->dropForeign(['task_status_id']);
            $table->dropForeign(['business_status_id']);
            $table->dropColumn(['task_status_id', 'business_status_id']);
        });
    }
};
