<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerReceipt;
use App\Models\Task;
use App\Models\VendorPayment;
use App\Services\FinanceWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(protected FinanceWorkflowService $financeWorkflowService)
    {
    }

    public function index()
    {
        $visibleTasks = $this->visibleTaskQuery();

        $bookings = Booking::withSum(['receipts as received_total' => fn ($query) => $query->whereIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)], 'amount_received')
            ->withSum(['vendorPayments as paid_total' => fn ($query) => $query->whereIn('payment_status', VendorPayment::APPROVED_STATUSES)], 'amount_paid')
            ->whereHas('task', fn ($query) => $this->applyVisibleTaskScope($query))
            ->get();

        $stats = [
            'pending_receipts' => CustomerReceipt::whereNotIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)
                ->whereHas('task', fn ($query) => $this->applyVisibleTaskScope($query))
                ->count(),
            'pending_collections' => $bookings->where(fn ($booking) => (float) $booking->sale_amount > (float) ($booking->received_total ?? 0))->count(),
            'collection_due_today' => (clone $visibleTasks)->financeRelevant()->whereDate('due_date', today())->count(),
            'pending_vendor_payments' => $bookings->where(fn ($booking) => (float) $booking->purchase_amount > (float) ($booking->paid_total ?? 0))->count(),
            'outstanding_balances' => $bookings->sum(fn ($booking) => max(0, (float) $booking->sale_amount - (float) ($booking->received_total ?? 0))),
            'vendor_outstanding' => $bookings->sum(fn ($booking) => max(0, (float) $booking->purchase_amount - (float) ($booking->paid_total ?? 0))),
            'refund_pending' => (clone $visibleTasks)->where('financial_status', Task::FINANCIAL_REFUND_PENDING)->count(),
            'collection_progress' => $bookings->sum('sale_amount') > 0
                ? round(($bookings->sum('received_total') / $bookings->sum('sale_amount')) * 100, 2)
                : 0,
        ];

        $recentReceipts = CustomerReceipt::with(['task', 'receivedBy'])
            ->whereHas('task', fn ($query) => $this->applyVisibleTaskScope($query))
            ->latest()
            ->take(10)
            ->get();
        $recentPayments = VendorPayment::with(['task', 'enteredBy'])
            ->whereHas('task', fn ($query) => $this->applyVisibleTaskScope($query))
            ->latest()
            ->take(10)
            ->get();

        return view('finance.dashboard', compact('stats', 'recentReceipts', 'recentPayments'));
    }

    public function ledger(Request $request)
    {
        $receipts = CustomerReceipt::with(['task', 'receivedBy'])
            ->whereHas('task', fn ($query) => $this->applyVisibleTaskScope($query))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($sub) use ($search) {
                    $sub->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('client_company_name', 'like', "%{$search}%")
                        ->orWhereHas('task', function ($taskQuery) use ($search) {
                            $taskQuery->where('task_no', 'like', "%{$search}%")
                                ->orWhere('client_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('receipt_status', $request->status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('payment_date', '<=', $request->date_to))
            ->when($request->transaction_type === 'vendor_payment', fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('payment_date')
            ->get()
            ->map(fn (CustomerReceipt $receipt) => [
                'reference_no' => $receipt->reference_no,
                'task' => $receipt->task,
                'client' => $receipt->task?->client_name ?? $receipt->client_company_name ?? '-',
                'transaction_type' => 'Receipt',
                'payment_mode' => $receipt->effective_payment_mode,
                'account_no' => '-',
                'amount' => (float) $receipt->amount_received,
                'status' => $receipt->receipt_status,
                'entered_by' => $receipt->receivedBy?->name ?? '-',
                'date' => $receipt->payment_date,
                'created_at' => $receipt->created_at,
            ]);

        $payments = VendorPayment::with(['task', 'enteredBy'])
            ->whereHas('task', fn ($query) => $this->applyVisibleTaskScope($query))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($sub) use ($search) {
                    $sub->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('vendor_name', 'like', "%{$search}%")
                        ->orWhere('custom_vendor_name', 'like', "%{$search}%")
                        ->orWhereHas('task', function ($taskQuery) use ($search) {
                            $taskQuery->where('task_no', 'like', "%{$search}%")
                                ->orWhere('client_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('payment_status', $request->status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('payment_date', '<=', $request->date_to))
            ->when($request->transaction_type === 'receipt', fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('payment_date')
            ->get()
            ->map(fn (VendorPayment $payment) => [
                'reference_no' => $payment->reference_no,
                'task' => $payment->task,
                'client' => $payment->task?->client_name ?? '-',
                'transaction_type' => 'Vendor Payment',
                'payment_mode' => $payment->effective_payment_mode,
                'account_no' => $payment->vendor_account_no ?: '-',
                'amount' => (float) $payment->amount_paid,
                'status' => $payment->payment_status,
                'entered_by' => $payment->enteredBy?->name ?? '-',
                'date' => $payment->payment_date,
                'created_at' => $payment->created_at,
            ]);

        $transactions = $receipts
            ->concat($payments)
            ->sortByDesc(fn ($row) => $row['date']?->timestamp ?? $row['created_at']?->timestamp ?? 0)
            ->values();

        $statuses = collect([
            CustomerReceipt::STATUS_DRAFT,
            CustomerReceipt::STATUS_SUBMITTED,
            CustomerReceipt::STATUS_VERIFIED,
            CustomerReceipt::STATUS_APPROVED,
            CustomerReceipt::STATUS_REJECTED,
        ])->unique()->values();

        return view('finance.ledger', compact('transactions', 'statuses'));
    }

    public function queue()
    {
        $tasks = $this->visibleTaskQuery()
            ->with(['booking', 'assignee', 'taskStatus'])
            ->financeRelevant()
            ->latest()
            ->paginate(15);

        $financialSummaries = $tasks->getCollection()
            ->mapWithKeys(fn (Task $task) => [$task->id => $this->financeWorkflowService->summary($task)]);

        return view('finance.queue', compact('tasks', 'financialSummaries'));
    }

    private function visibleTaskQuery(): Builder
    {
        return $this->applyVisibleTaskScope(Task::query());
    }

    private function applyVisibleTaskScope(Builder $query): Builder
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super-admin', 'finance'])) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            return $query->where('department_id', $user->department_id);
        }

        return $query->where('assigned_to', $user->id);
    }
}
