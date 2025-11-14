<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="/" class="b-brand text-primary">
                <!-- ========   Change your logo from here   ============ -->
                <img src="/images/hj.png" class="img-logo" referrerpolicy="no-referrer" />
                <span class="badge bg-light-success rounded-pill ms-2 theme-version">v1.0.0</span>
            </a>
        </div>
        <div class="navbar-content">
            <div class="card pc-user-card">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img src="/images/user/avatar-1.jpg" alt="user-image" class="user-avtar wid-45 rounded-3" referrerpolicy="no-referrer" />
                        </div>
                        <div class="flex-grow-1 ms-3 me-2">
                            <h6 class="mb-0 p-name">{{ auth()->user()->name ?? '-' }}</h6>
                            <small>
                                @if(auth()->user() && auth()->user()->roles && auth()->user()->roles->count())
                                {{ auth()->user()->roles->pluck('name')->join(', ') }}
                                @else
                                No role
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>Home</label>
                </li>
                <li class="pc-item">
                    <a href="/" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-status-up"></use>
                            </svg>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>
                <li class="pc-item pc-caption">
                    <label>Master Data</label>
                </li>
                <li class="pc-item">
                    <a href="/masterdata/employee" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-user-square"></use>
                            </svg>
                        </span><span class="pc-mtext">Employee</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="/masterdata/role" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-setting-2"></use>
                            </svg>
                        </span><span class="pc-mtext">Role</span>
                    </a>
                </li>
                <li class="pc-item pc-caption">
                    <label>Log</label>
                    <svg class="pc-icon">
                        <use xlink:href="#custom-box-1"></use>
                    </svg>
                </li>
                <li class="pc-item">
                    <a href="/log/audittrail" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-document"></use>
                            </svg>
                        </span><span class="pc-mtext">Audit Trail</span>
                    </a>
                </li>
                 <li class="pc-item pc-caption">
                    <label>Project Management</label>
                    <svg class="pc-icon">
                        <use xlink:href="#custom-box-1"></use>
                    </svg>
                </li>
                <li class="pc-item">
                    <a href="/project-mgt/projects" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-document"></use>
                            </svg>
                        </span><span class="pc-mtext">Projects</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->