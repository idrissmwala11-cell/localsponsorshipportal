<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterNotification;
use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isOfficialAdmin()) {
            $notificationsEnabled = Schema::hasTable('center_notifications');
            $allUsersQuery = User::query()->with('center');
            $adminsQuery = User::query()->with('center')->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN]);
            $standardUsersQuery = User::query()->with('center')->where('role', User::ROLE_USER);
            $notificationsQuery = $notificationsEnabled
                ? CenterNotification::query()->manual()
                : null;
            $unreadNotificationsQuery = $notificationsEnabled
                ? (clone $notificationsQuery)->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
                : null;

            return view('admin.index', [
                'centerId' => 'ALL',
                'centerName' => 'All Centers',
                'usersCount' => (clone $standardUsersQuery)->count(),
                'approvedUsersCount' => (clone $standardUsersQuery)->whereNotNull('approved_at')->count(),
                'pendingUsersCount' => (clone $standardUsersQuery)->whereNull('approved_at')->count(),
                'projectCount' => Center::query()->count(),
                'participantsCount' => 0,
                'activeParticipantsCount' => 0,
                'sponsorshipsCount' => 0,
                'notificationsCount' => $notificationsEnabled ? (clone $unreadNotificationsQuery)->count() : 0,
                'overdueCount' => 0,
                'recentUsers' => (clone $allUsersQuery)->latest()->take(8)->get(),
                'recentNotifications' => $notificationsEnabled ? (clone $notificationsQuery)->latest()->take(6)->get() : collect(),
                'managedProjectName' => $user->project_display_name,
                'managedClustersLabel' => 'All Centers / All Clusters',
                'isSupervisionDashboard' => false,
                'isOfficialAdminDashboard' => true,
                'adminsCount' => (clone $adminsQuery)->count(),
                'approvedAdminsCount' => (clone $adminsQuery)->whereNotNull('approved_at')->count(),
                'pendingAdminsCount' => (clone $adminsQuery)->whereNull('approved_at')->count(),
            ]);
        }

        if ($user->role === User::ROLE_ADMIN && !$user->isOfficialAdmin()) {
            $managedClusters = $user->managedClusterAssignments()
                ->pluck('cluster_name')
                ->filter()
                ->unique()
                ->values()
                ->all();
            $managedCenterIds = $user->accessibleCenterIds();
            $supervisedUsersQuery = User::query()
                ->with('center')
                ->where('role', User::ROLE_USER)
                ->when(!empty($managedClusters), fn ($query) => $query->whereIn('cluster_name', $managedClusters), fn ($query) => $query->whereIn('center_id', $managedCenterIds));
            $notificationsEnabled = Schema::hasTable('center_notifications');
            $notificationsQuery = $notificationsEnabled
                ? CenterNotification::query()->forCenter($managedCenterIds)->manual()
                : null;
            $unreadNotificationsQuery = $notificationsEnabled
                ? (clone $notificationsQuery)->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
                : null;
            $supervisedUsersCount = (clone $supervisedUsersQuery)->count();
            $approvedUsersCount = (clone $supervisedUsersQuery)->whereNotNull('approved_at')->count();
            $pendingUsersCount = (clone $supervisedUsersQuery)->whereNull('approved_at')->count();
            $projectCount = (clone $supervisedUsersQuery)->select('project_name')->distinct()->count('project_name');
            return view('admin.index', [
                'centerId' => implode(', ', $managedCenterIds),
                'centerName' => count($managedCenterIds) > 1 ? 'Managed Centers' : (optional($user->center)->center_name ?? ($managedCenterIds[0] ?? 'N/A')),
                'usersCount' => $supervisedUsersCount,
                'approvedUsersCount' => $approvedUsersCount,
                'pendingUsersCount' => $pendingUsersCount,
                'projectCount' => $projectCount,
                'participantsCount' => 0,
                'activeParticipantsCount' => 0,
                'sponsorshipsCount' => 0,
                'notificationsCount' => $notificationsEnabled ? (clone $unreadNotificationsQuery)->count() : 0,
                'overdueCount' => 0,
                'recentUsers' => (clone $supervisedUsersQuery)->latest()->take(8)->get(),
                'recentNotifications' => $notificationsEnabled ? (clone $notificationsQuery)->latest()->take(6)->get() : collect(),
                'managedProjectName' => $user->project_display_name,
                'managedClustersLabel' => !empty($managedClusters) ? implode(', ', $managedClusters) : 'No Cluster Selected',
                'isSupervisionDashboard' => true,
                'isOfficialAdminDashboard' => false,
            ]);
        }

        $centerIds = $user->accessibleCenterIds();

        $participantsQuery = Participant::query()->forCenter($centerIds);
        $usersQuery = User::query()->forCenter($centerIds);
        $sponsorshipsQuery = ParticipantSponsorship::query()->forCenter($centerIds);
        $notificationsEnabled = Schema::hasTable('center_notifications');
        $notificationsQuery = $notificationsEnabled
            ? CenterNotification::query()->forCenter($centerIds)->manual()
            : null;
        $unreadNotificationsQuery = $notificationsEnabled
            ? (clone $notificationsQuery)->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
            : null;

        return view('admin.index', [
            'centerId' => $user->isOfficialAdmin() ? 'ALL' : implode(', ', $centerIds),
            'centerName' => $user->isOfficialAdmin() ? 'All Centers' : (count($centerIds) > 1 ? 'Managed Centers' : (optional($user->center)->center_name ?? ($centerIds[0] ?? 'N/A'))),
            'usersCount' => (clone $usersQuery)->count(),
            'adminsCount' => (clone $usersQuery)->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])->count(),
            'participantsCount' => (clone $participantsQuery)->count(),
            'activeParticipantsCount' => (clone $participantsQuery)
                ->where(function ($query) {
                    $query->whereNull('participant_status')
                        ->orWhereNotIn('participant_status', ['Exited', 'Planned Exit']);
                })->count(),
            'sponsorshipsCount' => (clone $sponsorshipsQuery)->count(),
            'notificationsCount' => $notificationsEnabled ? (clone $unreadNotificationsQuery)->count() : 0,
            'overdueCount' => 0,
            'recentUsers' => (clone $usersQuery)->latest()->take(5)->get(),
            'recentNotifications' => $notificationsEnabled ? (clone $notificationsQuery)->latest()->take(6)->get() : collect(),
            'managedProjectName' => $user->project_display_name,
            'isSupervisionDashboard' => false,
            'isOfficialAdminDashboard' => false,
        ]);
    }
}
