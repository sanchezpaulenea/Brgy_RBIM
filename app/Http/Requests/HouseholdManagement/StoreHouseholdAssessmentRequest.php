<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Models\HouseholdManagement\CensusStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHouseholdAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $activePersonnel = fn () => Rule::exists('barangay_personnel', 'personnel_id')
            ->where('personnel_status_id', PersonnelStatus::ACTIVE);

        return [
            'census_status_id' => [
                'required',
                'integer',
                Rule::exists('census_status', 'census_status_id'),
            ],
            'interviewer_id' => ['required', 'integer', $activePersonnel()],
            'supervisor_id' => ['required', 'integer', $activePersonnel()],
            'next_visit_date' => [
                Rule::requiredIf(fn () => (int) $this->input('census_status_id') === CensusStatus::CALLBACK),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'census_status_id.required' => 'Census status is required.',
            'census_status_id.exists' => 'The selected census status does not exist.',
            'interviewer_id.required' => 'Interviewer is required.',
            'interviewer_id.exists' => 'The selected interviewer does not exist.',
            'supervisor_id.required' => 'Supervisor is required.',
            'supervisor_id.exists' => 'The selected supervisor does not exist.',
            'next_visit_date.required' => 'Next visit date is required when census status is CB (Callback).',
            'next_visit_date.after_or_equal' => 'Next visit date cannot be in the past.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $statusId = (int) $this->input('census_status_id');
                $nextVisit = $this->input('next_visit_date');

                if ($statusId !== CensusStatus::CALLBACK && filled($nextVisit)) {
                    $validator->errors()->add(
                        'next_visit_date',
                        'Next visit date is only allowed when census status is CB (Callback).',
                    );
                }
            },
        ];
    }
}
