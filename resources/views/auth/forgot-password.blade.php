<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - {{ config('app.name', 'Amigos TMS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 45%, #e0f2fe 100%);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .forgot-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .forgot-card {
            width: 100%;
            max-width: 430px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .12);
            overflow: hidden;
        }
        .forgot-card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #4f46e5, #2563eb);
            color: #fff;
        }
        .forgot-card-body {
            padding: 1.5rem;
        }
    </style>
</head>
<body>
<div class="forgot-shell">
    <div class="forgot-card">
        <div class="forgot-card-header">
            <h4 class="mb-1">Forgot Password</h4>
            <p class="mb-0 opacity-75">Enter your registered email to receive a reset link.</p>
        </div>
        <div class="forgot-card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@example.com" required autofocus>
                </div>
                <button class="btn btn-primary w-100">Email Password Reset Link</button>
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="text-decoration-none">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
