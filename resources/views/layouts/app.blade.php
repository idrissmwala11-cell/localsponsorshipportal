<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Local Sponsorship Portal') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" x-data="{ sidebarOpen: false, profileMenuOpen: false }">
    @php
        $currentUser = auth()->user();
        $currentUserName = $currentUser->name ?? 'User';
        $currentUserEmail = $currentUser->email ?? '';
        $currentUserRole = $currentUser?->display_title ?? ucfirst($currentUser->role ?? 'user');
        $currentUserInitial = strtoupper(substr($currentUserName, 0, 1));
        $accessibleCenterIds = $currentUser?->accessibleCenterIds() ?? [];
        $currentCenterId = $currentUser?->isOfficialAdmin() ? 'ALL' : (count($accessibleCenterIds) > 1 ? implode(', ', $accessibleCenterIds) : ($currentUser->center_id ?? 'N/A'));
        $currentCenterName = $currentUser?->isOfficialAdmin() ? 'All Centers' : (count($accessibleCenterIds) > 1 ? 'Managed Centers' : (optional($currentUser?->center)->center_name ?? $currentCenterId));
        $notificationCount = $layoutUnreadNotificationsCount ?? 0;
        $currentUserPhoto = $currentUser?->profile_photo_url;
        $currentProjectLogos = $currentUser?->project_logo_paths ?? [asset('images/compassion-mark.png')];
        $currentProjectLogo = $currentProjectLogos[0] ?? asset('images/compassion-mark.png');
        $currentProjectName = $currentUser?->project_display_name ?? 'Local Sponsorship Portal';
        $currentPortalTitle = $currentUser?->portal_title ?? \App\Models\User::defaultPortalTitle();
        $currentPortalSubtitle = $currentUser?->portal_subtitle ?? \App\Models\User::defaultPortalSubtitle();
        $hideOperationalAdminLinks = $currentUser?->isAdmin() ?? false;
        $notificationChatLabel = $currentUser?->isAdmin() ? 'Chat with user' : 'Chat with admin';
    @endphp
    <div class="portal-bg min-h-screen">
        {{-- DESKTOP SIDEBAR --}}
        <aside class="hidden fixed inset-y-0 left-0 z-40 w-[248px] flex-col">
            <div class="sidebar-panel flex flex-col h-full glass border-r border-white/5 sidebar-scroll overflow-y-auto">

                {{-- Brand --}}
                <div class="p-5 pb-3">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="brand-badge w-10 h-10 flex items-center justify-center">
                            <img
                                src="{{ $currentProjectLogo }}"
                                alt="{{ $currentProjectName }}"
                                class="js-portal-rotating-logo"
                                data-logos='@json($currentProjectLogos)'>
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-sm leading-tight">{{ $currentProjectName }}</h1>
                            <p class="text-white/70 text-[11px] font-medium">Sponsorship Portal</p>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-3 pt-3 pb-4 space-y-5">
                    <div>
                        <p class="section-title">Main</p>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-grid-1x2-fill"></i></span>
                                    <span>Dashboard</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>

                            @if(!$hideOperationalAdminLinks && Route::has('participants.index'))
                            <a href="{{ route('participants.index') }}" class="nav-item {{ request()->routeIs('participants.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-people-fill"></i></span>
                                    <span>Participants</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif

                            @if(!$hideOperationalAdminLinks && Route::has('sponsorships.index'))
                            <a href="{{ route('sponsorships.index') }}" class="nav-item {{ request()->routeIs('sponsorships.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-cash-coin"></i></span>
                                    <span>Sponsorships</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif
                            @if(!$hideOperationalAdminLinks && Route::has('treatments.index'))
                            <a href="{{ route('treatments.index') }}" class="nav-item {{ request()->routeIs('treatments.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-heart-pulse-fill"></i></span>
                                    <span>Treatment</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif

                            @if(!$hideOperationalAdminLinks && Route::has('attendance.program.index'))
                            <a href="{{ route('attendance.program.index') }}" class="nav-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-calendar-check-fill"></i></span>
                                    <span>Attendance</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            <div class="sidebar-subnav">
                                <a href="{{ route('attendance.program.index') }}" class="sidebar-subnav-link {{ request()->routeIs('attendance.program.*') ? 'active' : '' }}">
                                    <i class="bi bi-journal-check"></i>
                                    <span>Program</span>
                                </a>
                                <a href="{{ route('attendance.activity.index') }}" class="sidebar-subnav-link {{ request()->routeIs('attendance.activity.*') ? 'active' : '' }}">
                                    <i class="bi bi-list-task"></i>
                                    <span>Activity</span>
                                </a>
                            </div>
                            @endif

                            @if(Route::has('notifications.index'))
                            <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-bell-fill"></i></span>
                                    <span>{{ $notificationChatLabel }}</span>
                                </span>
                                <span class="nav-pill">{{ $notificationCount }}</span>
                            </a>
                            @endif

                            @if(Route::has('sponsors.index'))
                            <a href="{{ route('sponsors.index') }}" class="nav-item {{ request()->routeIs('sponsors.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-person-heart"></i></span>
                                    <span>Sponsors</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="section-title">Management</p>
                        <div class="space-y-1">
                            @if($currentUser?->isAdmin() && Route::has('admin.index'))
                            <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-shield-lock-fill"></i></span>
                                    <span>Admin Panel</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif

                            @if($currentUser?->isOfficialAdmin() && Route::has('admin.official.index'))
                            <a href="{{ route('admin.official.index') }}" class="nav-item {{ request()->routeIs('admin.official.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-diagram-3-fill"></i></span>
                                    <span>Oversight</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif

                            @if($currentUser?->isAdmin() && Route::has('admin.users.index'))
                            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-person-badge-fill"></i></span>
                                    <span>Users</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif

                            @if(Route::has('reports.index'))
                            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-bar-chart-line-fill"></i></span>
                                    <span>Reports</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="section-title">Account</p>
                        <div class="space-y-1">
                            @if(Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-person-gear"></i></span>
                                    <span>Profile Settings</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </nav>

                {{-- Logout --}}
                <div class="p-3 border-t border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-item logout-item w-full">
                            <span class="nav-item-label">
                                <span class="nav-icon-box"><i class="bi bi-box-arrow-left"></i></span>
                                <span>Logout</span>
                            </span>
                            <i class="bi bi-chevron-right nav-item-arrow"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- MOBILE SIDEBAR OVERLAY --}}
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 xl:hidden">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-300" x-transition:leave="transition-opacity duration-200"
                 class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="sidebarOpen = false"></div>

            <div x-show="sidebarOpen"
                 x-transition:enter="transform transition duration-300 ease-out" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                 class="sidebar-panel relative w-[280px] max-w-[88vw] h-full glass border-r border-white/5 overflow-y-auto sidebar-scroll">

                <div class="p-5 flex items-center justify-between border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="brand-badge w-10 h-10 flex items-center justify-center">
                            <img
                                src="{{ $currentProjectLogo }}"
                                alt="{{ $currentProjectName }}"
                                class="js-portal-rotating-logo"
                                data-logos='@json($currentProjectLogos)'>
                        </div>
                        <div>
                            <p class="text-white font-bold text-sm">{{ $currentProjectName }}</p>
                            <p class="text-slate-400 text-xs">Sponsorship Portal</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="menu-button w-10 h-10 rounded-2xl text-slate-300 hover:text-white hover:bg-white/12 transition flex items-center justify-center">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <nav class="p-4 space-y-1.5">
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-grid-1x2-fill"></i></span>
                            <span>Dashboard</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @if(!$hideOperationalAdminLinks && Route::has('participants.index'))
                    <a href="{{ route('participants.index') }}" class="nav-item {{ request()->routeIs('participants.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-people-fill"></i></span>
                            <span>Participants</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                  
                    @if(!$hideOperationalAdminLinks && Route::has('sponsorships.index'))
                    <a href="{{ route('sponsorships.index') }}" class="nav-item {{ request()->routeIs('sponsorships.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-cash-coin"></i></span>
                            <span>Sponsorships</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if(!$hideOperationalAdminLinks && Route::has('treatments.index'))
                    <a href="{{ route('treatments.index') }}" class="nav-item {{ request()->routeIs('treatments.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-heart-pulse-fill"></i></span>
                            <span>Treatment</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if(!$hideOperationalAdminLinks && Route::has('attendance.program.index'))
                    <a href="{{ route('attendance.program.index') }}" class="nav-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-calendar-check-fill"></i></span>
                            <span>Attendance</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    <div class="sidebar-subnav">
                        <a href="{{ route('attendance.program.index') }}" class="sidebar-subnav-link {{ request()->routeIs('attendance.program.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-check"></i>
                            <span>Program</span>
                        </a>
                        <a href="{{ route('attendance.activity.index') }}" class="sidebar-subnav-link {{ request()->routeIs('attendance.activity.*') ? 'active' : '' }}">
                            <i class="bi bi-list-task"></i>
                            <span>Activity</span>
                        </a>
                    </div>
                    @endif
                    @if(Route::has('notifications.index'))
                    <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-bell-fill"></i></span>
                            <span>{{ $notificationChatLabel }}</span>
                        </span>
                        <span class="nav-pill">{{ $notificationCount }}</span>
                    </a>
                    @endif
                  
                    @if(Route::has('sponsors.index'))
                    <a href="{{ route('sponsors.index') }}" class="nav-item {{ request()->routeIs('sponsors.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-person-heart"></i></span>
                            <span>Sponsors</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if($currentUser?->isAdmin() && Route::has('admin.index'))
                    <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-shield-lock-fill"></i></span>
                            <span>Admin Panel</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if($currentUser?->isOfficialAdmin() && Route::has('admin.official.index'))
                    <a href="{{ route('admin.official.index') }}" class="nav-item {{ request()->routeIs('admin.official.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-diagram-3-fill"></i></span>
                            <span>Oversight</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if($currentUser?->isAdmin() && Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-person-badge-fill"></i></span>
                            <span>Users</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if(Route::has('reports.index'))
                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-bar-chart-line-fill"></i></span>
                            <span>Reports</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif
                    @if(Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            <span class="nav-icon-box"><i class="bi bi-person-gear"></i></span>
                            <span>Profile Settings</span>
                        </span>
                        <i class="bi bi-chevron-right nav-item-arrow"></i>
                    </a>
                    @endif

                    <div class="pt-4 border-t border-white/5 mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-item logout-item w-full">
                                <span class="nav-item-label">
                                    <span class="nav-icon-box"><i class="bi bi-box-arrow-left"></i></span>
                                    <span>Logout</span>
                                </span>
                                <i class="bi bi-chevron-right nav-item-arrow"></i>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="min-h-screen relative z-[1]">

            {{-- Top Bar --}}
            <header class="topbar-shell sticky top-0 z-30 border-b border-white/5 topbar-shell-nav">
                <div class="topbar-ambient"></div>
                <div class="relative flex items-center justify-between gap-3 px-4 lg:px-6 h-[4.75rem]">
                    <div class="relative z-20 flex items-center gap-4 min-w-0">
                        <button @click="sidebarOpen = true" class="menu-button xl:hidden w-10 h-10 rounded-2xl text-slate-300 hover:text-white hover:bg-white/12 transition flex items-center justify-center">
                            <i class="bi bi-list text-xl"></i>
                        </button>
                        <a href="{{ route('dashboard') }}" class="topbar-brand min-w-0">
                            <div class="brand-badge topbar-brand-mark w-12 h-12 flex items-center justify-center">
                                <img
                                    src="{{ $currentProjectLogo }}"
                                    alt="{{ $currentProjectName }}"
                                    class="js-portal-rotating-logo"
                                    data-logos='@json($currentProjectLogos)'>
                            </div>
                            <div class="hidden sm:block min-w-0">
                                <h2 class="topbar-title text-slate-900 font-bold text-lg leading-tight">{{ strtoupper($currentPortalTitle) }}</h2>
                                <p class="text-slate-400 text-xs">{{ $currentPortalSubtitle }}</p>
                            </div>
                        </a>
                        <div class="sm:hidden min-w-0">
                            <h2 class="topbar-title text-slate-900 font-bold text-lg">{{ $header ?? 'Dashboard' }}</h2>
                        </div>
                    </div>

                    <div class="hidden xl:flex topbar-nav-center px-4">
                        <nav class="topbar-nav">
                            <a href="{{ route('dashboard') }}" class="topbar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-grid-1x2-fill"></i></span><span class="topbar-nav-link-text">Dashboard</span></a>
                            @if(!$hideOperationalAdminLinks && Route::has('participants.index'))
                                <a href="{{ route('participants.index') }}" class="topbar-nav-link {{ request()->routeIs('participants.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-people-fill"></i></span><span class="topbar-nav-link-text">Participants</span></a>
                            @endif
                            @if(!$hideOperationalAdminLinks && Route::has('sponsorships.index'))
                                <a href="{{ route('sponsorships.index') }}" class="topbar-nav-link {{ request()->routeIs('sponsorships.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-cash-coin"></i></span><span class="topbar-nav-link-text">Sponsorships</span></a>
                            @endif
                            @if(!$hideOperationalAdminLinks && Route::has('attendance.program.index'))
                                <div class="topbar-nav-dropdown">
                                    <a href="{{ route('attendance.program.index') }}" class="topbar-nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                                        <span class="topbar-nav-link-icon"><i class="bi bi-calendar-check-fill"></i></span>
                                        <span class="topbar-nav-link-text">Attendance</span>
                                    </a>
                                    <div class="topbar-nav-dropdown-panel">
                                        <a href="{{ route('attendance.program.index') }}" class="topbar-nav-sub-link {{ request()->routeIs('attendance.program.*') ? 'active' : '' }}">
                                            <span>Program</span>
                                            <i class="bi bi-arrow-right-short"></i>
                                        </a>
                                        <a href="{{ route('attendance.activity.index') }}" class="topbar-nav-sub-link {{ request()->routeIs('attendance.activity.*') ? 'active' : '' }}">
                                            <span>Activity</span>
                                            <i class="bi bi-arrow-right-short"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if(!$hideOperationalAdminLinks && Route::has('treatments.index'))
                                <a href="{{ route('treatments.index') }}" class="topbar-nav-link {{ request()->routeIs('treatments.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-heart-pulse-fill"></i></span><span class="topbar-nav-link-text">Treatment</span></a>
                            @endif
                            @if(Route::has('sponsors.index'))
                                <a href="{{ route('sponsors.index') }}" class="topbar-nav-link {{ request()->routeIs('sponsors.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-person-heart"></i></span><span class="topbar-nav-link-text">Sponsors</span></a>
                            @endif
                            @if($currentUser?->isAdmin() && Route::has('admin.index'))
                                <a href="{{ route('admin.index') }}" class="topbar-nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-shield-lock-fill"></i></span><span class="topbar-nav-link-text">Admin Panel</span></a>
                            @endif
                            @if($currentUser?->isOfficialAdmin() && Route::has('admin.official.index'))
                                <a href="{{ route('admin.official.index') }}" class="topbar-nav-link {{ request()->routeIs('admin.official.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-diagram-3-fill"></i></span><span class="topbar-nav-link-text">Oversight</span></a>
                            @endif
                            @if($currentUser?->isAdmin() && Route::has('admin.users.index'))
                                <a href="{{ route('admin.users.index') }}" class="topbar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-person-badge-fill"></i></span><span class="topbar-nav-link-text">Users</span></a>
                            @endif
                            @if(Route::has('reports.index'))
                                <a href="{{ route('reports.index') }}" class="topbar-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="topbar-nav-link-icon"><i class="bi bi-bar-chart-line-fill"></i></span><span class="topbar-nav-link-text">Reports</span></a>
                            @endif
                        </nav>
                    </div>

                    <div class="relative z-20 flex items-center gap-3 shrink-0 topbar-actions">
                        <div class="hidden md:block relative"
                             x-data="{
                                query: '',
                                results: [],
                                open: false,
                                loading: false,
                                timeout: null,
                                widthStyle() {
                                    const length = Math.max(this.query.length, 8);
                                    const width = Math.min(Math.max(length + 4, 8), 28);
                                    return `width: ${width}ch;`;
                                },
                                async runSearch() {
                                    clearTimeout(this.timeout);
                                    if (this.query.trim().length < 2) {
                                        this.results = [];
                                        this.open = false;
                                        return;
                                    }
                                    this.timeout = setTimeout(async () => {
                                        this.loading = true;
                                        try {
                                            const response = await fetch(`{{ route('participants.search') }}?q=${encodeURIComponent(this.query)}`, {
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                    'Accept': 'application/json',
                                                }
                                            });
                                            this.results = await response.json();
                                            this.open = true;
                                        } catch (error) {
                                            this.results = [];
                                            this.open = false;
                                        } finally {
                                            this.loading = false;
                                        }
                                    }, 220);
                                }
                             }"
                             @click.outside="open = false">
                            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                            <input type="text"
                                   placeholder="Search participants..."
                                   class="search-input pl-10 pr-3 py-2 text-sm transition-all duration-200 ease-out"
                                   :style="widthStyle()"
                                   x-model="query"
                                   @focus="if (results.length) open = true"
                                   @input="runSearch()">

                            <div x-cloak x-show="open" x-transition class="search-results-panel">
                                <div class="search-results-header">
                                    <span x-show="loading">Searching...</span>
                                    <span x-show="!loading">Participants in {{ $currentCenterId }}</span>
                                </div>

                                <template x-if="results.length">
                                    <div class="py-2">
                                        <template x-for="participant in results" :key="participant.id">
                                            <a :href="participant.url" class="search-result-item">
                                                <div>
                                                    <p class="search-result-name" x-text="participant.name"></p>
                                                    <p class="search-result-meta">
                                                        <span x-text="participant.participant_id"></span>
                                                        <span x-show="participant.preferred_name"> | </span>
                                                        <span x-text="participant.preferred_name || ''"></span>
                                                    </p>
                                                </div>
                                                <i class="bi bi-arrow-up-right"></i>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!loading && !results.length && query.trim().length >= 2">
                                    <div class="search-empty-state">
                                        No participant found in your center.
                                    </div>
                                </template>
                            </div>
                        </div>

                        @if(Route::has('notifications.index'))
                        <a href="{{ route('notifications.index') }}" class="notification-button">
                            <i class="bi bi-bell-fill"></i>
                            <span class="notification-button-label">{{ $notificationChatLabel }}</span>
                            @if($notificationCount > 0)
                                <span class="notification-badge">{{ $notificationCount }}</span>
                            @endif
                        </a>
                        @endif

                        <details class="user-menu relative z-50">
                            <summary class="topbar-user flex items-center gap-2.5 list-none cursor-pointer">
                                @if($currentUserPhoto)
                                    <img src="{{ $currentUserPhoto }}" alt="{{ $currentUserName }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200">
                                @else
                                    <div class="user-avatar w-9 h-9 rounded-xl flex items-center justify-center text-blue-100 font-bold text-sm">
                                        {{ $currentUserInitial }}
                                    </div>
                                @endif
                                <div class="hidden sm:block text-left">
                                    <p class="text-slate-900 text-sm font-semibold leading-tight">{{ $currentUserName }}</p>
                                    <p class="text-slate-500 text-xs">{{ $currentUserRole }} | {{ $currentCenterId }}</p>
                                </div>
                                <span class="user-menu-trigger">
                                    <i class="bi bi-chevron-down user-menu-chevron text-slate-500 text-[11px]"></i>
                                </span>
                            </summary>

                            <div class="user-menu-panel absolute right-0 mt-3 z-50 w-64 rounded-3xl border border-slate-200 bg-white p-3 shadow-xl">
                                <div class="flex items-center gap-3 px-2 py-2">
                                    @if($currentUserPhoto)
                                        <img src="{{ $currentUserPhoto }}" alt="{{ $currentUserName }}" class="w-11 h-11 rounded-2xl object-cover border border-slate-200">
                                    @else
                                        <div class="user-avatar w-11 h-11 rounded-2xl flex items-center justify-center text-blue-100 font-bold text-sm">
                                            {{ $currentUserInitial }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-slate-900 text-sm font-semibold truncate">{{ $currentUserName }}</p>
                                        <p class="text-slate-500 text-xs truncate">{{ $currentUserEmail }}</p>
                                        <p class="text-slate-400 text-xs mt-1">{{ $currentCenterId }}</p>
                                    </div>
                                </div>

                                <div class="mt-2 border-t border-slate-100 pt-2 space-y-1">
                                    @if(Route::has('profile.edit'))
                                    <a href="{{ route('profile.edit') }}" class="menu-dropdown-item">
                                        <i class="bi bi-person-circle"></i>
                                        <span>Profile</span>
                                    </a>

                                    <a href="{{ route('profile.edit') }}" class="menu-dropdown-item">
                                        <i class="bi bi-person-gear"></i>
                                        <span>Profile Settings</span>
                                    </a>

                                    <button type="button" class="menu-dropdown-item w-full text-left" onclick="window.alert('Coming soon')">
                                        <i class="bi bi-gear"></i>
                                        <span>Settings</span>
                                    </button>
                                    @endif

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="menu-dropdown-item w-full">
                                            <i class="bi bi-box-arrow-left"></i>
                                            <span>Logout</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </header>

            {{-- Optional Page Header --}}
            @isset($pageHeader)
            <div class="px-5 lg:px-8 pt-6">
                {{ $pageHeader }}
            </div>
            @endisset

            {{-- Page Content --}}
            <main class="min-h-screen px-0 pb-8 fade-up">
                {{ $slot }}
            </main>

            <x-system-footer />
        </div>
    </div>

    <script>
        (() => {
            const logos = document.querySelectorAll('.js-portal-rotating-logo');

            logos.forEach((logo) => {
                const items = JSON.parse(logo.dataset.logos || '[]');

                if (items.length < 2) {
                    return;
                }

                let currentIndex = 0;

                setInterval(() => {
                    currentIndex = (currentIndex + 1) % items.length;
                    logo.style.opacity = '0';

                    setTimeout(() => {
                        logo.src = items[currentIndex];
                        logo.style.opacity = '1';
                    }, 250);
                }, 5000);
            });
        })();
    </script>
</body>
</html>


