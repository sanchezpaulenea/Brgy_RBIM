<?php

namespace App\Models\ResidentManagement\Demographic;

use App\Models\Geographic\Barangay;
use App\Models\Geographic\Country;
use App\Models\Geographic\MunicipalityCity;
use App\Models\Geographic\Province;
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class Resident extends Model
{
    protected $table = 'resident';

    protected $primaryKey = 'resident_id';

    public $timestamps = false;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'suffix_id',
        'relationship_to_hh_id',
        'sex_id',
        'date_of_birth',
        'birth_city_municipality_id',
        'birth_province_id',
        'birth_country_id',
        'birth_barangay_id',
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

    private ?string $ageMemoKey = null;

    private bool $ageMemoReady = false;

    private ?int $ageMemo = null;

    private ?string $ageInMonthsMemoKey = null;

    private bool $ageInMonthsMemoReady = false;

    private ?int $ageInMonthsMemo = null;

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
     * @return BelongsTo<Province, $this>
     */
    public function birthProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'birth_province_id', 'province_id');
    }

    /**
     * @return BelongsTo<MunicipalityCity, $this>
     */
    public function birthMunicipality(): BelongsTo
    {
        return $this->belongsTo(MunicipalityCity::class, 'birth_city_municipality_id', 'municipality_city_id');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function birthCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'birth_country_id', 'country_id');
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function birthBarangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'birth_barangay_id', 'barangay_id');
    }

    public static function birthBarangayColumnExists(): bool
    {
        static $exists = null;

        if ($exists === null) {
            $exists = Schema::hasColumn('resident', 'birth_barangay_id');
        }

        return $exists;
    }

    /**
     * The most specific selected place determines the parent ids.
     *
     * @param  array<string, mixed>  $input
     * @return array{birth_barangay_id: ?int, birth_city_municipality_id: ?int, birth_province_id: ?int, birth_country_id: ?int}
     */
    public static function birthPlaceIds(array $input): array
    {
        $barangayId = self::positiveId($input['birth_barangay_id'] ?? null);
        $municipalityId = self::positiveId($input['birth_city_municipality_id'] ?? null);
        $provinceId = self::positiveId($input['birth_province_id'] ?? null);

        if ($barangayId !== null) {
            $barangay = Barangay::query()->with('municipalityCity.province')->find($barangayId);

            if ($barangay === null) {
                throw ValidationException::withMessages([
                    'birth_barangay_id' => ['The selected birth barangay does not exist.'],
                ]);
            }

            return [
                'birth_barangay_id' => (int) $barangay->barangay_id,
                'birth_city_municipality_id' => (int) $barangay->municipality_city_id,
                'birth_province_id' => $barangay->municipalityCity?->province_id,
                'birth_country_id' => $barangay->municipalityCity?->province?->country_id,
            ];
        }

        if ($municipalityId !== null) {
            $municipality = MunicipalityCity::query()->with('province')->find($municipalityId);

            if ($municipality === null) {
                throw ValidationException::withMessages([
                    'birth_city_municipality_id' => ['The selected birth city or municipality does not exist.'],
                ]);
            }

            return [
                'birth_barangay_id' => null,
                'birth_city_municipality_id' => (int) $municipality->municipality_city_id,
                'birth_province_id' => (int) $municipality->province_id,
                'birth_country_id' => $municipality->province?->country_id,
            ];
        }

        if ($provinceId !== null) {
            $province = Province::query()->find($provinceId);

            if ($province === null) {
                throw ValidationException::withMessages([
                    'birth_province_id' => ['The selected birth province does not exist.'],
                ]);
            }

            return [
                'birth_barangay_id' => null,
                'birth_city_municipality_id' => null,
                'birth_province_id' => (int) $province->province_id,
                'birth_country_id' => (int) $province->country_id,
            ];
        }

        return [
            'birth_barangay_id' => null,
            'birth_city_municipality_id' => null,
            'birth_province_id' => null,
            'birth_country_id' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function birthPlace(): array
    {
        $relations = ['birthProvince', 'birthMunicipality', 'birthCountry'];

        if (self::birthBarangayColumnExists()) {
            $relations[] = 'birthBarangay';
        }

        $this->loadMissing($relations);

        return [
            'birth_barangay_id' => self::birthBarangayColumnExists() ? $this->getAttribute('birth_barangay_id') : null,
            'birth_barangay' => self::birthBarangayColumnExists() ? $this->birthBarangay?->barangay_name : null,
            'birth_city_municipality_id' => $this->birth_city_municipality_id,
            'birth_city_municipality' => $this->birthMunicipality?->municipality_city_name,
            'birth_province_id' => $this->birth_province_id,
            'birth_province' => $this->birthProvince?->province_name,
            'birth_country_id' => $this->birth_country_id,
            'birth_country' => $this->birthCountry?->country_name,
        ];
    }

    private static function positiveId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    /**
     * @return BelongsTo<Suffix, $this>
     */
    public function nameSuffix(): BelongsTo
    {
        return $this->belongsTo(Suffix::class, 'suffix_id', 'suffix_id');
    }

    /**
     * Printed suffix. "Not Applicable" is blank, matching the personnel name.
     */
    public function getSuffixAttribute(): string
    {
        $this->loadMissing('nameSuffix');

        return $this->nameSuffix?->printed() ?? '';
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

    /**
     * Raw stored date of birth. The memo invalidates when this value changes.
     */
    private function dateOfBirthMemoKey(): string
    {
        if (! array_key_exists('date_of_birth', $this->attributes) || $this->attributes['date_of_birth'] === null) {
            return '';
        }

        $raw = $this->attributes['date_of_birth'];

        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('Y-m-d');
        }

        return (string) $raw;
    }

    public function age(): ?int
    {
        $key = $this->dateOfBirthMemoKey();

        if ($this->ageMemoReady && $this->ageMemoKey === $key) {
            return $this->ageMemo;
        }

        $age = $this->date_of_birth === null ? null : $this->date_of_birth->age;

        $this->ageMemoKey = $key;
        $this->ageMemo = $age;
        $this->ageMemoReady = true;

        return $age;
    }

    public function ageInMonths(): ?int
    {
        $key = $this->dateOfBirthMemoKey();

        if ($this->ageInMonthsMemoReady && $this->ageInMonthsMemoKey === $key) {
            return $this->ageInMonthsMemo;
        }

        $months = $this->date_of_birth === null
            ? null
            : (int) $this->date_of_birth->diffInMonths(now());

        $this->ageInMonthsMemoKey = $key;
        $this->ageInMonthsMemo = $months;
        $this->ageInMonthsMemoReady = true;

        return $months;
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
     * @param  array<string, bool>|null  $applicableSections  Reuse a result already computed by applicableSections().
     * @return array{
     *     completed_count: int,
     *     applicable_count: int,
     *     is_complete: bool,
     *     summary: string,
     *     completed: list<array{key: string, label: string}>,
     *     missing: list<array{key: string, label: string}>
     * }
     */
    public function profilingCompleteness(?array $applicableSections = null): array
    {
        $completed = [];
        $missing = [];

        foreach ($applicableSections ?? $this->applicableSections() as $key => $isApplicable) {
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

    /**
     * @return HasMany<SkillsDevelopment, $this>
     */
    public function skillsDevelopments(): HasMany
    {
        return $this->hasMany(SkillsDevelopment::class, 'resident_id', 'resident_id');
    }
}
