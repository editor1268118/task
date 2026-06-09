<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->string('query_no')->unique();
            $table->date('query_date')->index();
            $table->string('service_type')->index();
            $table->string('service_type_other')->nullable();
            $table->string('client_name');
            $table->string('company_name')->nullable()->index();
            $table->string('mobile')->index();
            $table->string('alternate_mobile')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('destination')->nullable()->index();
            $table->date('travel_date')->nullable()->index();
            $table->string('travel_month')->nullable()->index();
            $table->unsignedInteger('number_of_pax')->nullable();
            $table->string('source')->index();
            $table->string('priority')->default('Medium')->index();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stage')->default('New Query')->index();
            $table->string('status')->default('Open')->index();
            $table->decimal('expected_sale_amount', 14, 2)->nullable();
            $table->date('last_followup_date')->nullable()->index();
            $table->date('next_followup_date')->nullable()->index();
            $table->string('lost_reason')->nullable();
            $table->text('latest_remark')->nullable();
            $table->foreignId('converted_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'stage']);
            $table->index(['assigned_to', 'next_followup_date']);
            $table->index(['created_at', 'status']);
        });

        Schema::create('query_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('query_id')->constrained('queries')->cascadeOnDelete();
            $table->date('followup_date')->index();
            $table->text('remarks');
            $table->date('next_followup_date')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('query_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('query_id')->constrained('queries')->cascadeOnDelete();
            $table->timestamp('activity_at')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->text('remarks')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_activities');
        Schema::dropIfExists('query_followups');
        Schema::dropIfExists('queries');
    }
};
