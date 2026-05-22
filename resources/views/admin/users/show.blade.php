<x-app-layout>
    <x-slot name="header">User Detail</x-slot>

    @php
        $totalUploads = $sponsorshipsCount + $treatmentsCount + $programAttendanceCount + $activityAttendanceCount;
    @endphp

    <div class="workspace-page">
        <div class="workspace-container space-y-6">
            <div class="workspace-hero p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <p class="workspace-label">User Profile</p>
                        <h1 class="text-3xl lg:text-5xl font-black text-slate-900 mt-3">{{ $managedUser->name }}</h1>
                        <p class="text-slate-500 text-sm mt-3 max-w-3xl">
                            Review account details, scope, uploaded records, and supervision summary for this account.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="#user-participants" class="btn-primary">Participants</a>
                        <a href="#user-sponsorships" class="btn-ghost">Sponsorships</a>
                        <a href="#user-treatments" class="btn-ghost">Treatment</a>
                        <a href="#user-program-attendance" class="btn-ghost">Program Attendance</a>
                        <a href="#user-activity-attendance" class="btn-ghost">Activity Attendance</a>
                        <a href="{{ route('admin.users.edit', $managedUser) }}" class="btn-primary">Edit User</a>
                        <a href="{{ route('admin.users.index') }}" class="btn-ghost">Back To Users</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Role</p>
                    <h3 class="text-xl font-black text-slate-900 mt-3">{{ $managedUser->display_title }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ $managedUser->isApproved() ? 'Approved account' : 'Pending approval' }}</p>
                </div>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Visible Users</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $managedUsersCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">{{ $managedAdminsCount }} admins inside this access scope.</p>
                </div>
                <a href="#user-participants" class="workspace-stat p-5 block hover:border-blue-200 transition">
                    <p class="workspace-label">Participants</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $participantsCount }}</h3>
                    <p class="text-sm text-slate-500 mt-2">Click to view participant records added by this user.</p>
                </a>
                <div class="workspace-stat p-5">
                    <p class="workspace-label">Uploads</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">{{ $totalUploads }}</h3>
                    <p class="text-sm text-slate-500 mt-2">
                        {{ $sponsorshipsCount }} sponsorships, {{ $treatmentsCount }} treatments,
                        {{ $programAttendanceCount }} program, {{ $activityAttendanceCount }} activity.
                    </p>
                </div>
            </div>

            <div class="workspace-panel p-4">
                <div class="flex flex-wrap gap-2">
                    <a href="#user-participants" class="btn-action">Participants ({{ $participantsCount }})</a>
                    <a href="#user-sponsorships" class="btn-action">Sponsorships ({{ $sponsorshipsCount }})</a>
                    <a href="#user-treatments" class="btn-action">Treatment ({{ $treatmentsCount }})</a>
                    <a href="#user-program-attendance" class="btn-action">Program Attendance ({{ $programAttendanceCount }})</a>
                    <a href="#user-activity-attendance" class="btn-action">Activity Attendance ({{ $activityAttendanceCount }})</a>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="workspace-panel p-6">
                    <p class="workspace-label">Account Details</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Profile Summary</h2>
                    <div class="mt-5 space-y-3 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-900">Email:</span> {{ $managedUser->email }}</p>
                        <p><span class="font-semibold text-slate-900">Project:</span> {{ $managedUser->project_display_name }}</p>
                        <p><span class="font-semibold text-slate-900">Primary Center:</span> {{ $managedUser->center_id ?: 'N/A' }}</p>
                        <p><span class="font-semibold text-slate-900">Managed Centers:</span>
                            {{ $managedUser->managedCenters->count() ? $managedUser->managedCenters->pluck('center_id')->implode(', ') : ($managedUser->isOfficialAdmin() ? 'ALL' : ($managedUser->center_id ?: 'N/A')) }}
                        </p>
                        <p><span class="font-semibold text-slate-900">Registered:</span> {{ $managedUser->created_at?->format('Y-m-d H:i') }}</p>
                        <p><span class="font-semibold text-slate-900">Visible Scope:</span> {{ implode(', ', $scopeCenterIds) ?: 'N/A' }}</p>
                    </div>
                    <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
                        Passwords are stored securely and cannot be viewed directly. Use the reset password action to generate a temporary password when needed.
                    </div>
                </div>

                <div class="workspace-panel p-6">
                    <p class="workspace-label">Church Background</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Assigned Church Profile</h2>
                    @if($churchProfile)
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-900">Church Name:</span> {{ $churchProfile->church_name ?: 'N/A' }}</p>
                            <p><span class="font-semibold text-slate-900">Mission:</span> {{ $churchProfile->mission ?: 'N/A' }}</p>
                            <p><span class="font-semibold text-slate-900">Vision:</span> {{ $churchProfile->vision ?: 'N/A' }}</p>
                            <p><span class="font-semibold text-slate-900">Historical Background:</span> {{ $churchProfile->historical_background ?: 'N/A' }}</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                            No church profile is available for this account's primary center.
                        </div>
                    @endif
                </div>

                <div class="workspace-panel p-6">
                    <p class="workspace-label">{{ $managedUser->isAdmin() ? 'Admin Supervision Report' : 'Scope Summary' }}</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">{{ $managedUser->isAdmin() ? 'Management Snapshot' : 'Record Summary' }}</h2>
                    <div class="mt-5 space-y-3 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-900">Managed Centers Count:</span> {{ $managedUser->isOfficialAdmin() ? 'All Centers' : $managedUser->managedCenters->count() }}</p>
                        <p><span class="font-semibold text-slate-900">Visible Users:</span> {{ $managedUsersCount }}</p>
                        <p><span class="font-semibold text-slate-900">Participants:</span> {{ $participantsCount }}</p>
                        <p><span class="font-semibold text-slate-900">Sponsorships:</span> {{ $sponsorshipsCount }}</p>
                        <p><span class="font-semibold text-slate-900">Treatments:</span> {{ $treatmentsCount }}</p>
                        <p><span class="font-semibold text-slate-900">Program Attendance:</span> {{ $programAttendanceCount }}</p>
                        <p><span class="font-semibold text-slate-900">Activity Attendance:</span> {{ $activityAttendanceCount }}</p>
                        <p><span class="font-semibold text-slate-900">Notifications:</span> {{ $notificationsCount }}</p>
                    </div>
                </div>
            </div>

            @if($managedUser->isAdmin())
                <div class="workspace-panel overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200">
                        <p class="workspace-label">Managed Users</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Users Under This Admin</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full modern-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Center</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supervisedUsers as $scopeUser)
                                    <tr>
                                        <td class="font-semibold text-slate-900">{{ $scopeUser->name }}</td>
                                        <td class="text-slate-600">{{ $scopeUser->email }}</td>
                                        <td class="text-slate-600">{{ $scopeUser->display_title }}</td>
                                        <td class="text-slate-600">{{ $scopeUser->center_id ?: 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $scopeUser) }}" class="btn-action">View User</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center text-slate-500">No supervised users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div id="user-participants" class="workspace-panel overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="workspace-label">Participants</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">
                            {{ $managedUser->role === \App\Models\User::ROLE_USER ? 'Participants Added By This User' : 'Participants In This User Scope' }}
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            View every participant record linked to {{ $managedUser->name }} and export one participant at a time.
                        </p>
                    </div>
                    <div class="text-sm font-semibold text-slate-500">
                        Total: {{ $participantsCount }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Participant ID</th>
                                <th>Center</th>
                                <th>Status</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userParticipants as $participant)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $participant->preferred_name ?: $participant->account_name ?: 'N/A' }}</div>
                                        <div class="text-xs text-slate-500">{{ $participant->account_name ?: 'No project name' }}</div>
                                    </td>
                                    <td class="text-slate-600">{{ $participant->local_participant_id ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $participant->center_id ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $participant->participant_status ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $participant->created_at?->format('Y-m-d H:i') ?: 'N/A' }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('participants.show', $participant) }}" class="btn-action">View</a>
                                            <a href="{{ route('admin.users.participants.export', [$managedUser, $participant]) }}" class="btn-action btn-action-green">Export Excel</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-500">
                                        No participants have been added by this user yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($userParticipants->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $userParticipants->fragment('user-participants')->links() }}
                    </div>
                @endif
            </div>

            <div id="user-sponsorships" class="workspace-panel overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="workspace-label">Sponsorships</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Sponsorship Data Added By This User</h2>
                        <p class="text-sm text-slate-500 mt-1">All sponsorship records connected to this account.</p>
                    </div>
                    <div class="text-sm font-semibold text-slate-500">Total: {{ $sponsorshipsCount }}</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th>Sponsor</th>
                                <th>Participant</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Contact</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userSponsorships as $sponsorship)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $sponsorship->sponsor_name ?: $sponsorship->sponsored_by ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ optional($sponsorship->participant)->preferred_name ?: optional($sponsorship->participant)->account_name ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $sponsorship->sponsorship_type ?: $sponsorship->sponsor_type ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $sponsorship->sponsorship_status ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $sponsorship->sponsorship_start_date?->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $sponsorship->sponsor_contact ?: 'N/A' }}</td>
                                    <td>
                                        @if($sponsorship->participant)
                                            <a href="{{ route('participants.show', $sponsorship->participant) }}" class="btn-action">View Participant</a>
                                        @else
                                            <span class="text-sm text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-500">No sponsorship records available for this user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($userSponsorships->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $userSponsorships->fragment('user-sponsorships')->links() }}
                    </div>
                @endif
            </div>

            <div id="user-treatments" class="workspace-panel overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="workspace-label">Treatment</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Treatment Data Added By This User</h2>
                        <p class="text-sm text-slate-500 mt-1">Health and treatment records entered under this account.</p>
                    </div>
                    <div class="text-sm font-semibold text-slate-500">Total: {{ $treatmentsCount }}</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Treatment</th>
                                <th>Date</th>
                                <th>Diseases Tested</th>
                                <th>Illness</th>
                                <th>Location</th>
                                <th>Cost</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userTreatments as $treatment)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ optional($treatment->participant)->preferred_name ?: optional($treatment->participant)->account_name ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->treatment ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->treatment_date?->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->tested_diseases ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->illness_type ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->treatment_location ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->treatment_cost !== null ? number_format((float) $treatment->treatment_cost, 2) : 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $treatment->health_comments ?: 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-slate-500">No treatment records available for this user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($userTreatments->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $userTreatments->fragment('user-treatments')->links() }}
                    </div>
                @endif
            </div>

            <div id="user-program-attendance" class="workspace-panel overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="workspace-label">Program Attendance</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Program Attendance Added By This User</h2>
                        <p class="text-sm text-slate-500 mt-1">Program attendance sessions recorded under this account.</p>
                    </div>
                    <div class="text-sm font-semibold text-slate-500">Total: {{ $programAttendanceCount }}</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Instructor</th>
                                <th>Topic</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userProgramAttendances as $session)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $session->attendance_date?->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->instructor_name ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->topic ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->present_count ?? 0 }}</td>
                                    <td class="text-slate-600">{{ $session->absent_count ?? 0 }}</td>
                                    <td class="text-slate-600">{{ $session->comment ?: 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-500">No program attendance records available for this user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($userProgramAttendances->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $userProgramAttendances->fragment('user-program-attendance')->links() }}
                    </div>
                @endif
            </div>

            <div id="user-activity-attendance" class="workspace-panel overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="workspace-label">Activity Attendance</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Activity Attendance Added By This User</h2>
                        <p class="text-sm text-slate-500 mt-1">Activity records and attendance sessions entered under this account.</p>
                    </div>
                    <div class="text-sm font-semibold text-slate-500">Total: {{ $activityAttendanceCount }}</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Instructor</th>
                                <th>Topic</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Photos</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userActivityAttendances as $session)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $session->attendance_date?->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->activity_name ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->instructor_name ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->topic ?: 'N/A' }}</td>
                                    <td class="text-slate-600">{{ $session->present_count ?? 0 }}</td>
                                    <td class="text-slate-600">{{ $session->absent_count ?? 0 }}</td>
                                    <td class="text-slate-600">{{ count($session->activity_photo_gallery ?? []) }}</td>
                                    <td class="text-slate-600">{{ $session->comment ?: 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-slate-500">No activity attendance records available for this user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($userActivityAttendances->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $userActivityAttendances->fragment('user-activity-attendance')->links() }}
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="workspace-panel overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200">
                        <p class="workspace-label">Recent Participants</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Latest Records</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @forelse($recentParticipants as $participant)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-900">{{ $participant->account_name }}</p>
                                <p class="text-sm text-slate-500 mt-1">{{ $participant->local_participant_id }} | {{ $participant->center_id }}</p>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">No participant records available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="workspace-panel overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200">
                        <p class="workspace-label">Recent Sponsorships</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Uploaded Sponsorship Data</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @forelse($recentSponsorships as $sponsorship)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-900">{{ $sponsorship->sponsor_name ?: $sponsorship->sponsored_by ?: 'N/A' }}</p>
                                <p class="text-sm text-slate-500 mt-1">{{ optional($sponsorship->participant)->account_name ?: 'Participant N/A' }}</p>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">No sponsorship records available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="workspace-panel overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200">
                        <p class="workspace-label">Recent Treatments</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Uploaded Treatment Data</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @forelse($recentTreatments as $treatment)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-900">{{ $treatment->treatment ?: 'N/A' }}</p>
                                <p class="text-sm text-slate-500 mt-1">{{ $treatment->treatment_date?->format('Y-m-d') ?: 'No date' }} | {{ $treatment->center_id }}</p>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">No treatment records available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
