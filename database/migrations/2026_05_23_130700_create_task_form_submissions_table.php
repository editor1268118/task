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
        Schema::create('task_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('completion_form_id')->constrained('completion_forms')->cascadeOnDelete();
            $table->string('form_type')->comment('Morph type — Eloquent model class');
            $table->unsignedBigInteger('form_id')->nullable()->comment('Morph id — form record PK');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['pending', 'draft', 'submitted'])->default('pending');
            $table->timestamps();

            $table->unique(['task_id', 'completion_form_id'], 'tfs_task_form_unique');
            $table->index(['form_type', 'form_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_form_submissions');
    }
};
