<!-- Header (top) -->
<nav class="app-header navbar bg-body navbar-expand">
    <div class="container-fluid">
        <!-- Right -->
        <ul class="navbar-nav ms-auto">
            <!-- Dark mode toggle -->
            <li class="nav-item">
                <button type="button" id="darkModeToggle" class="btn btn-sm btn-outline-secondary nav-link"
                        title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </li>

            <!-- User dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="far fa-user me-1"></i> {{ auth()->user()->name ?? 'User' }}
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user-cog me-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt me-2"></i> Log Out
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
