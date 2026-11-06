<template>
    <AppLayout title="System settings">
        <div class="space-y-6">

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading settings...
            </div>
            <div v-else-if="!settings.length" class="rbim-card p-8 text-center text-sm text-slate-500">
                No settings found.
            </div>
            <section v-for="group in settingGroups" :key="group.title" class="rbim-card overflow-hidden">
                <header class="border-b border-slate-200 bg-slate-50 py-3 pl-16 pr-4">
                    <h2 class="text-sm font-semibold text-slate-900">{{ group.title }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ group.description }}</p>
                </header>
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left font-semibold text-slate-600">
                            <tr>
                                <th class="whitespace-nowrap py-3 pl-16 pr-4">Setting</th>
                                <th class="whitespace-nowrap px-4 py-3">Value</th>
                                <th class="whitespace-nowrap px-4 py-3">Description</th>
                                <th v-if="canUpdateSettings" class="whitespace-nowrap px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="setting in group.settings" :key="setting.setting_id">
                                <td class="whitespace-nowrap py-3 pl-16 pr-4 font-medium text-slate-900">
                                    {{ formatSettingLabel(setting.setting_key) }}
                                </td>
                                <td class="px-4 py-3">
                                    <template v-if="setting.setting_key === 'punong_barangay_signature'" />
                                    <template v-else-if="canUpdateSettings && editingId === setting.setting_id">
                                        <input
                                            v-model="editValues[setting.setting_id]"
                                            :type="setting.data_type === 'int' ? 'number' : 'text'"
                                            :inputmode="inputModeFor(setting)"
                                            :placeholder="placeholderFor(setting)"
                                            :min="rangeFor(setting)?.min"
                                            :max="rangeFor(setting)?.max"
                                            maxlength="45"
                                            class="rbim-input py-1.5"
                                            :class="{ 'rbim-input-error': editError }"
                                            @input="editError = ''"
                                        >
                                        <p v-if="editError" class="rbim-error">{{ editError }}</p>
                                        <p v-else-if="hintFor(setting)" class="rbim-hint">{{ hintFor(setting) }}</p>
                                    </template>
                                    <template v-else>
                                        <span class="whitespace-nowrap font-medium text-slate-900">
                                            {{ setting.typed_value ?? setting.setting_value }}
                                        </span>
                                        <p v-if="isRetentionSetting(setting)" class="rbim-hint whitespace-nowrap">
                                            Changed on Log Retention and Archiving.
                                        </p>
                                    </template>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                    {{ setting.description }}
                                </td>
                                <td v-if="canUpdateSettings" class="whitespace-nowrap px-4 py-3">
                                    <div class="flex justify-center">
                                        <div v-if="setting.setting_key === 'punong_barangay_signature'" class="flex flex-col items-center gap-2">
                                            <input
                                                id="punong-barangay-signature"
                                                type="file"
                                                accept="image/png"
                                                class="sr-only"
                                                :disabled="signatureUploading"
                                                @change="uploadSignature"
                                            >
                                            <label
                                                for="punong-barangay-signature"
                                                class="inline-flex h-9 w-[5.75rem] cursor-pointer items-center justify-center rounded-lg border border-brand bg-brand text-sm font-semibold text-white transition hover:bg-brand-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
                                                :class="signatureUploading ? 'pointer-events-none opacity-60' : ''"
                                            >
                                                {{ signatureUploading ? 'Uploading...' : 'Upload' }}
                                            </label>
                                            <button
                                                v-if="setting.setting_value"
                                                type="button"
                                                class="inline-flex h-9 w-[5.75rem] cursor-pointer items-center justify-center rounded-lg border border-red-500 bg-white text-sm font-medium text-red-600 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                                                :disabled="signatureUploading"
                                                @click="confirmRemoveSignature = true"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                        <p v-else-if="isRetentionSetting(setting)" class="whitespace-nowrap text-xs text-slate-500">
                                            Read only here.
                                        </p>
                                        <div v-else-if="editingId === setting.setting_id" class="flex flex-wrap gap-2">
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
                                            class="inline-flex h-9 w-[4.75rem] cursor-pointer items-center justify-center rounded-lg border border-brand bg-white text-sm font-medium text-brand transition hover:bg-brand-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
                                            @click="startEdit(setting)"
                                        >
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!group.settings.length">
                                <td :colspan="canUpdateSettings ? 4 : 3" class="px-4 py-8 text-center text-slate-500">
                                    No settings in this category.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <ConfirmDialog
                :open="confirmRemoveSignature"
                title="Remove Punong Barangay signature"
                message="The signature image will be removed. Certificates can still be issued with a blank signature line."
                confirm-label="Remove"
                variant="danger"
                @cancel="confirmRemoveSignature = false"
                @confirm="removeSignature"
            />
            <PasswordConfirmDialog
                :open="passwordPrompt.open"
                :title="passwordPrompt.title"
                :message="passwordPrompt.message"
                :confirm-label="passwordPrompt.confirmLabel"
                :error="passwordPrompt.error"
                :submitting="passwordPrompt.submitting"
                @confirm="confirmAdministratorPassword"
                @cancel="cancelAdministratorPassword"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PasswordConfirmDialog from '@/components/PasswordConfirmDialog.vue';
