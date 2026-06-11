@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-header', 'Notification Center')
@section('page-description', 'Your complete task, query, finance, and approval alert history.')

@section('page-actions')
    @if(auth()->user()->unreadNotifications()->exists())
        <form action="{{ route('notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">Mark All Read</button>
        </form>
    @endif
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $module = $data['module'] ?? ($data['type'] ?? 'task');
            @endphp
            <a href="{{ route('notifications.read', $notification->id) }}" class="d-flex gap-3 p-3 border-bottom text-decoration-none text-dark {{ $notification->read_at ? '' : 'bg-light' }}">
                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $notification->read_at ? 'bg-secondary-subtle text-secondary' : 'bg-primary-subtle text-primary' }}" style="width: 42px; height: 42px; min-width: 42px;">
                    <i class="fas {{ $module === 'finance' ? 'fa-indian-rupee-sign' : ($module === 'query' ? 'fa-headset' : 'fa-bell') }}"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 text-muted">{{ $data['message'] ?? 'New system notification.' }}</p>
                    <span class="badge bg-light text-dark border">{{ ucfirst($module) }}</span>
                    @if(!empty($data['priority']) && $data['priority'] === 'high')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">High Priority</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-bell-slash fa-2x mb-3"></i>
                <p class="mb-0">No notifications yet.</p>
            </div>
        @endforelse
    </div>
</div>

@if($notifications->hasPages())
    <div class="mt-3">{{ $notifications->links() }}</div>
@endif
@endsection
