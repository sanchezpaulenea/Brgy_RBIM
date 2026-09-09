<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class HouseholdRepository implements HouseholdRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'clan',
            'street',
            'status',
            'head.sex',
            'head.nationality',
            'head.religion',
            'head.ethnicity',
            'head.maritalStatus',
            'head.migration.residentType',
            'head.status',
            'head.clan',
            'head.relationshipToHouseholdHead',
            'latestAssessment.censusStatus',
            'latestAssessment.encoder',
            'latestAssessment.interviewer',
            'latestAssessment.supervisor',
            'latestAssessment.previousAssessment.censusStatus',
        ];
    }

    /**
     * @return list<string>
     */
    private function listRelations(): array
    {
        return [
            'clan',
            'street',
            'status',
            'head',
        ];
    }

    /**
     * @return list<string>
     */
    private function residentRelations(): array
    {
        return [
            'sex',
            'nationality',
            'religion',
            'ethnicity',
            'maritalStatus',
            'migration.residentType',
            'status',
            'clan',
            'relationshipToHouseholdHead',
        ];
    }

    /**
     * @param  array{clan_id?: int, street_id?: int, household_status_id?: int}  $filters
     * @return Collection<int, Household>
     */
    public function list(array $filters = []): Collection
    {
        return Household::query()
            ->with($this->listRelations())
            ->when(
                ! empty($filters['clan_id']),
                fn ($query) => $query->where('clan_id', $filters['clan_id']),
            )
            ->when(
                ! empty($filters['street_id']),
                fn ($query) => $query->where('street_id', $filters['street_id']),
            )
            ->when(
                ! empty($filters['household_status_id']),
                fn ($query) => $query->where('household_status_id', $filters['household_status_id']),
            )
            ->orderByDesc('household_id')
            ->get();
    }

    public function findById(int $householdId, bool $withResidents = false): ?Household
    {
        $query = Household::query()
            ->with($this->defaultRelations())
            ->where('household_id', $householdId);

        if ($withResidents) {
            $query->with([
                'residents' => fn ($residents) => $residents
                    ->with($this->residentRelations())
                    ->orderBy('last_name')
                    ->orderBy('first_name'),
            ]);
        }

        return $query->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Household
    {
        return Household::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Household $household, array $attributes): Household
    {
        unset($attributes['head_resident_id'], $attributes['head']);

        $household->fill($attributes);
        $household->save();

        return $household->fresh($this->defaultRelations()) ?? $household;
    }

    public function updateHeadResident(Household $household, int $residentId): Household
    {
        $household->head_resident_id = $residentId;
        $household->save();

        return $household->fresh($this->defaultRelations()) ?? $household;
    }
}
