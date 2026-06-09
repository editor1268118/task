@extends('layouts.admin')

@section('title', 'Operational Completion - ' . $task->task_no)
@section('page-header', 'Operational Completion Wizard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task) }}">{{ $task->task_no }}</a></li>
    <li class="breadcrumb-item active">Operational Completion</li>
@endsection

@push('styles')
<style>
    /* ─── Wizard Progress Bar ─────────────────────────────────── */
    .wizard-progress {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1.5rem 0;
        margin-bottom: 1.5rem;
        gap: 0;
    }
    .wizard-step {
        display: flex;
        align-items: center;
        position: relative;
    }
    .wizard-step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        position: relative;
        z-index: 2;
        border: 3px solid #dee2e6;
        background: #fff;
        color: #6c757d;
    }
    .wizard-step-circle.active {
        border-color: #0d6efd;
        background: #0d6efd;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
    .wizard-step-circle.completed {
        border-color: #198754;
        background: #198754;
        color: #fff;
    }
    .wizard-step-circle.draft {
        border-color: #ffc107;
        background: #ffc107;
        color: #000;
    }
    .wizard-step-label {
        position: absolute;
        top: 52px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.7rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        color: #6c757d;
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .wizard-step-label.active { color: #0d6efd; }
    .wizard-step-label.completed { color: #198754; }
    .wizard-step-connector {
        width: 60px;
        height: 3px;
        background: #dee2e6;
        transition: background 0.3s ease;
    }
    .wizard-step-connector.completed { background: #198754; }
    .wizard-step-connector.active { background: linear-gradient(90deg, #198754, #0d6efd); }

    /* ─── Form Card ─────────────────────────────────────────── */
    .form-step-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }
    .form-step-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    .form-step-card .card-header h5 { margin: 0; font-weight: 600; }
    .form-step-card .card-header .step-badge {
        background: rgba(255,255,255,0.2);
        color: #fff;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .form-section {
        border-left: 3px solid #667eea;
        padding-left: 1rem;
        margin-bottom: 1.5rem;
    }
    .form-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #667eea;
        margin-bottom: 0.75rem;
    }

    /* ─── Sticky Footer ────────────────────────────────────── */
    .wizard-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        z-index: 100;
        border-radius: 0 0 12px 12px;
    }

    /* Custom "Other" field animation */
    .other-custom-field {
        display: none;
        animation: slideDown 0.3s ease;
    }
    .other-custom-field.show { display: block; }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Task Info Bar --}}
        <div class="alert alert-light border d-flex align-items-center justify-content-between mb-4" style="border-radius: 10px;">
            <div>
                <strong class="text-primary">{{ $task->task_no }}</strong>
                <span class="mx-2 text-muted">|</span>
                {{ $task->title }}
                <span class="mx-2 text-muted">|</span>
                <span class="badge bg-info text-dark">{{ $task->taskType->name ?? 'N/A' }}</span>
            </div>
            <div>
                @switch($task->status)
                    @case('completion_pending') <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-hourglass-half me-1"></i>Completion Pending</span> @break
                    @case('forms_submitted') <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-clipboard-check me-1"></i>Forms Submitted</span> @break
                    @case('operationally_completed') <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i>Operationally Completed</span> @break
                @endswitch
            </div>
        </div>

        {{-- Wizard Progress Bar --}}
        <div class="wizard-progress mb-5">
            @foreach($steps as $i => $s)
                @if(!isset($s['is_review']))
                    {{-- Step circle --}}
                    <div class="wizard-step">
                        <div class="wizard-step-circle {{ $s['step'] == $currentStep ? 'active' : '' }} {{ $s['status'] == 'submitted' ? 'completed' : '' }} {{ $s['status'] == 'draft' ? 'draft' : '' }}">
                            @if($s['status'] == 'submitted')
                                <i class="fas fa-check"></i>
                            @elseif($s['status'] == 'draft')
                                <i class="fas fa-pencil-alt"></i>
                            @else
                                {{ $s['step'] }}
                            @endif
                        </div>
                        <div class="wizard-step-label {{ $s['step'] == $currentStep ? 'active' : '' }} {{ $s['status'] == 'submitted' ? 'completed' : '' }}">
                            {{ Str::limit($s['form']->display_name, 20) }}
                        </div>
                    </div>
                    {{-- Connector --}}
                    @if($i < count($steps) - 1)
                        <div class="wizard-step-connector {{ $s['status'] == 'submitted' ? 'completed' : '' }} {{ $s['step'] == $currentStep ? 'active' : '' }}"></div>
                    @endif
                @else
                    {{-- Review step --}}
                    <div class="wizard-step">
                        <div class="wizard-step-circle {{ $s['status'] == 'ready' ? 'completed' : '' }}">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="wizard-step-label">Review</div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Form Card --}}
        @if(isset($formData) && $formData)
            <div class="card form-step-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-file-alt me-2"></i>{{ $formData['form']->display_name }}</h5>
                    <span class="step-badge">Step {{ $currentStep }} of {{ $totalSteps - 1 }}</span>
                </div>
                <div class="card-body p-4">
                    {{-- Include the form partial --}}
                    @include($formData['form']->view_partial, [
                        'task'         => $task,
                        'existingData' => $formData['existingData'],
                        'submission'   => $formData['submission'],
                        'formSlug'     => $formData['slug'],
                        'currentStep'  => $currentStep,
                    ])
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show/hide "Other" custom fields
    document.querySelectorAll('.other-toggle').forEach(function(select) {
        select.addEventListener('change', function() {
            const target = document.getElementById(this.dataset.otherTarget);
            if (target) {
                if (this.value === 'Other') {
                    target.classList.add('show');
                } else {
                    target.classList.remove('show');
                    const input = target.querySelector('input');
                    if (input) input.value = '';
                }
            }
        });
        // Trigger on load
        select.dispatchEvent(new Event('change'));
    });
</script>
@endpush
