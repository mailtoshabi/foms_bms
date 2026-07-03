<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">

                {{-- ================= Dashboard ================= --}}
                <li class="{{ set_active('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="{{ set_active(['admin.staffs.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-user-tie"></i>
                        <span>Staff Management</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('admin.staffs.index') }}">List Staff</a></li>
                        <li><a href="{{ route('admin.staffs.create') }}">Add Staff</a></li>
                    </ul>
                </li>

                <li class="{{ set_active(['admin.courses.*']) }}">
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

                <li class="{{ set_active(['admin.class_rooms.*', 'admin.class-notes.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('admin.class_rooms.index') }}">List Classes</a></li>
                        <li><a href="{{ route('admin.class_rooms.create') }}">Add Class</a></li>
                        <li><a href="{{ route('admin.class-notes.index') }}">Class Notes</a></li>
                        <li class="{{ set_active(['admin.reports.class-hours']) }}">
                            <a href="{{ route('admin.reports.class-hours') }}">
                                <span>Sessions</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="{{ set_active(['admin.holidays.*']) }}">
                    <a href="{{ route('admin.holidays.index') }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Holidays & Alerts</span>
                    </a>
                </li>

                {{-- ================= Messages ================= --}}
                <li class="{{ set_active(['admin.messages.*', 'staff.messages.*', 'admin.st-messages.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-envelope"></i>
                        <span>Messages </span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">
                        {{-- Inbox --}}
                        <li class="{{ set_active(['admin.messages.index']) }}">
                            <a href="{{ route('admin.messages.index') }}">
                                Staff Messages
                            </a>
                        </li>

                        {{-- Student-Teacher Messages --}}
                        <li class="{{ set_active(['admin.st-messages.*']) }}">
                            <a href="{{ route('admin.st-messages.index') }}">
                                Student-Teacher
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="menu-title">Reports</li>
                <li class="{{ set_active(['admin.reports.fee.*', 'admin.reports.finance.expense', 'admin.fees.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="mdi mdi-cash-check"></i>
                        <span>Finance</span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{ route('admin.reports.fee.collection') }}">
                                <span>Fee Collection</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('admin.reports.fee') }}" class="">
                                <span>Pending Fee</span>
                            </a>
                        </li>
                        <li class="{{ set_active(['admin.fees.create']) }}">
                            <a href="{{ route('admin.fees.create') }}">
                                <span>Add Manual Fee</span>
                            </a>
                        </li>
                        <li class="{{ set_active(['admin.reports.finance.expense']) }}">
                            <a href="{{ route('admin.reports.finance.expense') }}">
                                <span>Expense Report</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li
                    class="{{ set_active(['admin.reports.teachers.*', 'admin.reports.teacher-leads', 'admin.reports.teacher-lead-notes', 'admin.reports.teacher.salary', 'admin.deposits.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="mdi mdi-account-tie"></i>
                        <span>Teachers</span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">
                        <li class="{{ set_active(['admin.reports.teacher-leads']) }}">
                            <a href="{{ route('admin.reports.teacher-leads') }}">
                                Leads
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.teacher-lead-notes']) }}">
                            <a href="{{ route('admin.reports.teacher-lead-notes') }}">
                                Lead Notes
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.teachers']) }}">
                            <a href="{{ route('admin.reports.teachers') }}">
                                Teachers
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.teacher.salary']) }}">
                            <a href="{{ route('admin.reports.teacher.salary') }}">
                                Salary
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.deposits.*']) }}">
                            <a href="{{ route('admin.deposits.index') }}">
                                Deposits
                            </a>
                        </li>
                    </ul>
                </li>

                <li
                    class="{{ set_active(['admin.reports.students.*', 'admin.reports.student-leads', 'admin.reports.student-lead-notes']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="mdi mdi-school"></i>
                        <span>Students</span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">
                        <li class="{{ set_active(['admin.reports.student-leads']) }}">
                            <a href="{{ route('admin.reports.student-leads') }}">
                                Leads
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.student-lead-notes']) }}">
                            <a href="{{ route('admin.reports.student-lead-notes') }}">
                                Lead Notes
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.students']) }}">
                            <a href="{{ route('admin.reports.students') }}">
                                Students
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.attendance']) }}">
                            <a href="{{ route('admin.reports.attendance') }}">
                                <span>Attendance</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="{{ set_active(['admin.reports.staffs', 'admin.reports.staff.salary']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="mdi mdi-account-group"></i>
                        <span>Staff</span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">
                        <li class="{{ set_active(['admin.reports.staffs']) }}">
                            <a href="{{ route('admin.reports.staffs') }}">
                                Staff List
                            </a>
                        </li>

                        <li class="{{ set_active(['admin.reports.staff.salary']) }}">
                            <a href="{{ route('admin.reports.staff.salary') }}">
                                Salary
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->