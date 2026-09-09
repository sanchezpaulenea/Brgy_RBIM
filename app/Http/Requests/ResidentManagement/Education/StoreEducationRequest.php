<?php

namespace App\Http\Requests\ResidentManagement\Education;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\SchoolLvl;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEducationRequest extends FormRequest
{
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
        $relevance = $this->educationRelevance();

        return [
            'highest_lvl_of_educ_id' => [
                Rule::requiredIf(fn () => $relevance['highest_level']),
                'nullable',
                'integer',
                Rule::exists('highest_lvl_of_educ', 'highest_lvl_of_educ_id'),
            ],
            'current_enrollement_status_id' => [
                Rule::requiredIf(fn () => $relevance['enrollment']),
                'nullable',
                'integer',
                Rule::exists('current_enrollment_status', 'current_enrollment_status_id'),
            ],
            'school_lvl_id' => [
                Rule::requiredIf(fn () => $relevance['enrollment'] && $this->isCurrentlyEnrolled()),
                'nullable',
                'integer',
                Rule::exists('school_lvl', 'school_lvl_id'),
            ],
            'place_of_school_brgy' => ['nullable', 'string', 'max:45', new ValidPlaceName('School barangay')],
            'place_of_school_city_municipality' => [
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('School city / municipality'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'highest_lvl_of_educ_id.required' => 'Highest level of education is required.',
            'highest_lvl_of_educ_id.exists' => 'The selected highest level of education does not exist.',
            'current_enrollement_status_id.required' => 'Current enrollment status is required.',
            'current_enrollement_status_id.exists' => 'The selected enrollment status does not exist.',
            'school_lvl_id.required' => 'School level is required.',
            'school_lvl_id.exists' => 'The selected school level does not exist.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! $this->educationRelevance()['enrollment']) {
                    return;
                }

                $statusId = (int) $this->input('current_enrollement_status_id');
                $status = CurrentEnrollmentStatus::query()
                    ->where('current_enrollment_status_id', $statusId)
                    ->first();

                if ($status === null || $status->isNotEnrolled()) {
                    return;
                }

                $schoolLvl = SchoolLvl::query()
                    ->where('school_lvl_id', $this->input('school_lvl_id'))
                    ->first();

                if ($schoolLvl === null || $schoolLvl->indicatesNotApplicable()) {
                    $validator->errors()->add('school_lvl_id', 'School level is required when the resident is enrolled.');
                }

                if (! is_string($this->input('place_of_school_brgy')) || trim((string) $this->input('place_of_school_brgy')) === '') {
                    $validator->errors()->add('place_of_school_brgy', 'School barangay is required when the resident is enrolled.');
                }

                if (! is_string($this->input('place_of_school_city_municipality')) || trim((string) $this->input('place_of_school_city_municipality')) === '') {
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
        $resident = $this->route('resident');

        if ($resident instanceof Resident) {
            return $resident->educationFieldRelevance();
        }

        return [
            'highest_level' => true,
            'enrollment' => true,
        ];
    }

    private function isCurrentlyEnrolled(): bool
    {
        $statusId = (int) $this->input('current_enrollement_status_id');

        if ($statusId === 0) {
            return false;
        }

        $status = CurrentEnrollmentStatus::query()
            ->where('current_enrollment_status_id', $statusId)
            ->first();

        return $status !== null && ! $status->isNotEnrolled();
    }
}
