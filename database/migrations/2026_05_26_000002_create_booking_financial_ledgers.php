<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->unique()->constrained('tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->decimal('sale_amount', 14, 2)->default(0);
            $table->decimal('purchase_amount', 14, 2)->default(0);
            $table->decimal('expected_profit', 14, 2)->default(0);
            $table->string('booking_type');
            $table->string('operational_status')->default('booking_in_process');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('customer_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->decimal('amount_received', 14, 2);
            $table->string('payment_mode');
            $table->date('payment_date');
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->string('receipt_status')->default('received');
            $table->timestamps();

            $table->index(['booking_id', 'receipt_status']);
        });

        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('vendor_name')->nullable();
            $table->decimal('amount_paid', 14, 2);
            $table->string('payment_mode');
            $table->date('payment_date');
            $table->text('remarks')->nullable();
            $table->string('payment_status')->default('paid');
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['booking_id', 'payment_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('customer_receipts');
        Schema::dropIfExists('bookings');
    }
};
