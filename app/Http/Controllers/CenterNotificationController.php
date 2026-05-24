<?php

namespace App\Http\Controllers;

use App\Models\CenterNotification;
use App\Models\CenterNotificationRead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CenterNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (!Schema::hasTable('center_notifications')) {
            return view('notifications.index', [
                'notifications' => new LengthAwarePaginator([], 0, 12),
            ]);
        }

        $notifications = CenterNotification::query()
            ->manual()
            ->visibleToUser($user)
            ->with([
                'participant',
                'sender',
                'recipient',
                'reads' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->latest()
            ->paginate(12);

        $managedUsers = $user->role === User::ROLE_ADMIN
            ? $this->availableManagedUsersFor($user)
            : collect();

        $adminRecipients = $user->role === User::ROLE_USER
            ? collect()
            : $this->availableAdminRecipientsFor($user);

        $officialAdminRecipients = $user->isOfficialAdmin()
            ? $this->availableOfficialAdminAdminRecipientsFor($user)
            : collect();

        $clusterRecipientOptions = $user->isOfficialAdmin()
            ? $this->availableClusterRecipientOptions()
            : collect();

        return view('notifications.index', [
            'notifications' => $notifications,
            'managedCenters' => $user->accessibleCenterIds(),
            'managedUsers' => $managedUsers,
            'adminRecipients' => $adminRecipients,
            'officialAdminRecipients' => $officialAdminRecipients,
            'clusterRecipientOptions' => $clusterRecipientOptions,
            'canSendNotifications' => true,
            'isAdminMessenger' => $user->role === User::ROLE_ADMIN,
            'isOfficialAdminMessenger' => $user->isOfficialAdmin(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (!Schema::hasTable('center_notifications')) {
                return back()->with('error', 'Notifications table is not ready yet.');
            }

            if ($user->role === User::ROLE_ADMIN) {
                $data = $request->validate([
                    'target_mode' => ['required', 'in:single_user,all_managed_users'],
                    'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
                    'title' => ['required', 'string', 'max:255'],
                    'message' => ['required', 'string', 'max:2000'],
                ]);

                if ($data['target_mode'] === 'all_managed_users') {
                    $recipients = $this->availableManagedUsersFor($user);

                    abort_if($recipients->isEmpty(), 422, 'No supervised users are available for messaging.');

                    $this->dispatchNotificationsToRecipients(
                        $recipients,
                        $user,
                        'admin_broadcast',
                        $data['title'],
                        $data['message'],
                        'all_managed_users'
                    );
                } else {
                    $recipient = $this->availableManagedUsersFor($user)
                        ->firstWhere('id', (int) $data['target_user_id']);

                    abort_unless($recipient, 403, 'Unauthorized access.');

                    CenterNotification::create([
                        'center_id' => $recipient->center_id,
                        'participant_id' => null,
                        'sent_by_user_id' => $user->id,
                        'target_user_id' => $recipient->id,
                        'type' => 'admin_message',
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'event_key' => sprintf('manual-%s-%s', $user->id, Str::uuid()),
                        'due_date' => null,
                        'meta' => [
                            'sender_name' => $user->name,
                            'sender_role' => $user->display_title,
                            'recipient_name' => $recipient->name,
                            'delivery_mode' => 'single_user',
                        ],
                        'is_manual' => true,
                        'sent_to_all_users' => false,
                    ]);
                }
            } elseif ($user->isOfficialAdmin()) {
                $data = $request->validate([
                    'target_mode' => ['required', 'in:single_admin,all_admins,all_users,cluster_users'],
                    'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
                    'target_cluster_name' => ['nullable', 'string', 'max:255'],
                    'title' => ['required', 'string', 'max:255'],
                    'message' => ['required', 'string', 'max:2000'],
                ]);

                if ($data['target_mode'] === 'single_admin') {
                    $recipient = $this->availableOfficialAdminAdminRecipientsFor($user)
                        ->firstWhere('id', (int) $data['target_user_id']);

                    abort_unless($recipient, 403, 'Unauthorized access.');

                    CenterNotification::create([
                        'center_id' => $recipient->center_id,
                        'participant_id' => null,
                        'sent_by_user_id' => $user->id,
                        'target_user_id' => $recipient->id,
                        'type' => 'official_admin_message',
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'event_key' => sprintf('official-admin-%s-%s', $user->id, Str::uuid()),
                        'due_date' => null,
                        'meta' => [
                            'sender_name' => $user->name,
                            'sender_role' => $user->display_title,
                            'recipient_name' => $recipient->name,
                            'delivery_mode' => 'single_admin',
                        ],
                        'is_manual' => true,
                        'sent_to_all_users' => false,
                    ]);
                } else {
                    $recipients = match ($data['target_mode']) {
                        'all_admins' => $this->availableOfficialAdminAdminRecipientsFor($user),
                        'all_users' => $this->availableSystemUsersForOfficialAdmin(),
                        'cluster_users' => $this->availableSystemUsersForOfficialAdmin($data['target_cluster_name'] ?? null),
                        default => collect(),
                    };

                    abort_if($recipients->isEmpty(), 422, 'No recipients are available for the selected target.');

                    $this->dispatchNotificationsToRecipients(
                        $recipients,
                        $user,
                        'official_admin_broadcast',
                        $data['title'],
                        $data['message'],
                        $data['target_mode']
                    );
                }
            } else {
                $data = $request->validate([
                    'target_user_id' => ['required', 'integer', 'exists:users,id'],
                    'title' => ['required', 'string', 'max:255'],
                    'message' => ['required', 'string', 'max:2000'],
                ]);

                $recipient = $this->availableAdminRecipientsFor($user)
                    ->firstWhere('id', (int) $data['target_user_id']);

                abort_unless($recipient, 403, 'Unauthorized access.');

                CenterNotification::create([
                    'center_id' => $user->center_id,
                    'participant_id' => null,
                    'sent_by_user_id' => $user->id,
                    'target_user_id' => $recipient->id,
                    'type' => 'user_message',
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'event_key' => sprintf('user-message-%s-%s', $user->id, Str::uuid()),
                    'due_date' => null,
                    'meta' => [
                        'sender_name' => $user->name,
                        'sender_role' => $user->display_title,
                        'recipient_name' => $recipient->name,
                        'delivery_mode' => 'admin_only',
                    ],
                    'is_manual' => true,
                    'sent_to_all_users' => false,
                ]);
            }

            return back()->with('success', 'Notification sent successfully.');
        } catch (Throwable $exception) {
            Log::error('Notification send failed.', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Notification could not be sent. Please review the form and try again.');
        }
    }

    public function markRead(Request $request, CenterNotification $notification): RedirectResponse
    {
        if (!Schema::hasTable('center_notifications')) {
            return back()->with('error', 'Notifications table is not ready yet.');
        }

        $this->ensureSameCenter($request, $notification);

        CenterNotificationRead::query()->updateOrCreate(
            [
                'center_notification_id' => $notification->id,
                'user_id' => $request->user()->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!Schema::hasTable('center_notifications')) {
            return back()->with('error', 'Notifications table is not ready yet.');
        }

        CenterNotification::query()
            ->manual()
            ->visibleToUser($user)
            ->pluck('id')
            ->each(function ($notificationId) use ($user) {
                CenterNotificationRead::query()->updateOrCreate(
                    [
                        'center_notification_id' => $notificationId,
                        'user_id' => $user->id,
                    ],
                    [
                        'read_at' => now(),
                    ]
                );
            });

        return back()->with('success', 'All notifications marked as read.');
    }

    protected function ensureSameCenter(Request $request, CenterNotification $notification): void
    {
        if (!$request->user()->canAccessCenter($notification->center_id)) {
            abort(403, 'Cross-center access is not allowed.');
        }
    }

    protected function availableManagedUsersFor(User $user): Collection
    {
        $supervisedUsers = Schema::hasTable('admin_user_supervisions')
            ? $user->supervisedUsers()
                ->where('role', User::ROLE_USER)
                ->orderBy('center_id')
                ->orderBy('name')
                ->get()
            : collect();

        if ($supervisedUsers->isNotEmpty()) {
            return $supervisedUsers;
        }

        return User::query()
            ->forCenter($user->accessibleCenterIds())
            ->where('role', User::ROLE_USER)
            ->orderBy('center_id')
            ->orderBy('name')
            ->get();
    }

    protected function availableAdminRecipientsFor(User $user): Collection
    {
        $recipients = collect();

        if (Schema::hasTable('admin_user_supervisions')) {
            $recipients = $recipients->merge(
                $user->supervisingAdmins()
                    ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])
                    ->get()
            );
        }

        if (filled($user->center_id)) {
            $recipients = $recipients->merge(
                User::query()
                    ->where('role', User::ROLE_ADMIN)
                    ->where(function ($query) use ($user) {
                        $query->where('center_id', $user->center_id)
                            ->orWhereHas('managedCenters', fn ($managedCentersQuery) => $managedCentersQuery->where('centers.center_id', $user->center_id));
                    })
                    ->get()
            );
        }

        $recipients = $recipients->merge(
            User::query()
                ->where('role', User::ROLE_OFFICIAL_ADMIN)
                ->get()
        );

        return $recipients
            ->filter(fn ($recipient) => $recipient->id !== $user->id)
            ->unique('id')
            ->sortBy([
                fn (User $recipient) => $recipient->role === User::ROLE_OFFICIAL_ADMIN ? 0 : 1,
                fn (User $recipient) => strtolower($recipient->name),
            ])
            ->values();
    }

    protected function availableOfficialAdminAdminRecipientsFor(User $user): Collection
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->whereNotNull('center_id')
            ->where('center_id', '!=', '')
            ->whereKeyNot($user->id)
            ->orderBy('name')
            ->get();
    }

    protected function availableSystemUsersForOfficialAdmin(?string $clusterName = null): Collection
    {
        return User::query()
            ->where('role', User::ROLE_USER)
            ->whereNotNull('center_id')
            ->where('center_id', '!=', '')
            ->when(filled($clusterName), fn ($query) => $query->where('cluster_name', $clusterName))
            ->orderBy('cluster_name')
            ->orderBy('center_id')
            ->orderBy('name')
            ->get();
    }

    protected function availableClusterRecipientOptions(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_USER)
            ->whereNotNull('cluster_name')
            ->where('cluster_name', '!=', '')
            ->select('cluster_name')
            ->distinct()
            ->orderBy('cluster_name')
            ->pluck('cluster_name')
            ->values();
    }

    protected function dispatchNotificationsToRecipients(
        Collection $recipients,
        User $sender,
        string $type,
        string $title,
        string $message,
        string $deliveryMode
    ): void {
        $recipients
            ->filter(fn (User $recipient) => filled($recipient->center_id))
            ->unique('id')
            ->each(function (User $recipient) use ($sender, $type, $title, $message, $deliveryMode) {
                CenterNotification::create([
                    'center_id' => $recipient->center_id,
                    'participant_id' => null,
                    'sent_by_user_id' => $sender->id,
                    'target_user_id' => $recipient->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'event_key' => sprintf('%s-%s-%s', $deliveryMode, $sender->id, Str::uuid()),
                    'due_date' => null,
                    'meta' => [
                        'sender_name' => $sender->name,
                        'sender_role' => $sender->display_title,
                        'recipient_name' => $recipient->name,
                        'delivery_mode' => $deliveryMode,
                    ],
                    'is_manual' => true,
                    'sent_to_all_users' => false,
                ]);
            });
    }
}
