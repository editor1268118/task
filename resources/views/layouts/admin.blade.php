<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Amigos TMS') }}</title>
    <meta name="description" content="@yield('meta_description', 'Amigos Task Management System — Internal Workflow Platform')">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Custom Admin CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Navbar -->
            @include('components.navbar')

            <!-- Page Content -->
            <div class="page-content">
                <!-- Breadcrumb -->
                @hasSection('breadcrumb')
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                @endif

                <!-- Flash Messages -->
                @include('components.flash-message')

                <!-- Page Header -->
                @hasSection('page-header')
                    <div class="page-header mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h1 class="page-title">@yield('page-header')</h1>
                                @hasSection('page-description')
                                    <p class="page-description text-muted mb-0">@yield('page-description')</p>
                                @endif
                            </div>
                            @hasSection('page-actions')
                                <div class="page-actions">
                                    @yield('page-actions')
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Main Content Area -->
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="app-footer">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <span>&copy; {{ date('Y') }} {{ config('app.name', 'Amigos TMS') }}. All rights reserved.</span>
                    <span class="text-muted">v1.0.0</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    @include('components.confirm-modal')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Admin JS -->
    <script src="{{ asset('js/admin.js') }}"></script>

    @stack('scripts')
</body>
</html>
