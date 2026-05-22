<?php

namespace App\Http\Controllers;

use App\Models\ChurchProfile;
use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use App\Models\ParticipantTreatment;
use App\Models\User;
use App\Models\CenterNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $centerIds = $user->accessibleCenterIds();
        $dashboardCenterId = $user->center_id ?: ($centerIds[0] ?? null);

        $participantsQuery = Participant::query()->visibleToUser($user);
        $sponsorshipsQuery = ParticipantSponsorship::query()
            ->with(['participant'])
            ->visibleToUser($user);
        $usersQuery = $user->role === User::ROLE_USER
            ? User::query()->whereKey($user->id)
            : User::query()->forCenter($centerIds);
        $notificationsEnabled = Schema::hasTable('center_notifications');
        $notificationsQuery = $notificationsEnabled
            ? CenterNotification::query()->forCenter($centerIds)->manual()
            : null;
        $unreadNotificationsQuery = $notificationsEnabled
            ? (clone $notificationsQuery)->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
            : null;

        $totalParticipants = (clone $participantsQuery)->count();

        $activeParticipants = (clone $participantsQuery)
            ->where(function ($query) {
                $query->whereNull('participant_status')
                      ->orWhereNotIn('participant_status', ['Exited', 'Planned Exit']);
            })
            ->count();

        $exits = (clone $participantsQuery)
            ->where('participant_status', 'Exited')
            ->count();

        $plannedExits = (clone $participantsQuery)
            ->where('participant_status', 'Planned Exit')
            ->count();

        $comingDue = (clone $participantsQuery)
            ->whereNotNull('next_photo_update_due_at')
            ->whereDate('next_photo_update_due_at', '>', now()->toDateString())
            ->whereDate('next_photo_update_due_at', '<=', now()->addDays(30)->toDateString())
            ->count();

        $seriousConditions = (clone $participantsQuery)
            ->whereNotNull('chronic_illnesses')
            ->where('chronic_illnesses', '!=', '')
            ->count();

        $overdue = (clone $participantsQuery)
            ->whereNotNull('next_photo_update_due_at')
            ->whereDate('next_photo_update_due_at', '<=', now()->toDateString())
            ->count();

        $totalSponsorships = (clone $sponsorshipsQuery)->count();

        $activeSponsorships = (clone $sponsorshipsQuery)
            ->where('sponsorship_status', 'Active')
            ->count();

        $recentParticipants = (clone $participantsQuery)
            ->latest()
            ->take(5)
            ->get();

        $recentSponsorships = (clone $sponsorshipsQuery)
            ->latest()
            ->take(5)
            ->get();

        $participants = (clone $participantsQuery)
            ->with('latestSponsorship')
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $recentNotifications = $notificationsEnabled
            ? (clone $notificationsQuery)->latest()->take(5)->get()
            : collect();

        $usersCount = (clone $usersQuery)->count();
        $adminUsersCount = $user->role === User::ROLE_USER
            ? 0
            : (clone $usersQuery)->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])->count();

        $usersMetricCopy = $user->isOfficialAdmin()
            ? $adminUsersCount . ' admins across all centers'
            : ($user->isAdmin()
                ? $usersCount . ' users across your managed centers'
                : 'Only your account is visible here');

        $usersMetricNote = $user->isOfficialAdmin()
            ? 'All admins and users across every center'
            : ($user->isAdmin()
                ? 'Managed users within your assigned centers'
                : 'Other user accounts are hidden from standard users');

        $churchProfile = ($dashboardCenterId && Schema::hasTable('church_profiles'))
            ? ChurchProfile::query()->where('center_id', $dashboardCenterId)->first()
            : null;

        return view('dashboard.index', [
            'user'                => $user,
            'isAdmin'             => $user->isAdmin(),
            'dashboardTitle'      => $user->isOfficialAdmin() ? 'Official Admin Dashboard' : ($user->isAdmin() ? 'Admin Center Dashboard' : 'Center Dashboard'),
            'dashboardSubtitle'   => $user->isOfficialAdmin()
                ? 'Overview of participant records, sponsorship records, users, admins, and notifications across all centers.'
                : ($user->isAdmin()
                    ? 'Overview of participant records, sponsorship records, users, and notifications for the centers you manage.'
                    : 'Overview of participant records, sponsorship records, users, and notifications for your assigned center.'),
            'centerName'          => $user->isOfficialAdmin() ? 'All Centers' : (count($centerIds) > 1 ? 'Managed Centers' : (optional($user->center)->center_name ?? 'No Center Assigned')),
            'centerId'            => $user->isOfficialAdmin() ? 'ALL' : (count($centerIds) > 1 ? implode(', ', $centerIds) : ($centerIds[0] ?? 'N/A')),

            'totalParticipants'   => $totalParticipants,
            'activeParticipants'  => $activeParticipants,
            'participantUpdates'  => $totalParticipants,
            'participantsCount'   => $totalParticipants,

            'exits'               => $exits,
            'plannedExits'        => $plannedExits,
            'comingDue'           => $comingDue,
            'seriousConditions'   => $seriousConditions,
            'overdue'             => $overdue,

            'notificationsCount'  => $notificationsEnabled ? (clone $unreadNotificationsQuery)->count() : 0,

            'totalSponsorships'   => $totalSponsorships,
            'activeSponsorships'  => $activeSponsorships,
            'usersCount'          => $usersCount,
            'adminUsersCount'     => $adminUsersCount,
            'usersMetricCopy'     => $usersMetricCopy,
            'usersMetricNote'     => $usersMetricNote,

            'recentParticipants'  => $recentParticipants,
            'recentSponsorships'  => $recentSponsorships,
            'recentNotifications' => $recentNotifications,
            'participants'        => $participants,
            'churchProfile'       => $churchProfile,
            'dashboardCenterId'   => $dashboardCenterId,
        ]);
    }

    public function updateChurchProfile(Request $request)
    {
        try {
            $user = $request->user();
            $centerId = $user->center_id ?: ($user->accessibleCenterIds()[0] ?? null);

            if (!$centerId) {
                return back()->withInput()->with('error', 'No center is assigned to this account.');
            }

            $data = $request->validate([
                'church_name' => ['nullable', 'string', 'max:255'],
                'historical_background' => ['nullable', 'string'],
                'mission' => ['nullable', 'string'],
                'vision' => ['nullable', 'string'],
                'church_photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ]);

            $profile = ChurchProfile::query()->firstOrNew([
                'center_id' => $centerId,
            ]);

            $profile->created_by_user_id = $profile->created_by_user_id ?: $user->id;
            $profile->church_name = $data['church_name'] ?? $profile->church_name;
            $profile->historical_background = $data['historical_background'] ?? $profile->historical_background;
            $profile->mission = $data['mission'] ?? $profile->mission;
            $profile->vision = $data['vision'] ?? $profile->vision;

            if ($request->hasFile('church_photos')) {
                $existingPaths = collect($profile->photo_paths ?? [])
                    ->filter(fn ($path) => Storage::disk('public')->exists($path));

                $newPaths = collect($request->file('church_photos'))
                    ->filter()
                    ->map(fn ($photo) => $photo->store('church-profiles', 'public'))
                    ->values();

                $profile->photo_paths = $existingPaths
                    ->concat($newPaths)
                    ->unique()
                    ->values()
                    ->all();
            }

            $profile->save();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Church historical background updated successfully.');
        } catch (Throwable $exception) {
            Log::error('Church profile save failed.', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Church profile could not be saved. Please review the form and try again.');
        }
    }

    public function deleteChurchPhoto(Request $request, int $photoIndex)
    {
        try {
            $user = $request->user();
            $centerId = $user->center_id ?: ($user->accessibleCenterIds()[0] ?? null);

            if (!$centerId) {
                return back()->with('error', 'No center is assigned to this account.');
            }

            $profile = ChurchProfile::query()->where('center_id', $centerId)->first();

            if (!$profile) {
                return back()->with('error', 'Church profile was not found.');
            }

            $photoPaths = collect($profile->photo_paths ?? [])->values();
            $pathToDelete = $photoPaths->get($photoIndex);

            if (!$pathToDelete) {
                return back()->with('error', 'Selected church photo was not found.');
            }

            if (Storage::disk('public')->exists($pathToDelete)) {
                Storage::disk('public')->delete($pathToDelete);
            }

            $profile->photo_paths = $photoPaths
                ->reject(fn ($path, $index) => $index === $photoIndex)
                ->values()
                ->all();
            $profile->save();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Church photo deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Church photo delete failed.', [
                'user_id' => $request->user()?->id,
                'photo_index' => $photoIndex,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Church photo could not be deleted. Please try again.');
        }
    }
}
