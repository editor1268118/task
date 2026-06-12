@extends('layouts.admin')

@section('title', 'Create Role')
@section('page-header', 'Create Role')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<form action="{{ route('admin.roles.store') }}" method="POST" data-loading>
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. HR Manager">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Assign Permissions</h5>

    <div class="alert alert-info border-0 shadow-sm">
        <div class="fw-semibold mb-1">Access Controls</div>
        <div class="small mb-0">
            Use <strong>Register Query</strong> and <strong>Create Task</strong> to control whether users with this role can create sales queries or operational tasks.
        </div>
    </div>

    <div class="row g-4">
        @foreach($permissions as $group => $perms)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 text-primary text-uppercase">{{ $group }} Module</h6>
                    </div>
                    <div class="card-body">
                        @foreach($perms as $permission)
                            @php
                                $permissionLabel = match ($permission->name) {
                                    'create-queries' => 'Register Query',
                                    'view-queries' => 'View Query Register',
                                    'create-tasks' => 'Create Task',
                                    default => Str::headline(str_replace('-', ' ', $permission->name)),
                                };
                            @endphp
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" {{ is_array(old('permissions')) && in_array($permission->name, old('permissions')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    {{ $permissionLabel }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 mb-5 d-flex justify-content-end gap-2">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Role</button>
    </div>
</form>
@endsection
