<?php

namespace App\Services\Lookups;

use App\Enums\LookupType;
use App\Models\Geographic\Barangay;
use App\Models\Geographic\Country;
use App\Models\Geographic\MunicipalityCity;
use App\Models\Geographic\Province;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Lookups\LookupRepositoryInterface;
use App\Support\IdenticalWriteLock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LookupService
{
    public function __construct(
        protected LookupRepositoryInterface $lookupRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(LookupType $type): array
    {
        if (! $type->isReadOnlyReference()) {
            throw new NotFoundHttpException("Lookup type [{$type->value}] is not supported.");
        }

        return $this->lookupRepository
            ->all($type)
            ->map(fn (Model $model) => $type->format($model))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function provinces(): array
    {
        return $this->lookupRepository
            ->provinces()
            ->map(fn (Province $province): array => $this->provinceItem($province))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function municipalities(int $provinceId): array
    {
        return $this->lookupRepository
            ->municipalitiesForProvince($provinceId)
            ->map(fn (MunicipalityCity $municipality): array => $this->municipalityItem($municipality))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchBarangays(string $term, ?int $municipalityId = null): array
    {
        $limit = $municipalityId !== null && $term === '' ? 500 : 20;

        return $this->lookupRepository
            ->searchBarangays($term, $municipalityId, $limit)
            ->map(fn (Barangay $barangay): array => $this->barangayItem($barangay))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createProvince(User $performedBy, array $data): array
    {
        $name = $this->placeName((string) $data['province_name']);

        return IdenticalWriteLock::acquire('province:'.mb_strtolower($name), function () use ($performedBy, $name) {
            return DB::transaction(function () use ($performedBy, $name) {
                $existing = Province::query()
                    ->with('country')
                    ->whereRaw('LOWER(province_name) = ?', [mb_strtolower($name)])
                    ->first();

                if ($existing !== null) {
                    return $this->provinceItem($existing);
                }

                try {
                    $province = Province::query()->create([
                        'country_id' => Country::philippinesId(),
                        'province_name' => $name,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $province = Province::query()
                        ->with('country')
                        ->whereRaw('LOWER(province_name) = ?', [mb_strtolower($name)])
                        ->firstOrFail();

                    return $this->provinceItem($province);
                }

                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: $province->province_id,
                    description: 'Create province',
                    oldValue: null,
                    newValue: $province->province_name,
                    target: 'province_name',
                    entity: 'province',
                );

                return $this->provinceItem($province->load('country'));
            });
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createMunicipality(User $performedBy, array $data): array
    {
        $name = $this->placeName((string) $data['municipality_city_name']);
        $provinceId = (int) $data['province_id'];

        return IdenticalWriteLock::acquire('municipality:'.$provinceId.':'.mb_strtolower($name), function () use ($performedBy, $name, $provinceId) {
            return DB::transaction(function () use ($performedBy, $name, $provinceId) {
                $existing = MunicipalityCity::query()
                    ->with('province.country')
                    ->where('province_id', $provinceId)
                    ->whereRaw('LOWER(municipality_city_name) = ?', [mb_strtolower($name)])
                    ->first();

                if ($existing !== null) {
                    return $this->municipalityItem($existing);
                }

                try {
                    $municipality = MunicipalityCity::query()->create([
                        'province_id' => $provinceId,
                        'municipality_city_name' => $name,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $municipality = MunicipalityCity::query()
                        ->with('province.country')
                        ->where('province_id', $provinceId)
                        ->whereRaw('LOWER(municipality_city_name) = ?', [mb_strtolower($name)])
                        ->firstOrFail();

                    return $this->municipalityItem($municipality);
                }

                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: $municipality->municipality_city_id,
                    description: 'Create city or municipality',
                    oldValue: null,
                    newValue: $municipality->municipality_city_name,
                    target: 'municipality_city_name',
                    entity: 'municipality_city',
                );

                return $this->municipalityItem($municipality->load('province.country'));
            });
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createBarangay(User $performedBy, array $data): array
    {
        $name = $this->placeName((string) $data['barangay_name']);
        $municipalityId = (int) $data['municipality_city_id'];

        return IdenticalWriteLock::acquire('barangay:'.$municipalityId.':'.mb_strtolower($name), function () use ($performedBy, $name, $municipalityId) {
            return DB::transaction(function () use ($performedBy, $name, $municipalityId) {
                $existing = Barangay::query()
                    ->with('municipalityCity.province')
                    ->where('municipality_city_id', $municipalityId)
                    ->whereRaw('LOWER(barangay_name) = ?', [mb_strtolower($name)])
                    ->first();

                if ($existing !== null) {
                    return $this->barangayItem($existing);
                }

                try {
                    $barangay = Barangay::query()->create([
                        'municipality_city_id' => $municipalityId,
                        'barangay_name' => $name,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $barangay = Barangay::query()
                        ->with('municipalityCity.province')
                        ->where('municipality_city_id', $municipalityId)
                        ->whereRaw('LOWER(barangay_name) = ?', [mb_strtolower($name)])
                        ->firstOrFail();

                    return $this->barangayItem($barangay);
                }

                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: $barangay->barangay_id,
                    description: 'Create barangay',
                    oldValue: null,
                    newValue: $barangay->barangay_name,
                    target: 'barangay_name',
                    entity: 'barangay',
                );

                return $this->barangayItem($barangay->load('municipalityCity.province'));
            });
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function provinceItem(Province $province): array
    {
        $province->loadMissing('country');

        return [
            'id' => $province->province_id,
            'label' => $province->province_name,
            'country_id' => $province->country_id,
            'country_name' => $province->country?->country_name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function municipalityItem(MunicipalityCity $municipality): array
    {
        $municipality->loadMissing('province.country');

        return [
            'id' => $municipality->municipality_city_id,
            'label' => $municipality->municipality_city_name,
            'province_id' => $municipality->province_id,
            'country_id' => $municipality->province?->country_id,
            'country_name' => $municipality->province?->country?->country_name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function barangayItem(Barangay $barangay): array
    {
        $barangay->loadMissing('municipalityCity.province');

        return [
            'id' => $barangay->barangay_id,
            'label' => $barangay->locationLabel(),
            'barangay_name' => $barangay->barangay_name,
            'municipality_city_id' => $barangay->municipality_city_id,
            'municipality_city_name' => $barangay->municipalityCity?->municipality_city_name,
            'province_id' => $barangay->municipalityCity?->province_id,
            'province_name' => $barangay->municipalityCity?->province?->province_name,
        ];
    }

    private function placeName(string $value): string
    {
        $name = Str::of($value)->squish()->title()->toString();

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['Enter a place name.'],
            ]);
        }

        return $name;
    }
}
