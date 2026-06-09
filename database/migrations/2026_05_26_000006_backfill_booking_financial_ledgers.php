<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bookings') || !Schema::hasTable('hotel_tour_forms')) {
            return;
        }

        $operationalStatusId = DB::table('task_statuses')->where('slug', 'operationally_completed')->value('id');
        foreach (DB::table('hotel_tour_forms')->whereNull('deleted_at')->get() as $form) {
            $sale = (float) ($form->sale_amount ?? 0);
            $purchase = (float) ($form->purchased_amount ?? 0);
            $operationalStatus = $form->status === 'submitted' ? 'operationally_completed' : 'booking_in_process';

            DB::table('bookings')->updateOrInsert(
                ['task_id' => $form->task_id],
                [
                    'customer_id' => null,
                    'sale_amount' => $sale,
                    'purchase_amount' => $purchase,
                    'expected_profit' => $sale - $purchase,
                    'booking_type' => $form->booking_type,
                    'operational_status' => $operationalStatus,
                    'created_by' => $form->entered_by,
                    'created_at' => $form->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );

            if ($form->status === 'submitted') {
                DB::table('tasks')->where('id', $form->task_id)->update([
                    'operational_status' => 'operationally_completed',
                    'task_status_id' => $operationalStatusId,
                ]);
            }
        }

        if (Schema::hasTable('receipt_forms')) {
            foreach (DB::table('receipt_forms')->where('status', 'submitted')->whereNull('deleted_at')->get() as $receipt) {
                $bookingId = DB::table('bookings')->where('task_id', $receipt->task_id)->value('id');
                if (!$bookingId) {
                    continue;
                }

                DB::table('customer_receipts')->insert([
                    'task_id' => $receipt->task_id,
                    'booking_id' => $bookingId,
                    'amount_received' => $receipt->amount_received,
                    'payment_mode' => $receipt->payment_mode === 'Other' ? ($receipt->custom_payment_mode ?: 'Other') : $receipt->payment_mode,
                    'payment_date' => $receipt->receipt_date,
                    'remarks' => $receipt->comments,
                    'received_by' => $receipt->entered_by,
                    'receipt_status' => 'received',
                    'created_at' => $receipt->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('payment_purchase_forms')) {
            foreach (DB::table('payment_purchase_forms')->where('status', 'submitted')->whereNull('deleted_at')->get() as $payment) {
                $bookingId = DB::table('bookings')->where('task_id', $payment->task_id)->value('id');
                if (!$bookingId) {
                    continue;
                }

                DB::table('vendor_payments')->insert([
                    'task_id' => $payment->task_id,
                    'booking_id' => $bookingId,
                    'vendor_name' => $payment->vendor_name === 'Other' ? ($payment->custom_vendor_name ?: 'Other') : $payment->vendor_name,
                    'amount_paid' => $payment->payable_amount,
                    'payment_mode' => $payment->payment_mode === 'Other' ? ($payment->custom_payment_mode ?: 'Other') : $payment->payment_mode,
                    'payment_date' => $payment->payment_date,
                    'remarks' => $payment->payment_comments,
                    'payment_status' => 'paid',
                    'entered_by' => $payment->entered_by,
                    'created_at' => $payment->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->refreshTaskFinancialStates();
    }

    protected function refreshTaskFinancialStates(): void
    {
        $statusIds = DB::table('task_statuses')
            ->whereIn('slug', ['collection_pending', 'vendor_payment_pending', 'finance_review_pending'])
            ->pluck('id', 'slug');

        foreach (DB::table('bookings')->get() as $booking) {
            $received = (float) DB::table('customer_receipts')->where('booking_id', $booking->id)->where('receipt_status', 'received')->sum('amount_received');
            $paid = (float) DB::table('vendor_payments')->where('booking_id', $booking->id)->where('payment_status', 'paid')->sum('amount_paid');
            $customerPending = max(0, (float) $booking->sale_amount - $received);
            $vendorPending = max(0, (float) $booking->purchase_amount - $paid);

            if ($customerPending > 0) {
                $financial = $received > 0 ? 'partial_payment' : 'unpaid';
                $final = 'under_collection';
                $statusId = $statusIds['collection_pending'] ?? null;
            } elseif ($vendorPending > 0) {
                $financial = 'vendor_pending';
                $final = 'active';
                $statusId = $statusIds['vendor_payment_pending'] ?? null;
            } else {
                $financial = 'fully_paid';
                $final = 'under_review';
                $statusId = $statusIds['finance_review_pending'] ?? null;
            }

            $updates = ['financial_status' => $financial, 'final_status' => $final];
            if ($booking->operational_status === 'operationally_completed' && $statusId) {
                $updates['task_status_id'] = $statusId;
            }

            DB::table('tasks')->where('id', $booking->task_id)->update($updates);
        }
    }

    public function down()
    {
        // Migrated ledger entries are financial records and are intentionally retained on rollback.
    }
};
