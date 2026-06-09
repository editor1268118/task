@extends('layouts.admin')

@section('title', 'Interactions')
@section('page-header', 'Customer Interactions')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form class="d-flex gap-2" method="GET">
            <select name="type" class="form-select form-select-sm" style="max-width:220px">
                <option value="">All Interaction Types</option>
                @foreach(\App\Models\CustomerInteraction::TYPES as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Customer</th><th>Task</th><th>Notes</th><th>Next Follow-Up</th><th>Created By</th></tr></thead>
            <tbody>
                @forelse($interactions as $interaction)
                    <tr>
                        <td>{{ $interaction->interaction_date->format('d M Y h:i A') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $interaction->interaction_type }}</span></td>
                        <td><a href="{{ route('crm.customers.show', $interaction->customer) }}">{{ $interaction->customer->company_name ?? $interaction->customer->contact_person }}</a></td>
                        <td>@if($interaction->task)<a href="{{ route('tasks.show', $interaction->task) }}">{{ $interaction->task->task_no }}</a>@else - @endif</td>
                        <td>{{ Str::limit($interaction->notes, 80) }}</td>
                        <td>{{ $interaction->next_followup_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $interaction->creator?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No interactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($interactions->hasPages())
        <div class="card-footer bg-white">{{ $interactions->links() }}</div>
    @endif
</div>
@endsection
