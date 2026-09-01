<?php

namespace Tests\Unit\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\StoreHouseholdAssessmentRequest;
use App\Models\HouseholdManagement\CensusStatus;
use Illuminate\Validation\Rules\RequiredIf;
use Tests\TestCase;

class StoreHouseholdAssessmentRequestTest extends TestCase
{
    public function test_next_visit_date_is_required_only_for_callback_status(): void
    {
        $request = new StoreHouseholdAssessmentRequest;
        $request->merge(['census_status_id' => CensusStatus::COMPLETED]);
        $rules = $request->rules();

        $this->assertArrayHasKey('census_status_id', $rules);
        $this->assertArrayHasKey('interviewer_id', $rules);
        $this->assertArrayHasKey('supervisor_id', $rules);
        $this->assertArrayHasKey('next_visit_date', $rules);

        $nextVisit = $rules['next_visit_date'];
        $this->assertContains('nullable', $nextVisit);
        $this->assertContains('after_or_equal:today', $nextVisit);
        $this->assertTrue(collect($nextVisit)->contains(
            fn ($rule) => $rule instanceof RequiredIf,
        ));
    }

    public function test_callback_status_constant_matches_seeded_id(): void
    {
        $this->assertSame(2, CensusStatus::CALLBACK);
        $this->assertSame(1, CensusStatus::COMPLETED);
        $this->assertSame(3, CensusStatus::REFUSED);
    }
}
