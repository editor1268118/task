<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->unique()->after('id');
            $table->timestamp('verified_at')->nullable()->after('receipt_status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('verified_by');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->after('rejected_by')->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable()->after('deleted_by');
            $table->softDeletes();
            $table->index(['task_id', 'reference_no']);
        });

        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->unique()->after('id');
            $table->timestamp('verified_at')->nullable()->after('payment_status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('verified_by');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->after('rejected_by')->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable()->after('deleted_by');
            $table->softDeletes();
            $table->index(['task_id', 'reference_no']);
        });

        DB::table('customer_receipts')->orderBy('id')->get(['id', 'receipt_status'])->each(function ($receipt) {
            DB::table('customer_receipts')
                ->where('id', $receipt->id)
                ->update([
                    'reference_no' => 'REC' . str_pad((string) $receipt->id, 6, '0', STR_PAD_LEFT),
                    'receipt_status' => $receipt->receipt_status === 'received' ? 'approved' : $receipt->receipt_status,
                    'approved_at' => now(),
                ]);
        });

        DB::table('vendor_payments')->orderBy('id')->get(['id', 'payment_status'])->each(function ($payment) {
            DB::table('vendor_payments')
                ->where('id', $payment->id)
                ->update([
                    'reference_no' => 'VPM' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                    'payment_status' => $payment->payment_status === 'paid' ? 'approved' : $payment->payment_status,
                    'approved_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->dropIndex(['task_id', 'reference_no']);
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropUnique(['reference_no']);
            $table->dropColumn([
                'reference_no',
                'verified_at',
                'verified_by',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'deleted_by',
                'delete_reason',
                'deleted_at',
            ]);
        });

        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->dropIndex(['task_id', 'reference_no']);
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropUnique(['reference_no']);
            $table->dropColumn([
                'reference_no',
                'verified_at',
                'verified_by',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'deleted_by',
                'delete_reason',
                'deleted_at',
            ]);
        });
    }
};
