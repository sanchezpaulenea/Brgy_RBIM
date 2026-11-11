<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Suffix extends Model
{
    protected $table = 'suffix';

    protected $primaryKey = 'suffix_id';

    public $timestamps = false;

    protected $fillable = [
        'suffix',
    ];

    public function getRouteKeyName(): string
    {
        return 'suffix_id';
    }

    public function printed(): string
    {
        return self::printedLabel((string) $this->suffix);
    }

    public static function printedLabel(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '' || str_starts_with(mb_strtolower($trimmed), 'not applic')) {
            return '';
        }

        return $trimmed;
    }

    public static function choiceLabel(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '' || str_starts_with(mb_strtolower($trimmed), 'not applic')) {
            return 'Not Applicable';
        }

        return $trimmed;
    }

    /**
     * Blank and "Not Applicable" both store the seeded not-applicable row.
     * suffix_id is NOT NULL and has no default.
     */
    public static function idMatchingLabel(?string $label, string $errorKey = 'suffix'): int
    {
        $trimmed = trim((string) $label);
        $query = static::query();

        if ($trimmed === '' || str_starts_with(mb_strtolower($trimmed), 'not applic')) {
            $id = $query
                ->whereRaw('LOWER(suffix) LIKE ?', ['not applic%'])
                ->orderBy('suffix_id')
                ->value('suffix_id');
        } else {
            $id = $query
                ->whereRaw('LOWER(suffix) = ?', [mb_strtolower($trimmed)])
                ->value('suffix_id');
        }

        if ($id === null) {
            throw ValidationException::withMessages([
                $errorKey => [$trimmed === ''
                    ? 'The Not Applicable suffix lookup is missing.'
                    : 'Select a suffix from the list, or leave it blank.'],
            ]);
        }

        return (int) $id;
    }

    public static function idForIdentityMatch(?string $label): ?int
    {
        try {
            return static::idMatchingLabel($label);
        } catch (ValidationException) {
            return null;
        }
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'suffix_id', 'suffix_id');
    }
}
