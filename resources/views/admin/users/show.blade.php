@extends('layouts.admin')

@section('title', 'Employee Profile')
@section('page-header', 'Employee Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Left Column (Profile Card) -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm text-center pt-4 pb-3 h-100">
            <div class="card-body">
                <div class="mb-4 d-flex justify-content-center">
                    <div class="user-avatar" style="width: 120px; height: 120px; font-size: 3rem;">
                        @if($user->profile_photo)
                            <img src="{{ $user->profilePhotoUrl() }}" alt="" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                        @else
                            <span>{{ substr($user->name, 0, 1) }}</span>
                        @endif
                    </div>
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->designation?->name ?? 'No Designation' }}</p>
                
                <div class="mb-4">
                    <span class="badge bg-primary-soft px-3 py-2" style="font-size: 0.85rem;">
                        {{ Str::headline($user->roles->first()?->name ?? 'No Role') }}
                    </span>
                </div>
                
                @if($user->status === 'active')
                    <span class="badge bg-success w-50 py-2"><i class="fas fa-check-circle me-1"></i> Active Account</span>
                @elseif($user->status === 'suspended')
                    <span class="badge bg-danger w-50 py-2"><i class="fas fa-ban me-1"></i> Suspended</span>
                @else
                    <span class="badge bg-secondary w-50 py-2"><i class="fas fa-user-alt-slash me-1"></i> Inactive</span>
                @endif
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.875rem;">
                    <span><i class="fas fa-calendar-alt me-2"></i>Joined</span>
                    <span class="fw-medium text-dark">{{ $user->joining_date ? $user->joining_date->format('M d, Y') : '—' }}</span>
                </div>
                <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 0.875rem;">
                    <span><i class="fas fa-sign-in-alt me-2"></i>Last Login</span>
                    <span class="fw-medium text-dark">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column (Details) -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary"><i class="fas fa-address-card me-2"></i>Contact & Job Details</h6>
                @can('edit-users')
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                        <tr>
                            <td class="w-30 text-muted ps-4 py-3 border-0 border-bottom">Employee ID</td>
                            <td class="fw-semibold py-3 border-0 border-bottom">{{ $user->employee_id }}</td>
                        </tr>
                        <tr>
                            <td class="w-30 text-muted ps-4 py-3 border-0 border-bottom">Email Address</td>
                            <td class="fw-semibold py-3 border-0 border-bottom">
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="w-30 text-muted ps-4 py-3 border-0 border-bottom">Phone Number</td>
                            <td class="fw-semibold py-3 border-0 border-bottom">
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="w-30 text-muted ps-4 py-3 border-0 border-bottom">Department</td>
                            <td class="fw-semibold py-3 border-0 border-bottom">
                                @if($user->department)
                                    {{ $user->department->name }} <span class="text-muted fw-normal">({{ $user->department->code }})</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="w-30 text-muted ps-4 py-3 border-0 border-bottom">Tasks Assigned To</td>
                            <td class="fw-semibold py-3 border-0 border-bottom">
                                <span class="badge bg-primary rounded-pill px-3">{{ $user->assignedTasks()->count() }} tasks</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="w-30 text-muted ps-4 py-3 border-0">Tasks Assigned By</td>
                            <td class="fw-semibold py-3 border-0">
                                <span class="badge bg-info rounded-pill px-3">{{ $user->createdTasks()->count() }} tasks</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
