@php
    $user = auth()->user();
@endphp

<div class="d-flex flex-column h-100">
    <div class="mb-3 px-3">
        <span class="text-uppercase text-gold fw-bold" style="font-size: 0.72rem; letter-spacing: 0.08em;">
            {{ $user->role }} Workspace
        </span>
    </div>

    <!-- Main Navigation Items -->
    <nav class="nav flex-column mb-auto">
        <a class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Overview</span>
        </a>

        @if($user->isAdmin())
            <div class="text-muted small fw-semibold text-uppercase px-3 mt-3 mb-1" style="font-size: 0.65rem;">Administration</div>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people-fill"></i>
                <span>Users & Roles</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.instruments.*') ? 'active' : '' }}" href="{{ route('admin.instruments.index') }}">
                <i class="bi bi-music-player-fill"></i>
                <span>Instruments</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">
                <i class="bi bi-collection-play-fill"></i>
                <span>All Courses</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                <i class="bi bi-cash-stack"></i>
                <span>Tuition & Fees</span>
            </a>

            <!-- Dedicated Site Customizer Page Selector in Sidebar -->
            <div class="text-muted small fw-semibold text-uppercase px-3 mt-3 mb-1 d-flex align-items-center justify-content-between" style="font-size: 0.65rem;">
                <span>Site Customizer</span>
                <span class="badge bg-gold text-dark" style="font-size: 0.6rem;">CMS</span>
            </div>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && (!request('tab') || request('tab') == 'colors') ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'colors']) }}">
                <i class="bi bi-palette2 text-gold"></i>
                <span>Theme & Colors</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'branding' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'branding']) }}">
                <i class="bi bi-badge-ad text-gold"></i>
                <span>Logo & Branding</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'navigation' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'navigation']) }}">
                <i class="bi bi-menu-button-wide text-gold"></i>
                <span>Header & Nav Menus</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'hero' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'hero']) }}">
                <i class="bi bi-megaphone text-gold"></i>
                <span>Landing: Hero</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'sections' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'sections']) }}">
                <i class="bi bi-grid-3x3 text-gold"></i>
                <span>Landing: Sections</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'about' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'about']) }}">
                <i class="bi bi-bank2 text-gold"></i>
                <span>About Us Page</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'student' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'student']) }}">
                <i class="bi bi-mortarboard text-gold"></i>
                <span>Student Portal</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'instructor' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'instructor']) }}">
                <i class="bi bi-music-note-beamed text-gold"></i>
                <span>Instructor Portal</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') && request('tab') == 'footer' ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => 'footer']) }}">
                <i class="bi bi-telephone text-gold"></i>
                <span>Contact & Footer</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.blogs.index') || request()->routeIs('admin.blogs.create') || request()->routeIs('admin.blogs.edit') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}">
                <i class="bi bi-journal-richtext text-gold"></i>
                <span>Blog Publications</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('admin.blogs.comments.*') ? 'active' : '' }}" href="{{ route('admin.blogs.comments.index') }}">
                <i class="bi bi-chat-square-quote text-gold"></i>
                <span>Blog Comments</span>
                @php $pendingBlogComments = \App\Models\BlogComment::pending()->count(); @endphp
                @if($pendingBlogComments > 0)
                    <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.65rem;">{{ $pendingBlogComments }}</span>
                @endif
            </a>
        @endif

        @if($user->isInstructor())
            <div class="text-muted small fw-semibold text-uppercase px-3 mt-3 mb-1" style="font-size: 0.65rem;">Instruction</div>
            <a class="sidebar-nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>My Courses</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}" href="{{ route('assignments.index') }}">
                <i class="bi bi-mic-fill"></i>
                <span>Submissions & Grading</span>
            </a>
        @endif

        @if($user->isStudent())
            <div class="text-muted small fw-semibold text-uppercase px-3 mt-3 mb-1" style="font-size: 0.65rem;">My Music Studies</div>
            <a class="sidebar-nav-link {{ request()->routeIs('student.courses') ? 'active' : '' }}" href="{{ route('student.courses') }}">
                <i class="bi bi-music-note-list"></i>
                <span>My Courses</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}" href="{{ route('assignments.index') }}">
                <i class="bi bi-mic-fill"></i>
                <span>Practice Assignments</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('student.certificates') ? 'active' : '' }}" href="{{ route('student.certificates') }}">
                <i class="bi bi-award-fill"></i>
                <span>Certificates</span>
            </a>
            <a class="sidebar-nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                <i class="bi bi-credit-card-2-front-fill"></i>
                <span>Tuition & Balance</span>
            </a>
        @endif

        <div class="text-muted small fw-semibold text-uppercase px-3 mt-3 mb-1" style="font-size: 0.65rem;">Academy Life</div>
        <a class="sidebar-nav-link {{ request()->routeIs('schedule.*') ? 'active' : '' }}" href="{{ route('schedule.index') }}">
            <i class="bi bi-calendar-event-fill"></i>
            <span>Class Schedule</span>
        </a>
        <a class="sidebar-nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}" href="{{ route('announcements.index') }}">
            <i class="bi bi-megaphone-fill"></i>
            <span>Announcements</span>
        </a>
        <a class="sidebar-nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
            <i class="bi bi-chat-dots-fill"></i>
            <span>Messages</span>
        </a>
    </nav>

    <!-- Bottom User Status -->
    <div class="pt-3 mt-3 border-top border-secondary">
        <a class="sidebar-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
            <i class="bi bi-gear-fill"></i>
            <span>My Settings</span>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="mt-1">
            @csrf
            <button type="submit" class="sidebar-nav-link border-0 bg-transparent text-danger w-100 text-start">
                <i class="bi bi-box-arrow-right text-danger"></i>
                <span>Log Out</span>
            </button>
        </form>
    </div>
</div>
