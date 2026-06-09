@extends('layouts.admin')

@section('title', 'Management Dashboard')
@section('page-header', 'Management Dashboard')
@section('page-description', 'Closure readiness, pending settlements, and final approval queue.')

@section('content')
<div class="row g-4 mb-4">
    @foreach([
        ['Active Tasks', $stats['active_tasks'], 'fa-briefcase', 'primary'],
        ['Operationally Completed', $stats['operationally_completed_tasks'], 'fa-check-double', 'info'],
        ['Collection Pending', $stats['collection_pending_tasks'], 'fa-wallet', 'warning'],
        ['Vendor Pending', $stats['vendor_pending_tasks'], 'fa-file-invoice-dollar', 'danger'],
        ['Management Review', $stats['management_review_tasks'], 'fa-user-check', 'purple'],
        ['Fully Closed', $stats['fully_closed_tasks'], 'fa-lock', 'success'],
        ['Total Sale Amount', 'INR '.number_format($stats['revenue'], 2), 'fa-chart-line', 'primary'],
        ['Expected Profit', 'INR '.number_format($stats['expected_profit'], 2), 'fa-coins', 'success'],
    ] as [$label, $value, $icon, $tone])
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">{{ $label }}</div>
                        <h4 class="mb-0 mt-2">{{ $value }}</h4>
                    </div>
                    <i class="fas {{ $icon }} fa-2x {{ $tone === 'purple' ? '' : 'text-'.$tone }}" style="{{ $tone === 'purple' ? 'color:#6f42c1;' : '' }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">Final Closure Queue</h6>
        <small class="text-muted">Finance-approved tasks waiting for management closure.</small>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Task</th>
                    <th>Client</th>
                    <th>Booking Status</th>
                    <th>Finance Approved By</th>
                    <th>Finance Approved At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviewTasks as $task)
                    <tr>
                        <td><a href="{{ route('tasks.show', $task) }}" class="fw-semibold">{{ $task->task_no }}</a></td>
                        <td>{{ $task->booking?->client_name ?? $task->client_name ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ Str::headline($task->booking?->booking_status ?? $task->operational_status) }}</span></td>
                        <td>{{ $task->financeApprover?->name ?? '-' }}</td>
                        <td>{{ $task->finance_approved_at?->format('d M Y h:i A') ?? '-' }}</td>
                        <td class="text-end"><a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-primary">Review</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No tasks are waiting for management closure.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
