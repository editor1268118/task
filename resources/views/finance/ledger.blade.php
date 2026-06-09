@extends('layouts.admin')

@section('title', 'Transactional Ledger')
@section('page-header', 'Transactional Ledger')
@section('page-description', 'Master ledger for all task receipts and vendor payments.')

@push('styles')
<style>
    #mainContent,
    #mainContent .page-content {
        min-width: 0;
        overflow-x: hidden;
    }

    .finance-ledger-card {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .finance-ledger-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        max-height: calc(100vh - 285px);
        overflow-x: auto;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-color: #94a3b8 #e2e8f0;
        scrollbar-width: thin;
    }

    .finance-ledger-scroll::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .finance-ledger-scroll::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 999px;
    }

    .finance-ledger-scroll::-webkit-scrollbar-thumb {
        background: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 999px;
    }

    .finance-ledger-table {
        width: max-content;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .finance-ledger-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #eef2ff;
        color: #334155;
        white-space: nowrap;
        font-size: .72rem;
        padding: .55rem .7rem;
        box-shadow: inset 0 -1px 0 var(--border-color);
    }

    .finance-ledger-table tbody td {
        background: #fff;
        white-space: nowrap;
        font-size: .8rem;
        padding: .5rem .7rem;
    }

    .finance-ledger-table tbody tr:hover td {
        background: #f8fafc;
    }

    .finance-ledger-ref-cell {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 140px;
        background: #fff;
        box-shadow: 1px 0 0 var(--border-color);
    }

    .finance-ledger-table thead .finance-ledger-ref-cell {
        z-index: 6;
        background: #eef2ff;
    }

    .finance-ledger-truncate {
        display: inline-block;
        max-width: 210px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
    }

    .finance-ledger-filters {
        display: grid;
        grid-template-columns: minmax(220px, 1.3fr) minmax(150px, .8fr) minmax(130px, .7fr) minmax(130px, .7fr) minmax(130px, .7fr) minmax(145px, auto);
        gap: .5rem;
        align-items: end;
        width: 100%;
        max-width: 100%;
    }

    .finance-ledger-filters .form-label {
        margin-bottom: .2rem;
        color: var(--gray-500);
        font-size: .64rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .finance-ledger-filter-actions {
        display: flex;
        gap: .35rem;
        min-width: 0;
    }

    .finance-ledger-filter-actions .btn {
        flex: 1 1 0;
        min-width: 0;
        padding-left: .45rem;
        padding-right: .45rem;
    }

    @media (max-width: 768px) {
        .finance-ledger-scroll {
            max-height: none;
        }

        .finance-ledger-table {
            min-width: 980px;
        }

        .finance-ledger-filters {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm finance-ledger-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold">Unified Finance Ledger</h6>
            <small class="text-muted">Every financial transaction remains linked to its task number.</small>
        </div>
        <a href="{{ route('finance.queue') }}" class="btn btn-sm btn-outline-primary">Outstanding Queue</a>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('finance.ledger') }}" class="finance-ledger-filters">
            <div>
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Reference, task no, client, vendor">
            </div>
            <div>
                <label class="form-label small text-muted">Transaction Type</label>
                <select name="transaction_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="receipt" @selected(request('transaction_type') === 'receipt')>Receipt</option>
                    <option value="vendor_payment" @selected(request('transaction_type') === 'vendor_payment')>Vendor Payment</option>
                </select>
            </div>
            <div>
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="form-label small text-muted">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="finance-ledger-filter-actions">
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                <a href="{{ route('finance.ledger') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="finance-ledger-scroll">
        <table class="table table-sm table-hover align-middle mb-0 finance-ledger-table">
            <thead class="table-light">
                <tr>
                    <th class="finance-ledger-ref-cell">Reference No</th>
                    <th>Task No</th>
                    <th>Client</th>
                    <th>Transaction Type</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th>Entered By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="fw-semibold finance-ledger-ref-cell">{{ $transaction['reference_no'] }}</td>
                        <td>
                            @if($transaction['task'])
                                <a href="{{ route('tasks.show', $transaction['task']) }}">{{ $transaction['task']->task_no }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td><span class="finance-ledger-truncate" title="{{ $transaction['client'] }}">{{ $transaction['client'] }}</span></td>
                        <td>{{ $transaction['transaction_type'] }}</td>
                        <td class="text-end">INR {{ number_format($transaction['amount'], 2) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ Str::headline($transaction['status']) }}</span></td>
                        <td>{{ $transaction['entered_by'] }}</td>
                        <td>{{ $transaction['date']?->format('d M Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No finance transactions found for the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
