<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('customer_type')->default('B2C');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile')->nullable()->index();
            $table->string('alternate_mobile')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('Active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('task_type_id')
                ->constrained('customers')->nullOnDelete();
            $table->index('customer_id');
        });

        Schema::create('customer_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('interaction_type');
            $table->dateTime('interaction_date');
            $table->text('notes')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'interaction_date']);
            $table->index(['task_id', 'interaction_date']);
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->date('followup_date')->index();
            $table->string('priority')->default('Medium')->index();
            $table->string('status')->default('Pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['assigned_to', 'status', 'followup_date']);
        });

        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('document_type');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $this->backfillCustomersFromTasks();
    }

    public function down()
    {
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('customer_interactions');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::dropIfExists('customers');
    }

    private function backfillCustomersFromTasks(): void
    {
        $counter = 1;

        foreach (DB::table('tasks')->whereNotNull('client_name')->orderBy('id')->get() as $task) {
            $mobile = $task->client_contact ?: null;
            $existingQuery = DB::table('customers')->where('company_name', $task->client_name);

            if ($mobile) {
                $existingQuery->orWhere('mobile', $mobile);
            }

            $customer = $existingQuery->first();

            if (!$customer) {
                while (DB::table('customers')->where('customer_code', 'CUS' . str_pad($counter, 6, '0', STR_PAD_LEFT))->exists()) {
                    $counter++;
                }

                $customerId = DB::table('customers')->insertGetId([
                    'customer_code' => 'CUS' . str_pad($counter, 6, '0', STR_PAD_LEFT),
                    'customer_type' => 'B2C',
                    'company_name' => $task->client_name,
                    'contact_person' => $task->client_name,
                    'mobile' => $mobile,
                    'status' => 'Active',
                    'created_by' => $task->assigned_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $counter++;
            } else {
                $customerId = $customer->id;
            }

            DB::table('tasks')->where('id', $task->id)->update(['customer_id' => $customerId]);
            DB::table('bookings')->where('task_id', $task->id)->update(['customer_id' => $customerId]);
        }
    }
};
