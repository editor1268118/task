@extends('layouts.admin')

@section('title', 'Finance Dashboard')
@section('page-header', 'Finance Dashboard')
@section('page-description', 'Collections, vendor settlements, and closure readiness.')

@section('content')
<div class="row g-4 mb-4">
    @foreach([
        ['Pending Receipts', $stats['pending_receipts'], 'fa-receipt', 'warning'],
        ['Pending Collections', $stats['pending_collections'], 'fa-hand-holding-usd', 'warning'],
        ['Collection Due Today', $stats['collection_due_today'], 'fa-calendar-day', 'info'],
        ['Pending Vendor Payments', $stats['pending_vendor_payments'], 'fa-file-invoice-dollar', 'danger'],
        ['Outstanding Balances', 'INR '.number_format($stats['outstanding_balances'], 2), 'fa-wallet', 'warning'],
        ['Vendor Outstanding', 'INR '.number_format($stats['vendor_outstanding'], 2), 'fa-money-check-alt', 'danger'],
        ['Refund Pending', $stats['refund_pending'], 'fa-undo', 'info'],
        ['Collection Progress', $stats['collection_progress'].'%', 'fa-chart-line', 'success'],
    ] as [$label, $value, $icon, $tone])
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">{{ $label }}</div>
                        <h4 class="mb-0 mt-2">{{ $value }}</h4>
                    </div>
                    <i class="fas {{ $icon }} fa-2x text-{{ $tone }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Recent Receipts</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Task</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse($recentReceipts as $receipt)
                            <tr>
                                <td><a href="{{ route('tasks.show', $receipt->task) }}">{{ $receipt->task->task_no }}</a></td>
                                <td>{{ $receipt->payment_date->format('d M Y') }}</td>
                                <td class="text-end">INR {{ number_format($receipt->amount_received, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center">No receipts recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Recent Vendor Payments</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Task</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                            <tr>
                                <td><a href="{{ route('tasks.show', $payment->task) }}">{{ $payment->task->task_no }}</a></td>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="text-end">INR {{ number_format($payment->amount_paid, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
