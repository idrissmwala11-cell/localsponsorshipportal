<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterNotification;
use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use App\Models\ParticipantTreatment;
use App\Models\ProgramAttendanceSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CenterReportController extends Controller
{
    protected int $spreadsheetRowNumber = 0;

    public function index(Request $request)
    {
        $user = $request->user();
        [$availableCenterIds, $selectedCenterId, $centerIds, $centerOptions] = $this->resolveCenterScope($request, $user);
        $moduleDefinitions = $this->reportModuleDefinitionsFor($user);
        $periodOptions = $this->periodOptions();
        $selectedModule = $request->string('module')->toString() ?: 'basic_information';
        $selectedPeriod = $request->string('period')->toString() ?: 'all_time';
        $selectedModule = array_key_exists($selectedModule, $moduleDefinitions) ? $selectedModule : array_key_first($moduleDefinitions);
        $selectedPeriod = array_key_exists($selectedPeriod, $periodOptions) ? $selectedPeriod : 'all_time';
        $periodStart = $this->resolvePeriodStart($selectedPeriod);

        $participants = Participant::query()->visibleToUser($user)->forCenter($centerIds);
        $users = $user->role === User::ROLE_USER
            ? User::query()->whereKey($user->id)
            : User::query()->forCenter($centerIds);
        $sponsorships = ParticipantSponsorship::query()->visibleToUser($user)->forCenter($centerIds);
        $notificationsEnabled = Schema::hasTable('center_notifications');
        $notifications = $notificationsEnabled
            ? CenterNotification::query()->forCenter($centerIds)->manual()
            : null;
        [$reportRows, $reportTotal, $reportColumns, $reportTableNote] = $this->buildReportDataset(
            $selectedModule,
            $moduleDefinitions[$selectedModule],
            $user,
            $periodStart,
            $centerIds
        );

        return view('admin.reports.index', [
            'centerId' => $selectedCenterId === 'all'
                ? ($user->isOfficialAdmin() ? 'ALL' : (empty($availableCenterIds) ? 'N/A' : implode(', ', $availableCenterIds)))
                : $selectedCenterId,
            'centerOptions' => $centerOptions,
            'selectedCenterId' => $selectedCenterId,
            'scopeLabel' => $user->isOfficialAdmin()
                ? 'Official Admin: report only on admins and users across all centers.'
                : ($user->isAdmin()
                    ? 'Admin: data for centers and users under your supervision.'
                    : 'User: only your own account data and the records you uploaded are visible here.'),
            'participantsCount' => (clone $participants)->count(),
            'activeParticipantsCount' => (clone $participants)
                ->where(function ($query) {
                    $query->whereNull('participant_status')
                        ->orWhereNotIn('participant_status', ['Exited', 'Planned Exit']);
                })->count(),
            'plannedExitCount' => (clone $participants)->where('participant_status', 'Planned Exit')->count(),
            'usersCount' => (clone $users)->count(),
            'adminCount' => $user->role === User::ROLE_USER ? 0 : (clone $users)->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN])->count(),
            'notificationsCount' => $notificationsEnabled ? (clone $notifications)->count() : 0,
            'unreadCount' => $notificationsEnabled
                ? (clone $notifications)->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $request->user()->id))->count()
                : 0,
            'sponsorshipsCount' => (clone $sponsorships)->count(),
            'moduleDefinitions' => $moduleDefinitions,
            'periodOptions' => $periodOptions,
            'selectedModule' => $selectedModule,
            'selectedPeriod' => $selectedPeriod,
            'reportModule' => $moduleDefinitions[$selectedModule],
            'reportColumns' => $reportColumns,
            'reportTableNote' => $reportTableNote,
            'reportRows' => $reportRows,
            'reportTotal' => $reportTotal,
            'latestParticipants' => (clone $participants)->latest()->take(8)->get(),
            'latestNotifications' => $notificationsEnabled ? (clone $notifications)->latest()->take(8)->get() : collect(),
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $user = $request->user();
        [$availableCenterIds, $selectedCenterId, $centerIds] = $this->resolveCenterScope($request, $user);
        $allowed = ['report', 'participants', 'users', 'notifications', 'sponsorships', 'all-items'];
        $moduleDefinitions = $this->reportModuleDefinitionsFor($user);
        $periodOptions = $this->periodOptions();
        $selectedModule = $request->string('module')->toString() ?: 'basic_information';
        $selectedPeriod = $request->string('period')->toString() ?: 'all_time';
        $selectedModule = array_key_exists($selectedModule, $moduleDefinitions) ? $selectedModule : array_key_first($moduleDefinitions);
        $selectedPeriod = array_key_exists($selectedPeriod, $periodOptions) ? $selectedPeriod : 'all_time';
        $periodStart = $this->resolvePeriodStart($selectedPeriod);

        abort_unless(in_array($type, $allowed, true), 404);

        $filenameType = $type === 'report' ? $selectedModule : $type;
        $scopeName = $selectedCenterId === 'all'
            ? ($user->isOfficialAdmin() ? 'all-centers' : (empty($availableCenterIds) ? 'no-centers' : implode('-', $availableCenterIds)))
            : $selectedCenterId;
        $filename = sprintf('%s-%s-%s.xls', $scopeName, $filenameType, now()->format('Ymd_His'));
        $exportTitle = $this->exportTitleFor($type, $moduleDefinitions[$selectedModule]);

        return response()->streamDownload(function () use ($type, $centerIds, $moduleDefinitions, $selectedModule, $periodStart, $exportTitle) {
            $handle = fopen('php://output', 'w');
            $user = request()->user();

            $this->writeCsvExcelHeader($handle, $exportTitle);

            match ($type) {
                'report' => $this->exportCurrentReport($handle, $moduleDefinitions[$selectedModule], $user, $periodStart, $centerIds),
                'participants' => $this->exportParticipants($handle, $centerIds, $user),
                'users' => $this->exportUsers($handle, $centerIds, $user),
                'notifications' => $this->exportNotifications($handle, $centerIds),
                'sponsorships' => $this->exportSponsorships($handle, $centerIds, $user),
                'all-items' => $this->exportAllItems($handle, $centerIds, $user),
            };

            $this->writeExcelDocumentEnd($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function exportTitleFor(string $type, array $moduleDefinition): string
    {
        return match ($type) {
            'report' => $moduleDefinition['label'] ?? 'Selected Report',
            'participants' => 'Participants Data',
            'users' => 'Users Data',
            'notifications' => 'Notifications Data',
            'sponsorships' => 'Sponsorships Data',
            'all-items' => 'All Report Data',
            default => 'Export Data',
        };
    }

    public function print(Request $request): View
    {
        $user = $request->user();
        [$availableCenterIds, $selectedCenterId, $centerIds] = $this->resolveCenterScope($request, $user);
        $moduleDefinitions = $this->reportModuleDefinitionsFor($user);
        $periodOptions = $this->periodOptions();
        $selectedModule = $request->string('module')->toString() ?: 'basic_information';
        $selectedPeriod = $request->string('period')->toString() ?: 'all_time';
        $selectedModule = array_key_exists($selectedModule, $moduleDefinitions) ? $selectedModule : array_key_first($moduleDefinitions);
        $selectedPeriod = array_key_exists($selectedPeriod, $periodOptions) ? $selectedPeriod : 'all_time';
        $periodStart = $this->resolvePeriodStart($selectedPeriod);
        $moduleDefinition = $moduleDefinitions[$selectedModule];
        $rows = $this->printReportRows($moduleDefinition, $user, $periodStart, $centerIds);

        return view('admin.reports.print', [
            'centerId' => $selectedCenterId === 'all'
                ? ($user->isOfficialAdmin() ? 'ALL' : (empty($availableCenterIds) ? 'N/A' : implode(', ', $availableCenterIds)))
                : $selectedCenterId,
            'periodLabel' => $periodOptions[$selectedPeriod],
            'scopeLabel' => $user->isOfficialAdmin()
                ? 'Official Admin: report only on admins and users across all centers.'
                : ($user->isAdmin()
                    ? 'Admin: data for centers and users under your supervision.'
                    : 'User: only your own account data and the records you uploaded are visible here.'),
            'reportTitle' => $moduleDefinition['label'],
            'reportColumns' => $this->columnsWithRecordedAt($moduleDefinition),
            'reportRows' => $rows,
            'reportTotal' => $rows->count(),
        ]);
    }

    protected function exportCurrentReport($handle, array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): void
    {
        $columns = $this->columnsWithRecordedAt($moduleDefinition);

        $this->writeCsvRow($handle, array_values($columns), true);

        match ($moduleDefinition['type'] ?? 'participant') {
            'accounts' => $this->exportAccountsReportRows($handle, $moduleDefinition, $user, $periodStart, $centerIds),
            'attendance' => $this->exportAttendanceReportRows($handle, $moduleDefinition, $user, $periodStart, $centerIds),
            'treated_participants' => $this->exportTreatedParticipantsReportRows($handle, $moduleDefinition, $user, $periodStart, $centerIds),
            default => $this->exportParticipantReportRows($handle, $moduleDefinition, $user, $periodStart, $centerIds),
        };
    }

    protected function columnsWithRecordedAt(array $moduleDefinition): array
    {
        $columns = $moduleDefinition['columns'];

        if (!array_key_exists('recorded_at', $columns)) {
            $columns['recorded_at'] = ($moduleDefinition['type'] ?? 'participant') === 'accounts' ? 'Created At' : 'Date';
        }

        return $columns;
    }

    protected function exportParticipants($handle, array $centerIds, User $user): void
    {
        $columns = array_values(array_filter(
            Schema::getColumnListing('participants'),
            fn ($column) => $column !== 'id'
        ));

        $headers = array_map(fn ($column) => Str::headline($column), $columns);
        $headers[] = 'Age';

        $this->writeCsvRow($handle, $headers, true);

        Participant::query()
            ->visibleToUser($user)
            ->forCenter($centerIds)
            ->orderBy('account_name')
            ->each(function ($participant) use ($handle, $columns) {
                $row = [];

                foreach ($columns as $column) {
                    $value = $participant->getAttribute($column);

                    if ($value instanceof \Carbon\CarbonInterface) {
                        $row[] = $value->format('Y-m-d H:i:s');
                        continue;
                    }

                    $row[] = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
                }

                $row[] = $participant->age;

                $this->writeCsvRow($handle, $row);
            });
    }

    protected function exportUsers($handle, array $centerIds, User $currentUser): void
    {
        $this->writeCsvRow($handle, ['Name', 'Email', 'Role', 'Center ID', 'Created At'], true);

        $query = $currentUser->role === User::ROLE_USER
            ? User::query()->whereKey($currentUser->id)
            : User::query()->forCenter($centerIds);

        $query
            ->orderBy('name')
            ->each(function ($user) use ($handle) {
                $this->writeCsvRow($handle, [
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->center_id,
                    optional($user->created_at)->format('Y-m-d H:i:s'),
                ]);
            });
    }

    protected function exportNotifications($handle, array $centerIds): void
    {
        if (!Schema::hasTable('center_notifications')) {
            return;
        }

        $this->writeCsvRow($handle, ['Type', 'Title', 'Message', 'Center ID', 'Due Date', 'Created At'], true);

        CenterNotification::query()
            ->forCenter($centerIds)
            ->latest()
            ->each(function ($notification) use ($handle) {
                $this->writeCsvRow($handle, [
                    $notification->type,
                    $notification->title,
                    $notification->message,
                    $notification->center_id,
                    optional($notification->due_date)->format('Y-m-d'),
                    optional($notification->created_at)->format('Y-m-d H:i:s'),
                ]);
            });
    }

    protected function exportSponsorships($handle, array $centerIds, User $user): void
    {
        $this->writeCsvRow($handle, ['Participant ID', 'Project Name', 'Sponsor Name', 'Sponsorship Type', 'Status', 'Contact'], true);

        ParticipantSponsorship::query()
            ->with('participant')
            ->visibleToUser($user)
            ->forCenter($centerIds)
            ->latest()
            ->each(function ($sponsorship) use ($handle) {
                $this->writeCsvRow($handle, [
                    optional($sponsorship->participant)->local_participant_id,
                    optional($sponsorship->participant)->account_name,
                    $sponsorship->sponsor_name ?: $sponsorship->sponsored_by,
                    $sponsorship->sponsorship_type,
                    $sponsorship->sponsorship_status,
                    $sponsorship->sponsor_contact,
                ]);
            });
    }

    protected function exportAllItems($handle, array $centerIds, User $user): void
    {
        $this->writeCsvRow($handle, ['Participants'], true);
        $this->exportParticipants($handle, $centerIds, $user);
        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Users'], true);
        $this->exportUsers($handle, $centerIds, $user);
        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Sponsorships'], true);
        $this->exportSponsorships($handle, $centerIds, $user);
        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Notifications'], true);
        $this->exportNotifications($handle, $centerIds);
    }

    protected function participantModuleDefinitions(): array
    {
        return [
            'basic_information' => [
                'label' => 'Basic Information',
                'type' => 'participant',
                'columns' => [
                    'local_participant_number' => 'Local Number',
                    'local_participant_id' => 'Participant ID',
                    'account_name' => 'Project Name',
                    'preferred_name' => 'Full Name',
                    'gender' => 'Gender',
                    'birthdate' => 'Birthdate',
                    'participant_status' => 'Status',
                ],
            ],
            'address_information' => [
                'label' => 'Address Information',
                'type' => 'participant',
                'columns' => [
                    'physical_address' => 'Physical Address',
                    'house_number' => 'House Number',
                    'region_city_street' => 'Region / City / Street',
                    'gps_location' => 'GPS Location',
                ],
            ],
            'contacts_parents' => [
                'label' => 'Contacts Za Parents',
                'type' => 'participant',
                'columns' => [
                    'parent_guardian_name' => 'Parent / Guardian',
                    'parent_guardian_occupation' => 'Occupation',
                    'parent_guardian_phone' => 'Phone',
                    'caregiver_name' => 'Caregiver',
                    'father_status' => 'Father Status',
                    'mother_status' => 'Mother Status',
                    'household_name' => 'House Hold Name',
                    'household_phone' => 'House Hold Phone',
                    'household_relationship' => 'House Hold Relationship',
                ],
            ],
            'fcp_association' => [
                'label' => 'FCP Association',
                'type' => 'participant',
                'columns' => [
                    'cluster' => 'Cluster',
                    'fcp_name' => 'FCP Name',
                    'partnership_facilitator' => 'PF',
                    'national_office_community_name' => 'National Office Community Name',
                ],
            ],
            'sponsorship_information' => [
                'label' => 'Sponsorship Information',
                'type' => 'participant',
                'columns' => [
                    'sponsor_name' => 'Sponsor Name',
                    'sponsored_by' => 'Sponsored By',
                    'sponsorship_type' => 'Sponsorship Type',
                    'sponsorship_status' => 'Sponsorship Status',
                    'sponsorship_start_date' => 'Start Date',
                    'sponsor_physical_address' => 'Sponsor Physical Address',
                    'sponsor_contact' => 'Sponsor Contact',
                    'sponsorship_category' => 'Sponsorship Category',
                ],
            ],
            'education_background' => [
                'label' => 'Education Background',
                'type' => 'participant',
                'columns' => [
                    'country' => 'Country',
                    'school_name' => 'School Name',
                    'current_class' => 'Current Class',
                    'education_stage' => 'Education Stage',
                    'primary_score' => 'Primary Average',
                    'secondary_average_score' => 'Secondary Average',
                    'university_gpa' => 'University GPA',
                    'education_grade' => 'Calculated Grade',
                    'is_in_school' => 'In School',
                    'not_in_school_reason' => 'Reason Not In School',
                ],
            ],
            'participant_interests_and_vision' => [
                'label' => 'Participant Interests And Vision',
                'type' => 'participant',
                'columns' => [
                    'things_i_like' => 'Things I Like',
                    'favorite_activities' => 'Favorite Activities',
                    'household_duties' => 'Household Duties',
                    'favorite_subjects' => 'Favorite Subjects',
                    'hobbies' => 'Hobbies',
                    'participant_needs' => 'Participant Needs',
                    'vision_for_tomorrow' => 'Vision For Tomorrow',
                ],
            ],
            'spiritual_information' => [
                'label' => 'Spiritual Information',
                'type' => 'participant',
                'columns' => [
                    'religious_affiliation' => 'Religious Affiliation',
                    'baptism_status' => 'Baptism Status',
                    'bible_distributed_date' => 'Bible Distributed Date',
                    'faith_confession_date' => 'Faith Confession Date',
                    'christian_activities' => 'Christian Activities',
                ],
            ],
            'general_assessment' => [
                'label' => 'General Assessment',
                'type' => 'participant',
                'columns' => [
                    'general_assessment_social' => 'Social',
                    'general_assessment_physical' => 'Physical',
                    'general_assessment_emotional' => 'Emotional',
                    'general_assessment_spiritual' => 'Spiritual',
                ],
            ],
            'medical_information' => [
                'label' => 'Medical Information',
                'type' => 'participant',
                'columns' => [
                    'weight' => 'Weight',
                    'height' => 'Height',
                    'disabilities' => 'Disabilities',
                    'chronic_illnesses' => 'Chronic Illnesses',
                    'treatment' => 'Treatment',
                    'treatment_date' => 'Treatment Date',
                    'tested_diseases' => 'Diseases Tested',
                    'illness_type' => 'Illness Type',
                    'treatment_location' => 'Treatment Location',
                    'treatment_cost' => 'Treatment Cost',
                    'health_comments' => 'Health Comments',
                ],
            ],
            'program_attendance_report' => [
                'label' => 'Program Attendance Report',
                'type' => 'attendance',
                'attendance_type' => 'program',
                'columns' => [
                    'attendance_date' => 'Attendance Date',
                    'center_id' => 'Center ID',
                    'instructor_name' => 'Instructor',
                    'topic' => 'Topic',
                    'present_count' => 'Present',
                    'absent_count' => 'Absent',
                    'total_count' => 'Total Participants',
                    'comment' => 'Comment',
                    'created_by' => 'Saved By',
                    'recorded_at' => 'Saved At',
                ],
            ],
            'activity_attendance_report' => [
                'label' => 'Activity Attendance Report',
                'type' => 'attendance',
                'attendance_type' => 'activity',
                'columns' => [
                    'activity_name' => 'Activity Name',
                    'attendance_date' => 'Attendance Date',
                    'center_id' => 'Center ID',
                    'instructor_name' => 'Instructor',
                    'topic' => 'Topic',
                    'present_count' => 'Present',
                    'absent_count' => 'Absent',
                    'total_count' => 'Total Participants',
                    'comment' => 'Comment',
                    'created_by' => 'Saved By',
                    'recorded_at' => 'Saved At',
                ],
            ],
            'treated_participants' => [
                'label' => 'Treatment Report',
                'type' => 'treated_participants',
                'columns' => [
                    'local_participant_id' => 'Participant ID',
                    'account_name' => 'Project Name',
                    'preferred_name' => 'Full Name',
                    'center_id' => 'Center ID',
                    'tested_diseases' => 'Diseases Tested',
                    'illness_type' => 'Illness / Injury',
                    'treatment' => 'Treatment',
                    'treatment_date' => 'Treatment Date',
                    'treatment_location' => 'Treatment Location',
                    'treatment_cost' => 'Treatment Cost',
                    'health_comments' => 'Health Comments',
                ],
            ],
        ];
    }

    protected function accountModuleDefinitions(): array
    {
        return [
            'users_summary' => [
                'label' => 'Users Report',
                'type' => 'accounts',
                'roles' => [User::ROLE_USER],
                'columns' => [
                    'name' => 'Name',
                    'email' => 'Email',
                    'role_label' => 'Account Type',
                    'project_name' => 'Project Name',
                    'cluster_name' => 'Cluster Name',
                    'center_id' => 'Center ID',
                    'approval_status' => 'Approval Status',
                ],
            ],
            'admins_summary' => [
                'label' => 'Admins Report',
                'type' => 'accounts',
                'roles' => [User::ROLE_ADMIN, User::ROLE_OFFICIAL_ADMIN],
                'columns' => [
                    'name' => 'Name',
                    'email' => 'Email',
                    'role_label' => 'Account Type',
                    'managed_scope' => 'Managed Scope',
                    'center_id' => 'Primary Center',
                    'approval_status' => 'Approval Status',
                ],
            ],
        ];
    }

    protected function reportModuleDefinitionsFor(User $user): array
    {
        return $user->isOfficialAdmin()
            ? $this->accountModuleDefinitions()
            : $this->participantModuleDefinitions();
    }

    protected function buildReportDataset(string $selectedModule, array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): array
    {
        return match ($moduleDefinition['type'] ?? 'participant') {
            'accounts' => $this->buildAccountsReportDataset($moduleDefinition, $user, $periodStart, $centerIds),
            'attendance' => $this->buildAttendanceReportDataset($moduleDefinition, $user, $periodStart, $centerIds),
            'treated_participants' => $this->buildTreatedParticipantsDataset($moduleDefinition, $user, $periodStart, $centerIds),
            default => $this->buildParticipantReportDataset($moduleDefinition, $user, $periodStart, $centerIds),
        };
    }

    protected function buildParticipantReportDataset(array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): array
    {
        $query = Participant::query()
            ->visibleToUser($user)
            ->forCenter($centerIds)
            ->when($periodStart, fn ($builder) => $builder->where('created_at', '>=', $periodStart))
            ->latest();

        $paginator = $query->paginate(25)->withQueryString()->through(function (Participant $participant) use ($moduleDefinition) {
            $row = [];

            foreach ($moduleDefinition['columns'] as $column => $label) {
                $value = data_get($participant, $column);

                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } elseif ($value instanceof \Carbon\CarbonInterface) {
                    $value = $value->format('d M Y');
                } elseif (in_array($column, ['father_status', 'mother_status'], true) && filled($value)) {
                    $value = match (mb_strtolower((string) $value)) {
                        'hai', 'alive' => 'Alive',
                        'wamekufa', 'amekufa', 'dead', 'deceased' => 'Deceased',
                        default => $value,
                    };
                } elseif (in_array($column, ['birthdate', 'sponsorship_start_date', 'treatment_date', 'bible_distributed_date', 'faith_confession_date', 'planned_completion_date', 'transition_date'], true) && $value) {
                    $value = Carbon::parse($value)->format('d M Y');
                }

                $row[$column] = filled($value) ? $value : 'N/A';
            }

            $row['recorded_at'] = optional($participant->created_at)->format('d M Y H:i');

            return $row;
        });

        return [
            $paginator,
            $query->count(),
            array_merge($moduleDefinition['columns'], ['recorded_at' => 'Date']),
            'Participant records returned by the selected period and scope.',
        ];
    }

    protected function buildAccountsReportDataset(array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): array
    {
        $query = User::query()
            ->with(['center', 'managedCenters'])
            ->whereIn('role', $moduleDefinition['roles'] ?? [User::ROLE_USER])
            ->when(!empty($centerIds), fn ($builder) => $builder->whereIn('center_id', $centerIds))
            ->when($periodStart, fn ($builder) => $builder->where('created_at', '>=', $periodStart))
            ->latest();

        $paginator = $query->paginate(25)->withQueryString()->through(function (User $account) use ($moduleDefinition) {
            return $this->mapAccountReportRow($account, $moduleDefinition);
        });

        return [
            $paginator,
            $query->count(),
            array_merge($moduleDefinition['columns'], ['recorded_at' => 'Created At']),
            'System account records returned by the selected report type and period.',
        ];
    }

    protected function exportParticipantReportRows($handle, array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): void
    {
        Participant::query()
            ->visibleToUser($user)
            ->forCenter($centerIds)
            ->when($periodStart, fn ($builder) => $builder->where('created_at', '>=', $periodStart))
            ->latest()
            ->each(function (Participant $participant) use ($handle, $moduleDefinition) {
                $row = $this->mapParticipantReportRow($participant, $moduleDefinition);
                $this->writeCsvRow($handle, array_values($row));
            });
    }

    protected function exportAccountsReportRows($handle, array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): void
    {
        User::query()
            ->with(['center', 'managedCenters'])
            ->whereIn('role', $moduleDefinition['roles'] ?? [User::ROLE_USER])
            ->when(!empty($centerIds), fn ($builder) => $builder->whereIn('center_id', $centerIds))
            ->when($periodStart, fn ($builder) => $builder->where('created_at', '>=', $periodStart))
            ->latest()
            ->each(function (User $account) use ($handle, $moduleDefinition) {
                $row = $this->mapAccountReportRow($account, $moduleDefinition);
                $this->writeCsvRow($handle, array_values($row));
            });
    }

    protected function buildAttendanceReportDataset(array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): array
    {
        $query = ProgramAttendanceSession::query()
            ->with('creator')
            ->visibleToUser($user)
            ->whereIn('center_id', $centerIds)
            ->when($moduleDefinition['attendance_type'] ?? null, fn ($builder, $type) => $builder->where('attendance_type', $type))
            ->when($periodStart, fn ($builder) => $builder->whereDate('attendance_date', '>=', $periodStart->toDateString()))
            ->latest('attendance_date')
            ->latest('id');

        $paginator = $query->paginate(25)->withQueryString()->through(
            fn (ProgramAttendanceSession $session) => $this->mapAttendanceReportRow($session, $moduleDefinition)
        );

        return [
            $paginator,
            $query->count(),
            $moduleDefinition['columns'],
            ($moduleDefinition['label'] ?? 'Attendance records') . ' returned by the selected period and scope.',
        ];
    }

    protected function exportAttendanceReportRows($handle, array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): void
    {
        ProgramAttendanceSession::query()
            ->with('creator')
            ->visibleToUser($user)
            ->whereIn('center_id', $centerIds)
            ->when($moduleDefinition['attendance_type'] ?? null, fn ($builder, $type) => $builder->where('attendance_type', $type))
            ->when($periodStart, fn ($builder) => $builder->whereDate('attendance_date', '>=', $periodStart->toDateString()))
            ->latest('attendance_date')
            ->latest('id')
            ->each(function (ProgramAttendanceSession $session) use ($handle, $moduleDefinition) {
                $row = $this->mapAttendanceReportRow($session, $moduleDefinition);
                $this->writeCsvRow($handle, array_values($row));
            });
    }

    protected function buildTreatedParticipantsDataset(array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): array
    {
        $query = ParticipantTreatment::query()
            ->with('participant')
            ->visibleToUser($user)
            ->whereIn('center_id', $centerIds)
            ->when($periodStart, fn ($builder) => $builder->whereDate('treatment_date', '>=', $periodStart->toDateString()))
            ->latest('treatment_date')
            ->latest('id');

        $paginator = $query->paginate(25)->withQueryString()->through(function (ParticipantTreatment $treatment) {
            return [
                'local_participant_id' => $treatment->participant?->local_participant_id ?: 'N/A',
                'account_name' => $treatment->participant?->account_name ?: 'N/A',
                'preferred_name' => $treatment->participant?->preferred_name ?: 'N/A',
                'center_id' => $treatment->center_id ?: 'N/A',
                'tested_diseases' => $treatment->tested_diseases ?: 'N/A',
                'illness_type' => $treatment->illness_type ?: 'N/A',
                'treatment' => $treatment->treatment ?: 'N/A',
                'treatment_date' => $treatment->treatment_date?->format('d M Y') ?: 'N/A',
                'treatment_location' => $treatment->treatment_location ?: 'N/A',
                'treatment_cost' => $treatment->treatment_cost !== null ? number_format((float) $treatment->treatment_cost, 2) : 'N/A',
                'health_comments' => $treatment->health_comments ?: 'N/A',
            ];
        });

        return [
            $paginator,
            $query->count(),
            $moduleDefinition['columns'],
            'Participants with treatment records returned by the selected period and scope.',
        ];
    }

    protected function exportTreatedParticipantsReportRows($handle, array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds): void
    {
        ParticipantTreatment::query()
            ->with('participant')
            ->visibleToUser($user)
            ->whereIn('center_id', $centerIds)
            ->when($periodStart, fn ($builder) => $builder->whereDate('treatment_date', '>=', $periodStart->toDateString()))
            ->latest('treatment_date')
            ->latest('id')
            ->each(function (ParticipantTreatment $treatment) use ($handle) {
                $row = $this->mapTreatedParticipantsReportRow($treatment);
                $this->writeCsvRow($handle, array_values($row));
            });
    }

    protected function printReportRows(array $moduleDefinition, User $user, ?Carbon $periodStart, array $centerIds)
    {
        return match ($moduleDefinition['type'] ?? 'participant') {
            'accounts' => User::query()
                ->with(['center', 'managedCenters'])
                ->whereIn('role', $moduleDefinition['roles'] ?? [User::ROLE_USER])
                ->when(!empty($centerIds), fn ($builder) => $builder->whereIn('center_id', $centerIds))
                ->when($periodStart, fn ($builder) => $builder->where('created_at', '>=', $periodStart))
                ->latest()
                ->get()
                ->map(fn (User $account) => $this->mapAccountReportRow($account, $moduleDefinition)),
            'attendance' => ProgramAttendanceSession::query()
                ->with('creator')
                ->visibleToUser($user)
                ->whereIn('center_id', $centerIds)
                ->when($moduleDefinition['attendance_type'] ?? null, fn ($builder, $type) => $builder->where('attendance_type', $type))
                ->when($periodStart, fn ($builder) => $builder->whereDate('attendance_date', '>=', $periodStart->toDateString()))
                ->latest('attendance_date')
                ->latest('id')
                ->get()
                ->map(fn (ProgramAttendanceSession $session) => $this->mapAttendanceReportRow($session, $moduleDefinition)),
            'treated_participants' => ParticipantTreatment::query()
                ->with('participant')
                ->visibleToUser($user)
                ->whereIn('center_id', $centerIds)
                ->when($periodStart, fn ($builder) => $builder->whereDate('treatment_date', '>=', $periodStart->toDateString()))
                ->latest('treatment_date')
                ->latest('id')
                ->get()
                ->map(fn (ParticipantTreatment $treatment) => $this->mapTreatedParticipantsReportRow($treatment)),
            default => Participant::query()
                ->visibleToUser($user)
                ->forCenter($centerIds)
                ->when($periodStart, fn ($builder) => $builder->where('created_at', '>=', $periodStart))
                ->latest()
                ->get()
                ->map(fn (Participant $participant) => $this->mapParticipantReportRow($participant, $moduleDefinition)),
        };
    }

    protected function mapParticipantReportRow(Participant $participant, array $moduleDefinition): array
    {
        $row = [];

        foreach ($moduleDefinition['columns'] as $column => $label) {
            $value = data_get($participant, $column);

            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            } elseif ($value instanceof \Carbon\CarbonInterface) {
                $value = $value->format('d M Y');
            } elseif (in_array($column, ['father_status', 'mother_status'], true) && filled($value)) {
                $value = match (mb_strtolower((string) $value)) {
                    'hai', 'alive' => 'Alive',
                    'wamekufa', 'amekufa', 'dead', 'deceased' => 'Deceased',
                    default => $value,
                };
            } elseif (in_array($column, ['birthdate', 'sponsorship_start_date', 'treatment_date', 'bible_distributed_date', 'faith_confession_date', 'planned_completion_date', 'transition_date'], true) && $value) {
                $value = Carbon::parse($value)->format('d M Y');
            }

            $row[$column] = filled($value) ? $value : 'N/A';
        }

        $row['recorded_at'] = optional($participant->created_at)->format('d M Y H:i') ?: 'N/A';

        return $row;
    }

    protected function mapAccountReportRow(User $account, array $moduleDefinition): array
    {
        $managedScope = $account->isOfficialAdmin()
            ? 'ALL'
            : ($account->isAdmin()
                ? ($account->managedCenters()->pluck('centers.center_id')->filter()->implode(', ') ?: ($account->cluster_name ?: ($account->center_id ?: 'N/A')))
                : ($account->cluster_name ?: 'N/A'));

        $values = [
            'name' => $account->name ?: 'N/A',
            'email' => $account->email ?: 'N/A',
            'role_label' => User::roles()[$account->role] ?? ucfirst((string) $account->role),
            'project_name' => $account->project_display_name ?: 'N/A',
            'cluster_name' => $account->cluster_name ?: 'N/A',
            'center_id' => $account->center_id ?: 'N/A',
            'approval_status' => $account->isApproved() ? 'Approved' : 'Pending',
            'managed_scope' => $managedScope,
            'recorded_at' => optional($account->created_at)->format('d M Y H:i') ?: 'N/A',
        ];

        $row = [];

        foreach (array_keys($moduleDefinition['columns']) as $column) {
            $row[$column] = $values[$column] ?? 'N/A';
        }

        $row['recorded_at'] = $values['recorded_at'];

        return $row;
    }

    protected function mapAttendanceReportRow(ProgramAttendanceSession $session, ?array $moduleDefinition = null): array
    {
        $values = [
            'attendance_type' => $session->attendance_type ? ucfirst($session->attendance_type) : 'Program',
            'activity_name' => $session->activity_name ?: 'N/A',
            'attendance_date' => optional($session->attendance_date)->format('d M Y') ?: 'N/A',
            'center_id' => $session->center_id ?: 'N/A',
            'instructor_name' => $session->instructor_name ?: 'N/A',
            'topic' => $session->topic ?: 'N/A',
            'present_count' => (string) $session->present_count,
            'absent_count' => (string) $session->absent_count,
            'total_count' => (string) ((int) $session->present_count + (int) $session->absent_count),
            'comment' => $session->comment ?: 'N/A',
            'created_by' => $session->creator?->name ?: 'N/A',
            'recorded_at' => optional($session->created_at)->format('d M Y H:i') ?: 'N/A',
        ];

        if (!$moduleDefinition) {
            return $values;
        }

        $row = [];
        foreach (array_keys($moduleDefinition['columns']) as $column) {
            $row[$column] = $values[$column] ?? 'N/A';
        }

        return $row;
    }

    protected function mapTreatedParticipantsReportRow(ParticipantTreatment $treatment): array
    {
        return [
            'local_participant_id' => $treatment->participant?->local_participant_id ?: 'N/A',
            'account_name' => $treatment->participant?->account_name ?: 'N/A',
            'preferred_name' => $treatment->participant?->preferred_name ?: 'N/A',
            'center_id' => $treatment->center_id ?: 'N/A',
            'tested_diseases' => $treatment->tested_diseases ?: 'N/A',
            'illness_type' => $treatment->illness_type ?: 'N/A',
            'treatment' => $treatment->treatment ?: 'N/A',
            'treatment_date' => $treatment->treatment_date?->format('d M Y') ?: 'N/A',
            'treatment_location' => $treatment->treatment_location ?: 'N/A',
            'treatment_cost' => $treatment->treatment_cost !== null ? number_format((float) $treatment->treatment_cost, 2) : 'N/A',
            'health_comments' => $treatment->health_comments ?: 'N/A',
            'recorded_at' => optional($treatment->created_at)->format('d M Y H:i') ?: 'N/A',
        ];
    }

    protected function writeCsvExcelHeader($handle, string $title = 'Report'): void
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
        th { font-weight: 700; background: #dbeafe; color: #0f172a; border: 1px solid #94a3b8; padding: 8px 10px; white-space: nowrap; min-width: 130px; mso-number-format: "\\@"; }
        td { border: 1px solid #cbd5e1; padding: 7px 10px; white-space: nowrap; mso-number-format: "\\@"; }
        td.long-cell { white-space: normal; min-width: 220px; max-width: 520px; }
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

    protected function formatCsvCellForExcel($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $this->excelTextCell($value->format('d M Y H:i'));
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $value = str_replace(["\r\n", "\r"], "\n", trim((string) $value));

        if ($value === '') {
            return '';
        }

        return $value;
    }

    protected function excelTextCell(string $value): string
    {
        return $value;
    }

    protected function writeExcelDocumentEnd($handle): void
    {
        fwrite($handle, '</table></body></html>');
    }

    protected function periodOptions(): array
    {
        return [
            'all_time' => 'All Time',
            'weekly' => 'This Week',
            'monthly' => 'This Month',
            'two_months' => 'Last 2 Months',
        ];
    }

    protected function resolvePeriodStart(string $period): ?Carbon
    {
        return match ($period) {
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            'two_months' => now()->subMonths(2),
            default => null,
        };
    }

    protected function resolveCenterScope(Request $request, User $user): array
    {
        $availableCenterIds = collect($user->accessibleCenterIds())
            ->filter(fn ($centerId) => filled($centerId))
            ->unique()
            ->values()
            ->all();
        $selectedCenterId = $request->string('center_id')->toString();
        $selectedCenterId = $selectedCenterId !== '' ? $selectedCenterId : 'all';

        if ($selectedCenterId !== 'all' && !in_array($selectedCenterId, $availableCenterIds, true)) {
            $selectedCenterId = 'all';
        }

        $centerIds = $selectedCenterId === 'all' ? $availableCenterIds : [$selectedCenterId];
        $centerNames = Center::query()
            ->whereIn('center_id', $availableCenterIds)
            ->pluck('center_name', 'center_id');
        $centerOptions = collect($availableCenterIds)
            ->map(fn (string $centerId) => [
                'value' => $centerId,
                'label' => $centerNames->get($centerId)
                    ? $centerId . ' - ' . $centerNames->get($centerId)
                    : $centerId,
            ])
            ->values();

        return [$availableCenterIds, $selectedCenterId, $centerIds, $centerOptions];
    }

}
