@extends('layouts.admin')

@section('title', 'Users')
@section('page-header', 'Employees')
@section('page-description', 'Manage system users and access.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, Email, or EMP ID" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Employees</h5>
        @can('create-users')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Employee
            </a>
        @endcan
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar-sm">
                                        @if($user->profile_photo)
                                            <img src="{{ Storage::url($user->profile_photo) }}" alt="" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                                        @else
                                            <span>{{ substr($user->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ $user->employee_id }} &bull; {{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-soft">
                                    {{ Str::headline($user->roles->first()?->name ?? 'No Role') }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $user->department?->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $user->designation?->name ?? '—' }}
                                </div>
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @elseif($user->status === 'suspended')
                                    <span class="badge bg-danger"><i class="fas fa-ban me-1"></i> Suspended</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-user-alt-slash me-1"></i> Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('view-users')
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                
                                @can('edit-users')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan

                                @can('delete-users')
                                    @if(auth()->id() !== $user->id && !$user->hasRole('super-admin'))
                                        <button class="btn btn-sm btn-outline-danger" data-confirm-delete data-url="{{ route('admin.users.destroy', $user) }}" data-message="Are you sure you want to delete {{ $user->name }}? This action will archive their record." data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-3 d-block opacity-25"></i>
                                No employees found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($users->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
