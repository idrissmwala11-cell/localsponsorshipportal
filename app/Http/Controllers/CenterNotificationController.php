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

        $managedUsers = $user->isAdmin()
            ? User::query()
                ->forCenter($user->accessibleCenterIds())
                ->where('role', User::ROLE_USER)
                ->orderBy('center_id')
                ->orderBy('name')
                ->get()
            : collect();

        $adminRecipients = $user->isAdmin()
            ? collect()
            : User::query()
                ->forCenter($user->accessibleCenterIds())
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])
                ->orderBy('name')
                ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
            'managedCenters' => $user->accessibleCenterIds(),
            'managedUsers' => $managedUsers,
            'adminRecipients' => $adminRecipients,
            'canSendNotifications' => true,
            'isAdminMessenger' => $user->isAdmin(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (!Schema::hasTable('center_notifications')) {
                return back()->with('error', 'Notifications table is not ready yet.');
            }

            if ($user->isAdmin()) {
                $data = $request->validate([
                    'target_mode' => ['required', 'in:single_user,all_managed_users'],
                    'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
                    'title' => ['required', 'string', 'max:255'],
                    'message' => ['required', 'string', 'max:2000'],
                ]);

                if ($data['target_mode'] === 'all_managed_users') {
                    foreach ($user->accessibleCenterIds() as $centerId) {
                        CenterNotification::create([
                            'center_id' => $centerId,
                            'participant_id' => null,
                            'sent_by_user_id' => $user->id,
                            'target_user_id' => null,
                            'type' => 'admin_broadcast',
                            'title' => $data['title'],
                            'message' => $data['message'],
                            'event_key' => sprintf('broadcast-%s-%s-%s', $user->id, $centerId, Str::uuid()),
                            'due_date' => null,
                            'meta' => [
                                'sender_name' => $user->name,
                                'sender_role' => $user->display_title,
                                'delivery_mode' => 'all_managed_users',
                            ],
                            'is_manual' => true,
                            'sent_to_all_users' => true,
                        ]);
                    }
                } else {
                    $recipient = User::query()
                        ->whereKey($data['target_user_id'])
                        ->where('role', User::ROLE_USER)
                        ->firstOrFail();

                    abort_unless($user->canAccessCenter($recipient->center_id), 403);

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
            } else {
                $data = $request->validate([
                    'target_user_id' => ['required', 'integer', 'exists:users,id'],
                    'title' => ['required', 'string', 'max:255'],
                    'message' => ['required', 'string', 'max:2000'],
                ]);

                $recipient = User::query()
                    ->whereKey($data['target_user_id'])
                    ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])
                    ->firstOrFail();

                abort_unless($recipient->canAccessCenter($user->center_id), 403);

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
}
