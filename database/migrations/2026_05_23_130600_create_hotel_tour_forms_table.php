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
        Schema::create('hotel_tour_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->date('booking_date');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('client_type')->nullable();
            $table->string('billed_to')->nullable();
            $table->string('booking_type');
            $table->string('service_type');
            $table->string('trip_type')->nullable();
            $table->unsignedSmallInteger('no_of_pax')->nullable();
            $table->string('pax_name')->nullable();
            $table->unsignedSmallInteger('no_of_rooms')->nullable();
            $table->string('confirmation_no')->nullable();
            $table->string('hotel_room_type')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->decimal('sale_amount', 12, 2)->nullable();
            $table->decimal('purchased_amount', 12, 2)->nullable();
            $table->decimal('sale_gst', 12, 2)->nullable();
            $table->decimal('gst_expected', 12, 2)->nullable();
            $table->decimal('tcs_calculation', 12, 2)->nullable();
            $table->string('vendor_name')->nullable();
            $table->decimal('total_vendor_payment', 12, 2)->nullable();
            $table->decimal('vendor_tds', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('payment_received', 12, 2)->nullable();
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->softDeletes();
            $table->timestamps();

            $table->index('task_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotel_tour_forms');
    }
};
