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
                            maxlength="100"
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
                            maxlength="100"
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
                            maxlength="100"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_middle_name }"
                            @input="validateName('personnel_middle_name', 'Middle name')"
                            @blur="validateName('personnel_middle_name', 'Middle name')"
                        >
                        <p v-if="formErrors.personnel_middle_name" class="rbim-error">{{ formErrors.personnel_middle_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_suffix" class="rbim-label">
                            Suffix<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="personnel_suffix"
                            v-model="form.personnel_suffix_id"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_suffix_id }"
                            @change="formErrors.personnel_suffix_id = ''"
                        >
                            <option value="">Select suffix</option>
                            <option v-for="suffix in suffixes" :key="suffix.id" :value="suffix.id">
                                {{ suffix.label }}
                            </option>
                        </select>
                        <p v-if="formErrors.personnel_suffix_id" class="rbim-error">{{ formErrors.personnel_suffix_id }}</p>
                    </div>
                    <BirthDateField
                        v-model="form.personnel_date_of_birth"
                        input-id="personnel_date_of_birth"
                        :max="maxBirthDate"
                        required
                        :error="formErrors.personnel_date_of_birth"
                        @update:model-value="clearDateOfBirthError"
                    />
                    <div>
                        <label for="personnel_sex" class="rbim-label">Gender<span class="rbim-required">*</span></label>
                        <select
                            id="personnel_sex"
                            v-model="form.sex_id"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.sex_id }"
                            @change="delete formErrors.sex_id"
                        >
                            <option value="" disabled>Select gender</option>
                            <option v-for="option in SEX_OPTIONS" :key="option.id" :value="option.id">
                                {{ option.label }}
                            </option>
                        </select>
                        <p v-if="formErrors.sex_id" class="rbim-error">{{ formErrors.sex_id }}</p>
                    </div>
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
                            @update:model-value="clearPositionErrors"
                            @update:query="clearPositionErrors"
                        />
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
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
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-end">
                    <div class="sm:w-72">
                        <label for="personnel-filter-name" class="rbim-label">Search personnel name</label>
                        <input
                            id="personnel-filter-name"
                            v-model="filters.name"
                            type="search"
                            class="rbim-input py-2"
                            placeholder="Search personnel name"
                            @keydown.enter.prevent
                        >
                    </div>
                    <div class="sm:w-48">
                        <label for="personnel-filter-status" class="rbim-label">Personnel Status</label>
                        <select id="personnel-filter-status" v-model="filters.statusId" class="rbim-input rbim-filter py-2">
                            <option value="all">All statuses</option>
                            <option v-for="status in PERSONNEL_STATUSES" :key="status.id" :value="status.id">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="w-full overflow-x-auto" role="table">
                    <div
                        class="grid w-full min-w-[48rem] items-center gap-x-[7.5rem] bg-slate-50 px-4 text-sm font-semibold text-slate-600"
                        :style="{ gridTemplateColumns: personnelColumnTemplate }"
                        role="row"
                    >
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Name</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Position</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Personnel Status</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Account</div>
                        <div v-if="canUpdate" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Action</div>
                    </div>
                    <div
                        v-for="person in filteredItems"
                        :key="person.personnel_id"
                        class="grid w-full min-w-[48rem] items-center gap-x-[7.5rem] border-t border-slate-100 px-4 text-sm"
                        :style="{ gridTemplateColumns: personnelColumnTemplate }"
                        role="row"
                    >
                        <div class="mx-auto w-[58%] translate-x-[2rem] break-words py-3 text-left font-medium text-slate-900" role="cell">{{ person.label }}</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] break-words py-3 text-left text-slate-600" role="cell">{{ person.position_name }}</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="cell">
                            <select
                                v-if="canUpdate"
                                :value="pendingStatus[person.personnel_id] ?? person.personnel_status_id"
                                class="max-w-full rounded-lg border border-slate-300 px-2 py-1 text-xs outline-none focus:border-brand"
                                :disabled="updatingStatusId === person.personnel_id"
                                @change="handleStatusChange(person, $event)"
                            >
                                <option v-for="status in PERSONNEL_STATUSES" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                            <span v-else class="text-slate-600">{{ statusLabel(person.personnel_status_id) }}</span>
                        </div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] break-words py-3 text-left text-slate-600" role="cell">{{ person.username || 'No account' }}</div>
                        <div v-if="canUpdate" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="cell">
                            <button type="button" class="rbim-btn-action" @click="startEdit(person)">
                                Update
                            </button>
                        </div>
                    </div>
                    <div v-if="!filteredItems.length" class="border-t border-slate-100 px-4 py-8 text-center text-sm text-slate-500">
                        {{ items.length ? 'No personnel match the current filters.' : 'No personnel records found.' }}
                    </div>
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
        <PasswordConfirmDialog
            :open="passwordPrompt.open"
            :title="passwordPrompt.title"
            :message="passwordPrompt.message"
            :confirm-label="passwordPrompt.confirmLabel"
            :variant="passwordPrompt.variant"
            :error="passwordPrompt.error"
            :submitting="passwordPrompt.submitting"
            @confirm="confirmAdministratorPassword"
            @cancel="cancelAdministratorPassword"
        />
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import BirthDateField from '@/components/BirthDateField.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PasswordConfirmDialog from '@/components/PasswordConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAdministratorPasswordConfirm } from '@/composables/useAdministratorPasswordConfirm';
import PositionCombobox from '@/components/PositionCombobox.vue';
import { PERSONNEL_STATUSES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as personnelService from '@/services/personnelService';
import { todayDate, matchesSearch } from '@/utils/format';
import { personnelNameValidationError, positionNameValidationError } from '@/utils/validation';

const SEX_OPTIONS = [
    { id: 1, label: 'Male' },
    { id: 2, label: 'Female' },
];

const emptyForm = () => ({
    personnel_last_name: '',
    personnel_first_name: '',
    personnel_middle_name: '',
    personnel_suffix_id: '',
    personnel_date_of_birth: '',
    sex_id: '',
    personnel_status_id: 1,
    position_id: '',
    position_name: '',
});

const { hasPermission } = useAuth();
const { personnelTabs } = useSectionTabs();
const {
    passwordPrompt,
    askAdministratorPassword,
    confirmAdministratorPassword,
    cancelAdministratorPassword,
} = useAdministratorPasswordConfirm();

const items = ref([]);
const positions = ref([]);
const suffixes = ref([]);
const loading = ref(false);
const saving = ref(false);
const updatingStatusId = ref(null);
const pendingStatus = reactive({});
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
const personnelColumnTemplate = computed(() => (
    canUpdate.value ? 'repeat(5, minmax(0, 1fr))' : 'repeat(4, minmax(0, 1fr))'
));
const canCreatePosition = computed(() => hasPermission('pposition.create'));

const positionHint = computed(() => (
    canCreatePosition.value && !editingId.value
        ? 'Not in the list? Type the position name and press Enter to create it here.'
        : ''
));

const filteredItems = computed(() => (
    items.value.filter((person) => {
        const matchesName = matchesSearch(person.label, filters.name)
            || matchesSearch(person.position_name, filters.name)
            || matchesSearch(person.username, filters.name);
        const matchesStatus = filters.statusId === 'all'
            || Number(person.personnel_status_id) === Number(filters.statusId);

        return matchesName && matchesStatus;
    })
));

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

function clearPositionErrors() {
    formErrors.position_id = '';
    formErrors.position_name = '';
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
        personnel_suffix_id: person.personnel_suffix_id ?? '',
        personnel_date_of_birth: person.personnel_date_of_birth?.slice?.(0, 10) ?? '',
        sex_id: person.sex_id ?? '',
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
        const [personnel, positionItems, suffixItems] = await Promise.all([
            personnelService.fetchPersonnel(),
            lookupService.fetchPersonnelPositions(),
            lookupService.fetchLookup('suffix'),
        ]);
        items.value = personnel;
        positions.value = positionItems;
        suffixes.value = suffixItems;
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
        personnel_suffix_id: Number(form.personnel_suffix_id),
        personnel_date_of_birth: form.personnel_date_of_birth,
        sex_id: Number(form.sex_id),
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
            const fullName = [form.personnel_first_name.trim(), form.personnel_last_name.trim()]
                .filter(Boolean)
                .join(' ');
            const allowed = await askConfirm({
                title: 'Create personnel',
                message: `Create personnel record for ${fullName}?`,
                confirmLabel: 'Create',
            });

            if (!allowed) {
                return;
            }

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
                variant: 'warning',
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

    if (!form.personnel_suffix_id) {
        formErrors.personnel_suffix_id = 'Suffix is required.';
    }

    if (!form.personnel_date_of_birth) {
        formErrors.personnel_date_of_birth = 'Date of birth is required.';
    }

    if (!form.sex_id) {
        formErrors.sex_id = 'Gender is required.';
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
            variant: 'warning',
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
    const nextStatusId = Number(event.target.value);

    if (!nextStatusId || nextStatusId === Number(person.personnel_status_id)) {
        delete pendingStatus[person.personnel_id];

        return;
    }

    pendingStatus[person.personnel_id] = nextStatusId;
    updatingStatusId.value = person.personnel_id;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await askAdministratorPassword({
            title: 'Update personnel status',
            message: `Set "${person.label}" to ${statusLabel(nextStatusId)}. Enter your Admin or Super Admin password to save this change.`,
            confirmLabel: 'Update status',
            run: (password) => personnelService.updatePersonnel(person.personnel_id, {
                personnel_last_name: person.personnel_last_name,
                personnel_first_name: person.personnel_first_name,
                personnel_middle_name: person.personnel_middle_name ?? '',
                personnel_suffix_id: person.personnel_suffix_id,
                personnel_date_of_birth: person.personnel_date_of_birth?.slice?.(0, 10) ?? '',
                ...(person.sex_id ? { sex_id: Number(person.sex_id) } : {}),
                position_id: Number(person.position_id),
                personnel_status_id: nextStatusId,
                confirm_duplicate: true,
                admin_password: password,
            }),
        });

        if (!updated) {
            delete pendingStatus[person.personnel_id];

            return;
        }

        items.value = items.value.map((row) => (
            row.personnel_id === updated.personnel_id ? updated : row
        ));

        if (editingId.value === updated.personnel_id) {
            form.personnel_status_id = updated.personnel_status_id;
        }

        delete pendingStatus[person.personnel_id];
        successMessage.value = `Updated status for "${person.label}" to ${statusLabel(nextStatusId)}.`;
    } catch (err) {
        delete pendingStatus[person.personnel_id];
        error.value = extractErrorMessage(err, 'Unable to update personnel status.');
    } finally {
        updatingStatusId.value = null;
    }
}

onMounted(load);
</script>
