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
            $table->index('department_id', 'tasks_department_id_analytics_idx');
            $table->index('status', 'tasks_status_analytics_idx');
            $table->index('priority', 'tasks_priority_analytics_idx');
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
            $table->dropIndex('tasks_department_id_analytics_idx');
            $table->dropIndex('tasks_status_analytics_idx');
            $table->dropIndex('tasks_priority_analytics_idx');
        });
    }
};
