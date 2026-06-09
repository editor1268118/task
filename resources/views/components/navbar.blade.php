<!-- Top Navbar -->
<header class="top-navbar" id="topNavbar">
    <div class="navbar-left">
        <!-- Sidebar Toggle -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Search Bar -->
        <div class="navbar-search d-none d-md-block">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control search-input" placeholder="Search tasks, users..." id="globalSearch">
            </div>
        </div>
    </div>

    <div class="navbar-right">
        <!-- Notifications -->
        <div class="dropdown nav-dropdown">
            @php
                $unreadCount = auth()->user()->unreadNotifications()->count();
                $recentNotifications = auth()->user()->notifications()->latest()->take(8)->get();
            @endphp
            <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" id="notificationDropdown">
                <i class="fas fa-bell"></i>
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                <div class="dropdown-header">
                    <h6 class="mb-0">Notifications</h6>
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-primary small p-0 m-0 align-baseline" style="text-decoration: none;">Mark all as read</button>
                        </form>
                    @endif
                </div>
                <div class="notification-list">
                    @forelse($recentNotifications as $notification)
                        @php
                            $module = $notification->data['module'] ?? ($notification->data['type'] ?? 'task');
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        <a href="{{ route('notifications.read', $notification->id) }}" class="notification-item {{ $isUnread ? 'unread' : '' }}">
                            <div class="notification-icon bg-primary-soft">
                                @if($module === 'finance')
                                    <i class="fas fa-indian-rupee-sign text-success"></i>
                                @elseif($module === 'query')
                                    <i class="fas fa-headset text-primary"></i>
                                @elseif(isset($notification->data['type']) && $notification->data['type'] == 'status_update')
                                    <i class="fas fa-sync text-primary"></i>
                                @elseif(isset($notification->data['type']) && $notification->data['type'] == 'comment')
                                    <i class="fas fa-comment text-success"></i>
                                @else
                                    <i class="fas fa-tasks text-info"></i>
                                @endif
                            </div>
                            <div class="notification-content">
                                @if(!empty($notification->data['title']))
                                    <strong class="d-block small">{{ $notification->data['title'] }}</strong>
                                @endif
                                <p class="notification-text">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="notification-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>No new notifications</p>
                        </div>
                    @endforelse
                </div>
                <div class="dropdown-footer">
                    <a href="{{ route('notifications.index') }}">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="dropdown nav-dropdown">
            <button class="user-dropdown-btn" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
                <div class="user-avatar-sm">
                    <span>{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div class="user-dropdown-info d-none d-md-block">
                    <span class="user-dropdown-name">{{ auth()->user()->name }}</span>
                    <span class="user-dropdown-role">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
                </div>
                <i class="fas fa-chevron-down ms-2 d-none d-md-inline"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="userDropdown">
                <div class="dropdown-header">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small class="d-block text-muted">{{ auth()->user()->email }}</small>
                </div>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
