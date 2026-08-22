<template>
    <AppLayout title="Barangay Personnel Management">
        <div class="space-y-6">
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
                <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="handleSave">
                    <div>
                        <label for="personnel_last_name" class="rbim-label">Last name</label>
                        <input id="personnel_last_name" v-model="form.personnel_last_name" type="text" maxlength="45" required class="rbim-input" :class="{ 'rbim-input-error': formErrors.personnel_last_name }">
                        <p v-if="formErrors.personnel_last_name" class="rbim-error">{{ formErrors.personnel_last_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_first_name" class="rbim-label">First name</label>
                        <input id="personnel_first_name" v-model="form.personnel_first_name" type="text" maxlength="45" required class="rbim-input" :class="{ 'rbim-input-error': formErrors.personnel_first_name }">
                        <p v-if="formErrors.personnel_first_name" class="rbim-error">{{ formErrors.personnel_first_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_middle_name" class="rbim-label">Middle name</label>
                        <input id="personnel_middle_name" v-model="form.personnel_middle_name" type="text" maxlength="45" class="rbim-input" :class="{ 'rbim-input-error': formErrors.personnel_middle_name }">
                        <p v-if="formErrors.personnel_middle_name" class="rbim-error">{{ formErrors.personnel_middle_name }}</p>
                    </div>
                    <div>
                        <label for="personnel_suffix" class="rbim-label">Suffix</label>
                        <input id="personnel_suffix" v-model="form.personnel_suffix" type="text" maxlength="10" class="rbim-input" :class="{ 'rbim-input-error': formErrors.personnel_suffix }">
                        <p v-if="formErrors.personnel_suffix" class="rbim-error">{{ formErrors.personnel_suffix }}</p>
                    </div>
                    <div>
                        <label for="personnel_date_of_birth" class="rbim-label">Date of birth</label>
                        <input
                            id="personnel_date_of_birth"
                            v-model="form.personnel_date_of_birth"
                            type="date"
                            required
                            :max="maxBirthDate"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': formErrors.personnel_date_of_birth }"
                        >
                        <p v-if="formErrors.personnel_date_of_birth" class="rbim-error">{{ formErrors.personnel_date_of_birth }}</p>
                    </div>
                    <div v-if="editingId">
                        <label for="personnel_status_id" class="rbim-label">Status</label>
                        <select id="personnel_status_id" v-model="form.personnel_status_id" class="rbim-input">
                            <option v-for="status in PERSONNEL_STATUSES" :key="status.id" :value="status.id">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <PositionCombobox
                            v-model="form.position_id"
                            v-model:query="form.position_name"
                            :options="positions"
                            :exclude-occupied-for-id="editingId"
                            :can-create="canCreatePosition && !editingId"
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Position</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Account</th>
                                <th v-if="canUpdate" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="person in items" :key="person.personnel_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ person.label }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ person.position_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ person.username || 'No account' }}</td>
                                <td v-if="canUpdate" class="px-4 py-3 text-right">
                                    <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" @click="startEdit(person)">
                                        Update
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td :colspan="canUpdate ? 4 : 3" class="px-4 py-8 text-center text-slate-500">
                                    No personnel records found.
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
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PositionCombobox from '@/components/PositionCombobox.vue';
import { PERSONNEL_STATUSES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
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

const items = ref([]);
const positions = ref([]);
const loading = ref(false);
const saving = ref(false);
const editingId = ref(null);
const error = ref('');
const successMessage = ref('');
const form = reactive(emptyForm());
const formErrors = reactive({});
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

async function handleSave() {
    clearFormErrors();

    const nameErrors = {
        personnel_last_name: personnelNameValidationError(form.personnel_last_name, 'Last name', true),
        personnel_first_name: personnelNameValidationError(form.personnel_first_name, 'First name', true),
        personnel_middle_name: personnelNameValidationError(form.personnel_middle_name, 'Middle name'),
        personnel_suffix: personnelNameValidationError(form.personnel_suffix, 'Suffix'),
    };

    Object.entries(nameErrors).forEach(([field, message]) => {
        if (message) {
            formErrors[field] = message;
        }
    });

    if (Object.values(nameErrors).some(Boolean)) {
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
            clearFormErrors();
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

    await savePersonnel(false);
}

onMounted(load);
</script>
