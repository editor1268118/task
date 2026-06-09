@extends('layouts.admin')

@section('title', '404 — Page Not Found')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="error-code text-primary mb-3" style="font-size: 6rem; font-weight: 700; line-height: 1; opacity: 0.2;">404</div>
        <h2 class="mb-3">Page Not Found</h2>
        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="fas fa-home me-2"></i>Go to Dashboard
        </a>
    </div>
</div>
@endsection
