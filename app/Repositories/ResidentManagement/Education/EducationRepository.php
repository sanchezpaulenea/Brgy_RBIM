<?php

namespace App\Repositories\ResidentManagement\Education;

use App\Models\ResidentManagement\Education\Education;
use App\Repositories\Interfaces\ResidentManagement\Education\EducationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EducationRepository implements EducationRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'highestLvlOfEduc',
            'currentEnrollmentStatus',
            'schoolLvl',
        ];
    }

    /**
     * @return Collection<int, Education>
     */
    public function listByResident(int $residentId): Collection
    {
        return Education::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->orderBy('education_id')
            ->get();
    }

    public function findById(int $educationId): ?Education
    {
        return Education::query()
            ->with($this->defaultRelations())
            ->where('education_id', $educationId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?Education
    {
        return Education::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Education
    {
        if (empty($attributes['education_id'])) {
            $max = Education::query()->lockForUpdate()->max('education_id');
            $attributes['education_id'] = (int) $max + 1;
        }

        $education = Education::create($attributes);

        return $education->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Education $education, array $attributes): Education
    {
        $education->fill($attributes);
        $education->save();

        return $education->fresh($this->defaultRelations()) ?? $education;
    }
}
