<?php

namespace Tests\Unit\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\StoreHouseholdQuestionsRequest;
use ReflectionMethod;
use Tests\TestCase;

class StoreHouseholdQuestionsRequestTest extends TestCase
{
    public function test_store_request_title_cases_named_lists_and_places(): void
    {
        $request = StoreHouseholdQuestionsRequest::create('/api/v1/households/1/questions', 'POST', [
            'common_diseases' => ['  pneumonia  ', '', 'high blood'],
            'primary_needs' => ['road repair'],
            'intend_to_stay_brgy' => 'happy hallow',
            'intend_to_stay_municipality' => 'baguio city',
            'intend_to_stay_province' => 'benguet',
        ]);

        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->invoke($request);

        $this->assertSame(['Pneumonia', 'High Blood'], $request->input('common_diseases'));
        $this->assertSame(['Road Repair'], $request->input('primary_needs'));
        $this->assertSame('Happy Hallow', $request->input('intend_to_stay_brgy'));
        $this->assertSame('Baguio City', $request->input('intend_to_stay_municipality'));
        $this->assertSame('Benguet', $request->input('intend_to_stay_province'));
    }
}
