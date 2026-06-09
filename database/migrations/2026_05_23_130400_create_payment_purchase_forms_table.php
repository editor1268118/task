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
        Schema::create('payment_purchase_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('vendor_name');
            $table->string('vendor_account_no')->nullable();
            $table->string('custom_vendor_name')->nullable()->comment('Used when vendor_name is Other');
            $table->decimal('payable_amount', 12, 2);
            $table->string('payment_mode');
            $table->string('custom_payment_mode')->nullable()->comment('Used when payment_mode is Other');
            $table->date('payment_date');
            $table->text('payment_comments')->nullable();
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
        Schema::dropIfExists('payment_purchase_forms');
    }
};
