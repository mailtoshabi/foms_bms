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
                    $administratorRoleId = utility('id_administrator_dept');
                    $financeRoleId = utility('id_finance_dept');
                    $hrRoleId = utility('id_hr_dept');
                    $operationRoleId = utility('id_operation_dept');
                    $staff = auth('staff')->user();
                    $pendingCount = \App\Models\StudentLead::where('status', 'pending')->count();
                    $pendingCount_t = \App\Models\TeacherLead::where('status', 'pending')->count();
                @endphp

                {{-- Students: enrolment | administrator | operation --}}
                @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($operationRoleId) || $staff->hasRoleId($financeRoleId))
                    <li class="{{ set_active(['staff.student-leads.*', 'staff.students.*']) }}">
                        <a href="javascript:void(0);" class="has-arrow">
                            <i class="fas fa-user-graduate"></i>
                            <span>Students</span>

                            @if($pendingCount > 0 && ($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($operationRoleId)))
                                <span class="badge bg-warning float-end">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>

                        <ul class="sub-menu" aria-expanded="false">

                            {{-- Student Leads: enrolment | operation only --}}
                            @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($operationRoleId))
                                <li class="{{ set_active(['staff.student-leads.*']) }}">
                                    <a href="{{ route('staff.student-leads.index') }}">
                                        Student Leads

                                        @if($pendingCount > 0)
                                            <span class="badge bg-warning float-end">
                                                {{ $pendingCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif

                            @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($operationRoleId))
                                <li class="{{ set_active(['staff.students.*']) }}">
                                    <a href="{{ route('staff.students.index') }}">
                                        Students
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif

                {{-- Teachers: administrator | hr | operation | finance --}}
                @if($staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($operationRoleId) || $staff->hasRoleId($financeRoleId))
                    <li class="{{ set_active(['staff.teacher-leads.*', 'staff.teachers.*', 'staff.deposits.*']) }}">
                        <a href="javascript:void(0);" class="has-arrow">
                            <i class="mdi mdi-teach"></i>

                            <span>Teachers</span>

                            @if($pendingCount_t > 0 && ($staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($operationRoleId)))
                                <span class="badge bg-warning float-end">
                                    {{ $pendingCount_t }}
                                </span>
                            @endif
                        </a>

                        <ul class="sub-menu" aria-expanded="false">

                            @if($staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($operationRoleId))
                                <li class="{{ set_active(['staff.teacher-leads.*']) }}">
                                    <a href="{{ route('staff.teacher-leads.index') }}">
                                        Teacher Leads

                                        @if($pendingCount_t > 0)
                                            <span class="badge bg-warning float-end">
                                                {{ $pendingCount_t }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif

                            @if($staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($operationRoleId))
                                <li class="{{ set_active(['staff.teachers.*']) }}">
                                    <a href="{{ route('staff.teachers.index') }}">
                                        Teachers
                                    </a>
                                </li>
                            @endif

                            @if($staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($operationRoleId))
                                <li class="{{ set_active(['staff.deposits.*']) }}">
                                    <a href="{{ route('staff.deposits.index') }}">
                                        Deposits
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif

                {{-- Classes: enrolment | administrator | operation --}}
                @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($operationRoleId))
                    <li class="{{ set_active(['staff.class_rooms.*']) }}">
                        <a href="javascript:void(0);" class="has-arrow">
                            <i class="fas fa-chalkboard"></i>
                            <span>Classes</span>
                        </a>
                        <ul class="sub-menu">
                            <li><a href="{{ route('staff.class_rooms.index') }}">List Classes</a></li>
                            <li><a href="{{ route('staff.class_rooms.create') }}">Add Class</a></li>
                        </ul>
                    </li>
                @endif

                {{-- Fees: enrolment | finance | operation --}}
                @if($staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($operationRoleId))
                    <li>
                        <a href="{{ route('staff.fees.index') }}">
                            <i class="fas fa-money-bill"></i>
                            <span>Fees</span>
                        </a>
                    </li>
                @endif

                {{-- Salaries + Expenses: hr | operation --}}
                @if($staff->hasRoleId($hrRoleId) || $staff->hasRoleId($operationRoleId))
                    <li class="{{ set_active(['staff.salaries.index']) }}">
                        <a href="{{ route('staff.salaries.index') }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Salaries</span>
                        </a>
                    </li>
                @endif
                {{-- Class Hours: hr | operation | finance --}}
                @if($staff->hasRoleId($hrRoleId) || $staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($operationRoleId))
                    <li class="{{ set_active(['staff.class-hours.index']) }}">
                        <a href="{{ route('staff.class-hours.index') }}">
                            <i class="fas fa-history"></i>
                            <span>Class Session</span>
                        </a>
                    </li>
                @endif

                @if($staff->hasRoleId($hrRoleId) || $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($operationRoleId))
                    <li class="{{ set_active(['staff.reports.attendance']) }}">
                        <a href="{{ route('staff.reports.attendance') }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Attendance</span>
                        </a>
                    </li>
                @endif


                @if($staff->hasRoleId($financeRoleId) || $staff->hasRoleId($operationRoleId))
                    <li>
                        <a href="{{ route('staff.expenses.index') }}">
                            <i class="fas fa-receipt"></i>
                            <span>Expenses</span>
                        </a>
                    </li>
                @endif

                {{-- Old Data: operation only --}}
                @if($staff->hasRoleId($operationRoleId))
                    <li class="{{ set_active(['staff.old_data.*']) }}">
                        <a href="{{ route('staff.old_data.index') }}">
                            <i class="fas fa-database"></i>
                            <span>Old Data</span>
                        </a>
                    </li>
                @endif

                @if($staff->hasRoleId($operationRoleId))
                    <li class="{{ set_active(['staff.holidays.*']) }}">
                        <a href="{{ route('staff.holidays.index') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Holidays & Alerts</span>
                        </a>
                    </li>
                @endif

                {{-- ================= Messages ================= --}}
                <li class="{{ set_active(['staff.messages.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-envelope"></i>
                        <span>Messages </span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">

                        {{-- Inbox --}}
                        <li class="{{ set_active(['staff.messages.index']) }}">
                            <a href="{{ route('staff.messages.index') }}">
                                Inbox
                            </a>
                        </li>

                        {{-- New Message --}}
                        <li class="{{ set_active(['staff.messages.create']) }}">
                            <a href="{{ route('staff.messages.create') }}">
                                New Message
                            </a>
                        </li>

                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->