<?php

namespace Tests\Unit\SystemSetting;

use App\Rules\ValidBarangayContactNumber;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidBarangayContactNumberTest extends TestCase
{
    #[DataProvider('validNumbers')]
    public function test_it_accepts_philippine_contact_numbers(string $number): void
    {
        $validator = Validator::make(
            ['setting_value' => $number],
            ['setting_value' => [new ValidBarangayContactNumber]],
        );

        $this->assertFalse($validator->fails(), $number);
    }

    #[DataProvider('invalidNumbers')]
    public function test_it_rejects_invalid_contact_numbers(string $number): void
    {
        $validator = Validator::make(
            ['setting_value' => $number],
            ['setting_value' => [new ValidBarangayContactNumber]],
        );

        $this->assertTrue($validator->fails(), $number);
        $this->assertSame(ValidBarangayContactNumber::MESSAGE, $validator->errors()->first('setting_value'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validNumbers(): array
    {
        return [
            'mobile local' => ['09123456789'],
            'mobile plus 63' => ['+639123456789'],
            'baguio landline' => ['074-123-4567'],
            'manila landline' => ['02-8123-4567'],
            'compact provincial' => ['0741234567'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidNumbers(): array
    {
        return [
            'six digits' => ['123456'],
            'too short mobile' => ['0912345678'],
            'landline without area code' => ['123-4567'],
            'letters' => ['callme09123'],
        ];
    }
}
