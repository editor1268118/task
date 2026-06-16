@extends('layouts.admin')

@section('title', 'My Follow-Ups')
@section('page-header', 'My Follow-Ups')
@section('page-description', 'Today, upcoming, and overdue query follow-ups.')

@section('content')
@php
    $groups = [
        'Overdue Follow-Ups' => $queries->getCollection()->filter(fn($q) => $q->next_followup_date && $q->next_followup_date->isPast() && !$q->next_followup_date->isToday()),
        "Today's Follow-Ups" => $queries->getCollection()->filter(fn($q) => $q->next_followup_date?->isToday()),
        'Upcoming Follow-Ups' => $queries->getCollection()->filter(fn($q) => $q->next_followup_date && $q->next_followup_date->isFuture()),
    ];
@endphp

@foreach($groups as $title => $items)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>{{ $title }}</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Query</th><th>Client</th><th>Assigned To</th><th>Next Follow-Up</th><th>Time</th><th>Stage</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($items as $query)
                        <tr>
                            <td><a href="{{ route('sales.queries.show', $query) }}">{{ $query->query_no }}</a></td>
                            <td>{{ $query->client_name }}</td>
                            <td>{{ $query->assignedTo?->name ?? '-' }}</td>
                            <td>{{ $query->next_followup_date?->format('d M Y') }}</td>
                            <td>{{ $query->formatted_next_followup_time }}</td>
                            <td>{{ $query->stage }}</td>
                            <td>{{ $query->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

{{ $queries->links() }}
@endsection
