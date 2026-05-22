<x-app-layout>
    <x-slot name="header">Center Users</x-slot>

    <div class="workspace-page">
        <div class="workspace-container space-y-6">
            <div class="workspace-hero p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="workspace-label">User Management</p>
                        <h1 class="text-3xl lg:text-5xl font-black text-slate-900 mt-3">Users</h1>
                        <p class="text-slate-500 text-sm mt-3">
                            {{ auth()->user()->isOfficialAdmin()
                                ? 'View all users, official admins, center admins, and their center assignments.'
                                : 'View, add, edit, assign roles, and reset passwords for users in your managed centers.' }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, role..." class="workspace-input px-4 py-3 sm:w-80">
                            <button type="submit" class="btn-primary justify-center">Search</button>
                        </form>
                        <a href="{{ route('admin.users.create') }}" class="btn-primary"><i class="bi bi-plus-lg"></i> Add User</a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="workspace-flash-success p-4 text-sm break-all">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="workspace-flash-error p-4 text-sm break-all">{{ session('error') }}</div>
            @endif

            <div class="workspace-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Approval</th>
                                <th>Center</th>
                                <th>Managed Centers</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $user->name }}</td>
                                    <td class="text-slate-600">{{ $user->email }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-blue-600">
                                            {{ $roles[$user->role] ?? ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->isApproved())
                                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-emerald-600">
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-amber-700">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-slate-600">{{ $user->center_id }}</td>
                                    <td class="text-slate-600">
                                        @if($user->managedCenters->count())
                                            {{ $user->managedCenters->pluck('center_id')->implode(', ') }}
                                        @elseif($user->isOfficialAdmin())
                                            ALL
                                        @else
                                            {{ $user->center_id }}
                                        @endif
                                    </td>
                                    <td class="text-slate-600">{{ $user->created_at?->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn-ghost">View</a>
                                            @if(auth()->user()->isOfficialAdmin() && !$user->isApproved() && !$user->isOfficialAdmin())
                                                <form method="POST" action="{{ route('admin.users.approve', $user) }}" onsubmit="return confirm('Approve this user account now?')">
                                                    @csrf
                                                    <button type="submit" class="btn-primary">Approve</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn-ghost">Edit</a>
                                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('Reset password for this user?')">
                                                @csrf
                                                <button type="submit" class="btn-ghost">Reset Password</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-500">No users found for your access scope.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t border-slate-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
