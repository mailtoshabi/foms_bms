<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('student.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <i class="fas fa-graduation-cap" style="font-size: 24px;"></i>
                    </span>
                    <span class="logo-lg">
                        <i class="fas fa-graduation-cap" style="font-size: 24px;"></i> <span class="logo-txt">FOMS ONLINE ACADEMY</span>
                    </span>
                </a>

                <a href="{{ route('student.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <i class="fas fa-graduation-cap" style="font-size: 24px; color: #fff;"></i>
                    </span>
                    <span class="logo-lg">
                        <i class="fas fa-graduation-cap" style="font-size: 24px; color: #fff;"></i> <span class="logo-txt">FOMS ONLINE ACADEMY</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="d-flex">



            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="moon" class="icon-lg layout-mode-dark"></i>
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>



            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon me-2 right-bar-toggle">
                    <i data-feather="clock" class="icon-lg"></i>
                    @if(isset($pendingClassHoursCount) && $pendingClassHoursCount > 0)
                        <span class="badge bg-danger rounded-pill">{{ $pendingClassHoursCount }}</span>
                    @endif
                </button>
            </div>


            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end" id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="@if (Auth::user()->photo != ''){{ URL::asset('images/'. Auth::user()->photo) }}@else https://ui-avatars.com/api/?name=FA&size=200 @endif"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium">{{Auth::user()->name}}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a href="{{ route('student.dashboard') }}" class="dropdown-item"><i class="mdi mdi-home font-size-16 align-middle me-1"></i> Dashboard</a>
                    <a class="dropdown-item" href="{{ route('student.profile') }}"><i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span key="t-logout">Logout</span></a>
                    <form id="logout-form" action="{{ route('student.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
