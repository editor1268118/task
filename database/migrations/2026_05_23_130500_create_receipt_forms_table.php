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
        Schema::create('receipt_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('client_type');
            $table->string('client_company_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->date('receipt_date');
            $table->string('payment_mode');
            $table->string('custom_payment_mode')->nullable()->comment('Used when payment_mode is Other');
            $table->decimal('amount_received', 12, 2);
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('receipt_forms');
    }
};
