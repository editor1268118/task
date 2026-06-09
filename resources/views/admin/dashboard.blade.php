@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-header', 'Dashboard')
@section('page-description', 'Welcome back! Here\'s an overview of your organization.')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Stats Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Users -->
    <div class="col-xl-4 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Total Users</span>
                    <h3 class="stat-value">{{ $stats['total_users'] ?? 0 }}</h3>
                    <span class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> Active workforce
                    </span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Tasks -->
    <div class="col-xl-4 col-md-6">
        <a href="{{ route('tasks.index') }}" class="stat-card stat-card-info text-decoration-none">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Total Tasks</span>
                    <h3 class="stat-value">{{ $stats['total_tasks'] ?? 0 }}</h3>
                    <span class="stat-trend">
                        <i class="fas fa-clipboard-list"></i> All time
                    </span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Departments -->
    <div class="col-xl-4 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Active Departments</span>
                    <h3 class="stat-value">{{ $stats['departments'] ?? 0 }}</h3>
                    <span class="stat-trend trend-up">
                        <i class="fas fa-building"></i> Operating
                    </span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="col-xl-4 col-md-6">
        <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="stat-card stat-card-warning text-decoration-none">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Pending Tasks</span>
                    <h3 class="stat-value">{{ $stats['pending_tasks'] ?? 0 }}</h3>
                    <span class="stat-trend">
                        <i class="fas fa-clock"></i> Awaiting action
                    </span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Completed Tasks -->
    <div class="col-xl-4 col-md-6">
        <a href="{{ route('tasks.index', ['status' => 'closed']) }}" class="stat-card stat-card-success text-decoration-none">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Closed Tasks</span>
                    <h3 class="stat-value">{{ $stats['completed_tasks'] ?? 0 }}</h3>
                    <span class="stat-trend trend-up">
                        <i class="fas fa-check-circle"></i> Done
                    </span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Overdue Tasks -->
    <div class="col-xl-4 col-md-6">
        <a href="{{ route('tasks.index', ['overdue' => 1]) }}" class="stat-card stat-card-danger text-decoration-none">
            <div class="stat-card-body">
                <div class="stat-info">
                    <span class="stat-label">Overdue Tasks</span>
                    <h3 class="stat-value">{{ $stats['overdue_tasks'] ?? 0 }}</h3>
                    <span class="stat-trend trend-down">
                        <i class="fas fa-exclamation-triangle"></i> Needs attention
                    </span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    @foreach([
        ['Pending Collections', 'INR '.number_format($stats['pending_collections'] ?? 0, 2), 'fa-wallet', 'warning', route('finance.queue')],
        ['Vendor Pending Payments', 'INR '.number_format($stats['vendor_pending_payments'] ?? 0, 2), 'fa-file-invoice-dollar', 'danger', route('finance.queue')],
        ['Operationally Completed', $stats['operationally_completed_tasks'] ?? 0, 'fa-check', 'info', route('operations.master-board.index', ['operational_status' => \App\Models\Task::OPERATIONAL_COMPLETED])],
        ['Financially Pending', $stats['financially_pending_tasks'] ?? 0, 'fa-hourglass-half', 'warning', route('finance.queue')],
        ['Fully Closed', $stats['fully_closed_tasks'] ?? 0, 'fa-lock', 'success', route('tasks.index', ['status' => 'closed'])],
    ] as [$label, $value, $icon, $tone, $url])
        <div class="col-xl col-md-6">
            <a href="{{ $url }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                <div class="card-body">
                    <small class="text-muted text-uppercase">{{ $label }}</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <h5 class="mb-0">{{ $value }}</h5>
                        <i class="fas {{ $icon }} text-{{ $tone }}"></i>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    @foreach([
        ['Query Follow-Ups', $stats['total_followups'] ?? 0, 'fa-phone-volume', 'primary', route('sales.queries.index', ['followup' => 'pending'])],
        ['Confirmed/Converted Queries', $stats['completed_followups'] ?? 0, 'fa-check-circle', 'success', route('sales.queries.index', ['status' => 'Confirmed'])],
        ['Missed Query Follow-Ups', $stats['missed_followups'] ?? 0, 'fa-exclamation-circle', 'danger', route('sales.queries.index', ['followup' => 'overdue'])],
        ['Conversion Tracking', $stats['conversion_tracking'] ?? 0, 'fa-user-check', 'info', route('sales.queries.reports')],
    ] as [$label, $value, $icon, $tone, $url])
        <div class="col-xl-3 col-md-6">
            <a href="{{ $url }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted text-uppercase">{{ $label }}</small><h5 class="mt-2 mb-0">{{ $value }}</h5></div>
                    <i class="fas {{ $icon }} text-{{ $tone }}"></i>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Quick Actions -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Task
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-1"></i> Add User
                    </a>
                    

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts (Phase 4) -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>Task Completion Trends
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
                    <i class="fas fa-chart-pie text-info me-2"></i>Priority Distribution
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="priorityChart" height="200"></canvas>
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

        // Trends Chart
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
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } }
            }
        });

        // Priority Chart
        new Chart(document.getElementById('priorityChart'), {
            type: 'doughnut',
            data: {
                labels: chartData.priorities.labels,
                datasets: [{
                    data: chartData.priorities.data,
                    backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    });
</script>
@endpush
