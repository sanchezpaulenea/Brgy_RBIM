<?php

namespace App\Http\Requests\ResidentManagement\Education;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\SchoolLvl;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEducationRequest extends FormRequest
{
    use RequiresAtLeastOneField;
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased([
            'place_of_school_brgy',
            'place_of_school_city_municipality',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'highest_lvl_of_educ_id' => [
                'sometimes',
                Rule::requiredIf(fn () => $this->educationRelevance()['highest_level'] && $this->exists('highest_lvl_of_educ_id')),
                'nullable',
                'integer',
                Rule::exists('highest_lvl_of_educ', 'highest_lvl_of_educ_id'),
            ],
            'current_enrollement_status_id' => [
                'sometimes',
                Rule::requiredIf(fn () => $this->educationRelevance()['enrollment'] && $this->exists('current_enrollement_status_id')),
                'nullable',
                'integer',
                Rule::exists('current_enrollment_status', 'current_enrollment_status_id'),
            ],
            'school_lvl_id' => ['sometimes', 'required', 'integer', Rule::exists('school_lvl', 'school_lvl_id')],
            'place_of_school_brgy' => ['sometimes', 'nullable', 'string', 'max:45', new ValidPlaceName('School barangay')],
            'place_of_school_city_municipality' => [
                'sometimes',
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('School city / municipality'),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'highest_lvl_of_educ_id',
                    'current_enrollement_status_id',
                    'school_lvl_id',
                    'place_of_school_brgy',
                    'place_of_school_city_municipality',
                ], 'education', 'Provide at least one education field to update.');
            },
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! $this->educationRelevance()['enrollment']) {
                    return;
                }

                $statusId = $this->input('current_enrollement_status_id')
                    ?? $this->route('education')?->current_enrollement_status_id;

                $status = CurrentEnrollmentStatus::query()
                    ->where('current_enrollment_status_id', $statusId)
                    ->first();

                if ($status === null || $status->isNotEnrolled()) {
                    return;
                }

                $schoolLvlId = $this->input('school_lvl_id')
                    ?? $this->route('education')?->school_lvl_id;
                $schoolLvl = SchoolLvl::query()
                    ->where('school_lvl_id', $schoolLvlId)
                    ->first();

                if ($schoolLvl === null || $schoolLvl->indicatesNotApplicable()) {
                    $validator->errors()->add('school_lvl_id', 'School level is required when the resident is enrolled.');
                }

                $brgy = $this->exists('place_of_school_brgy')
                    ? $this->input('place_of_school_brgy')
                    : $this->route('education')?->place_of_school_brgy;
                $city = $this->exists('place_of_school_city_municipality')
                    ? $this->input('place_of_school_city_municipality')
                    : $this->route('education')?->place_of_school_city_municipality;

                if (! is_string($brgy) || trim($brgy) === '') {
                    $validator->errors()->add('place_of_school_brgy', 'School barangay is required when the resident is enrolled.');
                }

                if (! is_string($city) || trim($city) === '') {
                    $validator->errors()->add(
                        'place_of_school_city_municipality',
                        'School city / municipality is required when the resident is enrolled.',
                    );
                }
            },
        ];
    }

    /**
     * @return array{highest_level: bool, enrollment: bool}
     */
    private function educationRelevance(): array
    {
        $education = $this->route('education');
        $resident = $education?->resident;

        if ($resident !== null) {
            return $resident->educationFieldRelevance();
        }

        return [
            'highest_level' => true,
            'enrollment' => true,
        ];
    }
}
