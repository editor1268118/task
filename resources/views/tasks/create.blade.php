@extends('layouts.admin')

@section('title', 'Create Task')
@section('page-header', 'Create Task')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<form action="{{ route('tasks.store') }}" method="POST" data-loading>
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4 h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 text-primary"><i class="fas fa-info-circle me-2"></i>Task Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="What needs to be done?">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="8" placeholder="Provide detailed instructions...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks / Additional Notes</label>
                        <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label for="customer_id" class="form-label"><i class="fas fa-address-book me-1 text-muted"></i>Customer Master</label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                            <option value="">Create/link from client details</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-name="{{ $customer->company_name ?: $customer->contact_person }}" data-mobile="{{ $customer->mobile }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->customer_code }} - {{ $customer->company_name ?: $customer->contact_person }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="client_name" class="form-label"><i class="fas fa-user-tie me-1 text-muted"></i>Client Name</label>
                        <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name') }}" placeholder="Client name">
                        @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_contact" class="form-label"><i class="fas fa-phone me-1 text-muted"></i>Client Contact</label>
                            <input type="text" class="form-control @error('client_contact') is-invalid @enderror" id="client_contact" name="client_contact" value="{{ old('client_contact') }}" placeholder="Phone / Email">
                            @error('client_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="additional_info" class="form-label"><i class="fas fa-info-circle me-1 text-muted"></i>Additional Info</label>
                        <textarea class="form-control @error('additional_info') is-invalid @enderror" id="additional_info" name="additional_info" rows="2" placeholder="Any additional details...">{{ old('additional_info') }}</textarea>
                        @error('additional_info')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 text-primary"><i class="fas fa-cogs me-2"></i>Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Task Number</label>
                        <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ $taskNo }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="task_type_id" class="form-label"><i class="fas fa-tag me-1 text-muted"></i>Task Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('task_type_id') is-invalid @enderror" id="task_type_id" name="task_type_id" required>
                            <option value="">Select Task Type</option>
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->id }}" {{ old('task_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Determines required completion forms.</small>
                        @error('task_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @if(auth()->user()->hasRole('super-admin'))
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                <option value="">Select Department (Optional)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">If empty, will inherit from assigned user.</small>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to" required>
                            <option value="">Select Employee</option>
                            @foreach($assignees as $assignee)
                                <option value="{{ $assignee->id }}" data-department-id="{{ $assignee->department_id }}" {{ old('assigned_to') == $assignee->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                        @if(auth()->user()->hasRole('super-admin'))
                            <small class="text-muted">Select a department first to show its employees.</small>
                        @endif
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}" required min="{{ date('Y-m-d') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" step="0.5" class="form-control @error('estimated_hours') is-invalid @enderror" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours') }}">
                        @error('estimated_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-5 d-flex justify-content-end gap-2">
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create Task</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect = document.getElementById('department_id');
        const assigneeSelect = document.getElementById('assigned_to');

        if (!departmentSelect || !assigneeSelect) {
            return;
        }

        const options = Array.from(assigneeSelect.options).map(function (option) {
            return {
                value: option.value,
                text: option.text,
                departmentId: option.dataset.departmentId || '',
                selected: option.selected,
            };
        });

        function syncAssignees() {
            const departmentId = departmentSelect.value;
            const selectedValue = assigneeSelect.value;
            assigneeSelect.innerHTML = '';
            assigneeSelect.add(new Option(departmentId ? 'Select Employee' : 'Select Department First', ''));

            options
                .filter(function (option) {
                    return option.value && departmentId && option.departmentId === departmentId;
                })
                .forEach(function (option) {
                    const employeeOption = new Option(option.text, option.value);
                    employeeOption.dataset.departmentId = option.departmentId;
                    assigneeSelect.add(employeeOption);
                });

            if (selectedValue && Array.from(assigneeSelect.options).some(function (option) { return option.value === selectedValue; })) {
                assigneeSelect.value = selectedValue;
            }
        }

        if (!departmentSelect.value) {
            const selectedAssignee = options.find(function (option) { return option.selected && option.value; });
            if (selectedAssignee) {
                departmentSelect.value = selectedAssignee.departmentId;
            }
        }

        departmentSelect.addEventListener('change', syncAssignees);
        syncAssignees();

        const customerSelect = document.getElementById('customer_id');
        if (customerSelect) {
            customerSelect.addEventListener('change', function () {
                const option = customerSelect.selectedOptions[0];
                if (!option || !option.value) return;
                document.getElementById('client_name').value = option.dataset.name || '';
                document.getElementById('client_contact').value = option.dataset.mobile || '';
            });
        }
    });
</script>
@endpush
