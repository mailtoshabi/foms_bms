<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">

                {{-- ================= Dashboard ================= --}}
                <li class="{{ set_active('teacher.dashboard') }}">
                    <a href="{{ route('teacher.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="{{ set_active(['teacher.classes.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('teacher.classes.index') }}">List Classes</a></li>
                    </ul>
                </li>

                {{-- <li class="{{ set_active(['admin.staffs.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-user-tie"></i>
                        <span>Staff Management</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('admin.staffs.index') }}">List Staff</a></li>
                        <li><a href="{{ route('admin.staffs.create') }}">Add Staff</a></li>
                        <li><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    </ul>
                </li> --}}

                {{-- <li class="{{ set_active(['admin.courses.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-book"></i>
                        <span>Course Management</span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ route('admin.courses.index') }}">Course List</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.courses.create') }}">Add Course</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ set_active(['admin.class_rooms.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('admin.class_rooms.index') }}">List Classes</a></li>
                        <li><a href="{{ route('admin.class_rooms.create') }}">Add Class</a></li>
                    </ul>
                </li> --}}

                {{-- ================= Messages ================= --}}
                <li class="{{ set_active(['admin.messages.*','staff.messages.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-envelope"></i>
                        <span>Messages </span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">

                        {{-- Inbox --}}
                        <li class="{{ set_active(['admin.messages.index']) }}">
                            <a href="{{ route('admin.messages.index') }}">
                                Inbox
                            </a>
                        </li>

                        {{-- New Message --}}
                        <li class="{{ set_active(['admin.messages.create']) }}">
                            <a href="{{ route('admin.messages.create') }}">
                                New Message
                            </a>
                        </li>

                    </ul>
                </li>


                {{-- ================= Reports ================= --}}
                <li class="{{ set_active(['admin.reports.*']) }}">
                    {{-- <a href="{{ route('admin.reports.index') }}" class="">
                        <i class="fas fa-file-alt"></i>
                        <span>Reports</span>
                    </a> --}}
                    <ul class="sub-menu" aria-expanded="false">

                        {{-- Employee --}}
                        {{-- @if(auth()->user() && auth()->user()->isEmployee())
                            <li>
                                <a href="{{ route('admin.reports.create') }}">
                                    Create Report
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.index') }}">
                                    My Reports
                                </a>
                            </li>
                        @endif --}}

                        {{-- Super Admin --}}
                        {{-- @if(auth()->user() && auth()->user()->isSuperAdmin())
                            <li>
                                <a href="{{ route('admin.reports.index') }}">
                                    All Reports
                                </a>
                            </li>
                        @endif --}}

                    </ul>
                </li>

                {{-- ================= Super Admin Only ================= --}}


            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->
