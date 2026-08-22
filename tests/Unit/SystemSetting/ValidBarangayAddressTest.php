<?php

namespace Tests\Unit\SystemSetting;

use App\Rules\ValidBarangayAddress;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidBarangayAddressTest extends TestCase
{
    #[DataProvider('validAddresses')]
    public function test_it_accepts_reasonable_addresses(string $address): void
    {
        $validator = Validator::make(
            ['setting_value' => $address],
            ['setting_value' => [new ValidBarangayAddress]],
        );

        $this->assertFalse($validator->fails(), $address);
    }

    #[DataProvider('invalidAddresses')]
    public function test_it_rejects_garbage_addresses(string $address): void
    {
        $validator = Validator::make(
            ['setting_value' => $address],
            ['setting_value' => [new ValidBarangayAddress]],
        );

        $this->assertTrue($validator->fails(), $address);
        $this->assertSame(ValidBarangayAddress::MESSAGE, $validator->errors()->first('setting_value'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validAddresses(): array
    {
        return [
            'seed address' => ['Barangay Happy Hallow, Baguio City, Benguet,'],
            'street with number' => ['123 Session Road'],
            'minimum length' => ['Main Street'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidAddresses(): array
    {
        return [
            'numeric garbage' => ['00110101'],
            'only digits' => ['1234567890'],
            'too short' => ['Baguio'],
            'repeated symbols' => ['!!!!!!!!!!'],
            'repeated digits' => ['0000000000'],
            'repeated letter' => ['aaaaaaaaaa'],
        ];
    }
}
