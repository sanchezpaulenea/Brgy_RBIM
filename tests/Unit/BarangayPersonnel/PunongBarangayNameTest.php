<?php

namespace Tests\Unit\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use App\Services\BarangayPersonnel\PunongBarangayName;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class PunongBarangayNameTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_display_name_uses_the_active_punong_barangay(): void
    {
        $personnel = $this->punong('Soriano', 'Abante', 'Palunan');
        $names = $this->names(
            personnel: $this->personnelRepository($personnel),
            settings: Mockery::mock(SettingRepositoryInterface::class),
        );

        $this->assertSame('Soriano Abante Palunan', $names->displayName());
    }

    public function test_setting_is_updated_to_the_registered_punong_barangay_name(): void
    {
        $personnel = $this->punong('Soriano', 'Abante', 'Palunan');
        $setting = $this->setting('Soriano A. Palunan');
        $settings = Mockery::mock(SettingRepositoryInterface::class);
        $settings->shouldReceive('findByKey')->once()->with('punong_barangay_name')->andReturn($setting);
        $settings->shouldReceive('update')->once()->with($setting, 'Soriano Abante Palunan')->andReturn($setting);
        $audit = Mockery::mock(AuditLogRepositoryInterface::class);
        $audit->shouldReceive('log')->once()->withArgs(function (
            int $userId,
            int $actionId,
            int $recordId,
            string $description,
            ?string $oldValue,
            string $newValue,
        ): bool {
            return $oldValue === 'Soriano A. Palunan' && $newValue === 'Soriano Abante Palunan';
        });

        $names = $this->names($this->personnelRepository($personnel), $settings, $audit);
        $names->alignSettingWithPersonnel($this->user());

        $this->assertTrue(true);
    }

    public function test_personnel_name_is_updated_from_the_barangay_information_setting(): void
    {
        $personnel = $this->punong('Soriano', 'Abante', 'Palunan');
        $personnelRepository = $this->personnelRepository($personnel);
        $personnelRepository->shouldReceive('update')->once()->withArgs(function (BarangayPersonnel $holder, array $parts) use ($personnel): bool {
            return $holder === $personnel
                && $parts === [
                    'personnel_first_name' => 'Soriano',
                    'personnel_middle_name' => 'A.',
                    'personnel_last_name' => 'Palunan',
                    'personnel_suffix' => null,
                ];
        })->andReturnUsing(function (BarangayPersonnel $holder, array $parts): BarangayPersonnel {
            $holder->fill($parts);

            return $holder;
        });
        $audit = Mockery::mock(AuditLogRepositoryInterface::class);
        $audit->shouldReceive('log')->once()->withArgs(function (
            int $userId,
            int $actionId,
            int $recordId,
            string $description,
            ?string $oldValue,
            string $newValue,
        ): bool {
            return $oldValue === 'Palunan, Soriano Abante'
                && $newValue === 'Palunan, Soriano A.';
        });

        $names = $this->names($personnelRepository, Mockery::mock(SettingRepositoryInterface::class), $audit);
        $names->alignPersonnelWithSetting($this->user(), 'Soriano A. Palunan');

        $this->assertTrue(true);
    }

    public function test_a_matching_name_is_left_unchanged(): void
    {
        $personnel = $this->punong('Soriano', 'A.', 'Palunan');
        $setting = $this->setting('Soriano A. Palunan');
        $settings = Mockery::mock(SettingRepositoryInterface::class);
        $settings->shouldReceive('findByKey')->once()->andReturn($setting);
        $settings->shouldReceive('update')->never();
        $personnelRepository = $this->personnelRepository($personnel);
        $personnelRepository->shouldReceive('update')->never();

        $names = $this->names($personnelRepository, $settings);
        $names->alignSettingWithPersonnel($this->user());
        $names->alignPersonnelWithSetting($this->user(), 'Soriano A. Palunan');

        $this->assertSame('Soriano A. Palunan', $names->displayName());
    }

    public function test_roman_numeral_suffixes_stay_uppercase(): void
    {
        $names = $this->names(
            Mockery::mock(BarangayPersonnelRepositoryInterface::class),
            Mockery::mock(SettingRepositoryInterface::class),
        );

        $this->assertSame([
            'personnel_first_name' => 'Soriano',
            'personnel_middle_name' => 'Abante',
            'personnel_last_name' => 'Palunan',
            'personnel_suffix' => 'III',
        ], $names->nameParts('Soriano Abante Palunan Iii'));
        $this->assertNull($names->nameParts('Palunan'));
    }

    public function test_a_name_longer_than_the_setting_is_not_written(): void
    {
        $personnel = $this->punong(str_repeat('A', 23), null, str_repeat('B', 23));
        $settings = Mockery::mock(SettingRepositoryInterface::class);
        $settings->shouldReceive('findByKey')->never();
        $settings->shouldReceive('update')->never();

        $names = $this->names($this->personnelRepository($personnel), $settings);
        $names->alignSettingWithPersonnel($this->user());

        $this->assertGreaterThan(45, mb_strlen($names->printName($personnel)));
    }

    private function names(
        BarangayPersonnelRepositoryInterface $personnel,
        SettingRepositoryInterface $settings,
        ?AuditLogRepositoryInterface $audit = null,
    ): PunongBarangayName {
        return new PunongBarangayName(
            $personnel,
            $settings,
            $audit ?? Mockery::mock(AuditLogRepositoryInterface::class),
        );
    }

    private function personnelRepository(?BarangayPersonnel $personnel): BarangayPersonnelRepositoryInterface
    {
        $repository = Mockery::mock(BarangayPersonnelRepositoryInterface::class);
        $repository->shouldReceive('findActiveByPositionNames')
            ->andReturn(new Collection($personnel === null ? [] : [$personnel]));

        return $repository;
    }

    private function punong(string $first, ?string $middle, string $last, ?string $suffix = null): BarangayPersonnel
    {
        $personnel = new BarangayPersonnel([
            'personnel_first_name' => $first,
            'personnel_middle_name' => $middle,
            'personnel_last_name' => $last,
            'personnel_suffix' => $suffix,
        ]);
        $personnel->personnel_id = 4;
        $personnel->setRelation('position', new PersonnelPosition([
            'position_name' => 'Punong Barangay',
        ]));

        return $personnel;
    }

    private function setting(string $value): Setting
    {
        $setting = new Setting([
            'setting_key' => 'punong_barangay_name',
            'setting_value' => $value,
        ]);
        $setting->setting_id = 13;

        return $setting;
    }

    private function user(): User
    {
        $user = new User;
        $user->user_id = 3;

        return $user;
    }
}
