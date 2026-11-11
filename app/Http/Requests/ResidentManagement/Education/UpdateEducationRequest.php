<?php

namespace App\Http\Requests\ResidentManagement\Education;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\SchoolLvl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEducationRequest extends FormRequest
{
    use RequiresAtLeastOneField;

    public function authorize(): bool
    {
        return true;
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
            'place_of_school_brgy_id' => ['sometimes', 'nullable', 'integer', Rule::exists('barangay', 'barangay_id')],
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
                    'place_of_school_brgy_id',
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

                $barangayId = $this->exists('place_of_school_brgy_id')
                    ? $this->input('place_of_school_brgy_id')
                    : $this->route('education')?->place_of_school_brgy_id;

                if ($barangayId === null || $barangayId === '') {
                    $validator->errors()->add('place_of_school_brgy_id', 'School barangay is required when the resident is enrolled.');
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
