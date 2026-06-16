@extends('layouts.admin')

@section('title', '500 - Server Error')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="error-code text-danger mb-3" style="font-size: 6rem; font-weight: 700; line-height: 1; opacity: 0.2;">500</div>
        <h2 class="mb-3">Something Went Wrong</h2>
        <p class="text-muted mb-4">The system could not complete this request. Please go back and try again, or return to the dashboard.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
