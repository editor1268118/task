@extends('layouts.admin')

@section('title', 'Query Reports')
@section('page-header', 'Query Reports')
@section('page-description', 'Register, follow-up, conversion, lost, employee, and source analysis using filters.')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('sales.queries.export', ['format' => 'xlsx'] + request()->query()) }}" class="btn btn-sm btn-success">XLSX</a>
    <a href="{{ route('sales.queries.export', ['format' => 'csv'] + request()->query()) }}" class="btn btn-sm btn-outline-success">CSV</a>
    <a href="{{ route('sales.queries.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-dark">Print</a>
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-2"><label class="form-label small">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option>@foreach($statuses as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Source</label><select name="source" class="form-select form-select-sm"><option value="">All</option>@foreach($sources as $source)<option value="{{ $source }}" {{ request('source') === $source ? 'selected' : '' }}>{{ $source }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Service</label><select name="service_type" class="form-select form-select-sm"><option value="">All</option>@foreach($serviceTypes as $type)<option value="{{ $type }}" {{ request('service_type') === $type ? 'selected' : '' }}>{{ $type }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Assigned To</label><select name="assigned_to" class="form-select form-select-sm"><option value="">All</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Run Report</button></div>
        </form>
        <table class="table table-sm align-middle">
            <thead class="table-light"><tr><th>Query</th><th>Client</th><th>Service</th><th>Source</th><th>Employee</th><th>Status</th><th>Expected Sale</th><th>Next Follow-Up</th></tr></thead>
            <tbody>
                @forelse($queries as $query)
                    <tr>
                        <td><a href="{{ route('sales.queries.show', $query) }}">{{ $query->query_no }}</a></td>
                        <td>{{ $query->client_name }}</td>
                        <td>{{ $query->effective_service_type }}</td>
                        <td>{{ $query->source }}</td>
                        <td>{{ $query->assignedTo?->name ?? '-' }}</td>
                        <td>{{ $query->status }}</td>
                        <td class="text-end">{{ $query->expected_sale_amount ? 'INR '.number_format((float) $query->expected_sale_amount, 2) : '-' }}</td>
                        <td>{{ $query->next_followup_date?->format('d M Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No report data.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $queries->links() }}
    </div>
</div>
@endsection
