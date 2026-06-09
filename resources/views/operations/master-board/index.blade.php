@extends('layouts.admin')

@section('title', 'Master Operations Board')
@section('page-header', 'Master Operations Board')
@section('page-description', 'Google Sheet replacement with operational, financial, workflow, and activity visibility.')

@push('styles')
<style>
    #mainContent,
    #mainContent .page-content {
        min-width: 0;
        overflow-x: hidden;
    }

    .master-board-kpi {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: var(--shadow-sm);
        min-height: 64px;
    }

    .master-board-kpi .card-body {
        padding: 0.65rem 0.8rem;
    }

    .master-board-kpi .kpi-label {
        color: var(--gray-500);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .master-board-kpi .kpi-value {
        color: var(--dark);
        font-size: 0.92rem;
        font-weight: 800;
        margin-top: 0.28rem;
        line-height: 1.2;
    }

    .master-board-table-card {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .master-board-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        max-height: calc(100vh - 250px);
        overflow-x: auto;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-color: #94a3b8 #e2e8f0;
        scrollbar-width: thin;
    }

    .master-board-scroll::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .master-board-scroll::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 999px;
    }

    .master-board-scroll::-webkit-scrollbar-thumb {
        background: #64748b;
        border-radius: 999px;
        border: 2px solid #e2e8f0;
    }

    .master-board-scroll::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }

    .master-board-table {
        width: max-content;
        min-width: 1350px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .master-board-table thead th {
        position: sticky;
        top: 0;
        z-index: 6;
        background: #eef2ff;
        color: #334155;
        font-size: 0.72rem;
        padding: 0.5rem 0.65rem;
        box-shadow: inset 0 -1px 0 var(--border-color);
    }

    .master-board-table tbody td {
        background: #ffffff;
        white-space: nowrap;
        font-size: 0.78rem;
        padding: 0.45rem 0.65rem;
    }

    .master-board-table tbody tr:hover td {
        background: #f8fafc;
    }

    .master-board-table .board-indicator-cell {
        position: sticky;
        left: 0;
        z-index: 5;
        width: 42px;
        min-width: 42px;
        text-align: center;
        box-shadow: 1px 0 0 var(--border-color);
    }

    .master-board-table thead .board-indicator-cell {
        z-index: 8;
        background: #eef2ff;
    }

    .master-board-table .board-task-cell {
        position: sticky;
        left: 42px;
        z-index: 4;
        min-width: 120px;
        box-shadow: 1px 0 0 var(--border-color);
    }

    .master-board-table thead .board-task-cell {
        z-index: 7;
        background: #eef2ff;
    }

    .master-board-table .board-actions-cell {
        position: sticky;
        right: 0;
        z-index: 4;
        min-width: 138px;
        box-shadow: -1px 0 0 var(--border-color);
    }

    .master-board-table thead .board-actions-cell {
        z-index: 7;
        background: #eef2ff;
    }

    .board-cell-truncate {
        display: inline-block;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
    }

    .board-money {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        color: #334155;
    }

    .indicator-dot {
        width: 12px;
        height: 12px;
        display: inline-block;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.06);
    }

    .indicator-green { background: #10b981; }
    .indicator-yellow { background: #facc15; }
    .indicator-orange { background: #f97316; }
    .indicator-red { background: #ef4444; }
    .indicator-blue { background: #3b82f6; }
    .indicator-gray { background: #94a3b8; }
    .indicator-light { background: #cbd5e1; }

    .board-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1rem;
        color: var(--gray-500);
        font-size: 0.75rem;
    }

    .board-legend span {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .board-filter-card .form-label {
        font-size: 0.64rem;
        font-weight: 700;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.2rem;
    }

    .board-filter-card .card-header {
        padding-top: 0.55rem;
        padding-bottom: 0.55rem;
    }

    .board-filter-card .card-body {
        padding: 0.75rem;
    }

    .board-filter-card .form-control,
    .board-filter-card .form-select {
        min-height: 32px;
        padding-top: 0.3rem;
        padding-bottom: 0.3rem;
        font-size: 0.8rem;
    }

    @media (max-width: 991.98px) {
        .master-board-scroll {
            max-height: 68vh;
        }

        .master-board-table .board-task-cell {
            position: static;
            box-shadow: none;
        }

        .master-board-table {
            min-width: 1200px;
        }
    }
</style>
@endpush

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('operations.master-board.export', ['format' => 'xlsx'] + request()->query()) }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel me-1"></i> XLSX</a>
    <a href="{{ route('operations.master-board.export', ['format' => 'csv'] + request()->query()) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-csv me-1"></i> CSV</a>
    <a href="{{ route('operations.master-board.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print</a>
</div>
@endsection

@section('content')
<div class="row g-2 mb-3">
    @foreach([
        ['Total Tasks', $kpis['total_tasks']],
        ['Active Tasks', $kpis['active_tasks']],
        ['Operationally Completed', $kpis['operationally_completed']],
        ['Collection Pending', 'INR '.number_format($kpis['collection_pending_amount'], 2)],
        ['Vendor Pending', 'INR '.number_format($kpis['vendor_pending_amount'], 2)],
        ['Closed Tasks', $kpis['closed_tasks']],
        ['Revenue', 'INR '.number_format($kpis['revenue'], 2)],
        ['Expected Profit', 'INR '.number_format($kpis['expected_profit'], 2)],
    ] as [$label, $value])
        <div class="col-6 col-lg-3 col-xxl-2">
            <div class="master-board-kpi h-100">
                <div class="card-body">
                    <div class="kpi-label">{{ $label }}</div>
                    <div class="kpi-value">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3 board-filter-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Advanced Filters</strong>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#columnManager">Columns</button>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#savedFilters">Saved Filters</button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label">Global Search</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Task, client, contact, employee"></div>
            <div class="col-md-2"><label class="form-label">Task No</label><input name="task_no" value="{{ request('task_no') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Client Name</label><input name="client_name" value="{{ request('client_name') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Employee</label><select name="assigned_to" class="form-select"><option value="">All</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Task Type</label><select name="task_type_id" class="form-select"><option value="">All</option>@foreach($taskTypes as $type)<option value="{{ $type->id }}" {{ request('task_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="">All</option>@foreach(\App\Models\Task::getPriorities() as $key => $label)<option value="{{ $key }}" {{ request('priority') === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>

            <div class="col-md-2"><label class="form-label">Task Status</label><select name="task_status" class="form-select"><option value="">All</option>@foreach($taskStatuses as $status)<option value="{{ $status->slug }}" {{ request('task_status') === $status->slug ? 'selected' : '' }}>{{ $status->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Business Status</label><select name="business_status_id" class="form-select"><option value="">All</option>@foreach($businessStatuses as $status)<option value="{{ $status->id }}" {{ request('business_status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Operational</label><select name="operational_status" class="form-select"><option value="">All</option>@foreach([\App\Models\Task::OPERATIONAL_PENDING, \App\Models\Task::OPERATIONAL_IN_PROGRESS, \App\Models\Task::OPERATIONAL_BOOKING_IN_PROCESS, \App\Models\Task::OPERATIONAL_COMPLETED] as $status)<option value="{{ $status }}" {{ request('operational_status') === $status ? 'selected' : '' }}>{{ Str::headline($status) }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Financial</label><select name="financial_status" class="form-select"><option value="">All</option>@foreach([\App\Models\Task::FINANCIAL_UNPAID, \App\Models\Task::FINANCIAL_PARTIAL, \App\Models\Task::FINANCIAL_PENDING_BALANCE, \App\Models\Task::FINANCIAL_VENDOR_PENDING, \App\Models\Task::FINANCIAL_FULLY_PAID, \App\Models\Task::FINANCIAL_REFUND_PENDING, \App\Models\Task::FINANCIAL_REFUNDED] as $status)<option value="{{ $status }}" {{ request('financial_status') === $status ? 'selected' : '' }}>{{ Str::headline($status) }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Department</label><select name="current_department" class="form-select"><option value="">All</option>@foreach([\App\Models\Task::DEPARTMENT_SALES, \App\Models\Task::DEPARTMENT_OPERATIONS, \App\Models\Task::DEPARTMENT_FINANCE, \App\Models\Task::DEPARTMENT_MANAGEMENT] as $dept)<option value="{{ $dept }}" {{ request('current_department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>@endforeach</select></div>
            <div class="col-md-1"><button class="btn btn-sm btn-primary w-100">Apply</button></div>

            <div class="col-md-2"><label class="form-label">Created From</label><input type="date" name="created_from" value="{{ request('created_from') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Created To</label><input type="date" name="created_to" value="{{ request('created_to') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Updated From</label><input type="date" name="updated_from" value="{{ request('updated_from') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Updated To</label><input type="date" name="updated_to" value="{{ request('updated_to') }}" class="form-control"></div>
            @foreach($columns as $column)
                <input type="hidden" name="columns[]" value="{{ $column }}">
            @endforeach
        </form>

        <div class="collapse mt-3" id="savedFilters">
            <div class="border rounded p-3">
                <form action="{{ route('operations.master-board.filters.store') }}" method="POST" class="d-flex gap-2 mb-3">
                    @csrf
                    @foreach(request()->except(['name', '_token']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input name="name" class="form-control form-control-sm" placeholder="Save current view as..." required>
                    <button class="btn btn-sm btn-primary">Save Filter</button>
                </form>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($savedFilters as $filter)
                        <a href="{{ route('operations.master-board.index', $filter->filters) }}" class="btn btn-sm btn-outline-secondary">{{ $filter->name }}</a>
                    @empty
                        <span class="text-muted small">No saved filters yet.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="collapse mt-3" id="columnManager">
            <form action="{{ route('operations.master-board.columns.store') }}" method="POST" class="border rounded p-3">
                @csrf
                <div class="row g-2">
                    @foreach($allColumns as $column)
                        <div class="col-md-3 col-sm-6">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $column }}" {{ in_array($column, $columns, true) ? 'checked' : '' }}>
                                <span class="form-check-label">{{ $columnLabels[$column] }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-sm btn-primary mt-3">Save Columns</button>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm master-board-table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <strong>Operations Board</strong>
            <div class="text-muted small">One row per task. Financial values are calculated live.</div>
        </div>
        <div class="board-legend">
            <span><i class="indicator-dot indicator-green"></i>Closed</span>
            <span><i class="indicator-dot indicator-yellow"></i>Collection Pending</span>
            <span><i class="indicator-dot indicator-orange"></i>Vendor Pending</span>
            <span><i class="indicator-dot indicator-red"></i>Overdue</span>
            <span><i class="indicator-dot indicator-blue"></i>Awaiting Finance</span>
            <span><i class="indicator-dot indicator-gray"></i>Cancelled</span>
        </div>
    </div>
    <div class="master-board-scroll">
        <table class="table table-sm table-hover align-middle mb-0 master-board-table">
            <thead class="table-light">
                <tr>
                    <th class="board-indicator-cell"></th>
                    @foreach($columns as $column)
                        <th class="{{ $column === 'task_no' ? 'board-task-cell' : '' }}">{{ $columnLabels[$column] }}</th>
                    @endforeach
                    <th class="board-actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="board-indicator-cell"><span class="indicator-dot indicator-{{ $row['_indicator'] }}"></span></td>
                        @foreach($columns as $column)
                            <td class="{{ in_array($column, ['sale_amount','total_received','pending_collection','purchase_amount','vendor_paid','vendor_pending','expected_profit'], true) ? 'text-end board-money' : '' }} {{ $column === 'task_no' ? 'board-task-cell fw-bold text-primary' : '' }}">
                                @if(in_array($column, ['sale_amount','total_received','pending_collection','purchase_amount','vendor_paid','vendor_pending','expected_profit'], true))
                                    INR {{ number_format($row[$column], 2) }}
                                @else
                                    <span class="board-cell-truncate" title="{{ $row[$column] }}">{{ $row[$column] }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-nowrap board-actions-cell">
                            <a href="{{ route('tasks.show', $row['_task_id']) }}" class="btn btn-sm btn-outline-primary">Task</a>
                            <a href="{{ route('tasks.show', $row['_task_id']) }}#activity" class="btn btn-sm btn-outline-secondary">Timeline</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 2 }}" class="text-center text-muted py-4">No tasks match this board view.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
        <div class="card-footer bg-white">{{ $tasks->links() }}</div>
    @endif
</div>
@endsection
