@extends('layouts.admin')

@section('title', 'Task Types')
@section('page-header', 'Task Types')
@section('page-description', 'Manage task categories used during task creation and operational completion.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Task Types</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <form action="{{ route('admin.task-types.index') }}" method="GET" class="d-flex gap-2">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Search task types..." value="{{ request('search') }}">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.task-types.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>

        @can('manage-task-types')
            <a href="{{ route('admin.task-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Task Type
            </a>
        @endcan
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Operational Form</th>
                        <th>Tasks</th>
                        <th>Status</th>
                        @can('manage-task-types')
                            <th class="text-end">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($taskTypes as $type)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $type->name }}</span>
                                @if($type->description)
                                    <div class="text-muted small">{{ Str::limit($type->description, 70) }}</div>
                                @endif
                            </td>
                            <td><code>{{ $type->slug }}</code></td>
                            <td>
                                @if($type->completionForms->where('slug', 'hotel-tour')->isNotEmpty())
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Hotel & Tour Form</span>
                                @else
                                    <span class="badge bg-secondary">Not Required</span>
                                @endif
                            </td>
                            <td>{{ $type->tasks_count }}</td>
                            <td>
                                @if($type->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            @can('manage-task-types')
                                <td class="text-end">
                                    <a href="{{ route('admin.task-types.edit', $type) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($type->tasks_count === 0)
                                        <button class="btn btn-sm btn-outline-danger" data-confirm-delete data-url="{{ route('admin.task-types.destroy', $type) }}" data-message="Are you sure you want to delete the {{ $type->name }} task type?" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-tags fa-2x mb-3 d-block opacity-25"></i>
                                No task types found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($taskTypes->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $taskTypes->links() }}
        </div>
    @endif
</div>
@endsection
