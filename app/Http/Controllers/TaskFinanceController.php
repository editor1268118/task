<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerReceiptRequest;
use App\Http\Requests\StoreVendorPaymentRequest;
use App\Models\CustomerReceipt;
use App\Models\Task;
use App\Models\VendorPayment;
use App\Services\FinanceWorkflowService;
use Illuminate\Support\Facades\Auth;

class TaskFinanceController extends Controller
{
    public function __construct(protected FinanceWorkflowService $financeWorkflowService)
    {
    }

    public function storeReceipt(StoreCustomerReceiptRequest $request, Task $task)
    {
        if (!$task->booking) {
            return back()->with('error', 'Create the booking record through the operational form before entering receipts.');
        }

        $this->financeWorkflowService->recordReceipt($task, $request->validated(), Auth::user());

        return back()->with('success', 'Customer receipt recorded. Outstanding balance has been recalculated.');
    }

    public function storeVendorPayment(StoreVendorPaymentRequest $request, Task $task)
    {
        if (!$task->booking) {
            return back()->with('error', 'Create the booking record through the operational form before entering vendor payments.');
        }

        $this->financeWorkflowService->recordVendorPayment($task, $request->validated(), Auth::user());

        return back()->with('success', 'Vendor payment recorded. Outstanding balance has been recalculated.');
    }

    public function approveFinance(Task $task)
    {
        $this->authorize('approveFinance', $task);

        try {
            $this->financeWorkflowService->approveFinance($task, Auth::user());
        } catch (\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Finance approved. Task moved to Management for final closure.');
    }

    public function approveReceipt(CustomerReceipt $receipt)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        if ($receipt->task->final_status === Task::FINAL_CLOSED && !Auth::user()->hasRole('super-admin')) {
            return back()->with('error', 'This task is closed. Only Super Admin can unlock or change closed finance records.');
        }

        $this->financeWorkflowService->approveReceipt($receipt, Auth::user());

        return back()->with('success', 'Receipt approved and financial totals recalculated.');
    }

    public function approveVendorPayment(VendorPayment $payment)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        if ($payment->task->final_status === Task::FINAL_CLOSED && !Auth::user()->hasRole('super-admin')) {
            return back()->with('error', 'This task is closed. Only Super Admin can unlock or change closed finance records.');
        }

        $this->financeWorkflowService->approveVendorPayment($payment, Auth::user());

        return back()->with('success', 'Vendor payment approved and financial totals recalculated.');
    }

    public function approveManagementAndClose(Task $task)
    {
        $this->authorize('approveManagement', $task);

        try {
            $this->financeWorkflowService->approveManagementAndClose($task, Auth::user());
        } catch (\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Management approved. Task has been closed.');
    }
}