import { useAdministratorPasswordConfirm } from '@/composables/useAdministratorPasswordConfirm';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as settingService from '@/services/settingService';
import { formatSettingLabel } from '@/utils/format';
import { SETTING_INT_RANGES, settingValueValidationError } from '@/utils/validation';

const SETTING_HINTS = {
    barangay_name: 'The official barangay name, for example “Barangay Happy Hallow”.',
    barangay_address: 'Include the barangay, city, and province, for example “Barangay Happy Hallow, Baguio City, Benguet”.',
    city_name: 'The city or municipality the barangay belongs to, for example “Baguio City”.',
    barangay_code: 'Philippine Standard Geographic Code (PSGC): exactly 10 digits, for example 1430300006.',
    barangay_contact_no: 'Mobile 09XXXXXXXXX or +639XXXXXXXXX, or landline with area code such as 074-123-4567.',
    barangay_email: 'A complete email address such as brgyhappyhallow@gmail.com.',
    default_password: 'At least 8 characters with one uppercase letter, one lowercase letter, and one number, and no spaces.',
    password_min_length: 'Whole number between 8 and 32 characters.',
    max_login_attempts: 'Whole number between 3 and 10 failed attempts before lockout.',
    account_lockout_minutes: 'Whole number between 1 and 1440 minutes (24 hours).',
    session_timeout_minutes: 'Whole number between 5 and 480 minutes (8 hours).',
    audit_log_retention_days: 'Whole number between 30 and 3650 days (10 years).',
    punong_barangay_name: 'Letters, spaces, periods, hyphens, and apostrophes only. Example: Soriano A. Palunan.',
};

const SETTING_PLACEHOLDERS = {
    barangay_name: 'Barangay Happy Hallow',
    barangay_address: 'Barangay Happy Hallow, Baguio City, Benguet',
    city_name: 'Baguio City',
    barangay_code: '1430300006',
    barangay_contact_no: '09123456789',
    barangay_email: 'brgyhappyhallow@gmail.com',
    default_password: 'Temp12345',
    punong_barangay_name: 'Soriano A. Palunan',
};

const NUMERIC_INPUT_MODES = {
    barangay_code: 'numeric',
    barangay_contact_no: 'tel',
    barangay_email: 'email',
};

/**
 * The settings table stores every key in one flat list; the page presents them
 * as three categories so barangay profile, security, and audit values are not
 * mixed together.
 */
const SETTING_GROUPS = [
    {
        title: 'Barangay Information',
        description: 'Official identity and contact details of the barangay.',
        keys: [
            'barangay_name',
            'barangay_address',
            'city_name',
            'barangay_code',
            'barangay_contact_no',
            'barangay_email',
            'punong_barangay_name',
            'punong_barangay_signature',
        ],
    },
    {
        title: 'Account and Security Settings',
        description: 'Password rules and sign-in protection for user accounts.',
        keys: [
            'password_min_length',
            'max_login_attempts',
            'account_lockout_minutes',
            'default_password',
        ],
    },
    {
        title: 'Session and Audit Settings',
        description: 'Idle session handling. Retention periods are changed on Log Retention and Archiving.',
        keys: [
            'session_timeout_minutes',
            'audit_log_retention_days',
            'user_log_retention_days',
        ],
    },
];

