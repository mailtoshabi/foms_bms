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

                <li class="{{ set_active(['teacher.classes.*','teacher.sessions.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('teacher.classes.index') }}">Classes</a></li>
                        <li><a href="{{ route('teacher.sessions.index') }}">Sessions</a></li>
                    </ul>
                </li>

                {{-- ================= Class Notes ================= --}}
                <li class="{{ set_active(['teacher.notes.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-file-upload"></i>
                        <span>Class Notes</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="{{ route('teacher.notes.index') }}">My Notes</a></li>
                        <li><a href="{{ route('teacher.notes.create') }}">Upload Notes</a></li>
                    </ul>
                </li>

                {{-- ================= Messages ================= --}}
                <li class="{{ set_active(['teacher.messages.*']) }}">
                    <a href="javascript:void(0);" class="has-arrow">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </a>

                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('teacher.messages.index') }}">Inbox</a></li>
                        <li><a href="{{ route('teacher.messages.create') }}">New Message</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->

