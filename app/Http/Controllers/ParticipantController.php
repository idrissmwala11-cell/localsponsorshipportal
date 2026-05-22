<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ParticipantController extends Controller
{
    protected const PHOTO_UPDATE_MONTHS = 18;

    public function search(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $user = Auth::user();

        if ($search === '') {
            return response()->json([]);
        }

        $participants = Participant::query()
            ->visibleToUser($user)
            ->where(function ($query) use ($search) {
                $query->where('account_name', 'like', "%{$search}%")
                    ->orWhere('preferred_name', 'like', "%{$search}%")
                    ->orWhere('local_participant_id', 'like', "%{$search}%")
                    ->orWhere('local_participant_number', 'like', "%{$search}%");
            })
            ->orderBy('account_name')
            ->limit(8)
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->account_name,
                    'preferred_name' => $participant->preferred_name,
                    'participant_id' => $participant->local_participant_id,
                    'center_id' => $participant->center_id,
                    'url' => route('participants.show', $participant->id),
                ];
            })
            ->values();

        return response()->json($participants);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Participant::with('latestSponsorship')
            ->visibleToUser($user);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                    ->orWhere('preferred_name', 'like', "%{$search}%")
                    ->orWhere('local_participant_id', 'like', "%{$search}%")
                    ->orWhere('local_participant_number', 'like', "%{$search}%")
                    ->orWhere('center_id', 'like', "%{$search}%")
                    ->orWhere('participant_status', 'like', "%{$search}%")
                    ->orWhere('sponsorship_status', 'like', "%{$search}%")
                    ->orWhere('physical_address', 'like', "%{$search}%")
                    ->orWhere('region_city_street', 'like', "%{$search}%")
                    ->orWhere('house_number', 'like', "%{$search}%")
                    ->orWhere('gps_location', 'like', "%{$search}%")
                    ->orWhere('parent_guardian_name', 'like', "%{$search}%")
                    ->orWhere('parent_guardian_phone', 'like', "%{$search}%")
                    ->orWhere('caregiver_name', 'like', "%{$search}%")
                    ->orWhere('father_status', 'like', "%{$search}%")
                    ->orWhere('mother_status', 'like', "%{$search}%")
                    ->orWhere('household_name', 'like', "%{$search}%")
                    ->orWhere('household_phone', 'like', "%{$search}%")
                    ->orWhere('household_relationship', 'like', "%{$search}%")
                    ->orWhere('cluster', 'like', "%{$search}%")
                    ->orWhere('fcp_name', 'like', "%{$search}%")
                    ->orWhere('partnership_facilitator', 'like', "%{$search}%")
                    ->orWhere('national_office_community_name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%")
                    ->orWhere('current_class', 'like', "%{$search}%")
                    ->orWhere('education_stage', 'like', "%{$search}%")
                    ->orWhere('religious_affiliation', 'like', "%{$search}%")
                    ->orWhere('baptism_status', 'like', "%{$search}%")
                    ->orWhereHas('latestSponsorship', function ($sponsorshipQuery) use ($search) {
                        $sponsorshipQuery->where('funding_type', 'like', "%{$search}%")
                            ->orWhere('sponsorship_status', 'like', "%{$search}%")
                            ->orWhere('sponsored_by', 'like', "%{$search}%")
                            ->orWhere('sponsor_name', 'like', "%{$search}%")
                            ->orWhere('sponsor_contact', 'like', "%{$search}%")
                            ->orWhere('sponsorship_category', 'like', "%{$search}%");
                    });
            });
        }

        $participants = $query->latest()->paginate(10)->withQueryString();

        return view('participants.index', compact('participants'));
    }

    public function create()
    {
        return view('participants.create');
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $this->validatedData($request);

                if (!Schema::hasColumn('participants', 'funding_type')) {
                    unset($data['funding_type']);
                }

                $centerId = Auth::user()->center_id ?: (Auth::user()->accessibleCenterIds()[0] ?? 'CENTER');
                [$nextParticipantNumber, $nextParticipantId] = $this->generateNextParticipantIdentifiers($centerId);

                $data['center_id'] = $centerId;
                $data['created_by_user_id'] = Auth::id();
                $data['local_participant_id'] = $nextParticipantId;

                if (empty($data['local_participant_number'])) {
                    $data['local_participant_number'] = $nextParticipantNumber;
                }

                $data = $this->prepareParticipantData($data, $request);

                $participant = Participant::create($data);
                $this->syncSponsorshipRecords($participant, $request);

                return redirect()
                    ->route('participants.index')
                    ->with('success', 'Participant created successfully.');
            });
        } catch (Throwable $exception) {
            Log::error('Participant create failed.', [
                'user_id' => Auth::id(),
                'center_id' => Auth::user()?->center_id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Participant could not be saved. Please review the form and try again.');
        }
    }

    public function show($id)
    {
        $participant = Participant::with(['latestSponsorship', 'sponsorships' => fn ($query) => $query->latest()])->findOrFail($id);

        if (!Auth::user()->canAccessCenter($participant->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $participant->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        return view('participants.show', compact('participant'));
    }

    public function edit($id)
    {
        $participant = Participant::with(['latestSponsorship', 'sponsorships' => fn ($query) => $query->latest()])->findOrFail($id);

        if (!Auth::user()->canAccessCenter($participant->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $participant->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        return view('participants.edit', compact('participant'));
    }

    public function update(Request $request, $id)
    {
        $participant = Participant::with(['latestSponsorship', 'sponsorships' => fn ($query) => $query->latest()])->findOrFail($id);

        if (!Auth::user()->canAccessCenter($participant->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $participant->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        try {
            return DB::transaction(function () use ($request, $participant) {
                $data = $this->validatedData($request);

                if (!Schema::hasColumn('participants', 'funding_type')) {
                    unset($data['funding_type']);
                }

                $data['center_id'] = $participant->center_id;

                if ($request->hasFile('photo')) {
                    if ($participant->photo && Storage::disk('public')->exists($participant->photo)) {
                        Storage::disk('public')->delete($participant->photo);
                    }
                }

                $data = $this->prepareParticipantData($data, $request);

                $participant->update($data);
                $this->syncSponsorshipRecords($participant->fresh(['latestSponsorship', 'sponsorships']), $request);

                return redirect()
                    ->route('participants.index')
                    ->with('success', 'Participant updated successfully.');
            });
        } catch (Throwable $exception) {
            Log::error('Participant update failed.', [
                'participant_id' => $participant->id,
                'user_id' => Auth::id(),
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Participant could not be updated. Please review the form and try again.');
        }
    }

    public function destroy($id)
    {
        $participant = Participant::findOrFail($id);

        if (!Auth::user()->canAccessCenter($participant->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $participant->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        if ($participant->photo && Storage::disk('public')->exists($participant->photo)) {
            Storage::disk('public')->delete($participant->photo);
        }

        $participant->delete();

        return redirect()
            ->route('participants.index')
            ->with('success', 'Participant deleted successfully.');
    }

    protected function syncSponsorshipRecords(Participant $participant, Request $request): void
    {
        $entries = collect($request->input('sponsor_entries', []))
            ->map(function ($entry) {
                $entry = is_array($entry) ? $entry : [];

                return [
                    'funding_type' => $entry['funding_type'] ?? null,
                    'sponsorship_status' => $entry['sponsorship_status'] ?? null,
                    'sponsored_by' => $entry['sponsored_by'] ?? null,
                    'sponsor_name' => ($entry['sponsor_name'] ?? null) ?: ($entry['sponsored_by'] ?? null),
                    'sponsor_type' => $entry['sponsor_type'] ?? null,
                    'sponsorship_type' => $entry['sponsorship_type'] ?? null,
                    'sponsor_physical_address' => $entry['sponsor_physical_address'] ?? null,
                    'sponsor_contact' => $entry['sponsor_contact'] ?? null,
                    'sponsorship_start_date' => $entry['sponsorship_start_date'] ?? null,
                    'sponsorship_category' => $entry['sponsorship_category'] ?? null,
                ];
            })
            ->filter(fn ($entry) => collect($entry)->filter(fn ($value) => filled($value))->isNotEmpty())
            ->values();

        if (!$request->has('sponsor_entries')) {
            $fallback = [
                'funding_type' => $request->input('sponsorship_funding_type'),
                'sponsorship_status' => $request->input('sponsorship_record_status') ?: $request->input('sponsorship_status'),
                'sponsored_by' => $request->input('sponsored_by'),
                'sponsor_name' => $request->input('sponsor_name') ?: $request->input('sponsored_by'),
                'sponsor_type' => $request->input('sponsor_type'),
                'sponsorship_type' => $request->input('sponsorship_type'),
                'sponsor_physical_address' => $request->input('sponsor_physical_address'),
                'sponsor_contact' => $request->input('sponsor_contact'),
                'sponsorship_start_date' => $request->input('sponsorship_start_date'),
                'sponsorship_category' => $request->input('sponsorship_category'),
            ];

            if (collect($fallback)->filter(fn ($value) => filled($value))->isNotEmpty()) {
                $entries = collect([$fallback]);
            }
        }

        $participant->sponsorships()->delete();

        $entries->each(function ($entry) use ($participant) {
            ParticipantSponsorship::create(array_merge($entry, [
                'participant_id' => $participant->id,
                'created_by_user_id' => Auth::id(),
            ]));
        });

        $latest = $participant->sponsorships()->latest()->first();
        $participantUpdates = [];

        foreach ([
            'sponsorship_status',
            'funding_type',
            'sponsorship_start_date',
            'sponsorship_category',
            'sponsor_type',
            'sponsorship_type',
            'sponsor_physical_address',
            'sponsor_contact',
        ] as $column) {
            if (Schema::hasColumn('participants', $column)) {
                $participantUpdates[$column] = $latest?->{$column};
            }
        }

        if (Schema::hasColumn('participants', 'sponsored_by')) {
            $participantUpdates['sponsored_by'] = $latest?->sponsor_name ?: $latest?->sponsored_by;
        }

        $participant->update($participantUpdates);
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'local_participant_number' => 'nullable|string|max:50',
            'account_name' => 'required|string|max:255',
            'preferred_name' => 'nullable|string|max:255',
            'gender' => 'required|string|max:50',
            'birthdate' => 'nullable|date',
            'participant_status' => 'required|string|max:255',
            'sponsorship_status' => 'nullable|string|max:255',
            'funding_type' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sponsorship_funding_type' => 'nullable|string|max:255',
            'sponsorship_record_status' => 'nullable|string|max:255',
            'sponsored_by' => 'nullable|string|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_type' => 'nullable|string|max:255',
            'sponsorship_type' => 'nullable|string|max:255',
            'sponsor_physical_address' => 'nullable|string',
            'sponsor_contact' => 'nullable|string|max:255',
            'sponsorship_start_date' => 'nullable|date',
            'sponsorship_category' => 'nullable|string|max:255',
            'cluster' => 'nullable|string|max:255',
            'fcp_name' => 'nullable|string|max:255',
            'partnership_facilitator' => 'nullable|string|max:255',
            'national_office_community_name' => 'nullable|string|max:255',
            'attending_location' => 'nullable|string|max:255',
            'planned_completion_date' => 'nullable|date',
            'transition_date' => 'nullable|date',
            'planned_exit_type' => 'nullable|string|max:255',
            'planned_exit_reason' => 'nullable|string',
            'unplanned_exit_lessons' => 'nullable|string',
            'address' => 'nullable|string',
            'gps_location' => 'nullable|string',
            'physical_address' => 'nullable|string',
            'house_number' => 'nullable|string|max:255',
            'region_city_street' => 'nullable|string|max:255',
            'parent_guardian_name' => 'nullable|string|max:255',
            'parent_guardian_occupation' => 'nullable|string|max:255',
            'parent_guardian_phone' => 'nullable|string|max:255',
            'caregiver_name' => 'nullable|string|max:255',
            'father_status' => 'nullable|string|max:50',
            'mother_status' => 'nullable|string|max:50',
            'household_name' => 'nullable|string|max:255',
            'household_phone' => 'nullable|string|max:255',
            'household_relationship' => 'nullable|string|max:255',
            'things_i_like' => 'nullable|string',
            'favorite_activities' => 'nullable|string',
            'household_duties' => 'nullable|string',
            'favorite_subjects' => 'nullable|string',
            'hobbies' => 'nullable|string',
            'participant_needs' => 'nullable|string',
            'vision_for_tomorrow' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'grade_level' => 'nullable|string|max:255',
            'school_performance' => 'nullable|string|max:255',
            'course_of_study' => 'nullable|string|max:255',
            'vocational_training' => 'nullable|string|max:255',
            'school_name' => 'nullable|string|max:255',
            'current_class' => 'nullable|string|max:255',
            'education_stage' => 'nullable|string|max:255',
            'primary_score' => 'nullable|numeric|min:0|max:100',
            'primary_kiswahili_score' => 'nullable|numeric|min:0|max:100',
            'primary_english_score' => 'nullable|numeric|min:0|max:100',
            'primary_mathematics_score' => 'nullable|numeric|min:0|max:100',
            'primary_science_score' => 'nullable|numeric|min:0|max:100',
            'primary_social_studies_score' => 'nullable|numeric|min:0|max:100',
            'secondary_english_score' => 'nullable|numeric|min:0|max:100',
            'secondary_mathematics_score' => 'nullable|numeric|min:0|max:100',
            'secondary_biology_score' => 'nullable|numeric|min:0|max:100',
            'secondary_chemistry_score' => 'nullable|numeric|min:0|max:100',
            'secondary_physics_score' => 'nullable|numeric|min:0|max:100',
            'secondary_average_score' => 'nullable|numeric|min:0|max:100',
            'o_level_score' => 'nullable|numeric|min:0|max:100',
            'a_level_score' => 'nullable|numeric|min:0|max:100',
            'college_score' => 'nullable|numeric|min:0|max:100',
            'university_semester_one_gpa' => 'nullable|numeric|min:0|max:5',
            'university_semester_two_gpa' => 'nullable|numeric|min:0|max:5',
            'university_semester_three_gpa' => 'nullable|numeric|min:0|max:5',
            'university_semester_four_gpa' => 'nullable|numeric|min:0|max:5',
            'university_gpa' => 'nullable|numeric|min:0|max:5',
            'is_in_school' => 'nullable|boolean',
            'not_in_school_reason' => 'nullable|string',
            'religious_affiliation' => 'nullable|string|max:255',
            'bible_distributed_date' => 'nullable|date',
            'faith_confession_date' => 'nullable|date',
            'christian_activities' => 'nullable|string',
            'baptism_status' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:100',
            'height' => 'nullable|string|max:100',
            'disabilities' => 'nullable|string',
            'chronic_illnesses' => 'nullable|array',
            'chronic_illnesses.*' => 'nullable|string|max:255',
            'chronic_illness_other' => 'nullable|string|max:255',
            'general_assessment_social' => 'nullable|string',
            'general_assessment_physical' => 'nullable|string',
            'general_assessment_emotional' => 'nullable|string',
            'general_assessment_spiritual' => 'nullable|string',
            'sponsor_entries' => 'nullable|array',
            'sponsor_entries.*.sponsor_name' => 'nullable|string|max:255',
            'sponsor_entries.*.sponsored_by' => 'nullable|string|max:255',
            'sponsor_entries.*.sponsorship_type' => 'nullable|string|max:255',
            'sponsor_entries.*.sponsorship_status' => 'nullable|string|max:255',
            'sponsor_entries.*.sponsorship_start_date' => 'nullable|date',
            'sponsor_entries.*.sponsor_physical_address' => 'nullable|string',
            'sponsor_entries.*.sponsor_contact' => 'nullable|string|max:255',
            'sponsor_entries.*.sponsorship_category' => 'nullable|string|max:255',
        ]);
    }

    protected function prepareParticipantData(array $data, Request $request): array
    {
        $data['is_in_school'] = $request->boolean('is_in_school', true);
        $data['father_status'] = $this->normalizeParentStatus($data['father_status'] ?? null);
        $data['mother_status'] = $this->normalizeParentStatus($data['mother_status'] ?? null);
        $data['gps_location'] = $this->normalizeGpsLocation(
            $data['gps_location'] ?? null,
            $data['physical_address'] ?? $data['address'] ?? null,
            $data['house_number'] ?? null
        );
        $selectedIllnesses = collect($request->input('chronic_illnesses', []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->values();

        $otherIllness = trim((string) $request->input('chronic_illness_other', ''));

        if ($selectedIllnesses->contains('Other') && $otherIllness !== '') {
            $selectedIllnesses = $selectedIllnesses
                ->reject(fn ($value) => $value === 'Other')
                ->push($otherIllness);
        }

        $data['chronic_illnesses'] = $selectedIllnesses
            ->unique()
            ->implode(', ');
        $data['primary_score'] = $this->calculateAverage([
            $data['primary_kiswahili_score'] ?? null,
            $data['primary_english_score'] ?? null,
            $data['primary_mathematics_score'] ?? null,
            $data['primary_science_score'] ?? null,
            $data['primary_social_studies_score'] ?? null,
        ]);
        $data['secondary_average_score'] = $this->calculateAverage([
            $data['secondary_english_score'] ?? null,
            $data['secondary_mathematics_score'] ?? null,
            $data['secondary_biology_score'] ?? null,
            $data['secondary_chemistry_score'] ?? null,
            $data['secondary_physics_score'] ?? null,
        ]);
        $data['university_gpa'] = $this->calculateAverage([
            $data['university_semester_one_gpa'] ?? null,
            $data['university_semester_two_gpa'] ?? null,
            $data['university_semester_three_gpa'] ?? null,
            $data['university_semester_four_gpa'] ?? null,
        ], 5);
        $data['education_grade'] = $this->calculateEducationGrade($data);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('participants', 'public');
            $data['photo_updated_at'] = now();
            $data['next_photo_update_due_at'] = Carbon::now()->addMonths(self::PHOTO_UPDATE_MONTHS)->toDateString();
        }

        if (!empty($data['birthdate'])) {
            $age = Carbon::parse($data['birthdate'])->age;

            if ($age >= 22 && ($data['participant_status'] ?? null) !== 'Exited') {
                $data['participant_status'] = 'Planned Exit';
                $data['planned_exit_type'] = $data['planned_exit_type'] ?: 'By Age';
                $data['planned_exit_reason'] = $data['planned_exit_reason'] ?: 'Participant has reached 22 years.';
                $data['planned_completion_date'] = $data['planned_completion_date']
                    ?: Carbon::parse($data['birthdate'])->addYears(22)->toDateString();
            }
        }

        if (($data['participant_status'] ?? null) === 'Planned Exit' && empty($data['planned_exit_type'])) {
            $data['planned_exit_type'] = 'Manual';
        }

        return $data;
    }

    protected function normalizeGpsLocation(?string $gpsLocation, ?string $physicalAddress, ?string $houseNumber): ?string
    {
        $gpsLocation = trim((string) $gpsLocation);

        if ($gpsLocation !== '') {
            return mb_substr($gpsLocation, 0, 255);
        }

        $fallback = collect([
            trim((string) $physicalAddress),
            trim((string) $houseNumber),
            'Tanzania',
        ])->filter(fn ($value) => $value !== '')->implode(', ');

        return $fallback !== '' ? mb_substr($fallback, 0, 255) : null;
    }

    protected function calculateEducationGrade(array $data): ?string
    {
        if (($data['education_stage'] ?? null) === 'University' && !empty($data['university_gpa'])) {
            $gpa = (float) $data['university_gpa'];

            return match (true) {
                $gpa >= 4.4 => 'A',
                $gpa >= 3.5 => 'B',
                $gpa >= 2.7 => 'C',
                $gpa >= 2.0 => 'D',
                default => 'F',
            };
        }

        if (($data['education_stage'] ?? null) === 'Primary' && !empty($data['primary_score'])) {
            return $this->gradeFromAverage((float) $data['primary_score']);
        }

        if (($data['education_stage'] ?? null) === 'Secondary' && !empty($data['secondary_average_score'])) {
            return $this->gradeFromAverage((float) $data['secondary_average_score']);
        }

        return null;
    }

    protected function calculateAverage(array $values, float $max = 100): ?float
    {
        $scores = collect($values)->filter(fn ($value) => $value !== null && $value !== '');

        if ($scores->isEmpty()) {
            return null;
        }

        return round(min($max, (float) $scores->avg()), 2);
    }

    protected function gradeFromAverage(float $average): string
    {
        return match (true) {
            $average >= 80 => 'A',
            $average >= 65 => 'B',
            $average >= 50 => 'C',
            $average >= 40 => 'D',
            default => 'F',
        };
    }

    protected function normalizeParentStatus(?string $status): ?string
    {
        $value = trim((string) $status);

        if ($value === '') {
            return null;
        }

        return match (mb_strtolower($value)) {
            'hai', 'alive' => 'Alive',
            'wamekufa', 'amekufa', 'dead', 'deceased' => 'Deceased',
            default => $value,
        };
    }

    protected function generateNextParticipantIdentifiers(string $centerId): array
    {
        $nextSequence = Participant::query()
            ->where('center_id', $centerId)
            ->pluck('local_participant_id')
            ->filter()
            ->map(function ($participantId) use ($centerId) {
                $value = (string) $participantId;

                if (str_starts_with($value, $centerId)) {
                    $value = substr($value, strlen($centerId));
                }

                return ctype_digit($value) ? (int) $value : null;
            })
            ->filter(fn ($value) => $value !== null)
            ->max();

        $nextSequence = ((int) $nextSequence) + 1;
        $nextParticipantNumber = str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);

        return [
            $nextParticipantNumber,
            $centerId . $nextParticipantNumber,
        ];
    }
}
