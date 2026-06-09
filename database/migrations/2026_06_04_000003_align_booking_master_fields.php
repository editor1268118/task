<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('task_id');
            $table->string('booking_status')->default('booking_in_process')->after('booking_type');
            $table->index('booking_status');
        });

        DB::table('bookings')
            ->join('tasks', 'tasks.id', '=', 'bookings.task_id')
            ->update([
                'bookings.client_name' => DB::raw('tasks.client_name'),
                'bookings.booking_status' => DB::raw('bookings.operational_status'),
            ]);
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['booking_status']);
            $table->dropColumn(['client_name', 'booking_status']);
        });
    }
};
