@extends('layouts.admin')

@section('title', '403 — Access Denied')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="error-code text-danger mb-3" style="font-size: 6rem; font-weight: 700; line-height: 1; opacity: 0.2;">403</div>
        <h2 class="mb-3">Access Denied</h2>
        <p class="text-muted mb-4">You do not have permission to access this page.<br>Please contact your administrator if you believe this is an error.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="fas fa-home me-2"></i>Go to Dashboard
        </a>
    </div>
</div>
@endsection
