@extends('layouts.admin')

@section('title', $task->task_no . ' - ' . $task->title)
@section('page-header', $task->task_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active">{{ $task->task_no }}</li>
@endsection

@push('styles')
<style>
    /* Timeline styles */
    .timeline {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0.75rem;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        top: 0.25rem;
        left: -2rem;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #0d6efd;
        z-index: 1;
    }
    .timeline-item.comment::before { border-color: #17a2b8; }
    .timeline-item.status::before { border-color: #28a745; }
    .timeline-item.attachment::before { border-color: #ffc107; }
</style>
@endpush

@section('content')
<div class="row g-4 mb-5">
    <!-- Left Column (Main Content) -->
    <div class="col-lg-8">
        
        <!-- Task Header & Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h3 class="mb-0 fw-bold">{{ $task->title }}</h3>
                    @can('update', $task)
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    @endcan
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @switch($task->status)
                        @case('pending') <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-clock me-1"></i>Pending</span> @break
                        @case('assigned') <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-user-check me-1"></i>Assigned</span> @break
                        @case('in_progress') <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-spinner fa-spin me-1"></i>In Progress</span> @break
                        @case('completion_pending') <span class="badge bg-purple px-3 py-2" style="background: #667eea;"><i class="fas fa-hourglass-half me-1"></i>Completion Pending</span> @break
                        @case('forms_submitted') <span class="badge px-3 py-2" style="background: #17a2b8;"><i class="fas fa-clipboard-check me-1"></i>Forms Submitted</span> @break
                        @case('completed') <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i>Completed</span> @break
                        @case('operationally_completed') <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-check me-1"></i>Operationally Completed</span> @break
                        @case('collection_pending') <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-wallet me-1"></i>Collection Pending</span> @break
                        @case('vendor_payment_pending') <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-file-invoice-dollar me-1"></i>Vendor Payment Pending</span> @break
                        @case('finance_review_pending') <span class="badge px-3 py-2" style="background:#6f42c1;"><i class="fas fa-user-check me-1"></i>Finance Review Pending</span> @break
                        @case('closed') <span class="badge bg-success px-3 py-2"><i class="fas fa-lock me-1"></i>Closed</span> @break
                        @case('on_hold') <span class="badge bg-secondary px-3 py-2"><i class="fas fa-pause me-1"></i>On Hold</span> @break
                        @case('cancelled') <span class="badge bg-danger px-3 py-2"><i class="fas fa-times me-1"></i>Cancelled</span> @break
                        @case('follow_up') <span class="badge bg-primary text-white px-3 py-2"><i class="fas fa-reply me-1"></i>Follow up</span> @break
                    @endswitch

                    @switch($task->priority)
                        @case('high') <span class="badge border border-danger text-danger px-3 py-2"><i class="fas fa-arrow-up me-1"></i>High Priority</span> @break
                        @case('medium') <span class="badge border border-warning text-warning px-3 py-2"><i class="fas fa-minus me-1"></i>Medium Priority</span> @break
                        @case('low') <span class="badge border border-info text-info px-3 py-2"><i class="fas fa-arrow-down me-1"></i>Low Priority</span> @break
                    @endswitch
                    
                    @if($task->department)
                        <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-building me-1"></i>{{ $task->department->name }}</span>
                    @endif
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Description</h6>
                    <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $task->description ?? 'No description provided.' }}</div>
                </div>

                @if($task->remarks)
                    <div class="mb-0">
                        <h6 class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Remarks</h6>
                        <div class="text-muted fst-italic">{{ $task->remarks }}</div>
                    </div>
                @endif
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <span class="badge bg-light text-dark border px-3 py-2">Operations: {{ Str::headline($task->operational_status ?? 'pending') }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Finance: {{ Str::headline($task->financial_status ?? 'unpaid') }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Final: {{ Str::headline($task->final_status ?? 'active') }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2">Department: {{ $task->current_department ?? 'Sales' }}</span>
                </div>
            </div>
        </div>

        @if($task->customer)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-primary"><i class="fas fa-address-book me-2"></i>Customer CRM</h6>
                    <a href="{{ route('crm.customers.show', $task->customer) }}" class="btn btn-sm btn-outline-primary">Open 360</a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><small class="text-muted">Customer Code</small><div class="fw-bold">{{ $task->customer->customer_code }}</div></div>
                        <div class="col-md-3"><small class="text-muted">Customer</small><div class="fw-bold">{{ $task->customer->company_name ?? $task->customer->contact_person }}</div></div>
                        <div class="col-md-3"><small class="text-muted">Mobile</small><div class="fw-bold">{{ $task->customer->mobile ?? '-' }}</div></div>
                        <div class="col-md-3"><small class="text-muted">Type</small><div class="fw-bold">{{ $task->customer->customer_type }}</div></div>
                    </div>

                    @can('addInteraction', $task->customer)
                        <div class="row g-3">
                            <div class="col-lg-12">
                                <form action="{{ route('crm.customers.interactions.store', $task->customer) }}" method="POST" class="border rounded p-3 h-100">
                                    @csrf
                                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                                    <h6 class="fw-bold">Quick Add Interaction</h6>
                                    <select name="interaction_type" class="form-select form-select-sm mb-2" required>
                                        @foreach(\App\Models\CustomerInteraction::TYPES as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Interaction notes"></textarea>
                                    <button class="btn btn-sm btn-success w-100">Save Interaction</button>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div class="row g-3 mt-2">
                        <div class="col-md-12">
                            <h6 class="fw-bold">Customer Timeline</h6>
                            @forelse($task->customer->interactions->take(5) as $interaction)
                                <div class="border-bottom py-2 small"><strong>{{ $interaction->interaction_type }}</strong> {{ $interaction->interaction_date->format('d M Y') }}<br>{{ Str::limit($interaction->notes, 80) }}</div>
                            @empty
                                <p class="text-muted small mb-0">No customer interactions yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-route me-2"></i>Operational Summary</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 rounded bg-light h-100">
                            <small class="text-muted">Booking Status</small>
                            <div class="fw-bold">{{ Str::headline($task->booking?->booking_status ?? $task->operational_status ?? 'pending') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded bg-light h-100">
                            <small class="text-muted">Task Status</small>
                            <div class="fw-bold">{{ $task->taskStatus?->name ?? Str::headline($task->status) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded bg-light h-100">
                            <small class="text-muted">Business Status</small>
                            <div class="fw-bold">{{ $task->businessStatus?->name ?? 'Not Set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded bg-light h-100">
                            <small class="text-muted">Current Department</small>
                            <div class="fw-bold">{{ $task->current_department ?? 'Sales' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary"><i class="fas fa-indian-rupee-sign me-2"></i>Finance Ledger</h6>
                @if($task->booking)
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark border">{{ $task->booking->booking_type }}</span>
                        <span class="badge bg-light text-dark border">{{ Str::headline($task->booking->booking_status ?? $task->booking->operational_status) }}</span>
                    </div>
                @endif
            </div>
            <div class="card-body p-4">
                @if($task->booking)
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><div class="p-3 rounded bg-light h-100"><small class="text-muted">Task Number</small><div class="fw-bold">{{ $task->task_no }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light h-100"><small class="text-muted">Client Name</small><div class="fw-bold">{{ $task->booking?->client_name ?? $task->client_name ?? '-' }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light h-100"><small class="text-muted">Task Type</small><div class="fw-bold">{{ $task->taskType?->display_name ?? $task->taskType?->name ?? '-' }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light h-100"><small class="text-muted">Finance Status</small><div class="fw-bold">{{ Str::headline($task->financial_status) }}</div></div></div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Sale Amount</small><div class="fw-bold">INR {{ number_format($financialSummary['sale_amount'], 2) }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Total Received</small><div class="fw-bold text-success">INR {{ number_format($financialSummary['received'], 2) }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Collection Pending</small><div class="fw-bold {{ $financialSummary['pending_balance'] > 0 ? 'text-danger' : 'text-success' }}">INR {{ number_format($financialSummary['pending_balance'], 2) }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Expected Profit</small><div class="fw-bold">INR {{ number_format($financialSummary['expected_profit'], 2) }}</div></div></div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1"><span>Collection Progress</span><strong>{{ $financialSummary['collection_percentage'] }}%</strong></div>
                        <div class="progress" style="height:10px;"><div class="progress-bar bg-success" style="width:{{ $financialSummary['collection_percentage'] }}%"></div></div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Purchase Amount</small><div class="fw-bold">INR {{ number_format($financialSummary['purchase_amount'], 2) }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Vendor Paid</small><div class="fw-bold text-success">INR {{ number_format($financialSummary['vendor_paid'], 2) }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Vendor Pending</small><div class="fw-bold text-danger">INR {{ number_format($financialSummary['vendor_pending'], 2) }}</div></div></div>
                        <div class="col-md-3"><div class="p-3 rounded bg-light"><small class="text-muted">Ledger Entries</small><div class="fw-bold">{{ $financeLedger->count() }}</div></div></div>
                    </div>
                    @can('updateStatus', $task)
                        @php
                            $receiptClientTypes = \App\Models\CustomerReceipt::CLIENT_TYPES;
                            $receiptPaymentModes = \App\Models\CustomerReceipt::PAYMENT_MODES;
                            $vendorOptions = \App\Models\VendorPayment::VENDOR_OPTIONS;
                            $vendorPaymentModes = \App\Models\VendorPayment::PAYMENT_MODES;
                        @endphp
                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <form action="{{ route('tasks.receipts.store', $task) }}" method="POST" class="border rounded p-3">
                                    @csrf
                                    <h6 class="fw-bold mb-3">Record Receipt</h6>
                                    <label class="form-label small">Client Type <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm mb-2 finance-other-toggle" name="client_type" data-other-target="receiptClientTypeOther" required>
                                        <option value="">Select client type</option>
                                        @foreach($receiptClientTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <input id="receiptClientTypeOther" class="form-control form-control-sm mb-2 d-none" name="custom_client_type" placeholder="Enter client type">
                                    <label class="form-label small">Client / Company Name <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm mb-2" name="client_company_name" placeholder="Client / company name" required>
                                    <label class="form-label small">Contact No. <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm mb-2" name="contact_no" placeholder="Contact no." required>
                                    <label class="form-label small">Date Of Receipt <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm mb-2" type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                                    <label class="form-label small">Payment Mode <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm mb-2 finance-other-toggle" name="payment_mode" data-other-target="receiptPaymentModeOther" required>
                                        <option value="">Select payment mode</option>
                                        @foreach($receiptPaymentModes as $mode)
                                            <option value="{{ $mode }}">{{ $mode }}</option>
                                        @endforeach
                                    </select>
                                    <input id="receiptPaymentModeOther" class="form-control form-control-sm mb-2 d-none" name="custom_payment_mode" placeholder="Enter payment mode">
                                    <label class="form-label small">Amount Received <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text">INR</span>
                                        <input class="form-control" type="number" step="0.01" min="0.01" name="amount_received" placeholder="0.00" required>
                                    </div>
                                    <label class="form-label small">Comments</label>
                                    <textarea class="form-control form-control-sm mb-2" name="remarks" placeholder="Comments"></textarea>
                                    <button class="btn btn-sm btn-success w-100">Record Receipt</button>
                                </form>
                            </div>
                            <div class="col-lg-6">
                                <form action="{{ route('tasks.vendor-payments.store', $task) }}" method="POST" class="border rounded p-3">
                                    @csrf
                                    <h6 class="fw-bold mb-3">Record Vendor Payment</h6>
                                    <label class="form-label small">Vendor Name <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm mb-2 finance-other-toggle" name="vendor_name" data-other-target="vendorNameOther" required>
                                        <option value="">Select vendor</option>
                                        @foreach($vendorOptions as $vendor)
                                            <option value="{{ $vendor }}">{{ $vendor }}</option>
                                        @endforeach
                                    </select>
                                    <input id="vendorNameOther" class="form-control form-control-sm mb-2 d-none" name="custom_vendor_name" placeholder="Enter vendor name">
                                    <label class="form-label small">Vendor Account No.</label>
                                    <input class="form-control form-control-sm mb-2" name="vendor_account_no" placeholder="Vendor account no.">
                                    <label class="form-label small">Payable Amount <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text">INR</span>
                                        <input class="form-control" type="number" step="0.01" min="0.01" name="amount_paid" placeholder="0.00" required>
                                    </div>
                                    <label class="form-label small">Payment Mode <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm mb-2 finance-other-toggle" name="payment_mode" data-other-target="vendorPaymentModeOther" required>
                                        <option value="">Select payment mode</option>
                                        @foreach($vendorPaymentModes as $mode)
                                            <option value="{{ $mode }}">{{ $mode }}</option>
                                        @endforeach
                                    </select>
                                    <input id="vendorPaymentModeOther" class="form-control form-control-sm mb-2 d-none" name="custom_payment_mode" placeholder="Enter payment mode">
                                    <label class="form-label small">Payment Date <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm mb-2" type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                                    <label class="form-label small">Payment Comments</label>
                                    <textarea class="form-control form-control-sm mb-2" name="remarks" placeholder="Payment comments"></textarea>
                                    <button class="btn btn-sm btn-primary w-100">Record Vendor Payment</button>
                                </form>
                            </div>
                        </div>
                    @endcan
                    <div class="border rounded overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Transaction Type</th>
                                        <th>Reference Number</th>
                                        <th>Party</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                        <th>Entered By</th>
                                        <th>Created At</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($financeLedger as $entry)
                                        <tr>
                                            <td>{{ $entry['date']?->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $entry['transaction_type'] }}</td>
                                            <td><span class="fw-semibold">{{ $entry['reference_no'] }}</span></td>
                                            <td>{{ $entry['party'] }}</td>
                                            <td class="text-end">INR {{ number_format($entry['amount'], 2) }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ Str::headline($entry['status']) }}</span></td>
                                            <td>{{ $entry['entered_by'] }}</td>
                                            <td>{{ $entry['created_at']?->timezone(config('app.display_timezone'))->format('d M Y h:i A') ?? '-' }}</td>
                                            <td class="text-end">
                                                @if(auth()->user()->hasRole('super-admin') && !in_array($entry['status'], ['approved', 'received', 'paid'], true))
                                                    @if($entry['transaction_type'] === 'Receipt')
                                                        <form action="{{ route('finance.receipts.approve', $entry['model']) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-success">Approve</button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('finance.vendor-payments.approve', $entry['model']) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-success">Approve</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted py-4">No finance transactions recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        @can('approveFinance', $task)
                            @if(!$task->finance_approved_at && $task->final_status !== App\Models\Task::FINAL_CLOSED)
                                <form action="{{ route('tasks.finance.approve', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Finance Approve</button>
                                </form>
                            @endif
                        @endcan

                        @can('approveManagement', $task)
                            @if($task->finance_approved_at && $task->final_status !== App\Models\Task::FINAL_CLOSED)
                                <form action="{{ route('tasks.management.close', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-primary"><i class="fas fa-lock me-1"></i> Management Approve & Close</button>
                                </form>
                            @endif
                        @endcan
                    </div>
                @else
                    <p class="text-muted mb-0">Submit the Hotel & Tour Package form to create the booking master record and activate financial tracking.</p>
                @endif
            </div>
        </div>

        <!-- Comments / Discussion -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-comments me-2"></i>Discussion</h6>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    @foreach($task->comments as $comment)
                        <div class="timeline-item comment">
                            <div class="d-flex justify-content-between mb-1">
                                <div class="fw-semibold">{{ $comment->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;" data-bs-toggle="tooltip" title="{{ $comment->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}">
                                    {{ $comment->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="p-3 bg-light rounded text-dark">
                                {!! nl2br(e($comment->comment)) !!}
                            </div>
                            @if(auth()->user()->hasRole('super-admin') || auth()->id() === $comment->user_id)
                                <div class="mt-1 text-end">
                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0" style="font-size: 0.75rem; text-decoration: none;" onclick="return confirm('Delete this comment?')">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @can('comment', $task)
                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST" data-loading>
                        @csrf
                        <div class="mb-3">
                            <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" rows="3" placeholder="Add a comment..." required></textarea>
                            @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Post Comment</button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-history me-2"></i>Activity Timeline</h6>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    @foreach($activities as $activity)
                        <div class="timeline-item status">
                            <div class="text-muted mb-1" style="font-size: 0.75rem;">
                                {{ $activity->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}
                            </div>
                            <div>
                                <span class="fw-semibold text-dark">{{ $activity->causer?->name ?? 'System' }}</span>
                                {{ $activity->description }}
                            </div>
                            @if($activity->properties->has('attributes'))
                                <div class="mt-2 p-2 bg-light rounded" style="font-size: 0.8125rem;">
                                    <ul class="mb-0 ps-3">
                                        @php
                                            $taskStatuses = \App\Models\TaskStatus::pluck('name', 'id')->toArray();
                                            $businessStatuses = \App\Models\BusinessStatus::pluck('name', 'id')->toArray();
                                            $users = \App\Models\User::pluck('name', 'id')->toArray();
                                        @endphp
                                        @foreach($activity->properties['attributes'] as $key => $value)
                                            @php
                                                $displayValue = $value;
                                                $displayOldValue = $activity->properties['old'][$key] ?? null;

                                                if ($key === 'task_status_id') {
                                                    $displayValue = $taskStatuses[$value] ?? $value;
                                                    if ($displayOldValue) $displayOldValue = $taskStatuses[$displayOldValue] ?? $displayOldValue;
                                                } elseif ($key === 'business_status_id') {
                                                    $displayValue = $businessStatuses[$value] ?? $value;
                                                    if ($displayOldValue) $displayOldValue = $businessStatuses[$displayOldValue] ?? $displayOldValue;
                                                } elseif (in_array($key, ['assigned_to', 'assigned_by'])) {
                                                    $displayValue = $users[$value] ?? $value;
                                                    if ($displayOldValue) $displayOldValue = $users[$displayOldValue] ?? $displayOldValue;
                                                }
                                            @endphp
                                            <li>
                                                <strong>{{ Str::headline(str_replace('_id', '', $key)) }}:</strong> 
                                                @if(isset($displayOldValue))
                                                    <span class="text-danger text-decoration-line-through">{{ $displayOldValue }}</span>
                                                    <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                @endif
                                                <span class="text-success">{{ $displayValue }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column (Sidebar Details) -->
    <div class="col-lg-4">

        {{-- ─── Completion Workflow Card ──────────────────────────── --}}
        @can('updateStatus', $task)
            @if($task->hasCompletionWorkflow() || ($task->canStartCompletion() && auth()->user()->hasRole('super-admin')))
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h6 class="mb-0 text-white"><i class="fas fa-clipboard-list me-2"></i>Completion Workflow</h6>
                    </div>
                    <div class="card-body">
                        @if(!$task->hasCompletionWorkflow())
                            <div class="alert alert-warning mb-0">
                                <div class="fw-bold mb-1"><i class="fas fa-triangle-exclamation me-1"></i>Task type has no operational form mapped.</div>
                                Map the Hotel & Tour Package Form under <a href="{{ route('admin.task-types.edit', $task->taskType) }}">Administration &gt; Task Types</a> to enable completion.
                            </div>
                        @elseif($task->canStartCompletion())
                            {{-- Can start completion --}}
                            <p class="text-muted mb-3" style="font-size: 0.85rem;">
                                Submit the operational booking form to finish execution. Settlement and final closure remain separate.
                            </p>
                            <form action="{{ route('tasks.completion.start', $task) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 fw-bold" onclick="return confirm('Start the completion process? Task status will change to Completion Pending.')">
                                    <i class="fas fa-play-circle me-1"></i> Start Completion Process
                                </button>
                            </form>
                        @elseif($task->isInCompletionProcess())
                            {{-- In progress — show form statuses --}}
                            <div class="mb-3">
                                @foreach($formSummary as $slug => $info)
                                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <span style="font-size: 0.85rem;">{{ $info['name'] }}</span>
                                        @if($info['status'] === 'submitted')
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Done</span>
                                        @elseif($info['status'] === 'draft')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-pencil-alt me-1"></i>Draft</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Pending</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('tasks.completion.wizard', $task) }}" class="btn btn-primary w-100 fw-bold">
                                <i class="fas fa-arrow-right me-1"></i> Continue Completion
                            </a>
                            @if($task->areAllFormsSubmitted())
                                <a href="{{ route('tasks.completion.review', $task) }}" class="btn btn-success w-100 fw-bold mt-2">
                                    <i class="fas fa-flag-checkered me-1"></i> Review Operations
                                </a>
                            @endif
                        @elseif($task->operational_status === App\Models\Task::OPERATIONAL_COMPLETED && !empty($formSummary))
                            {{-- Completed — show summary --}}
                            <div class="text-center mb-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="text-success fw-bold mb-0">Operations Submitted</p>
                            </div>
                            @foreach($formSummary as $slug => $info)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <span style="font-size: 0.85rem;">{{ $info['name'] }}</span>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ $info['submitted_at'] ?? 'Done' }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        @endcan
        
        <!-- Status Updater (If allowed) -->
        @can('updateStatus', $task)
            @if($task->operational_status !== App\Models\Task::OPERATIONAL_COMPLETED && $task->final_status !== App\Models\Task::FINAL_CLOSED)
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body">
                    <h6 class="mb-3 text-white"><i class="fas fa-tasks me-2"></i>Update Progress</h6>
                    <form action="{{ route('tasks.status.update', $task) }}" method="POST" data-loading>
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label text-white-50" style="font-size: 0.875rem;">Status</label>
                            <select name="status" class="form-select form-select-sm" required>
                                @foreach(App\Models\Task::getStatuses() as $key => $label)
                                    @if(!in_array($key, ['completion_pending', 'forms_submitted', 'operationally_completed', 'collection_pending', 'vendor_payment_pending', 'finance_review_pending', 'closed', 'completed']))
                                        <option value="{{ $key }}" {{ $task->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @if($task->hasCompletionWorkflow())
                                <small class="text-white-50 mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Operations, settlement, and finance closure are tracked independently.</small>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 d-flex justify-content-between" style="font-size: 0.875rem;">
                                <span>Completion</span>
                                <span id="progressValue">{{ $task->completion_percentage }}%</span>
                            </label>
                            <input type="range" class="form-range" name="completion_percentage" id="progressRange" min="0" max="100" value="{{ $task->completion_percentage }}" oninput="document.getElementById('progressValue').innerText = this.value + '%'">
                        </div>
                        <button type="submit" class="btn btn-light btn-sm w-100 fw-bold">Update Status</button>
                    </form>
                </div>
            </div>
            @endif
        @endcan


        <!-- Quick Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-users me-2"></i>Assignment Info</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted"><i class="fas fa-user-check me-2"></i>Assigned To</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                            @if($task->assignee?->profile_photo)
                                <img src="{{ Storage::url($task->assignee->profile_photo) }}" class="rounded-circle" width="24" height="24" alt="">
                            @endif
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted"><i class="fas fa-user-plus me-2"></i>Assigned By</span>
                        <span class="fw-medium">{{ $task->assigner?->name ?? 'System' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted"><i class="fas fa-play me-2"></i>Start Date</span>
                        <span class="fw-medium">{{ $task->start_date ? $task->start_date->format('M d, Y') : 'Not Set' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted"><i class="fas fa-flag-checkered me-2"></i>Due Date</span>
                        <span class="fw-medium {{ $task->isOverdue() ? 'text-danger' : '' }}">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'Not Set' }}</span>
                    </li>
                    @if($task->estimated_hours)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted"><i class="fas fa-clock me-2"></i>Est. Hours</span>
                            <span class="fw-medium">{{ $task->estimated_hours }} hrs</span>
                        </li>
                    @endif
                    @if($task->actual_hours)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted"><i class="fas fa-hourglass-end me-2"></i>Actual Hours</span>
                            <span class="fw-medium">{{ $task->actual_hours }} hrs</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Attachments -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-paperclip me-2"></i>Attachments ({{ $task->attachments->count() }})</h6>
            </div>
            <div class="card-body">
                @if($task->attachments->count() > 0)
                    <div class="d-flex flex-column gap-2 mb-3">
                        @foreach($task->attachments as $attachment)
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    @if(in_array(strtolower($attachment->file_type), ['pdf']))
                                        <i class="fas fa-file-pdf text-danger fa-lg"></i>
                                    @elseif(in_array(strtolower($attachment->file_type), ['jpg','jpeg','png']))
                                        <i class="fas fa-file-image text-success fa-lg"></i>
                                    @elseif(in_array(strtolower($attachment->file_type), ['doc','docx']))
                                        <i class="fas fa-file-word text-primary fa-lg"></i>
                                    @elseif(in_array(strtolower($attachment->file_type), ['xls','xlsx']))
                                        <i class="fas fa-file-excel text-success fa-lg"></i>
                                    @else
                                        <i class="fas fa-file text-secondary fa-lg"></i>
                                    @endif
                                    <div class="text-truncate">
                                        <div class="fw-medium" style="font-size: 0.8125rem;">{{ $attachment->file_name }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $attachment->formatted_size }} &bull; {{ $attachment->uploader->name }}</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-link text-primary p-1" data-bs-toggle="tooltip" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if(auth()->user()->hasRole('super-admin') || auth()->id() === $attachment->uploaded_by)
                                        <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-1" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Delete this attachment?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-3" style="font-size: 0.875rem;">No attachments uploaded yet.</p>
                @endif

                @can('attach', $task)
                    <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data" data-loading>
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
                            <button class="btn btn-outline-primary" type="submit">Upload</button>
                        </div>
                        @error('file')<div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>@enderror
                    </form>
                @endcan
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.finance-other-toggle').forEach(function (select) {
        function syncOtherField() {
            const target = document.getElementById(select.dataset.otherTarget);
            if (!target) return;
            const isOther = select.value === 'Other';
            target.classList.toggle('d-none', !isOther);
            target.required = isOther;
            if (!isOther) {
                target.value = '';
            }
        }

        select.addEventListener('change', syncOtherField);
        syncOtherField();
    });
</script>
@endpush
