<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">

                {{-- ================= Dashboard ================= --}}
                <li class="{{ set_active('staff.dashboard') }}">
                    <a href="{{ route('staff.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @php
                    $enrolmentRoleId = utility('id_enrolment_dept');
                    $operationRoleId = utility('id_operation_dept');
                    $staff = auth('staff')->user();
                    $pendingCount = \App\Models\StudentLead::where('status','pending')->count();
                @endphp

                @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($operationRoleId))
                <li class="{{ set_active(['staff.student-leads.*']) }}">
                    <a href="{{ route('staff.student-leads.index') }}">
                        <i class="fas fa-user-graduate text-primary"></i>
                        <span>Student Leads</span>

                        @if($pendingCount > 0)
                            <span class="badge bg-warning float-end">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>
                </li>
                @endif



                <li class="{{ set_active(['admin.classes.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('admin.classes.index') }}">List Classes</a></li>
                        <li><a href="{{ route('admin.classes.create') }}">Add Class</a></li>
                    </ul>
                </li>

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
