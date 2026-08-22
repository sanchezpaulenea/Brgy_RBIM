<template>
    <AppLayout title="System settings">
        <div class="space-y-6">
            <p class="text-sm text-slate-600">
                Values stored in the system settings table, including barangay profile, password rules, session timeout, and audit retention.
            </p>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading settings...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Setting</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Value</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                                <th v-if="canUpdateSettings" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="setting in settings" :key="setting.setting_id" class="align-top">
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ formatSettingLabel(setting.setting_key) }}
                                </td>
                                <td class="px-4 py-3">
                                    <template v-if="canUpdateSettings && editingId === setting.setting_id">
                                        <input
                                            v-model="editValues[setting.setting_id]"
                                            :type="setting.data_type === 'int' ? 'number' : 'text'"
                                            :inputmode="inputModeFor(setting)"
                                            :placeholder="placeholderFor(setting)"
                                            maxlength="45"
                                            class="rbim-input min-w-56 py-1.5"
                                            :class="{ 'rbim-input-error': editError }"
                                            @input="editError = ''"
                                        >
                                        <p v-if="editError" class="rbim-error">{{ editError }}</p>
                                        <p v-else-if="hintFor(setting)" class="rbim-hint">{{ hintFor(setting) }}</p>
                                    </template>
                                    <span v-else class="font-medium text-slate-900">
                                        {{ setting.typed_value ?? setting.setting_value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ setting.description }}</td>
                                <td v-if="canUpdateSettings" class="px-4 py-3 text-right">
                                    <div v-if="editingId === setting.setting_id" class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rbim-btn px-3 py-1.5 text-xs"
                                            :disabled="savingId === setting.setting_id"
                                            @click="saveSetting(setting)"
                                        >
                                            Save
                                        </button>
                                        <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" @click="cancelEdit">
                                            Cancel
                                        </button>
                                    </div>
                                    <button
                                        v-else
                                        type="button"
                                        class="rbim-btn-action"
                                        @click="startEdit(setting)"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                                        </svg>
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!settings.length">
                                <td :colspan="canUpdateSettings ? 4 : 3" class="px-4 py-8 text-center text-slate-500">
                                    No settings found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as settingService from '@/services/settingService';
import { formatSettingLabel } from '@/utils/format';
import { settingValueValidationError } from '@/utils/validation';

const SETTING_HINTS = {
    barangay_address: 'Include the barangay, city, and province, for example “Barangay Happy Hallow, Baguio City, Benguet”.',
    barangay_code: 'Philippine Standard Geographic Code (PSGC): exactly 10 digits, for example 1430300006.',
    barangay_contact_no: 'Mobile 09XXXXXXXXX or +639XXXXXXXXX, or landline with area code such as 074-123-4567.',
    barangay_email: 'A complete email address such as brgyhappyhallow@gmail.com.',
};

const NUMERIC_INPUT_MODES = {
    barangay_code: 'numeric',
    barangay_contact_no: 'tel',
    barangay_email: 'email',
};

const { hasPermission } = useAuth();

const settings = ref([]);
const loading = ref(false);
const savingId = ref(null);
const editingId = ref(null);
const editValues = reactive({});
const editError = ref('');
const error = ref('');
const successMessage = ref('');

const canUpdateSettings = computed(() => hasPermission('setting.update'));

function hintFor(setting) {
    return SETTING_HINTS[setting.setting_key] ?? '';
}

function placeholderFor(setting) {
    return setting.setting_key === 'barangay_code' ? '1430300006' : '';
}

function inputModeFor(setting) {
    return NUMERIC_INPUT_MODES[setting.setting_key] ?? 'text';
}

function startEdit(setting) {
    editingId.value = setting.setting_id;
    editValues[setting.setting_id] = setting.data_type === 'int'
        ? setting.typed_value
        : setting.setting_value;
    editError.value = '';
    error.value = '';
    successMessage.value = '';
}

function cancelEdit() {
    editingId.value = null;
    editError.value = '';
}

async function loadSettings() {
    loading.value = true;
    error.value = '';

    try {
        settings.value = await settingService.fetchSettings();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load settings.');
    } finally {
        loading.value = false;
    }
}

async function saveSetting(setting) {
    error.value = '';
    successMessage.value = '';

    const value = editValues[setting.setting_id];
    const validationError = settingValueValidationError(setting, value);

    if (validationError) {
        editError.value = validationError;

        return;
    }

    editError.value = '';
    savingId.value = setting.setting_id;

    try {
        const updated = await settingService.updateSetting(setting.setting_id, value);
        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));
        editingId.value = null;
        successMessage.value = `Updated "${formatSettingLabel(setting.setting_key)}" successfully.`;
    } catch (err) {
        editError.value = extractValidationErrors(err).setting_value ?? '';
        error.value = editError.value
            ? ''
            : extractErrorMessage(err, 'Unable to update setting.');
    } finally {
        savingId.value = null;
    }
}

onMounted(loadSettings);
</script>
