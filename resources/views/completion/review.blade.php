@extends('layouts.admin')

@section('title', 'Operational Review - ' . $task->task_no)
@section('page-header', 'Operational Completion Review')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task) }}">{{ $task->task_no }}</a></li>
    <li class="breadcrumb-item active">Review</li>
@endsection

@push('styles')
<style>
    .review-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .review-card .card-header {
        padding: 1rem 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .review-card .card-header.success { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; }
    .review-card .card-header.draft   { background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); color: #856404; }
    .review-card .card-header.pending { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
    .review-field {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .review-field:last-child { border-bottom: none; }
    .review-field-label { color: #6c757d; font-size: 0.85rem; }
    .review-field-value { font-weight: 500; text-align: right; }

    .completion-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 2rem;
        color: #fff;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .completion-hero h3 { font-weight: 700; margin-bottom: 0.5rem; }
    .completion-hero .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        font-weight: 600;
    }
    .completion-hero .status-indicator.ready { background: rgba(25,135,84,0.4); }
    .completion-hero .status-indicator.not-ready { background: rgba(220,53,69,0.4); }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Hero Section --}}
        <div class="completion-hero">
            <h3><i class="fas fa-clipboard-check me-2"></i>Completion Review</h3>
            <p class="mb-3 opacity-75">{{ $task->task_no }} — {{ $task->title }}</p>
            @if($allSubmitted)
                <div class="status-indicator ready">
                    <i class="fas fa-check-circle"></i> Operational form submitted - booking work complete
                </div>
            @else
                <div class="status-indicator not-ready">
                    <i class="fas fa-exclamation-triangle"></i> Some forms are missing — Cannot complete yet
                </div>
            @endif
        </div>

        {{-- Form Summaries --}}
        @foreach($steps as $step)
            @if(!isset($step['is_review']))
                @php
                    $form = $step['form'];
                    $slug = $form->slug;
                    $status = $step['status'];
                    $headerClass = $status === 'submitted' ? 'success' : ($status === 'draft' ? 'draft' : 'pending');
                @endphp
                <div class="card review-card">
                    <div class="card-header {{ $headerClass }}">
                        <span>
                            @if($status === 'submitted') <i class="fas fa-check-circle me-2"></i>
                            @elseif($status === 'draft') <i class="fas fa-pencil-alt me-2"></i>
                            @else <i class="fas fa-times-circle me-2"></i>
                            @endif
                            Step {{ $step['step'] }}: {{ $form->display_name }}
                        </span>
                        <span>
                            @if($status === 'submitted')
                                <span class="badge bg-success">Submitted</span>
                            @elseif($status === 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                                <a href="{{ route('tasks.completion.wizard', ['task' => $task, 'step' => $step['step']]) }}" class="btn btn-sm btn-warning ms-2">Complete</a>
                            @else
                                <span class="badge bg-danger">Not Started</span>
                                <a href="{{ route('tasks.completion.wizard', ['task' => $task, 'step' => $step['step']]) }}" class="btn btn-sm btn-danger ms-2">Fill Now</a>
                            @endif
                        </span>
                    </div>
                    @if($status === 'submitted' || $status === 'draft')
                        <div class="card-body p-4">
                            @if($slug === 'payment-purchase' && $task->paymentPurchaseForm)
                                @php $pf = $task->paymentPurchaseForm; @endphp
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="review-field"><span class="review-field-label">Vendor</span><span class="review-field-value">{{ $pf->effective_vendor_name }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Account No</span><span class="review-field-value">{{ $pf->vendor_account_no ?? '—' }}</span></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="review-field"><span class="review-field-label">Payable Amount</span><span class="review-field-value fw-bold text-success">₹{{ number_format($pf->payable_amount, 2) }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Payment Mode</span><span class="review-field-value">{{ $pf->effective_payment_mode }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Payment Date</span><span class="review-field-value">{{ $pf->payment_date->format('M d, Y') }}</span></div>
                                    </div>
                                </div>
                                @if($pf->payment_comments)
                                    <div class="mt-3 p-2 bg-light rounded"><small class="text-muted">{{ $pf->payment_comments }}</small></div>
                                @endif
                            @elseif($slug === 'receipt' && $task->receiptForm)
                                @php $rf = $task->receiptForm; @endphp
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="review-field"><span class="review-field-label">Client Type</span><span class="review-field-value">{{ $rf->client_type }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Company</span><span class="review-field-value">{{ $rf->client_company_name ?? '—' }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Contact</span><span class="review-field-value">{{ $rf->contact_no ?? '—' }}</span></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="review-field"><span class="review-field-label">Amount Received</span><span class="review-field-value fw-bold text-success">₹{{ number_format($rf->amount_received, 2) }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Payment Mode</span><span class="review-field-value">{{ $rf->effective_payment_mode }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Receipt Date</span><span class="review-field-value">{{ $rf->receipt_date->format('M d, Y') }}</span></div>
                                    </div>
                                </div>
                            @elseif($slug === 'hotel-tour' && $task->hotelTourForm)
                                @php $hf = $task->hotelTourForm; @endphp
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="review-field"><span class="review-field-label">Booking Type</span><span class="review-field-value">{{ $hf->booking_type }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Service Type</span><span class="review-field-value">{{ $hf->service_type }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Trip Type</span><span class="review-field-value">{{ $hf->trip_type ?? '—' }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Location</span><span class="review-field-value">{{ $hf->city ?? '' }}{{ $hf->state ? ', '.$hf->state : '' }}</span></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="review-field"><span class="review-field-label">Check-in</span><span class="review-field-value">{{ $hf->check_in_date ? $hf->check_in_date->format('M d, Y') : '—' }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Check-out</span><span class="review-field-value">{{ $hf->check_out_date ? $hf->check_out_date->format('M d, Y') : '—' }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Pax</span><span class="review-field-value">{{ $hf->no_of_pax ?? '—' }} ({{ $hf->pax_name ?? '—' }})</span></div>
                                        <div class="review-field"><span class="review-field-label">Rooms</span><span class="review-field-value">{{ $hf->no_of_rooms ?? '—' }} ({{ $hf->hotel_room_type ?? '—' }})</span></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="review-field"><span class="review-field-label">Sale Amount</span><span class="review-field-value">₹{{ number_format($hf->sale_amount ?? 0, 2) }}</span></div>
                                        <div class="review-field"><span class="review-field-label">Purchase Amount</span><span class="review-field-value">₹{{ number_format($hf->purchased_amount ?? 0, 2) }}</span></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        @endforeach

        {{-- Complete Button --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4 text-center">
                @if($allSubmitted)
                    <div class="mb-3">
                        <i class="fas fa-check-double fa-3x text-success mb-3"></i>
                        <h5 class="fw-bold text-success">Operations Verified</h5>
                        <p class="text-muted">Booking execution is complete. Customer collection and vendor payment remain independent until finance closes the task.</p>
                    </div>
                    <form action="{{ route('tasks.completion.complete', $task) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-flag-checkered me-2"></i> Confirm Operational Completion
                        </button>
                    </form>
                @else
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold text-warning">Operational Form Incomplete</h5>
                        <p class="text-muted">Please submit the Hotel & Tour Package form to finish operational work.</p>
                    </div>
                    <button class="btn btn-secondary btn-lg px-5" disabled>
                        <i class="fas fa-lock me-2"></i> Cannot Complete Yet
                    </button>
                @endif

                <div class="mt-3">
                    <a href="{{ route('tasks.completion.wizard', ['task' => $task, 'step' => 1]) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-1"></i> Edit Forms
                    </a>
                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Task
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
