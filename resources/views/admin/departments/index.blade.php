@extends('layouts.admin')

@section('title', 'Departments')
@section('page-header', 'Departments')
@section('page-description', 'Manage organizational departments.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Departments</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <form action="{{ route('admin.departments.index') }}" method="GET" class="d-flex gap-2">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Search departments..." value="{{ request('search') }}">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
        
        @can('manage-departments')
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Department
            </a>
        @endcan
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        @can('manage-departments')
                            <th class="text-end">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td><span class="fw-semibold">{{ $department->code }}</span></td>
                            <td>{{ $department->name }}</td>
                            <td>{{ Str::limit($department->description, 50) }}</td>
                            <td>
                                @if($department->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            @can('manage-departments')
                                <td class="text-end">
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" data-confirm-delete data-url="{{ route('admin.departments.destroy', $department) }}" data-message="Are you sure you want to delete the {{ $department->name }} department?" data-bs-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-building fa-2x mb-3 d-block opacity-25"></i>
                                No departments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($departments->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $departments->links() }}
        </div>
    @endif
</div>
@endsection
