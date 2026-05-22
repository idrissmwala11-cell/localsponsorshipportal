<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\File;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_OFFICIAL_ADMIN = 'official_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
    public const PROJECT_COMPASSION = 'compassion';
    public const PROJECT_ANGLICAN = 'anglican';
    public const PROJECT_MORAVIAN = 'moravian';
    public const PROJECT_BAPTIST = 'baptist';
    public const PROJECT_PAGT = 'pagt';
    public const PROJECT_FPCT = 'fpct';
    public const PROJECT_TAG = 'tag';
    public const PROJECT_TAGT = 'tagt';
    public const PROJECT_EAGT = 'eagt';
    public const PROJECT_KKKT = 'kkkt';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'job_title',
        'project_name',
        'email',
        'approved_at',
        'approved_by',
        'admin_onboarded_at',
        'password',
        'role',
        'center_id',
        'cluster_name',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'admin_onboarded_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function otpCodes()
    {
        return $this->hasMany(\App\Models\OtpCode::class);
    }

    public function center()
    {
        return $this->belongsTo(\App\Models\Center::class, 'center_id', 'center_id');
    }

    public function managedCenters()
    {
        return $this->belongsToMany(Center::class, 'center_user_assignments', 'user_id', 'center_id', 'id', 'center_id');
    }

    public function supervisedUsers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'admin_user_supervisions', 'admin_user_id', 'supervised_user_id')
            ->withTimestamps();
    }

    public function supervisingAdmins(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'admin_user_supervisions', 'supervised_user_id', 'admin_user_id')
            ->withTimestamps();
    }

    public function notificationReads()
    {
        return $this->hasMany(CenterNotificationRead::class);
    }

    public function managedClusterAssignments(): HasMany
    {
        return $this->hasMany(AdminClusterAssignment::class, 'admin_user_id');
    }

    public function scopeForCenter($query, string|array|null $centerId)
    {
        if (is_array($centerId)) {
            return $query->whereIn('center_id', $centerId);
        }

        return $query->where('center_id', $centerId);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_OFFICIAL_ADMIN, self::ROLE_ADMIN], true);
    }

    public function isOfficialAdmin(): bool
    {
        return $this->role === self::ROLE_OFFICIAL_ADMIN;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function needsAdminOnboarding(): bool
    {
        if ($this->isOfficialAdmin() || $this->role !== self::ROLE_ADMIN) {
            return false;
        }

        return Schema::hasColumn('users', 'admin_onboarded_at') && is_null($this->admin_onboarded_at);
    }

    public static function roles(): array
    {
        return [
            self::ROLE_OFFICIAL_ADMIN => 'Official Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_USER => 'User',
        ];
    }

    public static function projectOptions(): array
    {
        return [
            self::PROJECT_COMPASSION => 'Local Sponsorship Portal',
            self::PROJECT_ANGLICAN => 'Anglican',
            self::PROJECT_MORAVIAN => 'Moravian',
            self::PROJECT_BAPTIST => 'Baptist',
            self::PROJECT_PAGT => 'PAGT',
            self::PROJECT_FPCT => 'FPCT',
            self::PROJECT_TAG => 'TAG',
            self::PROJECT_TAGT => 'TAGT',
            self::PROJECT_EAGT => 'EAGT',
            self::PROJECT_KKKT => 'KKKT',
        ];
    }

    public static function normalizeProjectName(?string $projectName): string
    {
        $value = strtolower(trim((string) $projectName));

        if ($value === '') {
            return self::PROJECT_COMPASSION;
        }

        return preg_replace('/\s+/', ' ', $value) ?? self::PROJECT_COMPASSION;
    }

    public static function detectProjectKey(?string $projectName): string
    {
        $value = self::normalizeProjectName($projectName);

        return match (true) {
            str_contains($value, 'fpct') || str_contains($value, 'free pentecostal') => self::PROJECT_FPCT,
            str_contains($value, 'pagt') => self::PROJECT_PAGT,
            str_contains($value, 'tagt') => self::PROJECT_TAG,
            str_contains($value, 'tag') || str_contains($value, 'tanzania assemblies of god') => self::PROJECT_TAG,
            str_contains($value, 'eagt') || str_contains($value, 'assemblies of god') => self::PROJECT_EAGT,
            str_contains($value, 'kkkt') || str_contains($value, 'elct') || str_contains($value, 'evangelical lutheran') => self::PROJECT_KKKT,
            str_contains($value, 'anglican') => self::PROJECT_ANGLICAN,
            str_contains($value, 'baptist') || str_contains($value, 'babtist') => self::PROJECT_BAPTIST,
            str_contains($value, 'moravian') || str_contains($value, 'mwana kondoo') || str_contains($value, 'mwanakondoo') || str_contains($value, 'ameshinda tumfuate') => self::PROJECT_MORAVIAN,
            str_contains($value, 'compassion') || str_contains($value, 'local sponsorship') => self::PROJECT_COMPASSION,
            default => self::PROJECT_COMPASSION,
        };
    }

    public static function projectLogoFor(?string $projectName): string
    {
        return match (self::detectProjectKey($projectName)) {
            self::PROJECT_ANGLICAN => self::versionedAsset('images/project-anglican.jfif'),
            self::PROJECT_MORAVIAN => self::versionedAsset('images/project-moravian.jpeg'),
            self::PROJECT_BAPTIST => self::versionedAsset('images/project-baptist.jfif'),
            self::PROJECT_PAGT => self::versionedAsset('images/project-pagt.jfif'),
            self::PROJECT_FPCT => self::versionedAsset('images/project-fpct.jfif'),
            self::PROJECT_TAG => self::versionedAsset('images/project-tag.png'),
            self::PROJECT_EAGT => self::versionedAsset('images/project-eagt.png'),
            self::PROJECT_KKKT => self::versionedAsset('images/project-kkkt.png'),
            default => self::versionedAsset('images/compassion-mark.png'),
        };
    }

    protected static function versionedAsset(string $path): string
    {
        $absolutePath = public_path($path);
        $url = asset($path);

        if (!File::exists($absolutePath)) {
            return $url;
        }

        return $url . '?v=' . File::lastModified($absolutePath);
    }

    public static function rotatingPortalLogos(): array
    {
        return [
            asset('images/welcome-rotating-logo-1.jpeg'),
            asset('images/welcome-rotating-logo-2.jpeg'),
        ];
    }

    public function usesRotatingPortalLogos(): bool
    {
        return $this->isAdmin();
    }

    public function accessibleCenterIds(): array
    {
        if ($this->isOfficialAdmin()) {
            return Center::query()->pluck('center_id')->filter()->values()->all();
        }

        if ($this->role === self::ROLE_ADMIN) {
            $assigned = $this->managedCenters()->pluck('centers.center_id')->filter()->values()->all();

            if (!empty($assigned)) {
                return $assigned;
            }

            if (Schema::hasTable('admin_cluster_assignments') && Schema::hasColumn('users', 'cluster_name')) {
                $clusterNames = $this->managedClusterAssignments()->pluck('cluster_name')->filter()->values()->all();

                if (!empty($clusterNames)) {
                    return self::query()
                        ->where('role', self::ROLE_USER)
                        ->whereIn('cluster_name', $clusterNames)
                        ->pluck('center_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }
            }
        }

        return array_values(array_filter([$this->center_id]));
    }

    public function canAccessCenter(?string $centerId): bool
    {
        if ($this->isOfficialAdmin()) {
            return true;
        }

        return in_array($centerId, $this->accessibleCenterIds(), true);
    }

    public function getDisplayTitleAttribute(): string
    {
        if (Schema::hasColumn('users', 'job_title') && filled($this->job_title)) {
            return $this->job_title;
        }

        return self::roles()[$this->role ?? self::ROLE_USER] ?? 'User';
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $path = $this->profile_photo ?: $this->getDerivedProfilePhotoPath();

        return $path ? route('media.public', ['path' => $path]) : null;
    }

    public function getDerivedProfilePhotoPath(): ?string
    {
        $disk = Storage::disk('public');
        $prefix = 'profile-photos/user-' . $this->id . '.';

        foreach ($disk->files('profile-photos') as $file) {
            if (str_starts_with($file, $prefix)) {
                return $file;
            }
        }

        return null;
    }

    public function getProjectDisplayNameAttribute(): string
    {
        $rawName = trim((string) $this->project_name);
        $detectedKey = self::detectProjectKey($rawName);

        if ($rawName !== '' && self::normalizeProjectName($rawName) !== $detectedKey) {
            return $rawName;
        }

        return self::projectOptions()[$detectedKey] ?? 'Local Sponsorship Portal';
    }

    public function getProjectLogoPathAttribute(): string
    {
        if ($this->usesRotatingPortalLogos()) {
            return self::rotatingPortalLogos()[0];
        }

        return self::projectLogoFor($this->project_name);
    }

    public function getProjectLogoPathsAttribute(): array
    {
        if ($this->usesRotatingPortalLogos()) {
            return self::rotatingPortalLogos();
        }

        return [self::projectLogoFor($this->project_name)];
    }

    public function getPortalTitleAttribute(): string
    {
        return 'Local Sponsorship Portal';
    }

    public function getPortalSubtitleAttribute(): string
    {
        return "Releasing a participant from poverty in Jesus' name.";
    }

    public static function defaultPortalTitle(): string
    {
        return 'Local Sponsorship Portal';
    }

    public static function defaultPortalSubtitle(): string
    {
        return "Releasing a participant from poverty in Jesus' name.";
    }
}