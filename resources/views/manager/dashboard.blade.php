@extends('layouts.admin')

@section('title', 'Manager Dashboard')
@section('page-header', 'Manager Dashboard')
@section('page-description', 'Monitor your team\'s tasks and progress.')

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
                    <span class="stat-label">Team Tasks</span>
                    <h3 class="stat-value">{{ $stats['team_tasks'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
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
        <div class="stat-card stat-card-danger">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Delayed</span>
                    <h3 class="stat-value">{{ $stats['delayed_tasks'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
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
</div>

<div class="row g-4 mb-4">
    @foreach([
        ['Team Query Follow-Ups Today', $stats['team_followups_today'] ?? 0, 'fa-phone-volume', 'primary'],
        ['Missed Query Follow-Ups', $stats['missed_followups'] ?? 0, 'fa-exclamation-circle', 'danger'],
        ['Query Confirmation Rate', ($stats['followup_completion_rate'] ?? 0).'%', 'fa-chart-line', 'success'],
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
                    <i class="fas fa-chart-line text-primary me-2"></i>Team Completion Trends
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
                    <i class="fas fa-chart-pie text-info me-2"></i>Team Priority Distribution
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="priorityChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Assigned Tasks -->
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tasks text-primary me-2"></i>Recently Assigned Tasks
                </h5>
                <div class="d-flex gap-2">

                    <a href="{{ route('tasks.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Assign New Task
                </a>
                <a href="{{ route('tasks.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm ms-2">
                    <i class="fas fa-list-check fa-sm text-white-50"></i> Task Workflow
                </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Task #</th>
                                <th>Title</th>
                                <th>Assigned To</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTasks ?? [] as $task)
                                <tr>
                                    <td><span class="fw-semibold text-primary">{{ $task->task_no }}</span></td>
                                    <td>{{ Str::limit($task->title, 40) }}</td>
                                    <td>{{ $task->assignee->name ?? '—' }}</td>
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
                                    <td>{{ $task->due_date?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                        No tasks assigned yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
