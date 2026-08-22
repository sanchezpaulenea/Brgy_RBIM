<?php

namespace Tests\Unit\SystemSetting;

use App\Http\Requests\SystemSetting\UpdateSettingRequest;
use App\Models\Setting\Setting;
use App\Rules\ValidBarangayAddress;
use App\Rules\ValidBarangayContactNumber;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class UpdateSettingRequestTest extends TestCase
{
    #[DataProvider('acceptedValues')]
    public function test_it_accepts_valid_setting_values(string $key, string $value): void
    {
        $validator = $this->validatorFor($key, $value);

        $this->assertFalse($validator->fails(), (string) $validator->errors());
    }

    #[DataProvider('rejectedValues')]
    public function test_it_rejects_invalid_setting_values(string $key, string $value, string $message): void
    {
        $validator = $this->validatorFor($key, $value);

        $this->assertTrue($validator->fails(), $value);
        $this->assertSame($message, $validator->errors()->first('setting_value'));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function acceptedValues(): array
    {
        return [
            'address' => ['barangay_address', 'Barangay Happy Hallow, Baguio City'],
            'psgc' => ['barangay_code', '1430300006'],
            'mobile' => ['barangay_contact_no', '09123456789'],
            'email' => ['barangay_email', 'brgyhappyhallow@gmail.com'],
            'generic string' => ['barangay_name', 'Barangay Happy Hallow'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function rejectedValues(): array
    {
        return [
            'numeric address' => ['barangay_address', '00110101', ValidBarangayAddress::MESSAGE],
            'short psgc' => ['barangay_code', '143030000', 'Barangay code must be exactly 10 digits, following the Philippine Standard Geographic Code (PSGC) format.'],
            'alpha psgc' => ['barangay_code', '143030000A', 'Barangay code must be exactly 10 digits, following the Philippine Standard Geographic Code (PSGC) format.'],
            'short contact' => ['barangay_contact_no', '123456', ValidBarangayContactNumber::MESSAGE],
            'numeric email' => ['barangay_email', '12345', 'Please enter a valid email address.'],
        ];
    }

    private function validatorFor(string $settingKey, string $value): \Illuminate\Validation\Validator
    {
        $setting = new Setting([
            'setting_key' => $settingKey,
            'data_type' => 'string',
        ]);

        $request = new UpdateSettingRequest;
        $method = new ReflectionMethod($request, 'rulesForSetting');
        $messagesMethod = new ReflectionMethod($request, 'messages');

        return Validator::make(
            ['setting_value' => $value],
            ['setting_value' => $method->invoke($request, $setting)],
            $messagesMethod->invoke($request),
        );
    }
}
