@extends('layouts.admin')

@section('title', 'My Dashboard')
@section('page-header', 'My Dashboard')
@section('page-description', 'Track your tasks and upcoming deadlines.')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">My Tasks</span>
                    <h3 class="stat-value">{{ $stats['my_tasks'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Pending</span>
                    <h3 class="stat-value">{{ $stats['pending_tasks'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Completed</span>
                    <h3 class="stat-value">{{ $stats['completed_tasks'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Overdue</span>
                    <h3 class="stat-value">{{ $stats['overdue_tasks'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    @foreach([
        ['My Query Follow-Ups Today', $stats['my_followups_today'] ?? 0, 'fa-phone-volume', 'primary'],
        ['Upcoming Query Follow-Ups', $stats['upcoming_followups'] ?? 0, 'fa-calendar-plus', 'info'],
        ['Missed Query Follow-Ups', $stats['missed_followups'] ?? 0, 'fa-exclamation-circle', 'danger'],
    ] as [$label, $value, $icon, $tone])
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted text-uppercase">{{ $label }}</small><h4 class="mt-2 mb-0">{{ $value }}</h4></div>
                    <i class="fas {{ $icon }} fa-2x text-{{ $tone }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Charts (Phase 4) -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>My Completion Trends
                </h5>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie text-info me-2"></i>My Priority Distribution
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="priorityChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Deadlines and Activity Timeline -->
<div class="row g-4">
    <!-- Upcoming Deadlines -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>Upcoming Deadlines
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Task #</th>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingTasks ?? [] as $task)
                                <tr>
                                    <td><span class="fw-semibold text-primary">{{ $task->task_no }}</span></td>
                                    <td>{{ Str::limit($task->title, 40) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : ($task->status === 'on_hold' ? 'secondary' : 'warning')) }}">
                                            {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->due_date)
                                            <span class="{{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                                {{ $task->due_date->format('M d, Y') }}
                                                @if($task->isOverdue())
                                                    <i class="fas fa-exclamation-circle ms-1"></i>
                                                @endif
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                                        No upcoming tasks. You're all caught up!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Activity Timeline -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history text-info me-2"></i>My Recent Activity
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="timeline" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentActivities ?? [] as $activity)
                    <div class="timeline-item mb-3 pb-3 border-bottom position-relative" style="padding-left: 20px;">
                        <span class="position-absolute top-0 start-0 translate-middle p-1 bg-primary border border-light rounded-circle" style="width: 12px; height: 12px; margin-top: 6px;"></span>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold" style="font-size: 0.9rem;">
                                {{ $activity->description }}
                            </span>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <span data-bs-toggle="tooltip" title="{{ $activity->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </small>
                        </div>
                        @if($activity->subject)
                            <small class="text-muted d-block">
                                <a href="{{ route('tasks.show', $activity->subject_id) }}" class="text-decoration-none">View Task</a>
                            </small>
                        @endif
                        @if($activity->changes()->isNotEmpty())
                            <div class="mt-2 bg-light p-2 rounded" style="font-size: 0.8rem;">
                                @foreach($activity->changes()['attributes'] ?? [] as $key => $value)
                                    @if(isset($activity->changes()['old'][$key]))
                                        <div class="text-muted">
                                            Changed <strong class="text-dark">{{ $key }}</strong> 
                                            from <span class="text-danger">{{ $activity->changes()['old'][$key] }}</span> 
                                            to <span class="text-success">{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <p class="mb-0">No recent activity.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);

        new Chart(document.getElementById('trendsChart'), {
            type: 'line',
            data: {
                labels: chartData.trends.labels,
                datasets: [
                    {
                        label: 'Created Tasks',
                        data: chartData.trends.created,
                        borderColor: '#6c757d',
                        backgroundColor: 'rgba(108, 117, 125, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Completed Tasks',
                        data: chartData.trends.completed,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });

        new Chart(document.getElementById('priorityChart'), {
            type: 'pie',
            data: {
                labels: chartData.priorities.labels,
                datasets: [{
                    data: chartData.priorities.data,
                    backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    });
</script>
@endpush
