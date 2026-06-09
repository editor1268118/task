@extends('layouts.admin')

@section('title', 'Workload Monitoring')
@section('page-header', 'Workload Monitoring')
@section('page-description', 'Identify bottlenecks and overloaded employees.')

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
    <li class="breadcrumb-item active">Workloads</li>
@endsection

@section('content')

@if(auth()->user()->hasRole('super-admin'))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.workload') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Active Tasks</th>
                        <th>Pending</th>
                        <th>Overdue</th>
                        <th>Est. Hours</th>
                        <th>Warning</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workloads as $emp)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm">
                                        @if($emp->profile_photo)
                                            <img src="{{ Storage::url($emp->profile_photo) }}" alt="" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                                        @else
                                            <span>{{ substr($emp->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-medium text-dark">{{ $emp->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $emp->department?->name ?? 'No Dept' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary rounded-pill">{{ $emp->active_tasks }}</span></td>
                            <td>{{ $emp->pending_tasks }}</td>
                            <td class="text-danger fw-bold">{{ $emp->overdue_tasks }}</td>
                            <td>{{ $emp->assigned_tasks_sum_estimated_hours ?? 0 }} hrs</td>
                            <td>
                                @if($emp->is_overloaded)
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overloaded</span>
                                @else
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Healthy</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No workload data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
