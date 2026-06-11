@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-header', 'My Profile')
@section('page-description', 'Manage your account settings and preferences.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Profile Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center pt-4 pb-3 mb-4">
            <div class="card-body">
                <div class="mb-4 d-flex justify-content-center position-relative">
                    <div class="user-avatar" style="width: 120px; height: 120px; font-size: 3rem;">
                        @if($user->profile_photo)
                            <img src="{{ $user->profilePhotoUrl() }}" alt="" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                        @else
                            <span>{{ substr($user->name, 0, 1) }}</span>
                        @endif
                    </div>
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->designation?->name ?? 'No Designation' }}</p>
                
                <div class="mb-4">
                    <span class="badge bg-primary-soft px-3 py-2" style="font-size: 0.85rem;">
                        {{ Str::headline($user->roles->first()?->name ?? 'No Role') }}
                    </span>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.875rem;">
                    <span><i class="fas fa-building me-2"></i>Department</span>
                    <span class="fw-medium text-dark">{{ $user->department?->name ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 0.875rem;">
                    <span><i class="fas fa-id-badge me-2"></i>EMP ID</span>
                    <span class="fw-medium text-dark">{{ $user->employee_id }}</span>
                </div>
            </div>
        </div>
        
        <!-- Profile Photo Upload -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-camera me-2"></i>Update Photo</h6>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('profile.photo') }}" enctype="multipart/form-data" data-loading>
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <input class="form-control form-control-sm @error('profile_photo', 'updateProfilePhoto') is-invalid @enderror" id="profile_photo" type="file" name="profile_photo" accept="image/*" required>
                        @error('profile_photo', 'updateProfilePhoto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Upload Photo</button>
                    @if (session('status') === 'photo-updated')
                        <p class="text-success text-center mt-2 mb-0" style="font-size: 0.8125rem;"><i class="fas fa-check me-1"></i>Saved.</p>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Settings -->
    <div class="col-lg-8">
        <!-- Profile Information Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-user-edit me-2"></i>Profile Information</h6>
                <small class="text-muted d-block mt-1">Update your account's profile information and email address.</small>
            </div>
            <div class="card-body">
                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" class="mt-2" data-loading>
                    @csrf
                    @method('patch')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" name="name" type="text" class="form-control @error('name', 'updateProfileInformation') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                            @error('name', 'updateProfileInformation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" name="email" type="email" class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                            @error('email', 'updateProfileInformation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input id="phone" name="phone" type="text" class="form-control @error('phone', 'updateProfileInformation') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                            @error('phone', 'updateProfileInformation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        @if (session('status') === 'profile-updated')
                            <span class="text-success" style="font-size: 0.875rem;"><i class="fas fa-check me-1"></i>Saved.</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Password Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 text-primary"><i class="fas fa-lock me-2"></i>Update Password</h6>
                <small class="text-muted d-block mt-1">Ensure your account is using a long, random password to stay secure.</small>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('password.update') }}" class="mt-2" data-loading>
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label">Current Password</label>
                        <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label">New Password</label>
                        <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                        @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                        @if (session('status') === 'password-updated')
                            <span class="text-success" style="font-size: 0.875rem;"><i class="fas fa-check me-1"></i>Saved.</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
