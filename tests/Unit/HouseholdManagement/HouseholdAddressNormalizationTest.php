<?php

namespace Tests\Unit\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\StoreHouseholdRequest;
use App\Http\Requests\HouseholdManagement\UpdateHouseholdRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class HouseholdAddressNormalizationTest extends TestCase
{
    #[DataProvider('notApplicableValues')]
    public function test_store_request_treats_not_applicable_address_as_null(string $value): void
    {
        $request = StoreHouseholdRequest::create('/api/v1/households', 'POST', [
            'house_number' => $value,
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertNull($request->input('house_number'));
    }

    #[DataProvider('notApplicableValues')]
    public function test_update_request_treats_not_applicable_address_as_null(string $value): void
    {
        $request = UpdateHouseholdRequest::create('/api/v1/households/1', 'PATCH', [
            'house_number' => $value,
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertNull($request->input('house_number'));
    }

    public function test_store_request_keeps_real_lot_values(): void
    {
        $request = StoreHouseholdRequest::create('/api/v1/households', 'POST', [
            'house_number' => '  15  ',
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame(15, $request->input('house_number'));
    }

    public function test_non_numeric_house_number_is_rejected_with_a_message(): void
    {
        $request = StoreHouseholdRequest::create('/api/v1/households', 'POST', [
            'house_number' => '12A',
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame('12A', $request->input('house_number'));

        $rules = $request->rules();
        $validator = validator(
            ['house_number' => $request->input('house_number')],
            ['house_number' => $rules['house_number']],
            $request->messages(),
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'House number must contain numbers only.',
            $validator->errors()->first('house_number'),
        );
    }

    public function test_store_request_sets_basement_level_to_zero_when_house_has_no_basement(): void
    {
        $request = StoreHouseholdRequest::create('/api/v1/households', 'POST', [
            'has_basement' => false,
            'number_of_basement_level' => 3,
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame(0, $request->input('number_of_basement_level'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function notApplicableValues(): array
    {
        return [
            'n/a' => ['N/A'],
            'lowercase n/a' => ['n/a'],
            'na' => ['NA'],
            'n.a.' => ['N.A.'],
            'not applicable' => ['Not Applicable'],
            'blank' => ['   '],
        ];
    }

    private function invokePrepareForValidation(StoreHouseholdRequest|UpdateHouseholdRequest $request): void
    {
        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->invoke($request);
    }
}
