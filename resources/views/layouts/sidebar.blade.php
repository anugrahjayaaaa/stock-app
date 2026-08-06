<!-- Sidebar (renders on the left via AdminLTE 4 grid) -->
<aside class="app-sidebar shadow">
    <div class="sidebar-brand d-flex align-items-center justify-content-center">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text fw-bold">{{ config('app.name', 'Laravel') }}</span>
        </a>
        <a class="sb-toggle btn btn-link nav-link px-2" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-3">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @can('view roles')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}"
                       class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>Roles</p>
                    </a>
                </li>
                @endcan
                @can('view permissions')
                <li class="nav-item">
                    <a href="{{ route('permissions.index') }}"
                       class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-key"></i>
                        <p>Permissions</p>
                    </a>
                </li>
                @endcan
                @can('view audit logs')
                <li class="nav-item">
                    <a href="{{ route('audit-logs.index') }}"
                       class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Audit Logs</p>
                    </a>
                </li>
                @endcan
                <li class="nav-item">
                    <a href="{{ route('stock.chart') }}"
                       class="nav-link {{ request()->routeIs('stock.chart') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Stock Chart</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
