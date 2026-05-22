<x-app-layout>
    <x-slot name="header">Official Admin Oversight</x-slot>

    <div class="workspace-page">
        <div class="workspace-container space-y-6">
            <div class="workspace-hero p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                    <div>
                        <p class="workspace-label">Official Admin</p>
                        <h1 class="text-3xl lg:text-5xl font-black text-slate-900 mt-3">System Oversight</h1>
                        <p class="text-slate-500 text-sm mt-3 max-w-3xl">
                            Monitor all centers, review admins and center assignments, and track global activity across the full system.
                        </p>
                    </div>
                    <div class="rounded-3xl border border-blue-100 bg-blue-50 px-5 py-4">
                        <p class="workspace-label">Access Scope</p>
                        <p class="text-slate-900 font-bold text-lg mt-2">All Centers</p>
                        <p class="text-slate-500 text-sm mt-1">Full monitoring and control.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Centers</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $centersCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">Registered centers in the system.</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Users</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $usersCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ $adminsCount }} center admins, {{ $officialAdminsCount }} official admins.</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Participants</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $participantsCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">Global participant records.</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Sponsorships</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $sponsorshipsCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ $notificationsCount }} notifications generated system-wide.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="workspace-panel overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200">
                        <p class="workspace-label">Admin Directory</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Official And Center Admins</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full modern-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Primary Center</th>
                                    <th>Managed Centers</th>
                                    <th>Centers Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAdmins as $admin)
                                    <tr>
                                        <td class="font-semibold text-slate-900">{{ $admin->name }}</td>
                                        <td class="text-slate-600">{{ $admin->email }}</td>
                                        <td class="text-slate-600">{{ $admin->display_title }}</td>
                                        <td class="text-slate-600">{{ $admin->center_id ?: 'N/A' }}</td>
                                        <td class="text-slate-600">
                                            @if($admin->managedCenters->count())
                                                {{ $admin->managedCenters->pluck('center_id')->implode(', ') }}
                                            @elseif($admin->isOfficialAdmin())
                                                ALL
                                            @else
                                                {{ $admin->center_id ?: 'N/A' }}
                                            @endif
                                        </td>
                                        <td class="text-slate-600">{{ $admin->isOfficialAdmin() ? 'All' : max($admin->managedCenters->count(), $admin->center_id ? 1 : 0) }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $admin) }}" class="btn-ghost">View Profile</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-slate-500">No admin accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="workspace-panel overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200">
                        <p class="workspace-label">Center Coverage</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Center Monitoring Summary</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full modern-table">
                            <thead>
                                <tr>
                                    <th>Center</th>
                                    <th>Name</th>
                                    <th>Users</th>
                                    <th>Admins</th>
                                    <th>Participants</th>
                                    <th>Notifications</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($centerSummaries as $center)
                                    <tr>
                                        <td class="font-semibold text-slate-900">{{ $center['center_id'] }}</td>
                                        <td class="text-slate-600">{{ $center['center_name'] ?: 'N/A' }}</td>
                                        <td class="text-slate-600">{{ $center['users_count'] }}</td>
                                        <td class="text-slate-600">{{ $center['admins_count'] }}</td>
                                        <td class="text-slate-600">{{ $center['participants_count'] }}</td>
                                        <td class="text-slate-600">{{ $center['notifications_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-500">No centers available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
