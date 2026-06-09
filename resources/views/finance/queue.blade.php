@extends('layouts.admin')

@section('title', 'Finance Queue')
@section('page-header', 'Finance Queue')
@section('page-description', 'Tasks waiting on collections, vendor payments, refund handling, or finance review.')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold">Open Finance Work</h6>
            <small class="text-muted">Operational completion is shown here until settlement and approval are done.</small>
        </div>
        <a href="{{ route('finance.dashboard') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-chart-line me-1"></i> Dashboard
        </a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Task No</th>
                    <th>Client</th>
                    <th class="text-end">Sale Amount</th>
                    <th class="text-end">Received</th>
                    <th class="text-end">Pending</th>
                    <th class="text-end">Vendor Pending</th>
                    <th>Financial Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    @php($summary = $financialSummaries[$task->id])
                    <tr>
                        <td>
                            <a href="{{ route('tasks.show', $task) }}" class="fw-semibold">{{ $task->task_no }}</a>
                            <div class="small text-muted">{{ $task->current_department }}</div>
                        </td>
                        <td>{{ $task->booking?->client_name ?? $task->client_name ?? '-' }}</td>
                        <td class="text-end">INR {{ number_format($summary['sale_amount'], 2) }}</td>
                        <td class="text-end text-success">INR {{ number_format($summary['received'], 2) }}</td>
                        <td class="text-end {{ $summary['pending_balance'] > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                            INR {{ number_format($summary['pending_balance'], 2) }}
                        </td>
                        <td class="text-end {{ $summary['vendor_pending'] > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                            INR {{ number_format($summary['vendor_pending'], 2) }}
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ Str::headline($task->financial_status) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-primary">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No finance queue items right now.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
        <div class="card-footer bg-white">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
