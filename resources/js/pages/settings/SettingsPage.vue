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
                <header class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">{{ group.title }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ group.description }}</p>
                </header>
                <div class="w-full overflow-x-auto" role="table">
                    <div
                        class="grid w-full min-w-[44rem] items-center gap-x-[7.5rem] bg-slate-50 px-4 text-sm font-semibold text-slate-600"
                        :style="{ gridTemplateColumns: settingsColumnTemplate }"
                        role="row"
                    >
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Setting</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Value</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Description</div>
                        <div v-if="canUpdateSettings" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Action</div>
                    </div>
                    <div
                        v-for="setting in group.settings"
                        :key="setting.setting_id"
                        class="grid w-full min-w-[44rem] items-start gap-x-[7.5rem] border-t border-slate-100 px-4 text-sm"
                        :style="{ gridTemplateColumns: settingsColumnTemplate }"
                        role="row"
                    >
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left font-medium break-words text-slate-900" role="cell">
                            {{ formatSettingLabel(setting.setting_key) }}
                        </div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left break-words" role="cell">
                            <template v-if="setting.setting_key === 'punong_barangay_signature'">
                                <p class="font-medium text-slate-900">{{ setting.setting_value ? 'Uploaded' : 'Not set' }}</p>
                                <img
                                    v-if="signaturePreviewUrl"
                                    :src="signaturePreviewUrl"
                                    alt="Punong Barangay signature preview"
                                    class="mt-2 h-16 max-w-full bg-slate-100 object-contain"
                                >
                                <p class="rbim-hint">Use a transparent-background PNG. Width must be between 100 and 1500 pixels, and the file must be 1024 KB or smaller.</p>
                                <p v-if="signatureError" class="rbim-error">{{ signatureError }}</p>
                            </template>
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
                                <span class="font-medium text-slate-900">
                                    {{ setting.typed_value ?? setting.setting_value }}
                                </span>
                                <p v-if="isRetentionSetting(setting)" class="rbim-hint">
                                    Changed on Log Retention and Archiving.
                                </p>
                            </template>
                        </div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left break-words text-slate-600" role="cell">
                            {{ setting.description }}
                        </div>
                        <div v-if="canUpdateSettings" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="cell">
                            <div v-if="setting.setting_key === 'punong_barangay_signature'" class="flex flex-col items-start gap-2">
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
                                    class="rbim-btn-action"
                                    :class="signatureUploading ? 'pointer-events-none opacity-60' : ''"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M9.25 13.25a.75.75 0 001.5 0V4.81l1.72 1.72a.75.75 0 101.06-1.06l-3-3a.75.75 0 00-1.06 0l-3 3a.75.75 0 101.06 1.06l1.72-1.72v8.44z" />
                                        <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                                    </svg>
                                    {{ signatureUploading ? 'Uploading...' : 'Choose File' }}
                                </label>
                                <button
                                    v-if="setting.setting_value"
                                    type="button"
                                    class="rbim-btn-danger px-3 py-1.5 text-xs"
                                    :disabled="signatureUploading"
                                    @click="confirmRemoveSignature = true"
                                >
                                    Remove
                                </button>
                            </div>
                            <p v-else-if="isRetentionSetting(setting)" class="text-xs text-slate-500">
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
                                class="rbim-btn-action"
                                @click="startEdit(setting)"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                                </svg>
                                Edit
                            </button>
                        </div>
                    </div>
                    <div v-if="!group.settings.length" class="border-t border-slate-100 px-4 py-8 text-center text-sm text-slate-500">
                        No settings in this category.
                    </div>
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
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PasswordConfirmDialog from '@/components/PasswordConfirmDialog.vue';
import { useAdministratorPasswordConfirm } from '@/composables/useAdministratorPasswordConfirm';
import { useAuth } from '@/composables/useAuth';
import { fetchSignatureBlob } from '@/services/certificateService';
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
const signaturePreviewUrl = ref('');
const signatureError = ref('');
const signatureUploading = ref(false);
const confirmRemoveSignature = ref(false);

const canUpdateSettings = computed(() => (
    isSuperAdmin.value && hasPermission('setting.update')
));
const settingsColumnTemplate = computed(() => (
    canUpdateSettings.value ? 'repeat(4, minmax(0, 1fr))' : 'repeat(3, minmax(0, 1fr))'
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
        await loadSignaturePreview();
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

function signatureSetting() {
    return settings.value.find((setting) => setting.setting_key === 'punong_barangay_signature') ?? null;
}

function clearSignaturePreview() {
    if (signaturePreviewUrl.value) {
        URL.revokeObjectURL(signaturePreviewUrl.value);
        signaturePreviewUrl.value = '';
    }
}

async function loadSignaturePreview() {
    clearSignaturePreview();
    signatureError.value = '';

    if (!signatureSetting()?.setting_value) {
        return;
    }

    try {
        const blob = await fetchSignatureBlob();
        signaturePreviewUrl.value = URL.createObjectURL(blob);
    } catch (err) {
        signatureError.value = err.response?.status === 404
            ? 'No Punong Barangay signature uploaded.'
            : extractErrorMessage(err, 'Unable to preview the Punong Barangay signature.');
    }
}

async function uploadSignature(event) {
    const file = event.target.files?.[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    signatureUploading.value = true;
    signatureError.value = '';
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await settingService.uploadPunongBarangaySignature(file);
        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));
        await loadSignaturePreview();
        successMessage.value = 'Punong Barangay signature uploaded.';
    } catch (err) {
        signatureError.value = extractValidationErrors(err).signature
            ?? extractErrorMessage(err, 'Unable to upload the Punong Barangay signature.');
    } finally {
        signatureUploading.value = false;
    }
}

async function removeSignature() {
    confirmRemoveSignature.value = false;
    signatureUploading.value = true;
    signatureError.value = '';
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await settingService.removePunongBarangaySignature();
        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));
        clearSignaturePreview();
        successMessage.value = 'Punong Barangay signature removed.';
    } catch (err) {
        signatureError.value = extractErrorMessage(err, 'Unable to remove the Punong Barangay signature.');
    } finally {
        signatureUploading.value = false;
    }
}

onBeforeUnmount(clearSignaturePreview);

onMounted(loadSettings);
</script>
