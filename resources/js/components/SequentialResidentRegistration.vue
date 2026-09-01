<template>
    <div class="space-y-6">
        <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>
        <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ successMessage }}
        </div>

        <article v-if="step === 'count'" class="rbim-card p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                {{ countTitle }}
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Fields marked with <span class="rbim-required">*</span> are required.
                {{ countHint }}
            </p>

            <div v-if="$slots.household" class="mt-4 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900">Household</h3>
                <slot name="household" :disabled="false" />
            </div>

            <form
                v-if="household"
                class="mt-4 grid max-w-md gap-4"
                novalidate
                @submit.prevent="confirmMemberCount"
            >
                <div>
                    <label :for="countInputId" class="rbim-label">
                        {{ countLabel }}<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="countInputId"
                        v-model="memberCountInput"
                        type="number"
                        min="1"
                        :max="SEQUENTIAL_RESIDENT_MAX"
                        step="1"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': countError }"
                    >
                    <p v-if="countError" class="rbim-error">{{ countError }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="rbim-btn">
                        Continue
                    </button>
                    <button v-if="showBack" type="button" class="rbim-btn-outline" @click="emit('back')">
                        Back
                    </button>
                </div>
            </form>
        </article>

        <article v-else-if="step === 'members'" class="rbim-card p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                Register resident
            </h2>
            <p class="mt-1 text-sm font-medium text-slate-900">
                Member {{ currentMember }} of {{ totalMembers }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Fields marked with <span class="rbim-required">*</span> are required.
                This resident will be added to {{ householdLabel }}.
            </p>

            <form class="mt-4 space-y-8" novalidate @submit.prevent="handleSaveMember">
                <section class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-900">Household</h3>
                    <slot v-if="$slots.household" name="household" :disabled="true" />
                    <div v-else>
                        <label class="rbim-label">
                            Household<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            class="rbim-input"
                            :value="householdLabel"
                            disabled
                        >
                    </div>
                </section>

                <section class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-900">Demographics</h3>
                    <ResidentDemographicsFields
                        v-model="member"
                        :errors="memberErrors"
                        :sexes="sexes"
                        :relationship-options="memberRelationships"
                        :nationalities="nationalities"
                        :religions="religions"
                        :ethnicities="ethnicities"
                        :marital-statuses="maritalStatuses"
                        :resident-types="residentTypes"
                        :id-prefix="idPrefix"
                        @validate-name="validateMemberName"
                        @lookup-created="emit('lookup-created', $event)"
                        @lookup-error="onLookupError"
                    />
                </section>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="rbim-btn" :disabled="saving">
                        {{ saveButtonLabel }}
                    </button>
                </div>
            </form>
        </article>
    </div>

    <ConfirmDialog
        :open="confirm.open"
        :title="confirm.title"
        :message="confirm.message"
        :confirm-label="confirm.confirmLabel"
        :variant="confirm.variant"
        @confirm="confirm.onConfirm?.()"
        @cancel="handleConfirmCancel"
    />
</template>

<script setup>
import { computed, toRef, watch } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ResidentDemographicsFields from '@/components/ResidentDemographicsFields.vue';
import {
    SEQUENTIAL_RESIDENT_MAX,
    useSequentialResidentRegistration,
} from '@/composables/useSequentialResidentRegistration';
import { householdDisplayLabel } from '@/utils/format';

const props = defineProps({
    household: {
        type: Object,
        default: null,
    },
    sexes: {
        type: Array,
        default: () => [],
    },
    relationships: {
        type: Array,
        default: () => [],
    },
    nationalities: {
        type: Array,
        default: () => [],
    },
    religions: {
        type: Array,
        default: () => [],
    },
    ethnicities: {
        type: Array,
        default: () => [],
    },
    maritalStatuses: {
        type: Array,
        default: () => [],
    },
    residentTypes: {
        type: Array,
        default: () => [],
    },
    existingResidents: {
        type: Array,
        default: () => [],
    },
    countTitle: {
        type: String,
        default: 'Register resident',
    },
    countLabel: {
        type: String,
        default: 'How many residents would you like to add?',
    },
    countHint: {
        type: String,
        default: 'This is how many residents you will encode next for the selected household.',
    },
    noun: {
        type: String,
        default: 'residents',
    },
    showBack: {
        type: Boolean,
        default: false,
    },
    idPrefix: {
        type: String,
        default: 'sequential-resident',
    },
    countInputId: {
        type: String,
        default: 'resident_count',
    },
    ensureLookups: {
        type: Function,
        default: null,
    },
});

const emit = defineEmits(['finished', 'member-added', 'back', 'lookup-created']);

const {
    step,
    saving,
    error,
    successMessage,
    memberCountInput,
    countError,
    totalMembers,
    currentMember,
    member,
    memberErrors,
    confirm,
    memberRelationships,
    saveButtonLabel,
    confirmMemberCount,
    handleConfirmCancel,
    validateMemberName,
    handleSaveMember,
    startCount,
} = useSequentialResidentRegistration({
    getHouseholdId: () => props.household?.household_id,
    getExistingResidents: () => props.existingResidents,
    relationships: toRef(props, 'relationships'),
    noun: props.noun,
    onMemberAdded: () => emit('member-added'),
    onFinished: (result) => emit('finished', result),
    ensureLookups: (form) => props.ensureLookups?.(form),
});

function onLookupError({ field, message }) {
    if (field) {
        memberErrors[field] = message;
    }
}

const householdLabel = computed(() => householdDisplayLabel(props.household));

watch(() => props.household?.household_id, (householdId) => {
    if (!householdId && step.value === 'members') {
        startCount();
    }
});
</script>
