@extends('layouts.admin')

@section('title', 'Designations')
@section('page-header', 'Designations')
@section('page-description', 'Manage organizational job titles.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Designations</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <form action="{{ route('admin.designations.index') }}" method="GET" class="d-flex gap-2">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Search designations..." value="{{ request('search') }}">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.designations.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
        
        @can('manage-designations')
            <a href="{{ route('admin.designations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Designation
            </a>
        @endcan
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        @can('manage-designations')
                            <th class="text-end">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $designation)
                        <tr>
                            <td><span class="fw-semibold">{{ $designation->name }}</span></td>
                            <td>{{ Str::limit($designation->description, 50) }}</td>
                            <td>
                                @if($designation->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            @can('manage-designations')
                                <td class="text-end">
                                    <a href="{{ route('admin.designations.edit', $designation) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" data-confirm-delete data-url="{{ route('admin.designations.destroy', $designation) }}" data-message="Are you sure you want to delete the {{ $designation->name }} designation?" data-bs-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-id-badge fa-2x mb-3 d-block opacity-25"></i>
                                No designations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($designations->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $designations->links() }}
        </div>
    @endif
</div>
@endsection
