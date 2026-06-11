<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <div class="brand-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <span class="brand-text">Amigos<span class="text-accent">TMS</span></span>
        </a>
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">
            <span>{{ substr(auth()->user()->name, 0, 1) }}</span>
        </div>
        <div class="user-info">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="user-role">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <li class="nav-section">
                <span class="nav-section-title">Main</span>
            </li>
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('manager.dashboard') || request()->routeIs('employee.dashboard') || request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            @hasanyrole('super-admin|manager|employee')
                <li class="nav-section">
                    <span class="nav-section-title">Sales</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sales.queries.index') }}" class="nav-link {{ request()->routeIs('sales.queries.index') || request()->routeIs('sales.queries.create') || request()->routeIs('sales.queries.show') || request()->routeIs('sales.queries.edit') ? 'active' : '' }}">
                        <i class="fas fa-table nav-icon"></i>
                        <span class="nav-text">Query Register</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sales.queries.followups') }}" class="nav-link {{ request()->routeIs('sales.queries.followups') ? 'active' : '' }}">
                        <i class="fas fa-phone-volume nav-icon"></i>
                        <span class="nav-text">My Follow-Ups</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sales.queries.dashboard') }}" class="nav-link {{ request()->routeIs('sales.queries.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie nav-icon"></i>
                        <span class="nav-text">Query Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sales.queries.reports') }}" class="nav-link {{ request()->routeIs('sales.queries.reports') ? 'active' : '' }}">
                        <i class="fas fa-file-lines nav-icon"></i>
                        <span class="nav-text">Query Reports</span>
                    </a>
                </li>
            @endhasanyrole

            @hasanyrole('super-admin|manager|employee|finance')
                <li class="nav-section">
                    <span class="nav-section-title">CRM</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('crm.customers.index') }}" class="nav-link {{ request()->routeIs('crm.customers.*') ? 'active' : '' }}">
                        <i class="fas fa-address-book nav-icon"></i>
                        <span class="nav-text">Customers</span>
                    </a>
                </li>
                @hasanyrole('super-admin|manager|employee')
                    <li class="nav-item">
                        <a href="{{ route('crm.interactions.index') }}" class="nav-link {{ request()->routeIs('crm.interactions.*') ? 'active' : '' }}">
                            <i class="fas fa-comments nav-icon"></i>
                            <span class="nav-text">Interactions</span>
                        </a>
                    </li>
                @endhasanyrole
                <li class="nav-item">
                    <a href="{{ route('crm.reports.customers') }}" class="nav-link {{ request()->routeIs('crm.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie nav-icon"></i>
                        <span class="nav-text">Customer Reports</span>
                    </a>
                </li>
            @endhasanyrole

            <li class="nav-section">
                <span class="nav-section-title">Operations</span>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('tasks.*') && !request()->routeIs('tasks.completion.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list nav-icon"></i>
                    <span class="nav-text">Tasks</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu {{ request()->routeIs('tasks.*') && !request()->routeIs('tasks.completion.*') ? 'show' : '' }}">
                    <li><a href="{{ route('tasks.index') }}">All Tasks</a></li>
                    @can('create-tasks')
                        <li><a href="{{ route('tasks.create') }}">Create Task</a></li>
                    @endcan
                </ul>
            </li>
            @hasanyrole('super-admin|manager')
                <li class="nav-item">
                    <a href="{{ route('reviews.index') }}" class="nav-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check nav-icon"></i>
                        <span class="nav-text">Review Center</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('management.dashboard') }}" class="nav-link {{ request()->routeIs('management.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-user-check nav-icon"></i>
                        <span class="nav-text">Management Dashboard</span>
                    </a>
                </li>
            @endhasanyrole
            @hasanyrole('super-admin|manager|finance')
                <li class="nav-item">
                    <a href="{{ route('operations.master-board.index') }}" class="nav-link {{ request()->routeIs('operations.master-board.*') ? 'active' : '' }}">
                        <i class="fas fa-table-list nav-icon"></i>
                        <span class="nav-text">Master Operations Board</span>
                    </a>
                </li>
            @endhasanyrole
            @hasanyrole('super-admin|manager')
                <li class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('reports.productivity') || request()->routeIs('reports.workload') || request()->routeIs('reports.department') ? 'active' : '' }}">
                        <i class="fas fa-chart-line nav-icon"></i>
                        <span class="nav-text">Task Reports</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="nav-submenu {{ request()->routeIs('reports.productivity') || request()->routeIs('reports.workload') || request()->routeIs('reports.department') ? 'show' : '' }}">
                        <li><a href="{{ route('reports.productivity') }}">Productivity</a></li>
                        <li><a href="{{ route('reports.workload') }}">Workloads</a></li>
                        <li><a href="{{ route('reports.department') }}">Department Performance</a></li>
                    </ul>
                </li>
            @endhasanyrole

            <li class="nav-section">
                <span class="nav-section-title">Finance</span>
            </li>
            <li class="nav-item">
                <a href="{{ route('finance.queue') }}" class="nav-link {{ request()->routeIs('finance.queue') ? 'active' : '' }}">
                    <i class="fas fa-list-check nav-icon"></i>
                    <span class="nav-text">Approvals</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('finance.ledger') }}" class="nav-link {{ request()->routeIs('finance.ledger') ? 'active' : '' }}">
                    <i class="fas fa-table nav-icon"></i>
                    <span class="nav-text">Transactional Ledger</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('finance.dashboard') }}" class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line nav-icon"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>

            @role('super-admin')
                <li class="nav-section">
                    <span class="nav-section-title">Administration</span>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">Users</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="nav-submenu {{ request()->routeIs('admin.users.*') ? 'show' : '' }}">
                        <li><a href="{{ route('admin.users.index') }}">All Users</a></li>
                        <li><a href="{{ route('admin.users.create') }}">Add User</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-halved nav-icon"></i>
                        <span class="nav-text">Roles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                        <i class="fas fa-building nav-icon"></i>
                        <span class="nav-text">Departments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.designations.index') }}" class="nav-link {{ request()->routeIs('admin.designations.*') ? 'active' : '' }}">
                        <i class="fas fa-id-badge nav-icon"></i>
                        <span class="nav-text">Designations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.task-types.index') }}" class="nav-link {{ request()->routeIs('admin.task-types.*') ? 'active' : '' }}">
                        <i class="fas fa-tags nav-icon"></i>
                        <span class="nav-text">Task Types</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.audit') }}" class="nav-link {{ request()->routeIs('reports.audit') ? 'active' : '' }}">
                        <i class="fas fa-history nav-icon"></i>
                        <span class="nav-text">Activity Logs</span>
                    </a>
                </li>
            @endrole
        </ul>
    </nav>
</aside>
