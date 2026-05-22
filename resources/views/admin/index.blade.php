<x-app-layout>
    <x-slot name="header">Admin Panel</x-slot>

    <div class="workspace-page">
        <div class="workspace-container space-y-6">
            <div class="workspace-hero p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                    <div>
                        <p class="workspace-label">Phase 11</p>
                        <h1 class="text-3xl lg:text-5xl font-black text-slate-900 mt-3">{{ auth()->user()->isOfficialAdmin() ? 'Official Admin Panel' : 'Center Admin Panel' }}</h1>
                        <p class="text-slate-500 text-sm mt-3 max-w-3xl">
                            {{ auth()->user()->isOfficialAdmin()
                                ? 'Monitor all centers, review admins, run global reports, and oversee the full system from one place.'
                                : (($isSupervisionDashboard ?? false)
                                    ? 'Monitor the users under your supervision, follow their approval status, and manage your assigned project from one place.'
                                    : 'Manage users, monitor center notifications, run reports, and review participant and sponsorship records for the centers you manage.') }}
                        </p>
                    </div>
                    <div class="rounded-3xl border border-blue-100 bg-blue-50 px-5 py-4">
                        <p class="workspace-label">{{ ($isSupervisionDashboard ?? false) ? 'Managed Cluster' : 'Center Lock' }}</p>
                        <p class="text-slate-900 font-bold text-lg mt-2">{{ ($isSupervisionDashboard ?? false) ? ($managedClustersLabel ?? 'N/A') : $centerId }}</p>
                        <p class="text-slate-500 text-sm mt-1">{{ ($isSupervisionDashboard ?? false) ? ('Project: ' . ($managedProjectName ?? 'N/A')) : (($isOfficialAdminDashboard ?? false) ? 'Global system access for all admins and users.' : 'Cross-center access is blocked.') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="workspace-stat p-5">
                    <p class="workspace-label">{{ ($isOfficialAdminDashboard ?? false) ? 'Admins' : 'Users' }}</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ ($isOfficialAdminDashboard ?? false) ? ($adminsCount ?? 0) : $usersCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ ($isOfficialAdminDashboard ?? false) ? 'All official admins and center admins currently registered in the system.' : (($isSupervisionDashboard ?? false) ? 'Total users currently assigned under your supervision.' : (($adminsCount ?? 0) . ' admin users in this center, including newly registered portal users.')) }}</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">{{ ($isOfficialAdminDashboard ?? false) ? 'Approved Admins' : (($isSupervisionDashboard ?? false) ? 'Approved' : 'Participants') }}</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ ($isOfficialAdminDashboard ?? false) ? ($approvedAdminsCount ?? 0) : (($isSupervisionDashboard ?? false) ? ($approvedUsersCount ?? 0) : $participantsCount) }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ ($isOfficialAdminDashboard ?? false) ? 'Admin accounts already approved and active in the system.' : (($isSupervisionDashboard ?? false) ? 'Supervised users who are fully approved and active.' : ($activeParticipantsCount . ' active participant records. User signups are not counted here.')) }}</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">{{ ($isOfficialAdminDashboard ?? false) ? 'Users' : (($isSupervisionDashboard ?? false) ? 'Pending' : 'Sponsorships') }}</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ ($isOfficialAdminDashboard ?? false) ? $usersCount : (($isSupervisionDashboard ?? false) ? ($pendingUsersCount ?? 0) : $sponsorshipsCount) }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ ($isOfficialAdminDashboard ?? false) ? 'Standard user accounts registered across the whole system.' : (($isSupervisionDashboard ?? false) ? 'Supervised users still waiting for approval.' : 'Tracked sponsorship records linked to participants only.') }}</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">{{ ($isOfficialAdminDashboard ?? false) ? 'Pending Users' : (($isSupervisionDashboard ?? false) ? 'Projects' : 'Notifications') }}</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ ($isOfficialAdminDashboard ?? false) ? ($pendingUsersCount ?? 0) : (($isSupervisionDashboard ?? false) ? ($projectCount ?? 0) : $notificationsCount) }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ ($isOfficialAdminDashboard ?? false) ? 'User accounts still waiting for system approval.' : (($isSupervisionDashboard ?? false) ? 'Distinct user project names inside your supervision list.' : 'Unread admin messages for your centers.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="workspace-panel p-6 xl:col-span-2">
                    <p class="workspace-label">Admin Tools</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Workflows</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                        <a href="{{ route('admin.users.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 hover:border-blue-200 hover:bg-blue-50/40 transition">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                                <span class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Users</span>
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-slate-900">Manage Users</h3>
                            <p class="mt-2 text-sm text-slate-500">Add users, edit profiles, assign roles, and reset passwords for your center.</p>
                        </a>

                        <a href="{{ route('reports.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 hover:border-blue-200 hover:bg-blue-50/40 transition">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                    <i class="bi bi-bar-chart-line-fill"></i>
                                </span>
                                <span class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Reports</span>
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-slate-900">Run Center Reports</h3>
                            <p class="mt-2 text-sm text-slate-500">Review participant, user, and notification summaries and export CSV reports.</p>
                        </a>

                        @unless(($isSupervisionDashboard ?? false) || ($isOfficialAdminDashboard ?? false))
                            <a href="{{ route('participants.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 hover:border-blue-200 hover:bg-blue-50/40 transition">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                                        <i class="bi bi-person-vcard-fill"></i>
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600">Participants</span>
                                </div>
                                <h3 class="mt-4 text-lg font-bold text-slate-900">View Center Participants</h3>
                                <p class="mt-2 text-sm text-slate-500">Open the participant workspace already filtered to your center only.</p>
                            </a>
                        @endunless

                        <a href="{{ route('notifications.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 hover:border-blue-200 hover:bg-blue-50/40 transition">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                                    <i class="bi bi-bell-fill"></i>
                                </span>
                                <span class="text-xs font-bold uppercase tracking-[0.18em] text-rose-600">Notifications</span>
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-slate-900">View Center Notifications</h3>
                            <p class="mt-2 text-sm text-slate-500">Track new updates, coming due items, overdue items, and unread counts.</p>
                        </a>

                        @if(auth()->user()->isOfficialAdmin() && Route::has('admin.official.index'))
                            <a href="{{ route('admin.official.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 hover:border-blue-200 hover:bg-blue-50/40 transition">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                                        <i class="bi bi-diagram-3-fill"></i>
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Oversight</span>
                                </div>
                                <h3 class="mt-4 text-lg font-bold text-slate-900">Official Oversight</h3>
                                <p class="mt-2 text-sm text-slate-500">Review all admins, center coverage, and global system activity.</p>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="workspace-panel p-6">
                        <p class="workspace-label">{{ ($isSupervisionDashboard ?? false) ? 'Supervised Users' : (($isOfficialAdminDashboard ?? false) ? 'System Accounts' : 'Registered Users') }}</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">{{ ($isSupervisionDashboard ?? false) ? 'User Status Monitor' : (($isOfficialAdminDashboard ?? false) ? 'Latest Admins And Users' : 'Latest Signups') }}</h2>
                        <div class="mt-5 space-y-3">
                            @forelse($recentUsers as $recentUser)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-bold uppercase tracking-[0.18em] {{ $recentUser->isApproved() ? 'text-emerald-600' : 'text-amber-700' }}">{{ $recentUser->isApproved() ? 'Approved' : 'Pending' }}</span>
                                        <span class="text-xs text-slate-400">{{ $recentUser->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $recentUser->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $recentUser->email }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ \App\Models\User::roles()[$recentUser->role] ?? ucfirst((string) $recentUser->role) }} | {{ $recentUser->center_id ?: 'ALL' }}</p>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                                    {{ ($isSupervisionDashboard ?? false) ? 'No supervised users have been selected yet.' : (($isOfficialAdminDashboard ?? false) ? 'No admins or users have been registered yet.' : 'No recently registered users yet.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="workspace-panel p-6">
                        <p class="workspace-label">Admin Messages</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Latest Notifications</h2>
                        <div class="mt-5 space-y-3">
                            @forelse($recentNotifications as $notification)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">{{ str_replace('_', ' ', $notification->type) }}</span>
                                        <span class="text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $notification->title }}</p>
                                    <p class="mt-2 text-sm text-slate-500">{{ $notification->message }}</p>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                                    No admin messages yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
