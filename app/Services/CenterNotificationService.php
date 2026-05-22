<?php

namespace App\Services;

use App\Models\CenterNotification;
use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class CenterNotificationService
{
    public function syncForCenter(?string $centerId): void
    {
        if (!$centerId || !$this->notificationsTableExists()) {
            return;
        }

        $participants = Participant::query()
            ->forCenter($centerId)
            ->get([
                'id',
                'center_id',
                'local_participant_id',
                'account_name',
                'preferred_name',
                'next_photo_update_due_at',
                'updated_at',
            ]);

        $this->syncDueNotifications($centerId, $participants);
        $this->syncRecentUpdateNotifications($centerId, $participants);
    }

    public function createParticipantUpdateNotification(Participant $participant, string $action = 'updated'): void
    {
        if (!$participant->center_id || !$this->notificationsTableExists()) {
            return;
        }

        $this->createOrRefresh([
            'center_id' => $participant->center_id,
            'participant_id' => $participant->id,
            'type' => 'new_update',
            'title' => $action === 'created' ? 'New participant added' : 'Participant updated',
            'message' => sprintf(
                '%s was %s in center %s.',
                $participant->display_name,
                $action === 'created' ? 'added to the system' : 'updated',
                $participant->center_id
            ),
            'event_key' => sprintf(
                'participant-%s-%s-%s',
                $participant->id,
                $action,
                optional($participant->updated_at)->timestamp ?? now()->timestamp
            ),
            'due_date' => null,
            'meta' => [
                'participant_name' => $participant->display_name,
                'participant_code' => $participant->local_participant_id,
                'action' => $action,
            ],
        ]);
    }

    protected function syncDueNotifications(string $centerId, Collection $participants): void
    {
        $today = Carbon::today();
        $comingDueLimit = Carbon::today()->addDays(30);

        foreach ($participants as $participant) {
            if (!$participant->next_photo_update_due_at) {
                continue;
            }

            $dueDate = Carbon::parse($participant->next_photo_update_due_at);

            if ($dueDate->lessThanOrEqualTo($today)) {
                $this->createOrRefresh([
                    'center_id' => $centerId,
                    'participant_id' => $participant->id,
                    'type' => 'overdue',
                    'title' => 'Photo update overdue',
                    'message' => sprintf('%s is overdue for a photo update since %s.', $participant->display_name, $dueDate->format('Y-m-d')),
                    'event_key' => sprintf('participant-%s-overdue-%s', $participant->id, $dueDate->format('Ymd')),
                    'due_date' => $dueDate->toDateString(),
                    'meta' => [
                        'participant_name' => $participant->display_name,
                        'participant_code' => $participant->local_participant_id,
                    ],
                ]);

                continue;
            }

            if ($dueDate->betweenIncluded($today->copy()->addDay(), $comingDueLimit)) {
                $this->createOrRefresh([
                    'center_id' => $centerId,
                    'participant_id' => $participant->id,
                    'type' => 'coming_due',
                    'title' => 'Photo update coming due',
                    'message' => sprintf('%s needs a photo update by %s.', $participant->display_name, $dueDate->format('Y-m-d')),
                    'event_key' => sprintf('participant-%s-coming-due-%s', $participant->id, $dueDate->format('Ymd')),
                    'due_date' => $dueDate->toDateString(),
                    'meta' => [
                        'participant_name' => $participant->display_name,
                        'participant_code' => $participant->local_participant_id,
                    ],
                ]);
            }
        }
    }

    protected function syncRecentUpdateNotifications(string $centerId, Collection $participants): void
    {
        $recentCutoff = now()->subDays(7);

        foreach ($participants as $participant) {
            if (!$participant->updated_at || $participant->updated_at->lt($recentCutoff)) {
                continue;
            }

            $this->createOrRefresh([
                'center_id' => $centerId,
                'participant_id' => $participant->id,
                'type' => 'new_update',
                'title' => 'Recent participant update',
                'message' => sprintf('%s has a recent record update from %s.', $participant->display_name, $participant->updated_at->format('Y-m-d H:i')),
                'event_key' => sprintf('participant-%s-recent-update-%s', $participant->id, $participant->updated_at->timestamp),
                'due_date' => null,
                'meta' => [
                    'participant_name' => $participant->display_name,
                    'participant_code' => $participant->local_participant_id,
                ],
            ]);
        }
    }

    protected function createOrRefresh(array $attributes): void
    {
        CenterNotification::query()->updateOrCreate(
            ['event_key' => $attributes['event_key']],
            $attributes
        );
    }

    protected function notificationsTableExists(): bool
    {
        return Schema::hasTable('center_notifications');
    }
}
