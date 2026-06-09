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
        Schema::create('task_type_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_type_id')->constrained('task_types')->cascadeOnDelete();
            $table->foreignId('completion_form_id')->constrained('completion_forms')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['task_type_id', 'completion_form_id'], 'ttf_type_form_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_type_forms');
    }
};
