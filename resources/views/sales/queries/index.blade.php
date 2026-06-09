@extends('layouts.admin')

@section('title', 'Query Register')
@section('page-header', 'Query Register')
@section('page-description', 'Google Sheet replacement for pre-task client queries.')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('sales.queries.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> New Query</a>
    <a href="{{ route('sales.queries.export', ['format' => 'xlsx'] + request()->query()) }}" class="btn btn-sm btn-success">XLSX</a>
    <a href="{{ route('sales.queries.export', ['format' => 'csv'] + request()->query()) }}" class="btn btn-sm btn-outline-success">CSV</a>
    <a href="{{ route('sales.queries.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-dark">Print</a>
</div>
@endsection

@push('styles')
<style>
    #mainContent,
    #mainContent .page-content {
        min-width: 0;
        overflow-x: hidden;
    }

    .query-filter-card .card-body {
        padding: .75rem;
    }

    .query-filter-card .form-label {
        margin-bottom: .2rem;
        color: var(--gray-500);
        font-size: .64rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .query-table-card {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .query-register-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        max-height: calc(100vh - 260px);
        overflow-x: auto;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-color: #94a3b8 #e2e8f0;
        scrollbar-width: thin;
    }

    .query-register-scroll::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .query-register-scroll::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 999px;
    }

    .query-register-scroll::-webkit-scrollbar-thumb {
        background: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 999px;
    }

    .query-register-table {
        width: max-content;
        min-width: 1450px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .query-register-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #eef2ff;
        color: #334155;
        white-space: nowrap;
        font-size: .72rem;
        padding: .5rem .65rem;
        box-shadow: inset 0 -1px 0 var(--border-color);
    }

    .query-register-table tbody td {
        background: #fff;
        white-space: nowrap;
        font-size: .78rem;
        padding: .45rem .65rem;
    }

    .query-register-table tbody tr:hover td {
        background: #f8fafc;
    }

    .query-no-cell {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 118px;
        background: #fff;
        box-shadow: 1px 0 0 var(--border-color);
    }

    .query-register-table thead .query-no-cell {
        z-index: 6;
        background: #eef2ff;
    }

    .query-cell-truncate {
        display: inline-block;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
    }

    .query-money {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        color: #334155;
    }

    .age-orange { background: #fd7e14; color: #fff; }

    @media (max-width: 991.98px) {
        .query-register-scroll {
            max-height: 68vh;
        }

        .query-register-table {
            min-width: 1250px;
        }

        .query-no-cell {
            position: static;
            box-shadow: none;
        }
    }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm mb-3 query-filter-card">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Search</label><input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Query, client, company, mobile, destination"></div>
            <div class="col-md-2"><label class="form-label small">Query No</label><input name="query_no" value="{{ request('query_no') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Service</label><select name="service_type" class="form-select form-select-sm"><option value="">All</option>@foreach($serviceTypes as $type)<option value="{{ $type }}" {{ request('service_type') === $type ? 'selected' : '' }}>{{ $type }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Assigned To</label><select name="assigned_to" class="form-select form-select-sm"><option value="">All</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label small">Priority</label><select name="priority" class="form-select form-select-sm"><option value="">All</option>@foreach($priorities as $priority)<option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ $priority }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label small">Stage</label><select name="stage" class="form-select form-select-sm"><option value="">All</option>@foreach($stages as $stage)<option value="{{ $stage }}" {{ request('stage') === $stage ? 'selected' : '' }}>{{ $stage }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label small">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option>@foreach($statuses as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Client</label><input name="client_name" value="{{ request('client_name') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Company</label><input name="company_name" value="{{ request('company_name') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Mobile</label><input name="mobile" value="{{ request('mobile') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Source</label><select name="source" class="form-select form-select-sm"><option value="">All</option>@foreach($sources as $source)<option value="{{ $source }}" {{ request('source') === $source ? 'selected' : '' }}>{{ $source }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Destination</label><input name="destination" value="{{ request('destination') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Travel Month</label><input type="month" name="travel_month" value="{{ request('travel_month') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Date From</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Date To</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Apply Filters</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm query-table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>Query Register</strong>
            <div class="text-muted small">One row per query. Scroll inside the table to view all Google Sheet columns.</div>
        </div>
        <span class="badge bg-light text-dark border">{{ $queries->total() }} Records</span>
    </div>
    <div class="query-register-scroll">
        <table class="table table-sm table-hover align-middle mb-0 query-register-table">
            <thead class="table-light">
                <tr>
                    <th class="query-no-cell">Query No</th><th>Query Date</th><th>Query Details</th><th>Service Type</th><th>Client Name</th><th>Company</th><th>Mobile</th><th>Destination</th><th>Travel Date</th><th>Pax</th><th>Source</th><th>Assigned By</th><th>Assigned To</th><th>Priority</th><th>Stage</th><th>Status</th><th>Expected Sale</th><th>Last Follow-Up</th><th>Next Follow-Up</th><th>Latest Remark</th><th>Age</th><th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($queries as $query)
                    <tr>
                        <td class="query-no-cell"><a href="{{ route('sales.queries.show', $query) }}" class="fw-bold">{{ $query->query_no }}</a></td>
                        <td>{{ $query->query_date?->format('d M Y') }}</td>
                        <td><span class="query-cell-truncate" title="{{ $query->query_title ?? '-' }}">{{ $query->query_title ?? '-' }}</span></td>
                        <td><span class="query-cell-truncate" title="{{ $query->effective_service_type }}">{{ $query->effective_service_type }}</span></td>
                        <td><span class="query-cell-truncate" title="{{ $query->client_name }}">{{ $query->client_name }}</span></td>
                        <td><span class="query-cell-truncate" title="{{ $query->company_name ?? '-' }}">{{ $query->company_name ?? '-' }}</span></td>
                        <td>{{ $query->mobile }}</td>
                        <td><span class="query-cell-truncate" title="{{ $query->destination ?? '-' }}">{{ $query->destination ?? '-' }}</span></td>
                        <td>{{ $query->travel_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $query->number_of_pax ?? '-' }}</td>
                        <td>{{ $query->source }}</td>
                        <td><span class="query-cell-truncate" title="{{ $query->assignedBy?->name ?? '-' }}">{{ $query->assignedBy?->name ?? '-' }}</span></td>
                        <td><span class="query-cell-truncate" title="{{ $query->assignedTo?->name ?? 'Unassigned' }}">{{ $query->assignedTo?->name ?? 'Unassigned' }}</span></td>
                        <td><span class="badge bg-light text-dark border">{{ $query->priority }}</span></td>
                        <td>{{ $query->stage }}</td>
                        <td><span class="badge bg-{{ $query->status === 'Converted' ? 'success' : ($query->status === 'Lost' ? 'danger' : 'primary') }}">{{ $query->status }}</span></td>
                        <td class="text-end query-money">{{ $query->expected_sale_amount ? 'INR '.number_format((float) $query->expected_sale_amount, 2) : '-' }}</td>
                        <td>{{ $query->last_followup_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $query->next_followup_date?->format('d M Y') ?? '-' }}</td>
                        <td><span class="query-cell-truncate" title="{{ $query->latest_remark }}">{{ Str::limit($query->latest_remark, 40) }}</span></td>
                        <td><span class="badge bg-{{ $query->age_color === 'orange' ? 'warning text-dark' : $query->age_color }}">{{ $query->age_days }}d</span></td>
                        <td>{{ $query->created_at?->timezone(config('app.display_timezone'))->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="22" class="text-center text-muted py-4">No queries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($queries->hasPages())
        <div class="card-footer bg-white">{{ $queries->links() }}</div>
    @endif
</div>
@endsection
