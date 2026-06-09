@extends('layouts.admin')

@section('title', 'Customer Reports')
@section('page-header', 'Customer Reports')

@section('content')
<div class="row g-4 mb-4">
    @foreach([
        ['Total Customers', $customerStats['total_customers']],
        ['Repeat Customers', $customerStats['repeat_customers']],
        ['Customers With Queries', $customerStats['customers_with_queries']],
    ] as [$label, $value])
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted text-uppercase">{{ $label }}</small><h4 class="mt-2 mb-0">{{ $value }}</h4></div></div></div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Top Customers</div>
            <div class="list-group list-group-flush">
                @forelse($topCustomers as $customer)
                    <a href="{{ route('crm.customers.show', $customer) }}" class="list-group-item d-flex justify-content-between">
                        <span>{{ $customer->company_name ?? $customer->contact_person }}</span>
                        <strong>INR {{ number_format($customer->sales_total ?? 0, 2) }}</strong>
                    </a>
                @empty <div class="list-group-item text-muted">No data.</div> @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Most Active Customers</div>
            <div class="list-group list-group-flush">
                @forelse($mostActiveCustomers as $customer)
                    <a href="{{ route('crm.customers.show', $customer) }}" class="list-group-item d-flex justify-content-between">
                        <span>{{ $customer->company_name ?? $customer->contact_person }}</span>
                        <strong>{{ $customer->interactions_count }} interactions</strong>
                    </a>
                @empty <div class="list-group-item text-muted">No data.</div> @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Outstanding Customers</div>
            <div class="list-group list-group-flush">
                @forelse($outstandingCustomers as $customer)
                    <a href="{{ route('crm.customers.show', $customer) }}" class="list-group-item d-flex justify-content-between">
                        <span>{{ $customer->company_name ?? $customer->contact_person }}</span>
                        <strong>INR {{ number_format($customer->outstanding, 2) }}</strong>
                    </a>
                @empty <div class="list-group-item text-muted">No outstanding balances.</div> @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
