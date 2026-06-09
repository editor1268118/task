<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('current_department')->default('Sales')->after('final_status');
            $table->timestamp('management_approved_at')->nullable()->after('finance_approved_by');
            $table->foreignId('management_approved_by')->nullable()->after('management_approved_at')
                ->constrained('users')->nullOnDelete();

            $table->index('current_department');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['current_department']);
            $table->dropForeign(['management_approved_by']);
            $table->dropColumn(['current_department', 'management_approved_at', 'management_approved_by']);
        });
    }
};
