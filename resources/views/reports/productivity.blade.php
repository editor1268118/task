@extends('layouts.admin')

@section('title', 'Productivity Report')
@section('page-header', 'Employee Productivity')
@section('page-description', 'Analyze task completion and productivity metrics.')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success">
            <i class="fas fa-file-excel me-1"></i> Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary">
            <i class="fas fa-file-csv me-1"></i> CSV
        </a>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
    <li class="breadcrumb-item active">Productivity</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.productivity') }}" class="row g-3 align-items-end">
            @if(auth()->user()->hasRole(['super-admin', 'manager']))
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="user_id" class="form-select">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $targetUser->id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="col-md-4">
                <label class="form-label">Date Range</label>
                <select name="date_range" class="form-select">
                    <option value="">All Time</option>
                    <option value="{{ now()->startOfMonth()->format('Y-m-d') }} to {{ now()->endOfMonth()->format('Y-m-d') }}" {{ request('date_range') == now()->startOfMonth()->format('Y-m-d') . ' to ' . now()->endOfMonth()->format('Y-m-d') ? 'selected' : '' }}>This Month</option>
                    <option value="{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }} to {{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}" {{ request('date_range') == now()->subMonth()->startOfMonth()->format('Y-m-d') . ' to ' . now()->subMonth()->endOfMonth()->format('Y-m-d') ? 'selected' : '' }}>Last Month</option>
                    <option value="{{ now()->startOfYear()->format('Y-m-d') }} to {{ now()->endOfYear()->format('Y-m-d') }}" {{ request('date_range') == now()->startOfYear()->format('Y-m-d') . ' to ' . now()->endOfYear()->format('Y-m-d') ? 'selected' : '' }}>This Year</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Summary Cards -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Completion Rate</h6>
                <h2 class="mb-0 fw-bold">{{ $metrics['completionRate'] }}%</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Total Assigned</h6>
                <h2 class="mb-0 fw-bold">{{ $metrics['total'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Completed Tasks</h6>
                <h2 class="mb-0 fw-bold text-success">{{ $metrics['completed'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Overdue Tasks</h6>
                <h2 class="mb-0 fw-bold text-danger">{{ $metrics['overdue'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom">
        <h6 class="mb-0">Performance Breakdown for {{ $targetUser->name }}</h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-sm-4 mb-3">
                <div class="p-3 border rounded bg-light">
                    <h3 class="fw-bold text-warning">{{ $metrics['pending'] }}</h3>
                    <span class="text-muted">Pending</span>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                <div class="p-3 border rounded bg-light">
                    <h3 class="fw-bold text-info">{{ $metrics['active'] }}</h3>
                    <span class="text-muted">In Progress / On Hold</span>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                <div class="p-3 border rounded bg-light">
                    <h3 class="fw-bold text-secondary">{{ $metrics['cancelled'] }}</h3>
                    <span class="text-muted">Cancelled</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
