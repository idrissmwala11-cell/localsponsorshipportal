<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ProgramAttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceController extends Controller
{
    public function program()
    {
        return $this->attendanceWorkspace('program');
    }

    public function storeProgram(Request $request)
    {
        return $this->storeAttendance($request, 'program');
    }

    public function activity()
    {
        return $this->attendanceWorkspace('activity');
    }

    public function storeActivity(Request $request)
    {
        return $this->storeAttendance($request, 'activity');
    }

    protected function attendanceWorkspace(string $type)
    {
        $user = Auth::user();

        $participants = Participant::query()
            ->visibleToUser($user)
            ->orderBy('account_name')
            ->get();

        $recentSessions = ProgramAttendanceSession::query()
            ->with('creator')
            ->visibleToUser($user)
            ->where('attendance_type', $type)
            ->latest('attendance_date')
            ->latest('id')
            ->take(10)
            ->get();

        return view('attendance.program', [
            'participants' => $participants,
            'recentSessions' => $recentSessions,
            'attendanceType' => $type,
            'pageTitle' => $type === 'activity' ? 'Activity Attendance' : 'Program Attendance',
            'pageDescription' => $type === 'activity'
                ? 'Mark each registered participant as present or absent, then save the activity attendance together with the instructor, topic, and comment.'
                : 'Mark each registered participant as present or absent, then save the program attendance together with the instructor, topic, and comment.',
            'submitRoute' => $type === 'activity' ? 'attendance.activity.store' : 'attendance.program.store',
            'submitLabel' => $type === 'activity' ? 'Save Activity Attendance' : 'Save Program Attendance',
            'recentTitle' => $type === 'activity' ? 'Saved Activity Attendance' : 'Saved Program Attendance',
            'attendanceTypeLabel' => $type === 'activity' ? 'Activity' : 'Program',
        ]);
    }

    protected function storeAttendance(Request $request, string $type)
    {
        try {
            $user = $request->user();
            $participants = Participant::query()
                ->visibleToUser($user)
                ->orderBy('account_name')
                ->get(['id']);

            $participantIds = $participants->pluck('id')->map(fn ($id) => (string) $id)->all();

            $data = $request->validate([
                'attendance_date' => ['required', 'date'],
                'activity_name' => [$type === 'activity' ? 'required' : 'nullable', 'string', 'max:255'],
                'activity_photos' => [$type === 'activity' ? 'nullable' : 'prohibited', 'array'],
                'activity_photos.*' => [$type === 'activity' ? 'nullable' : 'prohibited', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'activity_photo_captions' => [$type === 'activity' ? 'nullable' : 'prohibited', 'array'],
                'activity_photo_captions.*' => [$type === 'activity' ? 'nullable' : 'prohibited', 'string', 'max:255'],
                'instructor_name' => ['nullable', 'string', 'max:255'],
                'topic' => ['nullable', 'string', 'max:255'],
                'comment' => ['nullable', 'string'],
                'present_participants' => ['nullable', 'array'],
                'present_participants.*' => ['string'],
            ]);

            $activityPhotoPath = null;
            $activityPhotoPaths = [];
            $activityPhotoCaptions = [];

            if ($type === 'activity' && $request->hasFile('activity_photos')) {
                $captions = collect($request->input('activity_photo_captions', []))->values();

                foreach ($request->file('activity_photos', []) as $index => $photo) {
                    if (!$photo) {
                        continue;
                    }

                    $storedPath = $photo->store('activity-attendance', 'public');
                    $activityPhotoPaths[] = $storedPath;
                    $activityPhotoCaptions[] = trim((string) $captions->get($index, '')) ?: null;
                }

                $activityPhotoPath = $activityPhotoPaths[0] ?? null;
            }

            $presentIds = collect($data['present_participants'] ?? [])
                ->filter(fn ($id) => in_array((string) $id, $participantIds, true))
                ->unique()
                ->values();

            $presentCount = $presentIds->count();
            $absentCount = max($participants->count() - $presentCount, 0);

            $session = ProgramAttendanceSession::create([
                'center_id' => $user->center_id ?: ($user->accessibleCenterIds()[0] ?? null),
                'created_by_user_id' => $user->id,
                'attendance_type' => $type,
                'attendance_date' => $data['attendance_date'],
                'activity_name' => $type === 'activity' ? ($data['activity_name'] ?? null) : null,
                'activity_photo_path' => $activityPhotoPath,
                'activity_photo_caption' => $type === 'activity' ? ($activityPhotoCaptions[0] ?? null) : null,
                'activity_photo_paths' => $type === 'activity' ? $activityPhotoPaths : null,
                'activity_photo_captions' => $type === 'activity' ? $activityPhotoCaptions : null,
                'instructor_name' => $data['instructor_name'] ?? null,
                'topic' => $data['topic'] ?? null,
                'comment' => $data['comment'] ?? null,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
            ]);

            $entries = $participants->map(function ($participant) use ($session, $presentIds) {
                return [
                    'program_attendance_session_id' => $session->id,
                    'participant_id' => $participant->id,
                    'is_present' => $presentIds->contains((string) $participant->id),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            $session->entries()->insert($entries);

            return redirect()
                ->route($type === 'activity' ? 'attendance.activity.index' : 'attendance.program.index')
                ->with('success', ucfirst($type) . ' attendance saved successfully.');
        } catch (Throwable $exception) {
            Log::error('Attendance save failed.', [
                'type' => $type,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', ucfirst($type) . ' attendance could not be saved. Please review the form and try again.');
        }
    }
}
