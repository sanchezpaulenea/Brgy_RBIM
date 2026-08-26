<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" active-name="households" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <RouterLink :to="{ name: 'households' }" class="text-sm font-medium text-brand hover:underline">
                    ← Back to households
                </RouterLink>
                <button
                    v-if="canUpdate && household"
                    type="button"
                    class="rbim-btn-action"
                    @click="startEdit"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                    </svg>
                    Update
                </button>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading household...
            </div>

            <template v-else-if="household">
                <article class="rbim-card p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Household {{ household.household_id }}
                    </h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Clan</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.clan_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Street</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.street_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">House/Lot Number</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.house_lot || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Block Number</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.block_num || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Building Name</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.building_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Unit Number</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.unit_num || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Household Status</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.household_status || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Registration Date</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ formatDateTime(household.registration_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Head Resident Name</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.head?.full_name || '—' }}</dd>
                        </div>
                    </dl>
                </article>

                <div class="rbim-card overflow-hidden">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h3 class="text-sm font-semibold text-slate-900">Household members</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Relationship to Household Head</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Sex</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Age</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="member in household.residents || []" :key="member.resident_id">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ member.full_name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ member.relationship_to_hh || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ member.sex || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ ageLabel(member.date_of_birth) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ member.resident_status || '—' }}</td>
                                </tr>
                                <tr v-if="!(household.residents || []).length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                        No residents belong to this household.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <div
            v-if="editing"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="cancelEdit"
        >
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Update household</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        The household head cannot be changed here.
                    </p>
                </div>
                <form class="space-y-4 px-5 py-4" novalidate @submit.prevent="handleUpdate">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="detail_street_id" class="rbim-label">
                                Street<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="detail_street_id"
                                v-model="editForm.street_id"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.street_id }"
                            >
                                <option value="">Select street</option>
                                <option v-for="street in streets" :key="street.id" :value="street.id">
                                    {{ street.label }}
                                </option>
                            </select>
                            <p v-if="editErrors.street_id" class="rbim-error">{{ editErrors.street_id }}</p>
                        </div>
                        <div>
                            <label for="detail_house_lot" class="rbim-label">House/Lot Number</label>
                            <input
                                id="detail_house_lot"
                                v-model="editForm.house_lot"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                            >
                        </div>
                        <div>
                            <label for="detail_block_num" class="rbim-label">Block Number</label>
                            <input
                                id="detail_block_num"
                                v-model="editForm.block_num"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                            >
                        </div>
                        <div>
                            <label for="detail_building_name" class="rbim-label">Building Name</label>
                            <input
                                id="detail_building_name"
                                v-model="editForm.building_name"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                            >
                        </div>
                        <div>
                            <label for="detail_unit_num" class="rbim-label">Unit Number</label>
                            <input
                                id="detail_unit_num"
                                v-model="editForm.unit_num"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                            >
                        </div>
                        <div>
                            <label for="detail_household_status_id" class="rbim-label">
                                Household Status<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="detail_household_status_id"
                                v-model="editForm.household_status_id"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.household_status_id }"
                            >
                                <option value="">Select household status</option>
                                <option v-for="status in householdStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                            <p v-if="editErrors.household_status_id" class="rbim-error">{{ editErrors.household_status_id }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="rbim-btn-outline" @click="cancelEdit">
                            Cancel
                        </button>
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Update household' }}
                        </button>
                    </div>
                </form>
            </div>
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
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import { ageFromDateOfBirth, formatDateTime } from '@/utils/format';
import { applyValidationErrors, optionalText, toId } from '@/utils/residentForm';

const route = useRoute();
const { hasPermission } = useAuth();
const { householdTabs } = useSectionTabs();

const household = ref(null);
const streets = ref([]);
const householdStatuses = ref([]);
const loading = ref(false);
const saving = ref(false);
const editing = ref(false);
const error = ref('');
const successMessage = ref('');
const editForm = reactive(emptyEditForm());
const editErrors = reactive({});
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canUpdate = computed(() => hasPermission('household.update'));

function emptyEditForm() {
    return {
        street_id: '',
        house_lot: '',
        block_num: '',
        building_name: '',
        unit_num: '',
        household_status_id: '',
    };
}

function ageLabel(dateOfBirth) {
    const age = ageFromDateOfBirth(dateOfBirth);

    return age === null ? '—' : String(age);
}

function handleConfirmCancel() {
    confirm.open = false;
    confirm.onCancel?.();
}

function askConfirm({ title, message, confirmLabel = 'Continue', variant = 'primary' }) {
    return new Promise((resolve) => {
        confirm.open = true;
        confirm.title = title;
        confirm.message = message;
        confirm.confirmLabel = confirmLabel;
        confirm.variant = variant;
        confirm.onConfirm = () => {
            confirm.open = false;
            resolve(true);
        };
        confirm.onCancel = () => resolve(false);
    });
}

function clearEditErrors() {
    Object.keys(editErrors).forEach((key) => {
        delete editErrors[key];
    });
}

function startEdit() {
    if (!household.value) {
        return;
    }

    editing.value = true;
    Object.assign(editForm, {
        street_id: household.value.street_id ?? '',
        house_lot: household.value.house_lot ?? '',
        block_num: household.value.block_num ?? '',
        building_name: household.value.building_name ?? '',
        unit_num: household.value.unit_num ?? '',
        household_status_id: household.value.household_status_id ?? '',
    });
    clearEditErrors();
}

function cancelEdit() {
    editing.value = false;
    Object.assign(editForm, emptyEditForm());
    clearEditErrors();
}

async function loadHousehold() {
    loading.value = true;
    error.value = '';

    try {
        const [item, streetItems, statusItems] = await Promise.all([
            householdService.fetchHousehold(route.params.id),
            lookupService.fetchStreets(),
            lookupService.fetchLookup('household-status'),
        ]);
        household.value = item;
        streets.value = streetItems;
        householdStatuses.value = statusItems;
    } catch (err) {
        household.value = null;
        error.value = extractErrorMessage(err, 'Unable to load this household.');
    } finally {
        loading.value = false;
    }
}

async function handleUpdate() {
    clearEditErrors();

    if (!toId(editForm.street_id)) {
        editErrors.street_id = 'Street is required.';
    }

    if (!toId(editForm.household_status_id)) {
        editErrors.household_status_id = 'Household status is required.';
    }

    if (Object.keys(editErrors).length) {
        return;
    }

    const allowed = await askConfirm({
        title: 'Update household',
        message: 'Save these changes to this household record?',
        confirmLabel: 'Save changes',
    });

    if (!allowed) {
        return;
    }

    saving.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        await householdService.updateHousehold(route.params.id, {
            street_id: toId(editForm.street_id),
            house_lot: optionalText(editForm.house_lot),
            block_num: optionalText(editForm.block_num),
            building_name: optionalText(editForm.building_name),
            unit_num: optionalText(editForm.unit_num),
            household_status_id: toId(editForm.household_status_id),
        });
        successMessage.value = 'Household updated successfully.';
        cancelEdit();
        await loadHousehold();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(editErrors, validationErrors);
        } else {
            error.value = extractErrorMessage(err, 'Unable to update this household.');
        }
    } finally {
        saving.value = false;
    }
}

watch(() => route.params.id, loadHousehold);
onMounted(loadHousehold);
</script>
