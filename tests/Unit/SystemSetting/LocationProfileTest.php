<?php

namespace Tests\Unit\SystemSetting;

use App\Services\SystemSetting\SystemSettingService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocationProfileTest extends TestCase
{
    #[DataProvider('addresses')]
    public function test_it_reads_province_from_the_barangay_address(
        string $address,
        string $city,
        string $barangay,
        string $expected,
    ): void {
        $this->assertSame(
            $expected,
            SystemSettingService::provinceFromAddress($address, $city, $barangay),
        );
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function addresses(): array
    {
        return [
            'seed address' => [
                'Barangay Happy Hallow, Baguio City, Benguet, ',
                'Baguio City',
                'Barangay Happy Hallow',
                'Benguet',
            ],
            'no trailing comma' => [
                'Barangay Happy Hallow, Baguio City, Benguet',
                'Baguio City',
                'Barangay Happy Hallow',
                'Benguet',
            ],
            'empty address' => ['', 'Baguio City', 'Barangay Happy Hallow', ''],
        ];
    }
}
