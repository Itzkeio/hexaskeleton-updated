<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="header-wrapper">
        <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <!-- ======= Menu collapse Icon ===== -->
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="dropdown pc-h-item">
                    <!-- <a
                        class="pc-head-link dropdown-toggle arrow-none m-0 trig-drp-search"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-search-normal-1"></use>
                        </svg>
                    </a>
                    <div class="dropdown-menu pc-h-dropdown drp-search">
                        <form class="px-3 py-2">
                            <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . ." />
                        </form>
                    </div> -->
                    <p class="fw-bold" style="margin-top: 17px;">Antusias - HJ Cikarang</p>
                </li>
            </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item">
                    <a
                        class="pc-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-notification"></use>
                        </svg>
                        <span class="badge bg-success pc-h-badge" id="total-notification"></span>
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Notifications</h5>
                            <!-- <a href="#!" class="btn btn-link btn-sm">Mark all read</a> -->
                        </div>
                        <div class="dropdown-body overflow-y-auto text-wrap header-notification-scroll py-1 position-relative" id="data-container" style="max-height: calc(100vh - 215px)">

                        </div>
                        <!-- <div class="text-center py-2">
                  <a href="#!" class="link-danger">Clear all Notifications</a>
                </div> -->
                    </div>
                </li>
                <li class="dropdown pc-h-item header-user-profile">
                    <a
                        class="pc-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        data-bs-auto-close="outside"
                        aria-expanded="false">
                        <img src="/images/user/avatar-2.jpg" alt="user-image" class="user-avtar" referrerpolicy="no-referrer" />
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Profile</h5>
                        </div>
                        <div class="dropdown-body">
                            <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                                <div class="d-flex mb-1">
                                    <div class="flex-shrink-0">
                                        <img src="/images/user/avatar-2.jpg" alt="user-image" class="user-avtar wid-35" referrerpolicy="no-referrer" />
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ auth()->user()->name ?? '-' }} 🖖</h6>
                                        <span style="font-size: 10pt;">{{ auth()->user()->userPrincipalName ?? '-' }} </span>
                                    </div>
                                </div>
                                <hr class="border-secondary border-opacity-50" />
                                <div class="d-grid mb-3">
                                    <a class="btn btn-primary" href="{{ route('doLogout') }}">
                                        <svg class="pc-icon me-2">
                                            <use xlink:href="#custom-logout-1-outline"></use>
                                        </svg>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
<!-- [ Header ] end -->