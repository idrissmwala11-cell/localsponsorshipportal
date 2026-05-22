<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $appends = [
        'age',
        'current_funding_type',
        'current_sponsorship_status',
        'current_sponsored_by',
        'current_sponsorship_start_date',
        'current_sponsorship_category',
        'project_name',
        'map_query',
        'map_embed_url',
    ];

    protected $fillable = [
        'local_participant_number',
        'local_participant_id',
        'account_name',
        'preferred_name',
        'gender',
        'birthdate',
        'participant_status',
        'sponsorship_status',
        'center_id',
        'created_by_user_id',
        'photo',
        'photo_updated_at',
        'next_photo_update_due_at',

        // C. FCP Association
        'cluster',
        'fcp_name',
        'partnership_facilitator',
        'national_office_community_name',
        'attending_location',

        // D. Significant Dates
        'planned_completion_date',
        'transition_date',

        // E. Address Information
        'address',
        'gps_location',
        'physical_address',
        'house_number',
        'region_city_street',

        // F. Participant Favorites
        'things_i_like',
        'favorite_activities',
        'household_duties',
        'favorite_subjects',
        'hobbies',
        'participant_needs',
        'vision_for_tomorrow',

        // G. Education Information
        'country',
        'grade_level',
        'school_performance',
        'course_of_study',
        'vocational_training',
        'school_name',
        'current_class',
        'education_stage',
        'education_grade',
        'primary_score',
        'primary_kiswahili_score',
        'primary_english_score',
        'primary_mathematics_score',
        'primary_science_score',
        'primary_social_studies_score',
        'secondary_english_score',
        'secondary_mathematics_score',
        'secondary_biology_score',
        'secondary_chemistry_score',
        'secondary_physics_score',
        'secondary_average_score',
        'o_level_score',
        'a_level_score',
        'college_score',
        'university_semester_one_gpa',
        'university_semester_two_gpa',
        'university_semester_three_gpa',
        'university_semester_four_gpa',
        'university_gpa',
        'is_in_school',
        'not_in_school_reason',

        // H. Spiritual Information
        'religious_affiliation',
        'bible_distributed_date',
        'faith_confession_date',
        'christian_activities',
        'baptism_status',

        // I. Medical Information
        'weight',
        'height',
        'disabilities',
        'chronic_illnesses',
        'treatment',
        'treatment_date',
        'tested_diseases',
        'illness_type',
        'treatment_location',
        'treatment_cost',
        'health_comments',
        'general_assessment_social',
        'general_assessment_physical',
        'general_assessment_emotional',
        'general_assessment_spiritual',
        'planned_exit_type',
        'planned_exit_reason',
        'unplanned_exit_lessons',
        'parent_guardian_name',
        'parent_guardian_occupation',
        'parent_guardian_phone',
        'caregiver_name',
        'father_status',
        'mother_status',
        'household_name',
        'household_phone',
        'household_relationship',
        'sponsored_by',
        'sponsorship_start_date',
        'sponsorship_category',
        'sponsor_type',
        'sponsorship_type',
        'sponsor_physical_address',
        'sponsor_contact',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'photo_updated_at' => 'datetime',
        'next_photo_update_due_at' => 'date',
        'planned_completion_date' => 'date',
        'transition_date' => 'date',
        'bible_distributed_date' => 'date',
        'faith_confession_date' => 'date',
        'treatment_date' => 'date',
        'sponsorship_start_date' => 'date',
        'primary_score' => 'decimal:2',
        'primary_kiswahili_score' => 'decimal:2',
        'primary_english_score' => 'decimal:2',
        'primary_mathematics_score' => 'decimal:2',
        'primary_science_score' => 'decimal:2',
        'primary_social_studies_score' => 'decimal:2',
        'secondary_english_score' => 'decimal:2',
        'secondary_mathematics_score' => 'decimal:2',
        'secondary_biology_score' => 'decimal:2',
        'secondary_chemistry_score' => 'decimal:2',
        'secondary_physics_score' => 'decimal:2',
        'secondary_average_score' => 'decimal:2',
        'o_level_score' => 'decimal:2',
        'a_level_score' => 'decimal:2',
        'college_score' => 'decimal:2',
        'university_semester_one_gpa' => 'decimal:2',
        'university_semester_two_gpa' => 'decimal:2',
        'university_semester_three_gpa' => 'decimal:2',
        'university_semester_four_gpa' => 'decimal:2',
        'university_gpa' => 'decimal:2',
        'treatment_cost' => 'decimal:2',
        'is_in_school' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function sponsorships()
    {
        return $this->hasMany(ParticipantSponsorship::class);
    }

    public function latestSponsorship()
    {
        return $this->hasOne(ParticipantSponsorship::class)->latestOfMany();
    }

    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id', 'center_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function treatments()
    {
        return $this->hasMany(ParticipantTreatment::class)->latest('treatment_date');
    }

    public function scopeForCenter($query, string|array|null $centerId)
    {
        if (is_array($centerId)) {
            return $query->whereIn('center_id', $centerId);
        }

        return $query->where('center_id', $centerId);
    }

    public function scopeVisibleToUser($query, User $user)
    {
        $query->forCenter($user->accessibleCenterIds());

        if ($user->role === User::ROLE_USER) {
            $query->where('created_by_user_id', $user->id);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPhotoDueForUpdate(): bool
    {
        return !empty($this->next_photo_update_due_at)
            && Carbon::today()->greaterThanOrEqualTo($this->next_photo_update_due_at);
    }

    public function hasPhoto(): bool
    {
        return !empty($this->photo);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? route('media.public', ['path' => $this->photo]) : null;
    }

    public function getPhotoStatusAttribute(): string
    {
        if (!$this->photo) {
            return 'No Photo';
        }

        if ($this->isPhotoDueForUpdate()) {
            return 'Due for Update';
        }

        return 'Updated';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->preferred_name ?: $this->account_name;
    }

    public function getProjectNameAttribute(): ?string
    {
        return $this->account_name;
    }

    public function getMapQueryAttribute(): ?string
    {
        $gpsLocation = trim((string) ($this->gps_location ?? ''));

        if ($gpsLocation !== '') {
            return $gpsLocation;
        }

        $addressParts = [
            trim((string) ($this->physical_address ?? $this->address ?? '')),
            trim((string) ($this->house_number ?? '')),
            'Tanzania',
        ];

        $query = collect($addressParts)
            ->filter(fn ($value) => $value !== '')
            ->implode(', ');

        return $query !== '' ? $query : null;
    }

    public function getMapEmbedUrlAttribute(): ?string
    {
        if (!$this->map_query) {
            return null;
        }

        return 'https://www.google.com/maps?q=' . urlencode($this->map_query) . '&t=k&z=18&output=embed';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;
    }

    public function getCurrentFundingTypeAttribute(): ?string
    {
        return $this->latestSponsorship?->funding_type ?? $this->getAttribute('funding_type');
    }

    public function getCurrentSponsorshipStatusAttribute(): ?string
    {
        return $this->latestSponsorship?->sponsorship_status ?? $this->getAttribute('sponsorship_status');
    }

    public function getCurrentSponsoredByAttribute(): ?string
    {
        return $this->latestSponsorship?->sponsor_name
            ?? $this->latestSponsorship?->sponsored_by
            ?? $this->getAttribute('sponsored_by');
    }

    public function getCurrentSponsorshipStartDateAttribute(): ?string
    {
        $date = $this->latestSponsorship?->sponsorship_start_date ?? $this->getAttribute('sponsorship_start_date');

        return $date ? Carbon::parse($date)->toDateString() : null;
    }

    public function getCurrentSponsorshipCategoryAttribute(): ?string
    {
        return $this->latestSponsorship?->sponsorship_category ?? $this->getAttribute('sponsorship_category');
    }
}
