<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\ChurchProfile;
use App\Models\CenterNotification;
use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use App\Models\ParticipantTreatment;
use App\Models\ProgramAttendanceSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UserManagementController extends Controller
{
    protected int $spreadsheetRowNumber = 0;

    public function index(Request $request): View
    {
        $admin = $request->user();
        $centerIds = $admin->accessibleCenterIds();
        $query = User::query()
            ->with(['center', 'managedCenters']);

        if (!$admin->isOfficialAdmin()) {
            $query->forCenter($centerIds);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        return view('admin.users.index', [
            'users' => $query->latest()->paginate(10)->withQueryString(),
            'roles' => User::roles(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        if (!$admin->isOfficialAdmin()) {
            abort(403, 'Only the system administrator can approve accounts.');
        }

        if ($user->isOfficialAdmin()) {
            abort(403, 'Official administrator accounts do not require manual approval.');
        }

        $user->forceFill([
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ])->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} has been approved and can now use the system after refreshing.");
    }

    public function create(Request $request): View
    {
        $admin = $request->user();

        return view('admin.users.create', [
            'roles' => User::roles(),
            'centers' => $this->availableCenters($admin),
            'isOfficialAdmin' => $admin->isOfficialAdmin(),
            'defaultCenterId' => $admin->center_id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $admin = $request->user();
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone_number' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{7,30}$/'],
                'role' => ['required', Rule::in(array_keys(User::roles()))],
                'project_name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'center_id' => ['nullable', 'string', 'max:255'],
                'managed_center_ids' => ['nullable', 'array'],
                'managed_center_ids.*' => ['string', 'max:255'],
            ]);

            $centerId = $this->resolvePrimaryCenterId($admin, $data);

            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone_number' => $this->normalizePhoneNumber($data['phone_number'] ?? null),
                'role' => $data['role'],
                'project_name' => trim((string) $data['project_name']),
                'center_id' => $centerId,
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'password' => Hash::make($data['password']),
            ]);

            $this->syncManagedCenters($user, $data['managed_center_ids'] ?? [$centerId]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User added successfully.');
        } catch (Throwable $exception) {
            Log::error('Admin user create failed.', [
                'admin_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'User could not be created. Please review the form and try again.');
        }
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureSameCenter($request->user(), $user);

        return view('admin.users.edit', [
            'managedUser' => $user,
            'roles' => User::roles(),
            'centers' => $this->availableCenters($request->user()),
            'isOfficialAdmin' => $request->user()->isOfficialAdmin(),
            'managedCenterIds' => $user->managedCenters()->pluck('centers.center_id')->all(),
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();
        $this->ensureSameCenter($viewer, $user);

        $scopeCenterIds = $this->managedScopeCenterIds($user, $viewer);
        $participantQuery = Participant::query()->whereIn('center_id', $scopeCenterIds);
        $sponsorshipQuery = ParticipantSponsorship::query()
            ->with('participant')
            ->whereHas('participant', fn ($query) => $query->whereIn('center_id', $scopeCenterIds));
        $treatmentQuery = ParticipantTreatment::query()->whereIn('center_id', $scopeCenterIds);
        $attendanceQuery = ProgramAttendanceSession::query()->whereIn('center_id', $scopeCenterIds);
        $userScopeQuery = User::query()->whereIn('center_id', $scopeCenterIds);
        $churchProfile = $user->center_id
            ? ChurchProfile::query()->where('center_id', $user->center_id)->first()
            : null;
        $notificationsCount = Schema::hasTable('center_notifications')
            ? CenterNotification::query()->forCenter($scopeCenterIds)->manual()->count()
            : 0;

        $programAttendanceQuery = (clone $attendanceQuery)->where('attendance_type', 'program');
        $activityAttendanceQuery = (clone $attendanceQuery)->where('attendance_type', 'activity');

        return view('admin.users.show', [
            'managedUser' => $user->load(['center', 'managedCenters']),
            'churchProfile' => $churchProfile,
            'scopeCenterIds' => $scopeCenterIds,
            'managedUsersCount' => (clone $userScopeQuery)->count(),
            'managedAdminsCount' => (clone $userScopeQuery)->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])->count(),
            'participantsCount' => (clone $participantQuery)->count(),
            'sponsorshipsCount' => (clone $sponsorshipQuery)->count(),
            'treatmentsCount' => (clone $treatmentQuery)->count(),
            'programAttendanceCount' => (clone $programAttendanceQuery)->count(),
            'activityAttendanceCount' => (clone $activityAttendanceQuery)->count(),
            'notificationsCount' => $notificationsCount,
            'recentParticipants' => (clone $participantQuery)->latest()->take(6)->get(),
            'userParticipants' => (clone $participantQuery)
                ->with(['latestSponsorship', 'creator'])
                ->latest()
                ->paginate(10, ['*'], 'participants_page')
                ->withQueryString(),
            'userSponsorships' => (clone $sponsorshipQuery)
                ->latest()
                ->paginate(10, ['*'], 'sponsorships_page')
                ->withQueryString(),
            'userTreatments' => (clone $treatmentQuery)
                ->with('participant')
                ->latest('treatment_date')
                ->latest()
                ->paginate(10, ['*'], 'treatments_page')
                ->withQueryString(),
            'userProgramAttendances' => (clone $programAttendanceQuery)
                ->with('creator')
                ->latest('attendance_date')
                ->latest('id')
                ->paginate(10, ['*'], 'program_attendance_page')
                ->withQueryString(),
            'userActivityAttendances' => (clone $activityAttendanceQuery)
                ->with('creator')
                ->latest('attendance_date')
                ->latest('id')
                ->paginate(10, ['*'], 'activity_attendance_page')
                ->withQueryString(),
            'recentSponsorships' => (clone $sponsorshipQuery)->latest()->take(6)->get(),
            'recentTreatments' => (clone $treatmentQuery)->latest()->take(6)->get(),
            'supervisedUsers' => $user->isAdmin()
                ? User::query()
                    ->whereIn('center_id', $scopeCenterIds)
                    ->latest()
                    ->take(12)
                    ->get()
                : collect(),
        ]);
    }

    public function exportParticipant(Request $request, User $user, Participant $participant): StreamedResponse
    {
        $viewer = $request->user();
        $this->ensureSameCenter($viewer, $user);
        $this->ensureParticipantBelongsToUserScope($viewer, $user, $participant);

        $participant->load(['latestSponsorship', 'sponsorships' => fn ($query) => $query->latest()]);

        $participantLabel = $participant->local_participant_id
            ?: $participant->preferred_name
            ?: $participant->account_name
            ?: 'participant';
        $filename = Str::slug((string) $participantLabel) . '-participant-data-' . now()->format('Ymd_His') . '.xls';

        return response()->streamDownload(function () use ($participant) {
            $handle = fopen('php://output', 'w');

            $this->writeCsvExcelHeader($handle, 'Participant Data');
            $this->writeCsvRow($handle, ['Field', 'Value'], true);

            foreach ($this->participantExportRows($participant) as $field => $value) {
                $this->writeCsvRow($handle, [$field, $value]);
            }

            if ($participant->sponsorships->isNotEmpty()) {
                $this->writeCsvRow($handle, []);
                $this->writeCsvRow($handle, ['Sponsorship Records'], true);
                $this->writeCsvRow($handle, ['Sponsor Name', 'Sponsored By', 'Type', 'Status', 'Start Date', 'Contact', 'Category'], true);

                foreach ($participant->sponsorships as $sponsorship) {
                    $this->writeCsvRow($handle, [
                        $sponsorship->sponsor_name ?: 'N/A',
                        $sponsorship->sponsored_by ?: 'N/A',
                        $sponsorship->sponsorship_type ?: 'N/A',
                        $sponsorship->sponsorship_status ?: 'N/A',
                        $sponsorship->sponsorship_start_date ? $sponsorship->sponsorship_start_date->format('d M Y') : 'N/A',
                        $sponsorship->sponsor_contact ?: 'N/A',
                        $sponsorship->sponsorship_category ?: 'N/A',
                    ]);
                }
            }

            $this->writeExcelDocumentEnd($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        try {
            $admin = $request->user();
            $this->ensureSameCenter($admin, $user);

            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'phone_number' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{7,30}$/'],
                'role' => ['required', Rule::in(array_keys(User::roles()))],
                'project_name' => ['required', 'string', 'max:255'],
                'center_id' => ['nullable', 'string', 'max:255'],
                'managed_center_ids' => ['nullable', 'array'],
                'managed_center_ids.*' => ['string', 'max:255'],
            ]);

            $centerId = $this->resolvePrimaryCenterId($admin, $data, $user);

            $user->update([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone_number' => $this->normalizePhoneNumber($data['phone_number'] ?? null),
                'role' => $data['role'],
                'project_name' => trim((string) $data['project_name']),
                'center_id' => $centerId,
            ]);

            $this->syncManagedCenters($user, $data['managed_center_ids'] ?? [$centerId]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully.');
        } catch (Throwable $exception) {
            Log::error('Admin user update failed.', [
                'admin_id' => $request->user()?->id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'User could not be updated. Please review the form and try again.');
        }
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->ensureSameCenter($request->user(), $user);

        $generatedPassword = Str::password(12, true, true, false, false);

        $user->update([
            'password' => Hash::make($generatedPassword),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Password reset successfully for {$user->name}. New password: {$generatedPassword}");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();
        $this->ensureSameCenter($admin, $user);

        if ($admin->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isOfficialAdmin() && !$admin->isOfficialAdmin()) {
            abort(403, 'Only the system administrator can delete an official administrator account.');
        }

        $user->managedCenters()->sync([]);
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} was deleted successfully.");
    }

    protected function ensureSameCenter(User $admin, User $managedUser): void
    {
        if ($admin->isOfficialAdmin()) {
            return;
        }

        if ($managedUser->isOfficialAdmin()) {
            abort(403, 'Only the system administrator can manage official administrator accounts.');
        }

        if (!$admin->canAccessCenter($managedUser->center_id)) {
            abort(403, 'Cross-center access is not allowed.');
        }
    }

    protected function availableCenters(User $admin)
    {
        $query = Center::query()->orderBy('center_id');

        if (!$admin->isOfficialAdmin()) {
            $query->whereIn('center_id', $admin->accessibleCenterIds());
        }

        return $query->get();
    }

    protected function resolvePrimaryCenterId(User $admin, array $data, ?User $managedUser = null): ?string
    {
        if ($admin->isOfficialAdmin()) {
            return $data['center_id'] ?: ($data['managed_center_ids'][0] ?? $managedUser?->center_id);
        }

        return $managedUser?->center_id ?? $admin->center_id;
    }

    protected function syncManagedCenters(User $user, array $centerIds): void
    {
        if (!Schema::hasTable('center_user_assignments')) {
            return;
        }

        $centerIds = array_values(array_unique(array_filter($centerIds)));

        if ($user->role !== User::ROLE_ADMIN) {
            $user->managedCenters()->sync([]);
            return;
        }

        $user->managedCenters()->sync($centerIds);
    }

    protected function normalizePhoneNumber(?string $phoneNumber): ?string
    {
        $phoneNumber = trim((string) $phoneNumber);

        if ($phoneNumber === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if ($digits === '') {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) config('services.sms.default_country_code', '255')) ?: '255';

        if (str_starts_with($digits, '0')) {
            return '+' . $countryCode . substr($digits, 1);
        }

        if (str_starts_with($digits, $countryCode) || str_starts_with($phoneNumber, '+')) {
            return '+' . $digits;
        }

        return '+' . $countryCode . $digits;
    }

    protected function managedScopeCenterIds(User $managedUser, User $viewer): array
    {
        if ($managedUser->isOfficialAdmin()) {
            return $viewer->accessibleCenterIds();
        }

        if ($managedUser->role === User::ROLE_ADMIN) {
            $centerIds = $managedUser->accessibleCenterIds();

            if (!$viewer->isOfficialAdmin()) {
                $centerIds = array_values(array_intersect($centerIds, $viewer->accessibleCenterIds()));
            }

            return $centerIds;
        }

        return array_values(array_filter([$managedUser->center_id]));
    }

    protected function ensureParticipantBelongsToUserScope(User $viewer, User $managedUser, Participant $participant): void
    {
        if (!$viewer->canAccessCenter($participant->center_id)) {
            abort(403, 'This participant is outside your access scope.');
        }

        $scopeCenterIds = $this->managedScopeCenterIds($managedUser, $viewer);

        if (!in_array($participant->center_id, $scopeCenterIds, true)) {
            abort(403, 'This participant is outside this user profile scope.');
        }

    }

    protected function participantExportRows(Participant $participant): array
    {
        $columns = array_values(array_filter(
            Schema::getColumnListing('participants'),
            fn ($column) => !in_array($column, ['id'], true)
        ));

        $rows = [];

        foreach ($columns as $column) {
            $rows[Str::headline($column)] = $this->formatExportValue($participant->getAttribute($column));
        }

        $rows['Age'] = $participant->age ?: 'N/A';
        $rows['Latest Sponsor'] = $participant->latestSponsorship?->sponsor_name
            ?: $participant->latestSponsorship?->sponsored_by
            ?: 'N/A';
        $rows['Latest Sponsorship Status'] = $participant->latestSponsorship?->sponsorship_status ?: 'N/A';

        return $rows;
    }

    protected function writeCsvExcelHeader($handle, string $title = 'Export'): void
    {
        $this->spreadsheetRowNumber = 0;
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        fwrite($handle, "\xEF\xBB\xBF");
        fwrite($handle, <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 12pt; }
        th { font-weight: 700; background: #dbeafe; color: #0f172a; border: 1px solid #94a3b8; padding: 8px 10px; white-space: nowrap; min-width: 140px; mso-number-format: "\\@"; }
        td { border: 1px solid #cbd5e1; padding: 7px 10px; white-space: nowrap; mso-number-format: "\\@"; }
        td.long-cell { white-space: normal; min-width: 240px; max-width: 560px; }
        .section-row th { background: #0f172a; color: #ffffff; font-size: 14pt; text-align: left; }
    </style>
</head>
<body>
<table>
    <tr class="section-row"><th colspan="40">{$safeTitle}</th></tr>
HTML);
    }

    protected function writeCsvRow($handle, array $row, bool $isHeader = false): void
    {
        $this->spreadsheetRowNumber++;

        if ($row === []) {
            fwrite($handle, '<tr><td colspan="40">&nbsp;</td></tr>');
            return;
        }

        $useHeader = $isHeader || count($row) === 1 || $this->spreadsheetRowNumber === 1;
        $tag = $useHeader ? 'th' : 'td';
        $class = count($row) === 1 ? ' class="section-row"' : '';

        fwrite($handle, "<tr{$class}>");

        foreach ($row as $value) {
            $cell = $this->formatCsvCellForExcel($value);
            $safeCell = htmlspecialchars($cell, ENT_QUOTES, 'UTF-8');
            $cellClass = mb_strlen($cell) > 35 ? ' class="long-cell"' : '';
            fwrite($handle, "<{$tag}{$cellClass}>{$safeCell}</{$tag}>");
        }

        fwrite($handle, '</tr>');
    }

    protected function formatExportValue($value): string
    {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d M Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'N/A';
        }

        return filled($value) ? (string) $value : 'N/A';
    }

    protected function formatCsvCellForExcel($value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '';
        }

        return $value;
    }

    protected function writeExcelDocumentEnd($handle): void
    {
        fwrite($handle, '</table></body></html>');
    }
}
