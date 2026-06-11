@extends('layouts.admin')

@section('title', 'Tasks')
@section('page-header', 'Tasks')
@section('page-description', 'Manage and track system tasks.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Tasks</li>
@endsection

@section('content')
<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('tasks.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Task No or Title" value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(App\Models\Task::getStatuses() as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    @foreach(App\Models\Task::getPriorities() as $key => $label)
                        <option value="{{ $key }}" {{ request('priority') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            @if(auth()->user()->hasRole('super-admin'))
                <div class="col-md-2">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(auth()->user()->hasRole(['super-admin', 'manager']))
                <div class="col-md-2">
                    <label class="form-label">Assignee</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">All Assignees</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" {{ request('assigned_to') == $assignee->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Task List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Task List</h5>
        @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Task
            </a>
        @endcan
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Task Details</th>
                        <th>Status / Priority</th>
                        <th>Assigned To</th>
                        <th>Dates</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <div class="mt-1">
                                        @if($task->isOverdue())
                                            <i class="fas fa-exclamation-circle text-danger" data-bs-toggle="tooltip" title="Overdue"></i>
                                        @else
                                            <i class="fas fa-tasks text-primary"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('tasks.show', $task) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                            {{ Str::limit($task->title, 50) }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <span class="text-primary fw-medium">{{ $task->task_no }}</span> &bull; {{ $task->department?->name ?? 'No Dept' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    @switch($task->status)
                                        @case('pending') <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span> @break
                                        @case('assigned') <span class="badge bg-info text-dark"><i class="fas fa-user-check me-1"></i>Assigned</span> @break
                                        @case('in_progress') <span class="badge bg-info text-dark"><i class="fas fa-spinner fa-spin me-1"></i>In Progress</span> @break
                                        @case('completion_pending') <span class="badge bg-purple" style="background: #667eea;"><i class="fas fa-hourglass-half me-1"></i>Completion Pending</span> @break
                                        @case('forms_submitted') <span class="badge" style="background: #17a2b8;"><i class="fas fa-clipboard-check me-1"></i>Forms Submitted</span> @break
                                        @case('completed') <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completed</span> @break
                                        @case('operationally_completed') <span class="badge bg-info text-dark">Operationally Completed</span> @break
                                        @case('collection_pending') <span class="badge bg-warning text-dark">Collection Pending</span> @break
                                        @case('vendor_payment_pending') <span class="badge bg-warning text-dark">Vendor Payment Pending</span> @break
                                        @case('finance_review_pending') <span class="badge" style="background:#6f42c1;">Finance Review Pending</span> @break
                                        @case('closed') <span class="badge bg-success">Closed</span> @break
                                        @case('on_hold') <span class="badge bg-secondary"><i class="fas fa-pause me-1"></i>On Hold</span> @break
                                        @case('cancelled') <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Cancelled</span> @break
                                        @case('follow_up') <span class="badge bg-primary text-white"><i class="fas fa-reply me-1"></i>Follow up</span> @break
                                    @endswitch
                                </div>
                                <div>
                                    @switch($task->priority)
                                        @case('high') <span class="badge border border-danger text-danger"><i class="fas fa-arrow-up me-1"></i>High</span> @break
                                        @case('medium') <span class="badge border border-warning text-warning"><i class="fas fa-minus me-1"></i>Medium</span> @break
                                        @case('low') <span class="badge border border-info text-info"><i class="fas fa-arrow-down me-1"></i>Low</span> @break
                                    @endswitch
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm">
                                        @if($task->assignee?->profile_photo)
                                            <img src="{{ $task->assignee->profilePhotoUrl() }}" alt="" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                                        @else
                                            <span>{{ substr($task->assignee?->name ?? 'U', 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-medium text-dark" style="font-size: 0.875rem;">{{ $task->assignee?->name ?? 'Unassigned' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size: 0.8125rem;">
                                    <div><strong>Start:</strong> {{ $task->start_date ? $task->start_date->format('M d, Y') : '—' }}</div>
                                    <div class="{{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                        <strong>Due:</strong> {{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $task)
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit Task">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('delete', $task)
                                    <button class="btn btn-sm btn-outline-danger" data-confirm-delete data-url="{{ route('tasks.destroy', $task) }}" data-message="Are you sure you want to delete this task?" data-bs-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-tasks fa-3x mb-3 d-block opacity-25"></i>
                                <h5>No tasks found</h5>
                                <p>Try adjusting your filters or create a new task.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($tasks->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