const { hasPermission, isSuperAdmin } = useAuth();
const {
    passwordPrompt,
    askAdministratorPassword,
    confirmAdministratorPassword,
    cancelAdministratorPassword,
} = useAdministratorPasswordConfirm();

const settings = ref([]);
const loading = ref(false);
const savingId = ref(null);
const editingId = ref(null);
const editValues = reactive({});
const editError = ref('');
const error = ref('');
const successMessage = ref('');
const signatureUploading = ref(false);
const confirmRemoveSignature = ref(false);

const canUpdateSettings = computed(() => (
    isSuperAdmin.value && hasPermission('setting.update')
));
const settingGroups = computed(() => {
    if (loading.value || !settings.value.length) {
        return [];
    }

    const grouped = SETTING_GROUPS.map((group) => ({
        title: group.title,
        description: group.description,
        settings: group.keys
            .map((key) => settings.value.find((setting) => setting.setting_key === key))
            .filter(Boolean),
    }));

    const categorized = new Set(SETTING_GROUPS.flatMap((group) => group.keys));
    const others = settings.value.filter((setting) => !categorized.has(setting.setting_key));

    if (others.length) {
        grouped.push({
            title: 'Other Settings',
            description: 'Values that do not belong to the categories above.',
            settings: others,
        });
    }

    return grouped.filter((group) => group.settings.length);
});

const RETENTION_SETTING_KEYS = ['audit_log_retention_days', 'user_log_retention_days'];
const ACCOUNT_SECURITY_SETTING_KEYS = SETTING_GROUPS.find((group) => group.title === 'Account and Security Settings')?.keys ?? [];

function isRetentionSetting(setting) {
    return RETENTION_SETTING_KEYS.includes(setting.setting_key);
}

function rangeFor(setting) {
    return SETTING_INT_RANGES[setting.setting_key] ?? null;
}

function hintFor(setting) {
    return SETTING_HINTS[setting.setting_key] ?? '';
}

function placeholderFor(setting) {
    return SETTING_PLACEHOLDERS[setting.setting_key] ?? '';
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

    if (ACCOUNT_SECURITY_SETTING_KEYS.includes(setting.setting_key)) {
        const current = setting.data_type === 'int' ? setting.typed_value : setting.setting_value;

        if (String(value) === String(current ?? '')) {
            editingId.value = null;
            editError.value = '';

            return;
        }
    }

    editError.value = '';
    savingId.value = setting.setting_id;

    try {
        const updated = ACCOUNT_SECURITY_SETTING_KEYS.includes(setting.setting_key)
            ? await askAdministratorPassword({
                title: 'Update account and security setting',
                message: `Save the new ${formatSettingLabel(setting.setting_key)} value. Enter your Admin or Super Admin password to continue.`,
                confirmLabel: 'Save',
                run: (password) => settingService.updateSetting(setting.setting_id, value, password),
            })
            : await settingService.updateSetting(setting.setting_id, value);

        if (!updated) {
            return;
        }

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

async function uploadSignature(event) {
    const file = event.target.files?.[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    signatureUploading.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await settingService.uploadPunongBarangaySignature(file);
        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));
        successMessage.value = 'Punong Barangay signature uploaded.';
    } catch (err) {
        error.value = extractValidationErrors(err).signature
            ?? extractErrorMessage(err, 'Unable to upload the Punong Barangay signature.');
    } finally {
        signatureUploading.value = false;
    }
}

async function removeSignature() {
    confirmRemoveSignature.value = false;
    signatureUploading.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await settingService.removePunongBarangaySignature();
        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));
        successMessage.value = 'Punong Barangay signature removed.';
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to remove the Punong Barangay signature.');
    } finally {
        signatureUploading.value = false;
    }
}

onMounted(loadSettings);
</script>
