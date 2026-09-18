<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="emit('cancel')"
    >
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Assign a new household head</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        This household is still active. Choose a household member to become the new head before this status change can be saved.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close"
                    @click="emit('cancel')"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 8.586 15.293 3.293a1 1 0 111.414 1.414L11.414 10l5.293 5.293a1 1 0 01-1.414 1.414L10 11.414l-5.293 5.293a1 1 0 01-1.414-1.414L8.586 10 3.293 4.707a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 px-5 py-4">
                <p v-if="!candidates.length" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    No eligible household members are available. Register an active member who is at least 15 years old first.
                </p>

                <template v-else>
                    <div>
                        <label for="assign-new-head" class="rbim-label">
                            New household head<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <select id="assign-new-head" v-model="selectedHeadId" class="rbim-input">
                            <option value="">Select household member</option>
                            <option v-for="member in candidates" :key="member.resident_id" :value="member.resident_id">
                                {{ member.full_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="assign-former-relationship" class="rbim-label">
                            Former head's relationship to the new household head<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <select id="assign-former-relationship" v-model="selectedRelationshipId" class="rbim-input">
                            <option value="">Select relationship</option>
                            <option v-for="option in relationshipOptions" :key="option.id" :value="option.id">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-2 px-5 py-4">
                <button type="button" class="rbim-btn-outline" @click="emit('cancel')">
                    Cancel
                </button>
                <button
                    type="button"
                    class="rbim-btn"
                    :disabled="!canConfirm"
                    @click="confirm"
                >
                    Assign household head
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { HEAD_RELATIONSHIP_ID } from '@/utils/residentForm';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    candidates: {
        type: Array,
        default: () => [],
    },
    relationships: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['confirm', 'cancel']);

const selectedHeadId = ref('');
const selectedRelationshipId = ref('');

const relationshipOptions = computed(() => (
    props.relationships.filter((option) => Number(option.id) !== HEAD_RELATIONSHIP_ID)
));

const canConfirm = computed(() => (
    props.candidates.length > 0
    && selectedHeadId.value !== ''
    && selectedRelationshipId.value !== ''
));

watch(() => props.open, (open) => {
    if (open) {
        selectedHeadId.value = '';
        selectedRelationshipId.value = '';
    }
});

function confirm() {
    if (!canConfirm.value) {
        return;
    }

    emit('confirm', {
        new_head_resident_id: Number(selectedHeadId.value),
        former_head_relationship_to_hh_id: Number(selectedRelationshipId.value),
    });
}
</script>
