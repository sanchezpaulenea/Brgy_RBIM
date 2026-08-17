<?php

namespace Tests\Unit\BarangayPersonnel;

use App\Http\Requests\BarangayPersonnel\StorePersonnelRequest;
use App\Http\Requests\BarangayPersonnel\UpdatePersonnelRequest;
use ReflectionMethod;
use Tests\TestCase;

class PersonnelNameNormalizationTest extends TestCase
{
    public function test_store_request_title_cases_personnel_names(): void
    {
        $request = StorePersonnelRequest::create('/api/v1/barangay-personnel', 'POST', [
            'personnel_last_name' => 'salibad',
            'personnel_first_name' => 'ellen',
            'personnel_middle_name' => 'marie',
            'personnel_suffix' => 'jr',
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame('Salibad', $request->input('personnel_last_name'));
        $this->assertSame('Ellen', $request->input('personnel_first_name'));
        $this->assertSame('Marie', $request->input('personnel_middle_name'));
        $this->assertSame('Jr', $request->input('personnel_suffix'));
    }

    public function test_update_request_title_cases_personnel_names(): void
    {
        $request = UpdatePersonnelRequest::create('/api/v1/barangay-personnel/1', 'PATCH', [
            'personnel_last_name' => 'dangpa',
            'personnel_first_name' => 'julia',
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame('Dangpa', $request->input('personnel_last_name'));
        $this->assertSame('Julia', $request->input('personnel_first_name'));
    }

    private function invokePrepareForValidation(StorePersonnelRequest|UpdatePersonnelRequest $request): void
    {
        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->invoke($request);
    }
}
