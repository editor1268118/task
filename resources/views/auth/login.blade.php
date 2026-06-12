<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Amigos TMS') }} — Login</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --accent: #06b6d4;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            -webkit-font-smoothing: antialiased;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon-lg {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1rem;
            box-shadow: 0 8px 32px rgba(79, 70, 229, 0.3);
        }

        .brand-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .brand-title span {
            color: var(--accent);
        }

        .brand-subtitle {
            color: #94a3b8;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .auth-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .auth-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .auth-card .subtitle {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.8125rem;
            color: #334155;
            margin-bottom: 0.375rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            color: #94a3b8;
        }

        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 10px;
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
            font-size: 0.8125rem;
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.8125rem;
            border: none;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            z-index: 5;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Brand -->
        <div class="auth-brand">
            <div class="brand-icon-lg">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="brand-title">Amigos<span>TMS</span></div>
            <div class="brand-subtitle">Task Management System</div>
        </div>

        <!-- Login Card -->
        <div class="auth-card">
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to your account to continue</p>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <!-- Error Messages -->
            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Login ID -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address or Employee ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input id="email" type="text" class="form-control" name="email"
                               value="{{ old('email') }}" required autofocus
                               placeholder="you@company.com or EMP0002">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group position-relative">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" class="form-control" name="password"
                               required autocomplete="current-password"
                               placeholder="Enter your password">
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me" style="font-size: 0.8125rem;">
                            Remember me
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="font-size: 0.8125rem; color: var(--primary); text-decoration: none;">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="auth-footer">
            &copy; {{ date('Y') }} Amigos TMS. All rights reserved.
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
