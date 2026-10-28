<?php

namespace App\Services\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\Logs\Action;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use App\Rules\ValidPersonnelName;
use Illuminate\Support\Str;

class PunongBarangayName
{
    public const POSITION = 'Punong Barangay';

    public const SETTING_KEY = 'punong_barangay_name';

    private const SETTING_MAX_LENGTH = 45;

    private const NAME_MAX_LENGTH = 45;

    private const SUFFIX_MAX_LENGTH = 10;

    /**
     * @var list<string>
     */
    private const SUFFIX_KEYS = ['jr', 'sr', 'ii', 'iii', 'iv', 'v'];

    /**
     * @var array<string, string>
     */
    private const ROMAN_SUFFIXES = [
        'ii' => 'II',
        'iii' => 'III',
        'iv' => 'IV',
        'v' => 'V',
    ];

    private bool $aligning = false;

    public function __construct(
        protected BarangayPersonnelRepositoryInterface $personnelRepository,
        protected SettingRepositoryInterface $settingRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function isPunong(BarangayPersonnel $personnel): bool
    {
        $personnel->loadMissing('position');

        $position = mb_strtolower(PersonnelPosition::standardizeName(
            (string) ($personnel->position?->position_name ?? '')
        ));

        return $position === mb_strtolower(PersonnelPosition::standardizeName(self::POSITION));
    }

    /**
     * Name printed on certificates and stored in barangay information.
     * The active Punong Barangay personnel record is used when one exists.
     */
    public function displayName(): string
    {
        $holder = $this->activeHolder();

        if ($holder instanceof BarangayPersonnel) {
            $printed = $this->printName($holder);

            if ($printed !== '') {
                return $printed;
            }
        }

        return $this->storedSettingName();
    }

    public function alignSettingWithPersonnel(User $performedBy): void
    {
        if ($this->aligning) {
            return;
        }

        $holder = $this->activeHolder();

        if (! $holder instanceof BarangayPersonnel) {
            return;
        }

        $printed = $this->printName($holder);

        if ($printed === '' || mb_strlen($printed) > self::SETTING_MAX_LENGTH) {
            return;
        }

        $setting = $this->settingRepository->findByKey(self::SETTING_KEY);

        if (! $setting instanceof Setting) {
            return;
        }

        $current = trim((string) $setting->setting_value);

        if ($current === $printed) {
            return;
        }

        $this->aligning = true;

        try {
            $updated = $this->settingRepository->update($setting, $printed);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: (int) $updated->setting_id,
                description: 'Update system setting',
                oldValue: $current,
                newValue: $printed,
                target: self::SETTING_KEY,
                entity: 'system_setting',
            );
        } finally {
            $this->aligning = false;
        }
    }

