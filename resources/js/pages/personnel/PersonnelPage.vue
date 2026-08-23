<template>
    <AppLayout title="Barangay Personnel Management">
        <div class="space-y-6">
            <PageTabs :tabs="personnelTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <article v-if="canCreate" class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    {{ editingId ? 'Update personnel' : 'Create barangay personnel' }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Fields marked with <span class="rbim-required">*</span> are required.
                </p>
                <form class="mt-4 grid gap-4 sm:grid-cols-2" novalidate @submit.prevent="handleSave">
                    <div>
                        <label for="personnel_last_name" class="rbim-label">
                            Last name<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="personnel_last_name"
                            v-model="form.personnel_last_name"
                            type="text"
                            maxlength="45"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_last_name }"
                            @input="validateName('personnel_last_name', 'Last name', true)"
                            @blur="validateName('personnel_last_name', 'Last name', true)"
                        >
                        <p v-if="formErrors.personnel_last_name" class="rbim-error">{{ formErrors.personnel_last_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_first_name" class="rbim-label">
                            First name<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="personnel_first_name"
                            v-model="form.personnel_first_name"
                            type="text"
                            maxlength="45"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_first_name }"
                            @input="validateName('personnel_first_name', 'First name', true)"
                            @blur="validateName('personnel_first_name', 'First name', true)"
                        >
                        <p v-if="formErrors.personnel_first_name" class="rbim-error">{{ formErrors.personnel_first_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_middle_name" class="rbim-label">Middle name</label>
                        <input
                            id="personnel_middle_name"
                            v-model="form.personnel_middle_name"
                            type="text"
                            maxlength="45"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_middle_name }"
                            @input="validateName('personnel_middle_name', 'Middle name')"
                            @blur="validateName('personnel_middle_name', 'Middle name')"
                        >
                        <p v-if="formErrors.personnel_middle_name" class="rbim-error">{{ formErrors.personnel_middle_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_suffix" class="rbim-label">Suffix</label>
                        <input
                            id="personnel_suffix"
                            v-model="form.personnel_suffix"
                            type="text"
                            maxlength="10"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_suffix }"
                            @input="validateName('personnel_suffix', 'Suffix')"
                            @blur="validateName('personnel_suffix', 'Suffix')"
                        >
                        <p v-if="formErrors.personnel_suffix" class="rbim-error">{{ formErrors.personnel_suffix }}</p>
                    </div>
                    <BirthDateField
                        v-model="form.personnel_date_of_birth"
                        input-id="personnel_date_of_birth"
                        :max="maxBirthDate"
                        required
                        :error="formErrors.personnel_date_of_birth"
                        @update:model-value="clearDateOfBirthError"
                    />
                    <div class="sm:col-span-2">
                        <PositionCombobox
                            v-model="form.position_id"
                            v-model:query="form.position_name"
                            :options="positions"
                            :exclude-occupied-for-id="editingId"
                            :can-create="canCreatePosition && !editingId"
                            required
                            :hint="positionHint"
                            :error="formErrors.position_id || formErrors.position_name"
                        />
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2">
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : (editingId ? 'Update personnel' : 'Create personnel') }}
                        </button>
                        <button v-if="editingId" type="button" class="rbim-btn-outline" @click="resetForm">
                            Cancel
                        </button>
                    </div>
                </form>
            </article>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading personnel...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="sm:w-72">
                        <label for="personnel-filter-name" class="rbim-label">Search personnel name</label>
                        <input
                            id="personnel-filter-name"
                            v-model="filters.name"
                            type="search"
                            class="rbim-input py-2"
                            placeholder="Search personnel name"
                        >
                    </div>
                    <div class="sm:w-48">
                        <label for="personnel-filter-status" class="rbim-label">Personnel Status</label>
                        <select id="personnel-filter-status" v-model="filters.statusId" class="rbim-input py-2">
                            <option value="all">All statuses</option>
                            <option v-for="status in PERSONNEL_STATUSES" :key="status.id" :value="status.id">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Position</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Personnel Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Account</th>
                                <th v-if="canUpdate" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="person in filteredItems" :key="person.personnel_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ person.label }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ person.position_name }}</td>
                                <td class="px-4 py-3">
                                    <select
                                        v-if="canUpdate"
                                        :value="person.personnel_status_id"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs outline-none focus:border-brand"
                                        :disabled="updatingStatusId === person.personnel_id"
                                        @change="handleStatusChange(person, $event)"
                                    >
                                        <option v-for="status in PERSONNEL_STATUSES" :key="status.id" :value="status.id">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <span v-else class="text-slate-600">{{ statusLabel(person.personnel_status_id) }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ person.username || 'No account' }}</td>
                                <td v-if="canUpdate" class="px-4 py-3 text-right">
                                    <button type="button" class="rbim-btn-action" @click="startEdit(person)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                                        </svg>
                                        Update
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredItems.length">
                                <td :colspan="canUpdate ? 5 : 4" class="px-4 py-8 text-center text-slate-500">
                                    {{ items.length ? 'No personnel match the current filters.' : 'No personnel records found.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import BirthDateField from '@/components/BirthDateField.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import PositionCombobox from '@/components/PositionCombobox.vue';
import { PERSONNEL_STATUSES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as personnelService from '@/services/personnelService';
import { todayDate } from '@/utils/format';
import { personnelNameValidationError, positionNameValidationError } from '@/utils/validation';

const emptyForm = () => ({
    personnel_last_name: '',
    personnel_first_name: '',
    personnel_middle_name: '',
    personnel_suffix: '',
    personnel_date_of_birth: '',
    personnel_status_id: 1,
    position_id: '',
    position_name: '',
});

const { hasPermission } = useAuth();
const { personnelTabs } = useSectionTabs();

const items = ref([]);
const positions = ref([]);
const loading = ref(false);
const saving = ref(false);
const updatingStatusId = ref(null);
const editingId = ref(null);
const error = ref('');
const successMessage = ref('');
const form = reactive(emptyForm());
const formErrors = reactive({});
const filters = reactive({ name: '', statusId: 'all' });
const maxBirthDate = todayDate();
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
});

const canCreate = computed(() => hasPermission('personnel.create'));
const canUpdate = computed(() => hasPermission('personnel.update'));
const canCreatePosition = computed(() => hasPermission('pposition.create'));

const positionHint = computed(() => (
    canCreatePosition.value && !editingId.value
        ? 'Not in the list? Type the position name and press Enter to create it here.'
        : ''
));

const filteredItems = computed(() => {
    const term = filters.name.trim().toLowerCase();

    return items.value.filter((person) => {
        const matchesName = !term || String(person.label ?? '').toLowerCase().includes(term);
        const matchesStatus = filters.statusId === 'all'
            || Number(person.personnel_status_id) === Number(filters.statusId);

        return matchesName && matchesStatus;
    });
});

function statusLabel(statusId) {
    return PERSONNEL_STATUSES.find((status) => status.id === Number(statusId))?.label ?? '—';
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

function clearFormErrors() {
    Object.keys(formErrors).forEach((key) => {
        delete formErrors[key];
    });
}

function clearDateOfBirthError() {
    delete formErrors.personnel_date_of_birth;
}

function validateName(field, label, required = false) {
    const message = personnelNameValidationError(form[field], label, required);

    if (message) {
        formErrors[field] = message;

        return;
    }

    delete formErrors[field];
}

function resetForm() {
    editingId.value = null;
    Object.assign(form, emptyForm());
    clearFormErrors();
}

function startEdit(person) {
    editingId.value = person.personnel_id;
    Object.assign(form, {
        personnel_last_name: person.personnel_last_name,
        personnel_first_name: person.personnel_first_name,
        personnel_middle_name: person.personnel_middle_name ?? '',
        personnel_suffix: person.personnel_suffix ?? '',
        personnel_date_of_birth: person.personnel_date_of_birth?.slice?.(0, 10) ?? '',
        personnel_status_id: person.personnel_status_id ?? 1,
        position_id: person.position_id ?? '',
        position_name: person.position_name ?? '',
    });
    clearFormErrors();
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const [personnel, positionItems] = await Promise.all([
            personnelService.fetchPersonnel(),
            lookupService.fetchPersonnelPositions(),
        ]);
        items.value = personnel;
        positions.value = positionItems;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load personnel.');
    } finally {
        loading.value = false;
    }
}

function buildPayload(confirmDuplicate = false) {
    const matched = positions.value.find((position) => (
        Number(position.id) === Number(form.position_id)
        || position.label.toLowerCase() === form.position_name.trim().toLowerCase()
    ));

    const payload = {
        personnel_last_name: form.personnel_last_name.trim(),
        personnel_first_name: form.personnel_first_name.trim(),
        personnel_middle_name: form.personnel_middle_name.trim(),
        personnel_suffix: form.personnel_suffix.trim(),
        personnel_date_of_birth: form.personnel_date_of_birth,
        confirm_duplicate: confirmDuplicate,
    };

    if (editingId.value) {
        payload.personnel_status_id = Number(form.personnel_status_id);
        payload.position_id = matched ? Number(matched.id) : Number(form.position_id);
    } else if (matched) {
        payload.position_id = Number(matched.id);
        payload.personnel_status_id = 1;
    } else {
        payload.position_name = form.position_name.trim();
        payload.personnel_status_id = 1;
    }

    return payload;
}

async function savePersonnel(confirmDuplicate = false) {
    saving.value = true;
    error.value = '';
    successMessage.value = '';
    clearFormErrors();

    const payload = buildPayload(confirmDuplicate);

    try {
        if (editingId.value) {
            const allowed = await askConfirm({
                title: 'Update personnel',
                message: 'Save these changes to this barangay personnel record?',
                confirmLabel: 'Save changes',
            });

            if (!allowed) {
                return;
            }

            await personnelService.updatePersonnel(editingId.value, payload);
            successMessage.value = 'Personnel updated successfully.';
        } else {
            await personnelService.createPersonnel(payload);
            successMessage.value = 'Personnel created successfully.';
        }

        resetForm();
        await load();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (validationErrors.duplicate && !confirmDuplicate) {
            const proceed = await askConfirm({
                title: 'Possible duplicate',
                message: validationErrors.duplicate,
                confirmLabel: 'Save anyway',
                variant: 'danger',
            });

            if (proceed) {
                await savePersonnel(true);
            }

            return;
        }

        Object.assign(formErrors, validationErrors);
        error.value = Object.keys(validationErrors).length
            ? ''
            : extractErrorMessage(err, 'Unable to save personnel.');
    } finally {
        saving.value = false;
    }
}

/**
 * Warns about an existing record with the same name before the record is sent,
 * so the confirmation appears even when the server has no duplicate rule for
 * the combination entered.
 */
function duplicateNameMatch() {
    const last = form.personnel_last_name.trim().toLowerCase();
    const first = form.personnel_first_name.trim().toLowerCase();

    if (!last || !first) {
        return null;
    }

    return items.value.find((person) => (
        person.personnel_id !== editingId.value
        && String(person.personnel_last_name ?? '').trim().toLowerCase() === last
        && String(person.personnel_first_name ?? '').trim().toLowerCase() === first
    )) ?? null;
}

async function handleSave() {
    clearFormErrors();

    validateName('personnel_last_name', 'Last name', true);
    validateName('personnel_first_name', 'First name', true);
    validateName('personnel_middle_name', 'Middle name');
    validateName('personnel_suffix', 'Suffix');

    if (!form.personnel_date_of_birth) {
        formErrors.personnel_date_of_birth = 'Date of birth is required.';
    }

    if (!form.position_id && !form.position_name.trim()) {
        formErrors.position_name = 'Position is required.';
    }

    if (Object.keys(formErrors).length) {
        return;
    }

    const typedName = form.position_name.trim();
    const matched = positions.value.find((position) => (
        Number(position.id) === Number(form.position_id)
        || position.label.toLowerCase() === typedName.toLowerCase()
    ));

    if (!editingId.value && !matched && typedName && canCreatePosition.value) {
        const validationError = positionNameValidationError(typedName);

        if (validationError) {
            formErrors.position_name = validationError;

            return;
        }

        const allowed = await askConfirm({
            title: 'Add new position',
            message: `“${typedName}” is not in the list. Add it as a new personnel position?`,
            confirmLabel: 'Add position',
        });

        if (!allowed) {
            return;
        }
    }

    const duplicate = duplicateNameMatch();

    if (duplicate) {
        const proceed = await askConfirm({
            title: 'Personnel with the same name exists',
            message: `“${duplicate.label}” is already recorded as ${duplicate.position_name || 'a barangay personnel'}. Save this record anyway?`,
            confirmLabel: 'Save anyway',
            variant: 'danger',
        });

        if (!proceed) {
            return;
        }

        await savePersonnel(true);

        return;
    }

    await savePersonnel(false);
}

async function handleStatusChange(person, event) {
    const select = event.target;
    const nextStatusId = Number(select.value);

    if (!nextStatusId || nextStatusId === Number(person.personnel_status_id)) {
        select.value = person.personnel_status_id;

        return;
    }

    const allowed = await askConfirm({
        title: 'Update personnel status',
        message: `Set "${person.label}" to ${statusLabel(nextStatusId)}?`,
        confirmLabel: 'Update status',
    });

    if (!allowed) {
        select.value = person.personnel_status_id;

        return;
    }

    updatingStatusId.value = person.personnel_id;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await personnelService.updatePersonnel(person.personnel_id, {
            personnel_last_name: person.personnel_last_name,
            personnel_first_name: person.personnel_first_name,
            personnel_middle_name: person.personnel_middle_name ?? '',
            personnel_suffix: person.personnel_suffix ?? '',
            personnel_date_of_birth: person.personnel_date_of_birth?.slice?.(0, 10) ?? '',
            position_id: Number(person.position_id),
            personnel_status_id: nextStatusId,
            confirm_duplicate: true,
        });

        items.value = items.value.map((row) => (
            row.personnel_id === updated.personnel_id ? updated : row
        ));
        successMessage.value = `Updated status for "${person.label}" to ${statusLabel(nextStatusId)}.`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update personnel status.');
        select.value = person.personnel_status_id;
    } finally {
        updatingStatusId.value = null;
    }
}

onMounted(load);
</script>
