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
                            <template v-if="canUpdateSettings && editingId === setting.setting_id">
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
                            <span v-else class="font-medium text-slate-900">
                                {{ setting.typed_value ?? setting.setting_value }}
                            </span>
                        </div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left break-words text-slate-600" role="cell">
                            {{ setting.description }}
                        </div>
                        <div v-if="canUpdateSettings" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="cell">
                            <div v-if="editingId === setting.setting_id" class="flex flex-wrap gap-2">
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
};

const SETTING_PLACEHOLDERS = {
    barangay_name: 'Barangay Happy Hallow',
    barangay_address: 'Barangay Happy Hallow, Baguio City, Benguet',
    city_name: 'Baguio City',
    barangay_code: '1430300006',
    barangay_contact_no: '09123456789',
    barangay_email: 'brgyhappyhallow@gmail.com',
    default_password: 'Temp12345',
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
        description: 'Idle session handling and how long activity records are kept.',
        keys: [
            'session_timeout_minutes',
            'audit_log_retention_days',
        ],
    },
];

const { hasPermission, isSuperAdmin } = useAuth();

const settings = ref([]);
const loading = ref(false);
const savingId = ref(null);
const editingId = ref(null);
const editValues = reactive({});
const editError = ref('');
const error = ref('');
const successMessage = ref('');

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
