@extends('layouts.admin')

@section('title', 'System Audit Log')
@section('page-header', 'System Audit Log')
@section('page-description', 'Global chronological record of all system activity.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
    <li class="breadcrumb-item active">Audit Log</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Model Context</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted">{{ $log->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i:s A') }}</td>
                            <td class="fw-medium">{{ $log->causer?->name ?? 'System' }}</td>
                            <td>
                                @if($log->event == 'created')
                                    <span class="badge bg-success-soft text-success border border-success">Created</span>
                                @elseif($log->event == 'updated')
                                    <span class="badge bg-warning-soft text-warning border border-warning">Updated</span>
                                @elseif($log->event == 'deleted')
                                    <span class="badge bg-danger-soft text-danger border border-danger">Deleted</span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary border border-secondary">{{ ucfirst($log->event) }}</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            </td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $logs->links() }}
        </div>
    @endif
</div>

@endsection
