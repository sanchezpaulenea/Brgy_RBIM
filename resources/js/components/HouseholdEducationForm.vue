<template>
    <fieldset class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
        <legend class="px-1 text-sm font-semibold text-brand">Education</legend>

        <div v-if="showAttainment" class="mb-4">
            <label class="rbim-label" :for="`${idPrefix}-highest_education`">
                Highest level of education attained<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-highest_education`"
                :value="modelValue.highest_education_attained"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.highest_education_attained }"
                @change="patch('highest_education_attained', $event.target.value)"
            >
                <option value="">Select highest level attained</option>
                <option v-for="option in highestEducationLevels" :key="option" :value="option">{{ option }}</option>
            </select>
            <p v-if="errors.highest_education_attained" class="rbim-error">{{ errors.highest_education_attained }}</p>
        </div>

        <div v-if="showEnrollment" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-enrollment`">
                    Enrollment status<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-enrollment`"
                    :value="modelValue.enrollment_status"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.enrollment_status }"
                    @change="patch('enrollment_status', $event.target.value)"
                >
                    <option value="">Select enrollment status</option>
                    <option v-for="option in enrollmentStatuses" :key="option" :value="option">{{ option }}</option>
                </select>
                <p v-if="errors.enrollment_status" class="rbim-error">{{ errors.enrollment_status }}</p>
            </div>
            <div v-if="isEnrolled">
                <label class="rbim-label" :for="`${idPrefix}-school_level`">
                    School level<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-school_level`"
                    :value="modelValue.school_level"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.school_level }"
                    @change="patch('school_level', $event.target.value)"
                >
                    <option value="">Select school level</option>
                    <option v-for="option in schoolLevels" :key="option" :value="option">{{ option }}</option>
                </select>
                <p v-if="errors.school_level" class="rbim-error">{{ errors.school_level }}</p>
            </div>
            <div v-if="isEnrolled" class="sm:col-span-2">
                <label class="rbim-label" :for="`${idPrefix}-school_location`">
                    School location<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-school_location`"
                    :value="modelValue.school_location"
                    type="text"
                    maxlength="45"
                    placeholder="Barangay, city, or school name"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.school_location }"
                    @input="patch('school_location', $event.target.value)"
                >
                <p v-if="errors.school_location" class="rbim-error">{{ errors.school_location }}</p>
            </div>
        </div>
    </fieldset>
</template>

<script setup>
import { computed } from 'vue';
import { ageFromDateOfBirth } from '@/utils/format';

const highestEducationLevels = [
    'No grade completed',
    'Preschool',
    'Elementary undergraduate',
    'Elementary graduate',
    'High school undergraduate',
    'High school graduate',
    'Vocational / TESDA',
    'College undergraduate',
    'College graduate',
    'Postgraduate',
];

const enrollmentStatuses = [
    'Currently enrolled',
    'Not currently enrolled',
];

const schoolLevels = [
    'Day care',
    'Kindergarten',
    'Elementary',
    'Junior high school',
    'Senior high school',
    'ALS',
    'Vocational / TESDA',
    'College',
    'Postgraduate',
];

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    idPrefix: {
        type: String,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue']);

const age = computed(() => ageFromDateOfBirth(props.modelValue.date_of_birth));
const showAttainment = computed(() => age.value !== null && age.value >= 5);
const showEnrollment = computed(() => age.value !== null && age.value >= 3 && age.value <= 24);
const isEnrolled = computed(() => props.modelValue.enrollment_status === 'Currently enrolled');

function patch(field, value) {
    const next = {
        ...props.modelValue,
        [field]: value,
    };

    if (field === 'enrollment_status' && value !== 'Currently enrolled') {
        next.school_level = '';
        next.school_location = '';
    }

    emit('update:modelValue', next);
}
</script>
