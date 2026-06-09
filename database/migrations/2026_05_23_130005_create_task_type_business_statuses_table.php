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
        Schema::create('task_type_business_statuses', function (Blueprint $table) {
            $table->foreignId('task_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_status_id')->constrained('business_statuses')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            
            $table->primary(['task_type_id', 'business_status_id'], 'tt_bs_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_type_business_statuses');
    }
};
