@extends('layouts.admin')

@section('title', 'Department Performance')
@section('page-header', 'Department Performance')
@section('page-description', 'Compare task execution across departments.')

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
    <li class="breadcrumb-item active">Department Performance</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.department') }}" class="row g-3 align-items-end">
            @if(auth()->user()->hasRole('super-admin'))
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($allDepartments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
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
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>Overdue</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        @php
                            $rate = $dept->total_tasks > 0 ? round(($dept->completed_tasks / $dept->total_tasks) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $dept->name }}</td>
                            <td>{{ $dept->total_tasks }}</td>
                            <td class="text-success">{{ $dept->completed_tasks }}</td>
                            <td class="text-danger">{{ $dept->overdue_tasks }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress w-100" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $rate > 70 ? 'success' : ($rate > 40 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $rate }}%;"></div>
                                    </div>
                                    <span class="small text-muted" style="min-width: 40px;">{{ $rate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
