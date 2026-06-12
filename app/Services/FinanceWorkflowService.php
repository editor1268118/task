<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CustomerReceipt;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\VendorPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceWorkflowService
{
    public function __construct(protected SystemNotificationService $systemNotificationService)
    {
    }

    public function syncBookingFromHotelForm(Task $task, array $data, User $user): Booking
    {
        $saleAmount = (float) ($data['sale_amount'] ?? 0);
        $purchaseAmount = (float) ($data['purchased_amount'] ?? 0);

        $booking = Booking::updateOrCreate(
            ['task_id' => $task->id],
            [
                'sale_amount' => $saleAmount,
                'purchase_amount' => $purchaseAmount,
                'expected_profit' => $saleAmount - $purchaseAmount,
                'booking_type' => $data['booking_type'],
                'customer_id' => $task->customer_id,
                'client_name' => $task->client_name,
                'booking_status' => $task->operational_status === Task::OPERATIONAL_COMPLETED
                    ? Task::OPERATIONAL_COMPLETED
                    : Task::OPERATIONAL_BOOKING_IN_PROCESS,
                'operational_status' => $task->operational_status === Task::OPERATIONAL_COMPLETED
                    ? Task::OPERATIONAL_COMPLETED
                    : Task::OPERATIONAL_BOOKING_IN_PROCESS,
                'created_by' => $task->booking?->created_by ?? $user->id,
            ]
        );

        activity()
            ->performedOn($booking)
            ->causedBy($user)
            ->withProperties([
                'task_id' => $task->id,
                'task_no' => $task->task_no,
                'sale_amount' => $booking->sale_amount,
                'purchase_amount' => $booking->purchase_amount,
                'expected_profit' => $booking->expected_profit,
            ])
            ->log($booking->wasRecentlyCreated ? 'Booking master record created' : 'Booking master record updated');

        return $booking;
    }

    public function summary(Task $task): array
    {
        $booking = $task->booking;
        $saleAmount = (float) ($booking?->sale_amount ?? 0);
        $purchaseAmount = (float) ($booking?->purchase_amount ?? 0);
        $received = (float) $task->customerReceipts()->whereIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)->sum('amount_received');
        $vendorPaid = (float) $task->vendorPayments()->whereIn('payment_status', VendorPayment::APPROVED_STATUSES)->sum('amount_paid');
        $pendingBalance = max(0, $saleAmount - $received);
        $vendorPending = max(0, $purchaseAmount - $vendorPaid);

        return [
            'sale_amount' => $saleAmount,
            'received' => $received,
            'pending_balance' => $pendingBalance,
            'collection_percentage' => $saleAmount > 0 ? min(100, round(($received / $saleAmount) * 100, 2)) : 0,
            'purchase_amount' => $purchaseAmount,
            'vendor_paid' => $vendorPaid,
            'vendor_pending' => $vendorPending,
            'expected_profit' => $saleAmount - $purchaseAmount,
        ];
    }

    public function recordReceipt(Task $task, array $data, User $user): CustomerReceipt
    {
        return DB::transaction(function () use ($task, $data, $user) {
            $booking = $task->booking()->firstOrFail();
            $receipt = $task->customerReceipts()->create([
                'booking_id' => $booking->id,
                'client_type' => $data['client_type'],
                'custom_client_type' => $data['custom_client_type'] ?? null,
                'client_company_name' => $data['client_company_name'],
                'contact_no' => $data['contact_no'],
                'amount_received' => $data['amount_received'],
                'payment_mode' => $data['payment_mode'],
                'custom_payment_mode' => $data['custom_payment_mode'] ?? null,
                'payment_date' => $data['payment_date'],
                'remarks' => $data['remarks'] ?? null,
                'received_by' => $user->id,
                'receipt_status' => $user->hasAnyRole(['super-admin', 'finance']) ? CustomerReceipt::STATUS_APPROVED : CustomerReceipt::STATUS_SUBMITTED,
                'approved_at' => $user->hasAnyRole(['super-admin', 'finance']) ? now() : null,
                'approved_by' => $user->hasAnyRole(['super-admin', 'finance']) ? $user->id : null,
            ]);

            activity()->performedOn($task)->causedBy($user)->withProperties([
                'receipt_id' => $receipt->id,
                'reference_no' => $receipt->reference_no,
                'amount_received' => $receipt->amount_received,
                'receipt_status' => $receipt->receipt_status,
            ])->log('Receipt ' . $receipt->reference_no . ' added');

            $this->refreshFinancialState($task, $user);
            $this->systemNotificationService->financeEvent(
                $task->fresh(['assigner', 'assignee']),
                $user,
                'Customer Receipt Recorded: ' . $task->task_no,
                'Receipt of Rs. ' . number_format((float) $receipt->amount_received, 2) . ' was recorded.',
                'receipt_recorded'
            );

            return $receipt;
        });
    }

    public function recordVendorPayment(Task $task, array $data, User $user): VendorPayment
    {
        return DB::transaction(function () use ($task, $data, $user) {
            $booking = $task->booking()->firstOrFail();
            $payment = $task->vendorPayments()->create([
                'booking_id' => $booking->id,
                'vendor_id' => $data['vendor_id'] ?? null,
                'vendor_name' => $data['vendor_name'] ?? null,
                'vendor_account_no' => $data['vendor_account_no'] ?? null,
                'custom_vendor_name' => $data['custom_vendor_name'] ?? null,
                'amount_paid' => $data['amount_paid'],
                'payment_mode' => $data['payment_mode'],
                'custom_payment_mode' => $data['custom_payment_mode'] ?? null,
                'payment_date' => $data['payment_date'],
                'remarks' => $data['remarks'] ?? null,
                'payment_status' => $user->hasAnyRole(['super-admin', 'finance']) ? VendorPayment::STATUS_APPROVED : VendorPayment::STATUS_SUBMITTED,
                'entered_by' => $user->id,
                'approved_at' => $user->hasAnyRole(['super-admin', 'finance']) ? now() : null,
                'approved_by' => $user->hasAnyRole(['super-admin', 'finance']) ? $user->id : null,
            ]);

            activity()->performedOn($task)->causedBy($user)->withProperties([
                'vendor_payment_id' => $payment->id,
                'reference_no' => $payment->reference_no,
                'amount_paid' => $payment->amount_paid,
                'payment_status' => $payment->payment_status,
            ])->log('Vendor Payment ' . $payment->reference_no . ' added');

            $this->refreshFinancialState($task, $user);
            $this->systemNotificationService->financeEvent(
                $task->fresh(['assigner', 'assignee']),
                $user,
                'Vendor Payment Recorded: ' . $task->task_no,
                'Vendor payment of Rs. ' . number_format((float) $payment->amount_paid, 2) . ' was recorded.',
                'vendor_payment_recorded'
            );

            return $payment;
        });
    }

    public function ledger(Task $task): Collection
    {
        $receipts = $task->customerReceipts()
            ->with(['receivedBy'])
            ->get()
            ->map(fn (CustomerReceipt $receipt) => [
                'id' => $receipt->id,
                'date' => $receipt->payment_date,
                'transaction_type' => 'Receipt',
                'reference_no' => $receipt->reference_no,
                'party' => $receipt->client_company_name ?: $receipt->effective_client_type,
                'payment_mode' => $receipt->effective_payment_mode,
                'account_no' => '-',
                'amount' => (float) $receipt->amount_received,
                'status' => $receipt->receipt_status,
                'entered_by' => $receipt->receivedBy?->name ?? '-',
                'created_at' => $receipt->created_at,
                'model' => $receipt,
            ]);

        $payments = $task->vendorPayments()
            ->with(['enteredBy'])
            ->get()
            ->map(fn (VendorPayment $payment) => [
                'id' => $payment->id,
                'date' => $payment->payment_date,
                'transaction_type' => 'Vendor Payment',
                'reference_no' => $payment->reference_no,
                'party' => $payment->effective_vendor_name,
                'payment_mode' => $payment->effective_payment_mode,
                'account_no' => $payment->vendor_account_no ?: '-',
                'amount' => (float) $payment->amount_paid,
                'status' => $payment->payment_status,
                'entered_by' => $payment->enteredBy?->name ?? '-',
                'created_at' => $payment->created_at,
                'model' => $payment,
            ]);

        return $receipts
            ->concat($payments)
            ->sortByDesc(fn ($row) => $row['date']?->timestamp ?? $row['created_at']?->timestamp ?? 0)
            ->values();
    }

    public function approveReceipt(CustomerReceipt $receipt, User $user): void
    {
        DB::transaction(function () use ($receipt, $user) {
            $receipt->update([
                'receipt_status' => CustomerReceipt::STATUS_APPROVED,
                'verified_at' => $receipt->verified_at ?? now(),
                'verified_by' => $receipt->verified_by ?? $user->id,
                'approved_at' => now(),
                'approved_by' => $user->id,
                'rejected_at' => null,
                'rejected_by' => null,
            ]);

            activity()->performedOn($receipt->task)->causedBy($user)->withProperties([
                'receipt_id' => $receipt->id,
                'reference_no' => $receipt->reference_no,
                'amount_received' => $receipt->amount_received,
            ])->log('Receipt ' . $receipt->reference_no . ' approved');

            $this->refreshFinancialState($receipt->task->fresh(), $user);
        });
    }

    public function approveVendorPayment(VendorPayment $payment, User $user): void
    {
        DB::transaction(function () use ($payment, $user) {
            $payment->update([
                'payment_status' => VendorPayment::STATUS_APPROVED,
                'verified_at' => $payment->verified_at ?? now(),
                'verified_by' => $payment->verified_by ?? $user->id,
                'approved_at' => now(),
                'approved_by' => $user->id,
                'rejected_at' => null,
                'rejected_by' => null,
            ]);

            activity()->performedOn($payment->task)->causedBy($user)->withProperties([
                'vendor_payment_id' => $payment->id,
                'reference_no' => $payment->reference_no,
                'amount_paid' => $payment->amount_paid,
            ])->log('Vendor Payment ' . $payment->reference_no . ' approved');

            $this->refreshFinancialState($payment->task->fresh(), $user);
        });
    }

    public function markOperationallyCompleted(Task $task, User $user): void
    {
        DB::transaction(function () use ($task, $user) {
            $status = TaskStatus::where('slug', Task::STATUS_OPERATIONALLY_COMPLETED)->first();
            $task->update([
                'operational_status' => Task::OPERATIONAL_COMPLETED,
                'task_status_id' => $status?->id ?? $task->task_status_id,
                'completion_percentage' => 100,
                'current_department' => Task::DEPARTMENT_FINANCE,
            ]);
            $task->booking?->update([
                'operational_status' => Task::OPERATIONAL_COMPLETED,
                'booking_status' => Task::OPERATIONAL_COMPLETED,
            ]);
            $this->refreshFinancialState($task->fresh(), $user);

            activity()->performedOn($task)->causedBy($user)->log('Operational work completed; financial settlement remains open');
            $this->systemNotificationService->financeEvent(
                $task->fresh(['assigner', 'assignee']),
                $user,
                'Operationally Completed: ' . $task->task_no,
                'Operational work is complete. Finance settlement can continue until all balances are cleared.',
                'operationally_completed',
                'high'
            );
        });
    }

    public function approveFinance(Task $task, User $user): void
    {
        $summary = $this->summary($task);

        if ($task->operational_status !== Task::OPERATIONAL_COMPLETED) {
            throw new \LogicException('Operational completion is required before finance can approve this task.');
        }
        if ($summary['pending_balance'] > 0) {
            throw new \LogicException('Customer balance is still pending.');
        }
        if ($summary['vendor_pending'] > 0) {
            throw new \LogicException('Vendor payment balance is still pending.');
        }

        DB::transaction(function () use ($task, $user) {
            $status = TaskStatus::where('slug', Task::STATUS_FINANCE_REVIEW_PENDING)->first();
            $task->update([
                'financial_status' => Task::FINANCIAL_FULLY_PAID,
                'final_status' => Task::FINAL_UNDER_REVIEW,
                'finance_approved_at' => now(),
                'finance_approved_by' => $user->id,
                'current_department' => Task::DEPARTMENT_MANAGEMENT,
                'task_status_id' => $status?->id ?? $task->task_status_id,
            ]);
            $task->booking?->update(['approved_by' => $user->id]);

            activity()->performedOn($task)->causedBy($user)->withProperties([
                'finance_approved_by' => $user->id,
            ])->log('Finance approved; task moved to management closure queue');
            $this->systemNotificationService->financeEvent(
                $task->fresh(['assigner', 'assignee']),
                $user,
                'Finance Approved: ' . $task->task_no,
                'Finance approved this task. It is now ready for management closure.',
                'finance_approved',
                'high'
            );
        });
    }

    public function approveManagementAndClose(Task $task, User $user): void
    {
        if (!$task->finance_approved_at) {
            throw new \LogicException('Finance approval is required before management can close this task.');
        }

        $summary = $this->summary($task);
        if ($task->operational_status !== Task::OPERATIONAL_COMPLETED || $summary['pending_balance'] > 0 || $summary['vendor_pending'] > 0) {
            throw new \LogicException('Task is not ready for management closure.');
        }

        DB::transaction(function () use ($task, $user) {
            $status = TaskStatus::where('slug', Task::STATUS_CLOSED)->first();
            $task->update([
                'final_status' => Task::FINAL_CLOSED,
                'management_approved_at' => now(),
                'management_approved_by' => $user->id,
                'current_department' => Task::DEPARTMENT_MANAGEMENT,
                'task_status_id' => $status?->id ?? $task->task_status_id,
                'completed_at' => now(),
            ]);

            activity()->performedOn($task)->causedBy($user)->withProperties([
                'final_status' => Task::FINAL_CLOSED,
                'management_approved_by' => $user->id,
            ])->log('Management approved and task closed');
            $this->systemNotificationService->financeEvent(
                $task->fresh(['assigner', 'assignee']),
                $user,
                'Task Closed: ' . $task->task_no,
                'Management approved and closed this task.',
                'task_closed',
                'high'
            );
        });
    }

    public function refreshFinancialState(Task $task, User $user): void
    {
        $summary = $this->summary($task);
        $oldStatus = $task->financial_status;

        if ($summary['pending_balance'] > 0) {
            $financialStatus = $summary['received'] > 0 ? Task::FINANCIAL_PARTIAL : Task::FINANCIAL_UNPAID;
            $finalStatus = Task::FINAL_UNDER_COLLECTION;
            $statusSlug = Task::STATUS_COLLECTION_PENDING;
            $department = Task::DEPARTMENT_FINANCE;
        } elseif ($summary['vendor_pending'] > 0) {
            $financialStatus = Task::FINANCIAL_VENDOR_PENDING;
            $finalStatus = Task::FINAL_ACTIVE;
            $statusSlug = Task::STATUS_VENDOR_PAYMENT_PENDING;
            $department = Task::DEPARTMENT_FINANCE;
        } else {
            $financialStatus = Task::FINANCIAL_FULLY_PAID;
            $finalStatus = Task::FINAL_UNDER_REVIEW;
            $statusSlug = Task::STATUS_FINANCE_REVIEW_PENDING;
            $department = $task->finance_approved_at ? Task::DEPARTMENT_MANAGEMENT : Task::DEPARTMENT_FINANCE;
        }

        if ($task->final_status === Task::FINAL_CLOSED) {
            return;
        }

        $status = TaskStatus::where('slug', $statusSlug)->first();
        $task->update([
            'financial_status' => $financialStatus,
            'final_status' => $finalStatus,
            'current_department' => $task->operational_status === Task::OPERATIONAL_COMPLETED ? $department : $task->current_department,
            'task_status_id' => $task->operational_status === Task::OPERATIONAL_COMPLETED
                ? ($status?->id ?? $task->task_status_id)
                : $task->task_status_id,
        ]);

        if ($oldStatus !== $financialStatus) {
            activity()->performedOn($task)->causedBy($user)->withProperties([
                'old_financial_status' => $oldStatus,
                'financial_status' => $financialStatus,
                'summary' => $summary,
            ])->log('Financial status changed');
        }
    }
}