    public function alignPersonnelWithSetting(User $performedBy, string $settingValue): void
    {
        if ($this->aligning) {
            return;
        }

        $holder = $this->activeHolder();

        if (! $holder instanceof BarangayPersonnel) {
            return;
        }

        $parts = $this->nameParts($settingValue);

        if ($parts === null || $this->sameName($holder, $parts)) {
            return;
        }

        $previous = $this->rosterName($holder);
        $this->aligning = true;

        try {
            $updated = $this->personnelRepository->update($holder, $parts);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: (int) $updated->personnel_id,
                description: 'Updated barangay personnel name',
                oldValue: $previous,
                newValue: $this->rosterName($updated),
                target: 'name',
                entity: 'barangay_personnel',
            );
        } finally {
            $this->aligning = false;
        }
    }

    public function printName(BarangayPersonnel $personnel): string
    {
        $parts = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_last_name,
        ])->map(fn (mixed $part): string => $this->titlePart((string) $part))
            ->filter()
            ->values();

        $suffix = $this->displaySuffix($personnel->personnel_suffix);

        if ($suffix !== '') {
            $parts->push($suffix);
        }

        return $parts->implode(' ');
    }

    /**
     * @return array{
     *     personnel_first_name: string,
     *     personnel_middle_name: ?string,
     *     personnel_last_name: string,
     *     personnel_suffix: ?string
     * }|null
     */
    public function nameParts(string $name): ?array
    {
        $squished = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        if ($squished === '') {
            return null;
        }

        $tokens = explode(' ', $squished);
        $suffix = null;

        if (count($tokens) >= 3) {
            $suffix = $this->suffixToken((string) $tokens[array_key_last($tokens)]);

            if ($suffix !== null) {
                array_pop($tokens);
            }
        }

        $tokens = array_values(array_filter(
            array_map(fn (string $token): string => $this->titlePart($token), $tokens),
            fn (string $token): bool => $token !== '',
        ));

        if (count($tokens) < 2) {
            return null;
        }

        $first = array_shift($tokens);
        $last = array_pop($tokens);
        $middle = $tokens === [] ? null : implode(' ', $tokens);

        if ($first === null || $last === null) {
            return null;
        }

        if (
            mb_strlen($first) > self::NAME_MAX_LENGTH
            || mb_strlen($last) > self::NAME_MAX_LENGTH
            || ($middle !== null && mb_strlen($middle) > self::NAME_MAX_LENGTH)
            || ($suffix !== null && mb_strlen($suffix) > self::SUFFIX_MAX_LENGTH)
        ) {
            return null;
        }

        foreach ([$first, $middle, $last, $suffix] as $part) {
            if ($part === null || $part === '') {
                continue;
            }

            if (preg_match(ValidPersonnelName::PATTERN, $part) !== 1 || preg_match('/\p{L}/u', $part) !== 1) {
                return null;
            }
        }

        return [
            'personnel_first_name' => $first,
            'personnel_middle_name' => $middle,
            'personnel_last_name' => $last,
            'personnel_suffix' => $suffix,
        ];
    }

    private function activeHolder(): ?BarangayPersonnel
    {
        return $this->personnelRepository
            ->findActiveByPositionNames([self::POSITION])
            ->first();
    }

    private function storedSettingName(): string
    {
        return trim((string) ($this->settingRepository->findByKey(self::SETTING_KEY)?->setting_value ?? ''));
    }

    /**
     * @param  array{
     *     personnel_first_name: string,
     *     personnel_middle_name: ?string,
     *     personnel_last_name: string,
     *     personnel_suffix: ?string
     * }  $parts
     */
    private function sameName(BarangayPersonnel $personnel, array $parts): bool
    {
        $current = [
            'personnel_first_name' => $personnel->personnel_first_name,
            'personnel_middle_name' => $personnel->personnel_middle_name,
            'personnel_last_name' => $personnel->personnel_last_name,
            'personnel_suffix' => $personnel->personnel_suffix,
        ];

        foreach ($parts as $key => $value) {
            $left = mb_strtolower(trim((string) ($current[$key] ?? '')));
            $right = mb_strtolower(trim((string) ($value ?? '')));

            if ($left !== $right) {
                return false;
            }
        }

        return true;
    }

    private function rosterName(BarangayPersonnel $personnel): string
    {
        $given = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_suffix,
        ])->map(fn (mixed $part): string => trim((string) $part))
            ->filter()
            ->implode(' ');

        $last = trim((string) $personnel->personnel_last_name);

        if ($given === '') {
            return $last;
        }

        return $last.', '.$given;
    }

    private function titlePart(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        return Str::of($trimmed)->title()->toString();
    }

    private function displaySuffix(mixed $suffix): string
    {
        $trimmed = trim((string) $suffix);

        if ($trimmed === '') {
            return '';
        }

        $key = mb_strtolower(rtrim($trimmed, '.'));

        return self::ROMAN_SUFFIXES[$key] ?? $this->titlePart($trimmed);
    }

    private function suffixToken(string $token): ?string
    {
        $key = mb_strtolower(rtrim(trim($token), '.'));

        if (! in_array($key, self::SUFFIX_KEYS, true)) {
            return null;
        }

        $suffix = $this->displaySuffix($token);

        return $suffix === '' ? null : $suffix;
    }
}
