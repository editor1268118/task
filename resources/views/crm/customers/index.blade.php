@extends('layouts.admin')

@section('title', 'Customers')
@section('page-header', 'Customers')
@section('page-description', 'Centralized customer master and CRM tracking.')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search customer, mobile, email">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                @foreach(\App\Models\Customer::STATUSES as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
        @can('create', App\Models\Customer::class)
            <a href="{{ route('crm.customers.create') }}" class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i> Add Customer</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Tasks</th>
                    <th>Queries</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="fw-semibold">{{ $customer->customer_code }}</td>
                        <td>
                            <a href="{{ route('crm.customers.show', $customer) }}" class="fw-semibold">{{ $customer->company_name ?? $customer->contact_person ?? 'Unnamed' }}</a>
                            <div class="small text-muted">{{ $customer->email }}</div>
                        </td>
                        <td>{{ $customer->customer_type }}</td>
                        <td>{{ $customer->mobile ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $customer->status }}</span></td>
                        <td>{{ $customer->tasks_count }}</td>
                        <td>{{ $customer->queries_count }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('crm.customers.show', $customer) }}" class="btn btn-sm btn-primary">Open</a>
                                @role('super-admin')
                                    <a href="{{ route('crm.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('crm.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
