@extends('layouts.admin')

@section('title', 'Query Dashboard')
@section('page-header', 'Query Dashboard')
@section('page-description', 'Sales query KPIs, follow-up pressure, and conversion visibility.')

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['Total Queries', $stats['total_queries'], 'primary', route('sales.queries.index')],
        ["Today's Queries", $stats['todays_queries'], 'info', route('sales.queries.index', ['quick' => 'today'])],
        ['Pending Follow-Ups', $stats['pending_followups'], 'warning', route('sales.queries.index', ['followup' => 'pending'])],
        ['Overdue Follow-Ups', $stats['overdue_followups'], 'danger', route('sales.queries.index', ['followup' => 'overdue'])],
        ['Confirmed Queries', $stats['confirmed_queries'], 'success', route('sales.queries.index', ['status' => 'Confirmed'])],
        ['Lost Queries', $stats['lost_queries'], 'danger', route('sales.queries.index', ['status' => 'Lost'])],
        ['Converted Queries', $stats['converted_queries'], 'success', route('sales.queries.index', ['status' => 'Converted'])],
        ['Conversion Rate', $stats['conversion_rate'].'%', 'primary', route('sales.queries.reports')],
    ] as [$label, $value, $color, $url])
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ $url }}" class="card border-0 shadow-sm h-100 text-decoration-none">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-bold">{{ $label }}</small>
                    <div class="h5 mt-2 text-{{ $color }}">{{ $value }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    @foreach(['Queries By Service Type' => $stats['by_service_type'], 'Queries By Source' => $stats['by_source'], 'Queries By Employee' => $stats['by_employee']] as $title => $items)
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>{{ $title }}</strong></div>
                <div class="card-body">
                    @forelse($items as $label => $count)
                        <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $label }}</span><strong>{{ $count }}</strong></div>
                    @empty
                        <p class="text-muted">No data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
