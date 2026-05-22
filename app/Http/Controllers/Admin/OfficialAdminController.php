<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterNotification;
use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class OfficialAdminController extends Controller
{
    public function index()
    {
        $centers = Center::query()->orderBy('center_id')->get();
        $users = User::query()->with(['center', 'managedCenters'])->latest();
        $notificationsEnabled = Schema::hasTable('center_notifications');

        return view('admin.official.index', [
            'centersCount' => $centers->count(),
            'usersCount' => User::query()->count(),
            'officialAdminsCount' => User::query()->where('role', User::ROLE_OFFICIAL_ADMIN)->count(),
            'adminsCount' => User::query()->where('role', User::ROLE_ADMIN)->count(),
            'participantsCount' => Participant::query()->count(),
            'sponsorshipsCount' => ParticipantSponsorship::query()->count(),
            'notificationsCount' => $notificationsEnabled ? CenterNotification::query()->manual()->count() : 0,
            'recentAdmins' => User::query()
                ->with(['center', 'managedCenters'])
                ->whereIn('role', [User::ROLE_OFFICIAL_ADMIN, User::ROLE_ADMIN])
                ->latest()
                ->take(20)
                ->get(),
            'centerSummaries' => $centers->map(function ($center) use ($notificationsEnabled) {
                return [
                    'center_id' => $center->center_id,
                    'center_name' => $center->center_name,
                    'users_count' => User::query()->where('center_id', $center->center_id)->count(),
                    'admins_count' => User::query()->where('role', User::ROLE_ADMIN)->whereHas('managedCenters', fn ($query) => $query->where('centers.center_id', $center->center_id))->count(),
                    'participants_count' => Participant::query()->where('center_id', $center->center_id)->count(),
                    'notifications_count' => $notificationsEnabled ? CenterNotification::query()->manual()->where('center_id', $center->center_id)->count() : 0,
                ];
            }),
        ]);
    }
}
