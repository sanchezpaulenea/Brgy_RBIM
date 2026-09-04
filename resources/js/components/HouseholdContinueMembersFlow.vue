<template>
    <div class="space-y-6">
        <article v-if="step === 'choice'" class="rbim-card p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                Household registered
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                The household and head resident are saved. You can add more members now, or finish and encode this visit's assessment.
            </p>
            <p class="mt-4 text-sm text-slate-900">
                {{ householdLabel }}
            </p>
            <div class="mt-6 flex flex-wrap items-center gap-2">
                <button type="button" class="rbim-btn" @click="step = 'register'">
                    Continue Registering Members
                </button>
                <button type="button" class="rbim-btn-outline" @click="emit('members-complete')">
                    Finish
                </button>
            </div>
        </article>

        <SequentialResidentRegistration
            v-else
            :household="household"
            :sexes="sexes"
            :relationships="relationships"
            :nationalities="nationalities"
            :religions="religions"
            :ethnicities="ethnicities"
            :marital-statuses="maritalStatuses"
            :resident-types="residentTypes"
            :existing-residents="existingResidents"
            :ensure-lookups="ensureLookups"
            :lookup-limit="REGISTER_HOUSEHOLD_LOOKUP_LIMIT"
            :preferred-lookup-ids="REGISTER_HOUSEHOLD_LOOKUP_IDS"
            count-title="Additional household members"
            count-label="How many household members would you like to add?"
            count-hint="The household head is already registered. This is how many more members you will encode next."
            noun="members"
            show-back
            id-prefix="continue-member"
            count-input-id="member_count"
            @back="step = 'choice'"
            @member-added="emit('member-added')"
            @finished="emit('members-complete')"
            @lookup-created="emit('lookup-created', $event)"
        />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import SequentialResidentRegistration from '@/components/SequentialResidentRegistration.vue';
import { householdDisplayLabel } from '@/utils/format';
import {
    REGISTER_HOUSEHOLD_LOOKUP_IDS,
    REGISTER_HOUSEHOLD_LOOKUP_LIMIT,
} from '@/utils/demographicLookups';

const props = defineProps({
    household: {
        type: Object,
        required: true,
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
    ensureLookups: {
        type: Function,
        default: null,
    },
});

const emit = defineEmits(['members-complete', 'member-added', 'lookup-created']);

const step = ref('choice');
const householdLabel = computed(() => householdDisplayLabel(props.household));
</script>
