<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-5">
                <div>
                    <label for="household-search" class="rbim-label">Search</label>
                    <input
                        id="household-search"
                        v-model="filters.search"
                        type="search"
                        name="household-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search street or head resident name"
                    >
                </div>
                <div>
                    <label for="household-filter-street" class="rbim-label">Street</label>
                    <select id="household-filter-street" v-model="filters.street_id" class="rbim-input py-2">
                        <option value="">All streets</option>
                        <option v-for="street in streets" :key="street.id" :value="street.id">
                            {{ street.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="household-filter-status" class="rbim-label">Household Status</label>
                    <select id="household-filter-status" v-model="filters.household_status_id" class="rbim-input py-2">
                        <option value="">All statuses</option>
                        <option v-for="status in householdStatuses" :key="status.id" :value="status.id">
                            {{ status.label }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2">
                    <button type="button" class="rbim-btn-outline" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading households...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Household ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Street</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">House/Lot Number</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Head Resident Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Registration Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="household in filteredItems"
                                :key="household.household_id"
                                class="cursor-pointer hover:bg-slate-50"
                                @click="openDetail(household.household_id)"
                            >
                                <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ household.household_id }}</td>
                                <td class="px-4 py-3 text-slate-900">{{ household.street_name || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ household.house_lot || '—' }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ household.head_name || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ household.household_status || '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDate(household.registration_date) }}</td>
                                <td class="px-4 py-3" @click.stop>
                                    <div class="flex gap-2">
                                        <button type="button" class="rbim-btn-action" @click="openDetail(household.household_id)">
                                            View
                                        </button>
                                        <button
                                            v-if="canUpdate"
                                            type="button"
                                            class="rbim-btn-action"
                                            @click="startEdit(household)"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                                            </svg>
                                            Update
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredItems.length">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    {{ items.length ? 'No households match the current filters.' : 'No household records found.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
                        Fields marked with <span class="rbim-required">*</span> are required.
                        The household head cannot be changed here.
                    </p>
                </div>
                <form class="space-y-4 px-5 py-4" novalidate @submit.prevent="handleUpdate">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit_street_id" class="rbim-label">
                                Street<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="edit_street_id"
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
                            <label for="edit_house_lot" class="rbim-label">House/Lot Number</label>
                            <input
                                id="edit_house_lot"
                                v-model="editForm.house_lot"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.house_lot }"
                            >
                            <p v-if="editErrors.house_lot" class="rbim-error">{{ editErrors.house_lot }}</p>
                        </div>
                        <div>
                            <label for="edit_block_num" class="rbim-label">Block Number</label>
                            <input
                                id="edit_block_num"
                                v-model="editForm.block_num"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.block_num }"
                            >
                            <p v-if="editErrors.block_num" class="rbim-error">{{ editErrors.block_num }}</p>
                        </div>
                        <div>
                            <label for="edit_building_name" class="rbim-label">Building Name</label>
                            <input
                                id="edit_building_name"
                                v-model="editForm.building_name"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.building_name }"
                            >
                            <p v-if="editErrors.building_name" class="rbim-error">{{ editErrors.building_name }}</p>
                        </div>
                        <div>
                            <label for="edit_unit_num" class="rbim-label">Unit Number</label>
                            <input
                                id="edit_unit_num"
                                v-model="editForm.unit_num"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.unit_num }"
                            >
                            <p v-if="editErrors.unit_num" class="rbim-error">{{ editErrors.unit_num }}</p>
                        </div>
                        <div>
                            <label for="edit_household_status_id" class="rbim-label">
                                Household Status<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="edit_household_status_id"
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
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import { formatDate, matchesSearch } from '@/utils/format';
import { applyValidationErrors, optionalText, toId } from '@/utils/residentForm';

const router = useRouter();
const { hasPermission } = useAuth();
const { householdTabs } = useSectionTabs();

const items = ref([]);
const streets = ref([]);
const householdStatuses = ref([]);
const loading = ref(false);
const saving = ref(false);
const editing = ref(false);
const editingId = ref(null);
const error = ref('');
const successMessage = ref('');
const filters = reactive({
    search: '',
    street_id: '',
    household_status_id: '',
});
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

const filteredItems = computed(() => (
    items.value.filter((household) => (
        matchesSearch(household.street_name, filters.search)
        || matchesSearch(household.head_name, filters.search)
        || matchesSearch(household.household_id, filters.search)
    ))
));

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

function openDetail(id) {
    router.push({ name: 'household-detail', params: { id } });
}

function startEdit(household) {
    editing.value = true;
    editingId.value = household.household_id;
    Object.assign(editForm, {
        street_id: household.street_id ?? '',
        house_lot: household.house_lot ?? '',
        block_num: household.block_num ?? '',
        building_name: household.building_name ?? '',
        unit_num: household.unit_num ?? '',
        household_status_id: household.household_status_id ?? '',
    });
    clearEditErrors();
}

function cancelEdit() {
    editing.value = false;
    editingId.value = null;
    Object.assign(editForm, emptyEditForm());
    clearEditErrors();
}

async function loadLookups() {
    const [streetItems, statusItems] = await Promise.all([
        lookupService.fetchStreets(),
        lookupService.fetchLookup('household-status'),
    ]);

    streets.value = streetItems;
    householdStatuses.value = statusItems;
}

async function loadHouseholds() {
    loading.value = true;
    error.value = '';

    try {
        items.value = await householdService.fetchHouseholds({
            street_id: toId(filters.street_id) ?? undefined,
            household_status_id: toId(filters.household_status_id) ?? undefined,
        });
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load households.');
    } finally {
        loading.value = false;
    }
}

function clearFilters() {
    filters.search = '';
    filters.street_id = '';
    filters.household_status_id = '';
    loadHouseholds();
}

watch(
    () => [filters.street_id, filters.household_status_id],
    () => {
        loadHouseholds();
    },
);

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
        await householdService.updateHousehold(editingId.value, {
            street_id: toId(editForm.street_id),
            house_lot: optionalText(editForm.house_lot),
            block_num: optionalText(editForm.block_num),
            building_name: optionalText(editForm.building_name),
            unit_num: optionalText(editForm.unit_num),
            household_status_id: toId(editForm.household_status_id),
        });
        successMessage.value = 'Household updated successfully.';
        cancelEdit();
        await loadHouseholds();
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

onMounted(async () => {
    try {
        await loadLookups();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load household filters.');
    }

    await loadHouseholds();
});
</script>
