<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">

                {{-- ================= Dashboard ================= --}}
                <li class="{{ set_active('student.dashboard') }}">
                    <a href="{{ route('student.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- ================= Classes ================= --}}
                <li class="{{ set_active('student.classes.*') }}">
                    <a href="{{ route('student.classes.index') }}">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                    </a>
                </li>

                {{-- ================= Class Notes ================= --}}
                <li class="{{ set_active('student.notes.*') }}">
                    <a href="{{ route('student.notes.index') }}">
                        <i class="fas fa-sticky-note"></i>
                        <span>Class Notes</span>
                    </a>
                </li>

                {{-- ================= Messages ================= --}}
                <li class="{{ set_active(['student.messages.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-envelope"></i>
                        <span>Messages </span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">

                        {{-- Inbox --}}
                        <li class="{{ set_active(['student.messages.index']) }}">
                            <a href="{{ route('student.messages.index') }}">
                                Inbox
                            </a>
                        </li>

                        {{-- New Message --}}
                        <li class="{{ set_active(['student.messages.create']) }}">
                            <a href="{{ route('student.messages.create') }}">
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
