@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('page-header', 'Roles & Permissions')
@section('page-description', 'Manage system roles and access control.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">System Roles</h5>
        @can('manage-roles')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Role
            </a>
        @endcan
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Assigned Users</th>
                        <th>Permissions</th>
                        @can('manage-roles')
                            <th class="text-end">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary">
                                    {{ Str::headline($role->name) }}
                                </span>
                                @if(in_array($role->name, ['super-admin', 'manager', 'employee']))
                                    <span class="badge bg-secondary ms-2">System Default</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark rounded-pill px-3">
                                    <i class="fas fa-users me-1"></i> {{ $role->users_count }}
                                </span>
                            </td>
                            <td>
                                @if($role->name === 'super-admin')
                                    <span class="badge bg-success">All Permissions</span>
                                @else
                                    <span class="text-muted">{{ $role->permissions->count() }} permissions assigned</span>
                                @endif
                            </td>
                            @can('manage-roles')
                                <td class="text-end">
                                    @if($role->name !== 'super-admin')
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit Permissions">
                                            <i class="fas fa-shield-alt"></i>
                                        </a>
                                        @if(!in_array($role->name, ['manager', 'employee']))
                                            <button class="btn btn-sm btn-outline-danger" data-confirm-delete data-url="{{ route('admin.roles.destroy', $role) }}" data-message="Are you sure you want to delete the {{ $role->name }} role?" data-bs-toggle="tooltip" title="Delete Role">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
