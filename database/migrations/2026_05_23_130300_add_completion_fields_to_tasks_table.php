<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add new columns
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_type_id')
                  ->nullable()
                  ->after('remarks')
                  ->constrained('task_types')
                  ->nullOnDelete();

            $table->string('client_name')->nullable()->after('task_type_id');
            $table->string('client_contact')->nullable()->after('client_name');
            $table->text('additional_info')->nullable()->after('client_contact');

            $table->timestamp('completion_started_at')->nullable()->after('additional_info');
            $table->timestamp('completed_at')->nullable()->after('completion_started_at');
        });

        // 2. Expand status ENUM to include new workflow states (Removed because status column is dropped)
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert status ENUM (Removed)

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_type_id']);
            $table->dropColumn([
                'task_type_id',
                'client_name',
                'client_contact',
                'additional_info',
                'completion_started_at',
                'completed_at',
            ]);
        });
    }
};
