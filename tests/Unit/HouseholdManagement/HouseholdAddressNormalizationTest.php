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
            'house_lot' => $value,
            'block_num' => $value,
            'building_name' => $value,
            'unit_num' => $value,
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertNull($request->input('house_lot'));
        $this->assertNull($request->input('block_num'));
        $this->assertNull($request->input('building_name'));
        $this->assertNull($request->input('unit_num'));
    }

    #[DataProvider('notApplicableValues')]
    public function test_update_request_treats_not_applicable_address_as_null(string $value): void
    {
        $request = UpdateHouseholdRequest::create('/api/v1/households/1', 'PATCH', [
            'house_lot' => $value,
            'block_num' => $value,
            'building_name' => $value,
            'unit_num' => $value,
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertNull($request->input('house_lot'));
        $this->assertNull($request->input('block_num'));
        $this->assertNull($request->input('building_name'));
        $this->assertNull($request->input('unit_num'));
    }

    public function test_store_request_keeps_real_lot_and_block_values(): void
    {
        $request = StoreHouseholdRequest::create('/api/v1/households', 'POST', [
            'house_lot' => '  Lot 15  ',
            'block_num' => 'Blk 4',
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame('Lot 15', $request->input('house_lot'));
        $this->assertSame('Blk 4', $request->input('block_num'));
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
