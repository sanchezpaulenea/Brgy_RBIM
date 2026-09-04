<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="emit('cancel')"
    >
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-900">Update census status</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Only the census status can be changed, and only while the latest assessment is still CB (Callback).
                </p>
            </div>
            <form class="space-y-4 px-5 py-4" novalidate @submit.prevent="emit('submit')">
                <p v-if="householdLabel" class="text-sm text-slate-900">
                    {{ householdLabel }}
                </p>
                <div>
                    <p class="rbim-label">Current status</p>
                    <p class="mt-1 text-sm text-slate-900">{{ currentStatusLabel }}</p>
                </div>
                <div>
                    <label :for="inputId" class="rbim-label">
                        Census Status<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        :id="inputId"
                        :value="modelValue"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': error }"
                        @change="emit('update:modelValue', $event.target.value)"
                    >
                        <option value="">Select census status</option>
                        <option v-for="status in statusOptions" :key="status.id" :value="status.id">
                            {{ censusStatusLabel(status) }}
                        </option>
                    </select>
                    <p v-if="error" class="rbim-error">{{ error }}</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button type="button" class="rbim-btn-outline" @click="emit('cancel')">
                        Cancel
                    </button>
                    <button type="submit" class="rbim-btn" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Update status' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { censusStatusLabel, isCallbackCensusStatus } from '@/utils/format';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    modelValue: {
        type: [Number, String],
        default: '',
    },
    statuses: {
        type: Array,
        default: () => [],
    },
    currentStatusLabel: {
        type: String,
        default: '—',
    },
    householdLabel: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    saving: {
        type: Boolean,
        default: false,
    },
    inputId: {
        type: String,
        default: 'assessment-status-id',
    },
});

const emit = defineEmits(['update:modelValue', 'submit', 'cancel']);

const statusOptions = computed(() => (
    props.statuses.filter((status) => !isCallbackCensusStatus(status))
));
</script>
