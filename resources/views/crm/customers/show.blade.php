@extends('layouts.admin')

@section('title', $customer->customer_code)
@section('page-header', $customer->company_name ?? $customer->contact_person ?? $customer->customer_code)
@section('page-description', 'Customer Master Database profile')

@section('page-actions')
    @role('super-admin')
        <div class="d-flex gap-2">
            <a href="{{ route('crm.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">Edit Customer</a>
            <form action="{{ route('crm.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
        </div>
    @endrole
@endsection

@push('styles')
<style>
    .crm-tabs .nav-link {
        color: #475569;
        border: 1px solid transparent;
        font-weight: 700;
    }
    .crm-tabs .nav-link:hover {
        color: #0f172a;
        background: #e0e7ff;
        border-color: #c7d2fe;
    }
    .crm-tabs .nav-link.active {
        color: #fff;
        background: #4f46e5;
        border-color: #4f46e5;
    }
</style>
@endpush

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['Queries', $overview['total_queries']],
        ['Tasks', $overview['total_tasks']],
        ['Active Tasks', $overview['active_tasks']],
        ['Revenue', 'INR '.number_format($overview['total_sales'], 2)],
        ['Collections', 'INR '.number_format($overview['total_received'], 2)],
        ['Outstanding', 'INR '.number_format($overview['pending_balance'], 2)],
        ['Vendor Cost', 'INR '.number_format($overview['vendor_cost'], 2)],
        ['Profit Estimate', 'INR '.number_format($overview['profit_estimate'], 2)],
    ] as [$label, $value])
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted text-uppercase fw-bold">{{ $label }}</small><h5 class="mt-2 mb-0">{{ $value }}</h5></div></div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-tabs crm-tabs gap-1" role="tablist">
            @foreach(['overview' => 'Overview', 'queries' => 'Queries', 'tasks' => 'Tasks', 'finance' => 'Finance', 'interactions' => 'Interactions', 'timeline' => 'Activity Timeline'] as $id => $label)
                <li class="nav-item"><button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#{{ $id }}" type="button">{{ $label }}</button></li>
            @endforeach
        </ul>
        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="overview">
                <div class="row g-3">
                    @foreach([
                        'Customer Code' => $customer->customer_code,
                        'Customer Name' => $customer->contact_person ?? '-',
                        'Company' => $customer->company_name ?? '-',
                        'Mobile' => $customer->mobile ?? '-',
                        'Email' => $customer->email ?? '-',
                        'Address' => $customer->address ?? '-',
                        'Created Date' => $customer->created_at?->timezone(config('app.display_timezone'))->format('d M Y h:i A'),
                        'Assigned Manager' => $customer->creator?->name ?? '-',
                    ] as $label => $value)
                        <div class="col-md-4"><div class="p-3 bg-light rounded h-100"><small class="text-muted">{{ $label }}</small><div class="fw-semibold">{{ $value }}</div></div></div>
                    @endforeach
                </div>
                @can('update', $customer)
                    <a href="{{ route('crm.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary mt-3">Edit Customer</a>
                @endcan
            </div>

            <div class="tab-pane fade" id="queries">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Query No</th><th>Service Type</th><th>Status</th><th>Stage</th><th>Assigned To</th><th>Created</th><th>Converted Task</th></tr></thead>
                    <tbody>
                        @forelse($customer->queries as $query)
                            <tr>
                                <td><a href="{{ route('sales.queries.show', $query) }}">{{ $query->query_no }}</a></td>
                                <td>{{ $query->effective_service_type }}</td>
                                <td>{{ $query->status }}</td>
                                <td>{{ $query->stage }}</td>
                                <td>{{ $query->assignedTo?->name ?? '-' }}</td>
                                <td>{{ $query->created_at?->timezone(config('app.display_timezone'))->format('d M Y') }}</td>
                                <td>@if($query->convertedTask)<a href="{{ route('tasks.show', $query->convertedTask) }}">{{ $query->convertedTask->task_no }}</a>@else - @endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted">No linked queries.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="tasks">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Task No</th><th>Task Type</th><th>Assigned To</th><th>Task Status</th><th>Financial Status</th><th>Sale Amount</th><th>Pending Collection</th><th>Created</th></tr></thead>
                    <tbody>
                        @forelse($customer->tasks as $task)
                            @php($sale = (float) ($task->booking?->sale_amount ?? 0))
                            @php($received = (float) ($task->booking?->receipts?->whereIn('receipt_status', \App\Models\CustomerReceipt::APPROVED_STATUSES)->sum('amount_received') ?? 0))
                            <tr>
                                <td><a href="{{ route('tasks.show', $task) }}">{{ $task->task_no }}</a></td>
                                <td>{{ $task->taskType?->name ?? '-' }}</td>
                                <td>{{ $task->assignee?->name ?? '-' }}</td>
                                <td>{{ Str::headline($task->status) }}</td>
                                <td>{{ Str::headline($task->financial_status ?? 'unpaid') }}</td>
                                <td class="text-end">INR {{ number_format($sale, 2) }}</td>
                                <td class="text-end">INR {{ number_format(max(0, $sale - $received), 2) }}</td>
                                <td>{{ $task->created_at?->timezone(config('app.display_timezone'))->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted">No linked tasks.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="finance">
                @can('viewFinancials', $customer)
                    <div class="row g-3 mb-3">
                        @foreach([
                            'Total Revenue' => $overview['total_sales'],
                            'Total Collections' => $overview['total_received'],
                            'Outstanding Amount' => $overview['pending_balance'],
                            'Total Vendor Cost' => $overview['vendor_cost'],
                            'Profit Estimate' => $overview['profit_estimate'],
                        ] as $label => $amount)
                            <div class="col-md"><div class="p-3 bg-light rounded"><small class="text-muted">{{ $label }}</small><div class="fw-bold">INR {{ number_format($amount, 2) }}</div></div></div>
                        @endforeach
                    </div>
                    <h6>Receipts</h6>
                    <table class="table table-sm"><tbody>@forelse($receipts as $receipt)<tr><td>{{ $receipt->payment_date->format('d M Y') }}</td><td>{{ $receipt->effective_payment_mode }}</td><td class="text-end">INR {{ number_format($receipt->amount_received, 2) }}</td></tr>@empty<tr><td class="text-muted">No receipts.</td></tr>@endforelse</tbody></table>
                    <h6>Vendor Payments</h6>
                    <table class="table table-sm"><tbody>@forelse($vendorPayments as $payment)<tr><td>{{ $payment->payment_date->format('d M Y') }}</td><td>{{ $payment->effective_vendor_name }}</td><td class="text-end">INR {{ number_format($payment->amount_paid, 2) }}</td></tr>@empty<tr><td class="text-muted">No vendor payments.</td></tr>@endforelse</tbody></table>
                @else
                    <p class="text-muted">Financial details are restricted.</p>
                @endcan
            </div>

            <div class="tab-pane fade" id="interactions">
                @can('addInteraction', $customer)
                    <form action="{{ route('crm.customers.interactions.store', $customer) }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-2"><input type="datetime-local" name="interaction_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}"></div>
                        <div class="col-md-2"><select name="interaction_type" class="form-select form-select-sm">@foreach(\App\Models\CustomerInteraction::TYPES as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></div>
                        <div class="col-md-6"><input name="notes" class="form-control form-control-sm" placeholder="Remarks"></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-success w-100">Add</button></div>
                    </form>
                @endcan
                @forelse($customer->interactions as $interaction)
                    <div class="border-bottom py-2"><strong>{{ $interaction->interaction_type }}</strong> <span class="text-muted">{{ $interaction->interaction_date->format('d M Y h:i A') }}</span><div>{{ $interaction->notes }}</div><small class="text-muted">{{ $interaction->creator?->name ?? '-' }}</small></div>
                @empty
                    <p class="text-muted">No interactions.</p>
                @endforelse
            </div>

            <div class="tab-pane fade" id="timeline">
                @foreach($queryActivities as $activity)
                    <div class="border-bottom py-2"><span class="text-muted">{{ $activity->activity_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}</span><div><strong>{{ $activity->action }}</strong> {{ $activity->remarks }}</div><small>{{ $activity->user?->name ?? 'System' }}</small></div>
                @endforeach
                @foreach($activities as $activity)
                    <div class="border-bottom py-2"><span class="text-muted">{{ $activity->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}</span><div>{{ $activity->description }}</div></div>
                @endforeach
                @if($queryActivities->isEmpty() && $activities->isEmpty())
                    <p class="text-muted">No timeline activity.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
