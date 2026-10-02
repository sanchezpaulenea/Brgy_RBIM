<?php

namespace App\Models\ResidentManagement\Demographic;

use App\Models\HouseholdManagement\Clan;
use App\Models\HouseholdManagement\Household;
use App\Models\ResidentManagement\Ctc\Ctc;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\ResidentManagement\Education\Education;
use App\Models\ResidentManagement\Health\Health;
use App\Models\ResidentManagement\Health\InfantHealth;
use App\Models\ResidentManagement\Health\WomenHealth;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\ResidentManagement\Sociocivic\Sociocivic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Resident extends Model
{
    protected $table = 'resident';

    protected $primaryKey = 'resident_id';

    public $timestamps = false;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'relationship_to_hh_id',
        'sex_id',
        'date_of_birth',
        'birth_city_municipality',
        'birth_province',
        'birth_country',
        'nationality_id',
        'religion_id',
        'ethnicity_id',
        'marital_status_id',
        'clan_id',
        'resident_status_id',
        'household_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'resident_id';
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    /**
     * @return BelongsTo<Clan, $this>
     */
    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class, 'clan_id', 'clan_id');
    }

    /**
     * @return BelongsTo<RelationshipToHouseholdHead, $this>
     */
    public function relationshipToHouseholdHead(): BelongsTo
    {
        return $this->belongsTo(
            RelationshipToHouseholdHead::class,
            'relationship_to_hh_id',
            'relationship_to_hh_id'
        );
    }

    /**
     * @return BelongsTo<Sex, $this>
     */
    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class, 'sex_id', 'sex_id');
    }

    /**
     * @return BelongsTo<Nationality, $this>
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id', 'nationality_id');
    }

    /**
     * @return BelongsTo<Religion, $this>
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'religion_id');
    }

    /**
     * @return BelongsTo<Ethnicity, $this>
     */
    public function ethnicity(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id', 'ethnicity_id');
    }

    /**
     * @return BelongsTo<MaritalStatus, $this>
     */
    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'marital_status_id');
    }

    /**
     * @return BelongsTo<ResidentStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ResidentStatus::class, 'resident_status_id', 'resident_status_id');
    }

    public function age(): ?int
    {
        if ($this->date_of_birth === null) {
            return null;
        }

        return $this->date_of_birth->age;
    }

    public function ageInMonths(): ?int
    {
        if ($this->date_of_birth === null) {
            return null;
        }

        return (int) $this->date_of_birth->diffInMonths(now());
    }

    public function isInfant(): bool
    {
        $months = $this->ageInMonths();

        return $months !== null && $months >= 0 && $months <= 11;
    }

    public function isFemale(): bool
    {
        return (int) $this->sex_id === Sex::FEMALE;
    }

    public function canHaveInfantHealth(): bool
    {
        return $this->isInfant();
    }

    public function canHaveWomenHealth(): bool
    {
        $age = $this->age();

        return $this->isFemale() && $age !== null && $age >= 10 && $age <= 54;
    }

    public function canHaveCtc(): bool
    {
        $age = $this->age();

        return $age !== null && $age >= 18;
    }

    public function canHaveSkills(): bool
    {
        $age = $this->age();

        return $age !== null && $age >= 15;
    }

    public function canHaveEducation(): bool
    {
        $age = $this->age();

        return $age !== null && $age >= 3;
    }

    public function canHaveEconomic(): bool
    {
        $age = $this->age();

        return $age !== null && $age >= 15;
    }

    public function canHaveSociocivic(): bool
    {
        $age = $this->age();

        return $age !== null && $age >= 10;
    }

    /**
     * Education fields that apply at the resident's current age.
     *
     * @return array{highest_level: bool, enrollment: bool}
     */
    public function educationFieldRelevance(): array
    {
        $age = $this->age();

        return [
            'highest_level' => $age !== null && $age >= 5,
            'enrollment' => $age !== null && $age >= 3 && $age <= 24,
        ];
    }

    /**
     * Sociocivic fields that apply at the resident's current age.
     *
     * @return array{solo_parent: bool, senior_citizen: bool, barangay_voter: bool}
     */
    public function sociocivicFieldRelevance(): array
    {
        $age = $this->age();

        return [
            'solo_parent' => $age !== null && $age >= 10,
            'senior_citizen' => $age !== null && $age >= 60,
            'barangay_voter' => $age !== null && $age >= 15,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function applicableSections(): array
    {
        return [
            'education' => $this->canHaveEducation(),
            'economic' => $this->canHaveEconomic(),
            'infant_health' => $this->canHaveInfantHealth(),
            'health' => true,
            'women_health' => $this->canHaveWomenHealth(),
            'sociocivic' => $this->canHaveSociocivic(),
            'migration' => true,
            'ctc' => $this->canHaveCtc(),
            'skills' => $this->canHaveSkills(),
        ];
    }

    /**
     * Profiling completeness for age/sex-applicable sub-records only.
     * Infant health and women's health are omitted when the resident is outside those gates.
     *
     * @return array{
     *     completed_count: int,
     *     applicable_count: int,
     *     is_complete: bool,
     *     summary: string,
     *     completed: list<array{key: string, label: string}>,
     *     missing: list<array{key: string, label: string}>
     * }
     */
    public function profilingCompleteness(): array
    {
        $completed = [];
        $missing = [];

        foreach ($this->applicableSections() as $key => $isApplicable) {
            if (! $isApplicable) {
                continue;
            }

            $entry = [
                'key' => $key,
                'label' => self::sectionLabel($key),
            ];

            if ($this->hasSectionRecord($key)) {
                $completed[] = $entry;
            } else {
                $missing[] = $entry;
            }
        }

        $applicableCount = count($completed) + count($missing);
        $completedCount = count($completed);

        return [
            'completed_count' => $completedCount,
            'applicable_count' => $applicableCount,
            'is_complete' => $applicableCount > 0 && $completedCount === $applicableCount,
            'summary' => $applicableCount === 0
                ? 'No applicable sections'
                : $completedCount.' of '.$applicableCount.' applicable sections completed',
            'completed' => $completed,
            'missing' => $missing,
        ];
    }

    public static function sectionLabel(string $key): string
    {
        return match ($key) {
            'education' => 'Education',
            'economic' => 'Economic',
            'infant_health' => 'Infant health',
            'health' => 'Health',
            'women_health' => 'Women\'s health',
            'sociocivic' => 'Sociocivic',
            'migration' => 'Migration',
            'ctc' => 'CTC',
            'skills' => 'Skills',
            default => $key,
        };
    }

    public function hasSectionRecord(string $section): bool
    {
        return match ($section) {
            'education' => $this->presenceFromExistsOrRelation('education_exists', 'education'),
            'economic' => $this->presenceFromExistsOrRelation('economic_exists', 'economic'),
            'infant_health' => $this->presenceFromExistsOrRelation('infant_health_exists', 'infantHealth'),
            'health' => $this->presenceFromExistsOrRelation('health_exists', 'health'),
            'women_health' => $this->womenHealthPresent(),
            'sociocivic' => $this->presenceFromExistsOrRelation('sociocivic_exists', 'sociocivic'),
            'migration' => $this->presenceFromExistsOrRelation('migration_exists', 'migration'),
            'ctc' => $this->presenceFromExistsOrRelation('community_tax_cert_exists', 'communityTaxCert'),
            'skills' => $this->presenceFromExistsOrRelation('skills_development_exists', 'skillsDevelopment'),
            default => false,
        };
    }

    private function presenceFromExistsOrRelation(string $existsAttribute, string $relation): bool
    {
        if (array_key_exists($existsAttribute, $this->attributes)) {
            return (bool) $this->getAttribute($existsAttribute);
        }

        if ($this->relationLoaded($relation)) {
            return $this->getRelation($relation) !== null;
        }

        return $this->{$relation}()->exists();
    }

    private function womenHealthPresent(): bool
    {
        if (array_key_exists('women_health_exists', $this->attributes)) {
            return (bool) $this->getAttribute('women_health_exists');
        }

        if ($this->relationLoaded('health')) {
            $health = $this->getRelation('health');

            if ($health === null) {
                return false;
            }

            if ($health->relationLoaded('womenHealth')) {
                return $health->getRelation('womenHealth') !== null;
            }
        }

        if ($this->relationLoaded('womenHealth')) {
            return $this->getRelation('womenHealth') !== null;
        }

        return $this->womenHealth()->exists();
    }

    /**
     * @return HasOne<Education, $this>
     */
    public function education(): HasOne
    {
        return $this->hasOne(Education::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOne<Economic, $this>
     */
    public function economic(): HasOne
    {
        return $this->hasOne(Economic::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOne<InfantHealth, $this>
     */
    public function infantHealth(): HasOne
    {
        return $this->hasOne(InfantHealth::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOne<Health, $this>
     */
    public function health(): HasOne
    {
        return $this->hasOne(Health::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOneThrough<WomenHealth, Health, $this>
     */
    public function womenHealth(): HasOneThrough
    {
        return $this->hasOneThrough(
            WomenHealth::class,
            Health::class,
            'resident_id',
            'health_id',
            'resident_id',
            'health_id'
        );
    }

    /**
     * @return HasOne<Sociocivic, $this>
     */
    public function sociocivic(): HasOne
    {
        return $this->hasOne(Sociocivic::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOne<Migration, $this>
     */
    public function migration(): HasOne
    {
        return $this->hasOne(Migration::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOne<Ctc, $this>
     */
    public function communityTaxCert(): HasOne
    {
        return $this->hasOne(Ctc::class, 'resident_id', 'resident_id');
    }

    /**
     * @return HasOne<SkillsDevelopment, $this>
     */
    public function skillsDevelopment(): HasOne
    {
        return $this->hasOne(SkillsDevelopment::class, 'resident_id', 'resident_id');
    }
}
