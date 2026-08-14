<template>
    <AppLayout title="System Settings">
        <div class="space-y-6">
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-1">
                <button
                    v-for="tab in visibleTabs"
                    :key="tab.id"
                    type="button"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium transition"
                    :class="activeTab === tab.id
                        ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-200 ring-b-white'
                        : 'text-slate-600 hover:bg-slate-50'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <!-- Password configuration -->
            <section v-if="activeTab === 'password'" class="space-y-4">
                <p class="text-sm text-slate-600">
                    Configure default passwords, minimum length, and login lockout rules for new and existing accounts.
                </p>

                <div v-if="loadingSettings" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                    Loading password settings...
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                                <tr v-for="setting in passwordSettings" :key="setting.setting_id">
                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        {{ formatSettingLabel(setting.setting_key) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            v-if="canUpdateSettings && editingId === setting.setting_id"
                                            v-model="editValues[setting.setting_id]"
                                            :type="inputType(setting.data_type)"
                                            class="w-full min-w-32 rounded-lg border border-slate-300 px-2 py-1.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        >
                                        <span v-else class="font-medium text-slate-900">
                                            {{ displayValue(setting) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ setting.description }}
                                    </td>
                                    <td v-if="canUpdateSettings" class="px-4 py-3 text-right">
                                        <div v-if="editingId === setting.setting_id" class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                class="rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
                                                :disabled="savingId === setting.setting_id"
                                                @click="saveSetting(setting)"
                                            >
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                                @click="cancelEdit"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            @click="startEdit(setting)"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- User accounts -->
            <section v-else-if="activeTab === 'accounts'" class="space-y-6">
                <article
                    v-if="canCreateUser"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Add user account
                    </h2>
                    <form class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" @submit.prevent="handleCreateUser">
                        <div>
                            <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                            <input
                                id="username"
                                v-model="createForm.username"
                                type="text"
                                required
                                maxlength="45"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                            <p v-if="createErrors.username" class="mt-1 text-xs text-red-600">{{ createErrors.username }}</p>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <p class="mb-2 text-sm font-medium text-slate-700">Personnel position assignment</p>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <label class="inline-flex items-center gap-2">
                                    <input
                                        v-model="personnelMode"
                                        type="radio"
                                        value="available"
                                        class="text-blue-700 focus:ring-blue-500"
                                    >
                                    Use available slot
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input
                                        v-model="personnelMode"
                                        type="radio"
                                        value="position"
                                        class="text-blue-700 focus:ring-blue-500"
                                    >
                                    Assign by position
                                </label>
                            </div>
                        </div>
                        <div v-if="personnelMode === 'available'">
                            <label for="personnel" class="mb-1.5 block text-sm font-medium text-slate-700">Available position slot</label>
                            <select
                                id="personnel"
                                v-model="createForm.personnel_id"
                                :required="personnelMode === 'available'"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="" disabled>Select available slot</option>
                                <option
                                    v-for="person in createOptions.personnel"
                                    :key="person.personnel_id"
                                    :value="person.personnel_id"
                                >
                                    {{ person.label }}
                                </option>
                            </select>
                            <p v-if="!createOptions.personnel.length" class="mt-1 text-xs text-amber-600">
                                No available slots. Switch to “Assign by position” instead.
                            </p>
                            <p v-if="createErrors.personnel_id" class="mt-1 text-xs text-red-600">{{ createErrors.personnel_id }}</p>
                        </div>
                        <div v-else class="sm:col-span-2">
                            <label for="position" class="mb-1.5 block text-sm font-medium text-slate-700">Personnel position</label>
                            <select
                                id="position"
                                v-model="createForm.position_id"
                                :required="personnelMode === 'position'"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="" disabled>Select personnel position</option>
                                <option
                                    v-for="position in createOptions.positions"
                                    :key="position.position_id"
                                    :value="position.position_id"
                                >
                                    {{ position.label }}
                                </option>
                            </select>
                            <p v-if="createErrors.position_id" class="mt-1 text-xs text-red-600">{{ createErrors.position_id }}</p>

                            <div v-if="canCreatePosition" class="mt-3 rounded-lg border border-dashed border-slate-300 p-3">
                                <p class="text-xs font-medium text-slate-600">Add new personnel position</p>
                                <div class="mt-2 flex gap-2">
                                    <input
                                        v-model="newPositionName"
                                        type="text"
                                        maxlength="45"
                                        placeholder="e.g. Barangay Treasurer"
                                        class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                    <button
                                        type="button"
                                        class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                                        :disabled="creatingPosition"
                                        @click="handleCreatePosition"
                                    >
                                        {{ creatingPosition ? 'Adding...' : 'Add position' }}
                                    </button>
                                </div>
                                <p v-if="newPositionError" class="mt-1 text-xs text-red-600">{{ newPositionError }}</p>
                            </div>
                        </div>
                        <div>
                            <label for="role" class="mb-1.5 block text-sm font-medium text-slate-700">Role</label>
                            <select
                                id="role"
                                v-model="createForm.role_id"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="" disabled>Select role</option>
                                <option
                                    v-for="role in createOptions.roles"
                                    :key="role.role_id"
                                    :value="role.role_id"
                                >
                                    {{ role.role_name }}
                                </option>
                            </select>
                            <p v-if="createErrors.role_id" class="mt-1 text-xs text-red-600">{{ createErrors.role_id }}</p>
                        </div>
                        <div class="flex items-end">
                            <button
                                type="submit"
                                class="w-full rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
                                :disabled="creatingUser"
                            >
                                {{ creatingUser ? 'Creating...' : 'Create account' }}
                            </button>
                        </div>
                    </form>
                    <p class="mt-3 text-xs text-slate-500">
                        New accounts receive the configured default password and must change it on first login.
                    </p>
                </article>

                <div v-if="loadingUsers" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                    Loading user accounts...
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Username</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Personnel position</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Roles</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="account in users" :key="account.user_id">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ account.username }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ formatPersonnelPosition(account.personnel) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ account.roles?.join(', ') || '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <select
                                            v-if="canUpdateStatus"
                                            :value="account.user_status_id"
                                            class="rounded-lg border border-slate-300 px-2 py-1 text-xs outline-none focus:border-blue-500"
                                            :disabled="updatingStatusId === account.user_id"
                                            @change="handleStatusChange(account, $event)"
                                        >
                                            <option
                                                v-for="status in userStatuses"
                                                :key="status.id"
                                                :value="status.id"
                                            >
                                                {{ status.label }}
                                            </option>
                                        </select>
                                        <span v-else class="text-slate-600">{{ account.user_status }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                v-if="canResetPassword"
                                                type="button"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                                                :disabled="resettingId === account.user_id"
                                                @click="handleResetPassword(account.user_id)"
                                            >
                                                Reset password
                                            </button>
                                            <button
                                                v-if="canDeleteUser && account.user_id !== currentUserId"
                                                type="button"
                                                class="rounded-lg border border-red-200 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-60"
                                                :disabled="deletingId === account.user_id"
                                                @click="handleDeleteUser(account)"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!users.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                        No user accounts found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- General settings -->
            <section v-else class="space-y-4">
                <p class="text-sm text-slate-600">
                    Barangay profile, session timeout, and audit log retention settings.
                </p>

                <div v-if="loadingSettings" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                    Loading settings...
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Key</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Value</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                                    <th v-if="canUpdateSettings" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="setting in generalSettings" :key="setting.setting_id">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-800">{{ setting.setting_key }}</td>
                                    <td class="px-4 py-3">
                                        <input
                                            v-if="canUpdateSettings && editingId === setting.setting_id"
                                            v-model="editValues[setting.setting_id]"
                                            :type="inputType(setting.data_type)"
                                            class="w-full min-w-32 rounded-lg border border-slate-300 px-2 py-1.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        >
                                        <span v-else class="font-medium text-slate-900">{{ displayValue(setting) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ setting.description }}</td>
                                    <td v-if="canUpdateSettings" class="px-4 py-3 text-right">
                                        <div v-if="editingId === setting.setting_id" class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                class="rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
                                                :disabled="savingId === setting.setting_id"
                                                @click="saveSetting(setting)"
                                            >
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                                @click="cancelEdit"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            @click="startEdit(setting)"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as settingService from '@/services/settingService';
import * as userService from '@/services/userService';

const PASSWORD_SETTING_KEYS = [
    'default_password',
    'password_min_length',
    'max_login_attempts',
    'account_lockout_minutes',
];

const { user, hasPermission, isSystemAdministrator } = useAuth();

const activeTab = ref('password');
const settings = ref([]);
const users = ref([]);
const userStatuses = ref([]);
const createOptions = reactive({ roles: [], personnel: [], positions: [] });
const personnelMode = ref('position');
const newPositionName = ref('');
const newPositionError = ref('');
const creatingPosition = ref(false);
const loadingSettings = ref(false);
const loadingUsers = ref(false);
const savingId = ref(null);
const editingId = ref(null);
const editValues = reactive({});
const error = ref('');
const successMessage = ref('');
const creatingUser = ref(false);
const deletingId = ref(null);
const resettingId = ref(null);
const updatingStatusId = ref(null);

const createForm = reactive({
    username: '',
    personnel_id: '',
    position_id: '',
    role_id: '',
});

const createErrors = reactive({
    username: '',
    personnel_id: '',
    position_id: '',
    role_id: '',
});

const canViewSettings = computed(() => isSystemAdministrator.value && hasPermission('setting.view'));
const canUpdateSettings = computed(() => isSystemAdministrator.value && hasPermission('setting.update'));
const canViewUsers = computed(() => isSystemAdministrator.value && hasPermission('user.view'));
const canCreateUser = computed(() => isSystemAdministrator.value && hasPermission('user.create'));
const canCreatePosition = computed(() => isSystemAdministrator.value && hasPermission('pposition.create'));
const canDeleteUser = computed(() => isSystemAdministrator.value && hasPermission('user.delete'));
const canResetPassword = computed(() => isSystemAdministrator.value && hasPermission('user.resetpassword'));
const canUpdateStatus = computed(() => isSystemAdministrator.value && hasPermission('user.updatestatus'));
const currentUserId = computed(() => user.value?.user_id ?? null);

const visibleTabs = computed(() => {
    const tabs = [];

    if (canViewSettings.value) {
        tabs.push({ id: 'password', label: 'Password & Security' });
    }

    if (canViewUsers.value) {
        tabs.push({ id: 'accounts', label: 'User Accounts' });
    }

    tabs.push({ id: 'general', label: 'General' });

    return tabs;
});

const passwordSettings = computed(() => (
    settings.value.filter((setting) => PASSWORD_SETTING_KEYS.includes(setting.setting_key))
));

const generalSettings = computed(() => (
    settings.value.filter((setting) => !PASSWORD_SETTING_KEYS.includes(setting.setting_key))
));

function formatSettingLabel(key) {
    return key.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

function displayValue(setting) {
    return setting.typed_value ?? setting.setting_value;
}

function inputType(dataType) {
    return dataType === 'int' ? 'number' : 'text';
}

function formatPersonnelPosition(personnel) {
    if (!personnel) {
        return '—';
    }

    return personnel.position_name ?? 'Unassigned position';
}

function startEdit(setting) {
    editingId.value = setting.setting_id;
    editValues[setting.setting_id] = setting.data_type === 'int'
        ? setting.typed_value
        : setting.setting_value;
    successMessage.value = '';
    error.value = '';
}

function cancelEdit() {
    editingId.value = null;
}

function clearCreateErrors() {
    createErrors.username = '';
    createErrors.personnel_id = '';
    createErrors.position_id = '';
    createErrors.role_id = '';
}

async function loadSettings() {
    loadingSettings.value = true;
    error.value = '';

    try {
        settings.value = await settingService.fetchSettings();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load settings.');
    } finally {
        loadingSettings.value = false;
    }
}

async function loadUsers() {
    if (!canViewUsers.value) {
        return;
    }

    loadingUsers.value = true;
    error.value = '';

    try {
        users.value = await userService.fetchUsers();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load user accounts.');
    } finally {
        loadingUsers.value = false;
    }
}

async function loadCreateOptions() {
    if (!canCreateUser.value) {
        return;
    }

    try {
        const data = await userService.fetchCreateOptions();
        createOptions.roles = data.roles ?? [];
        createOptions.personnel = data.personnel ?? [];
        createOptions.positions = data.positions ?? [];

        if (createOptions.personnel.length > 0) {
            personnelMode.value = 'available';
        }
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load account creation options.');
    }
}

async function loadUserStatuses() {
    try {
        const items = await lookupService.fetchLookups('user-status');
        userStatuses.value = items.map((item) => ({
            id: item.id,
            label: item.label,
        }));
    } catch {
        userStatuses.value = [];
    }
}

async function saveSetting(setting) {
    savingId.value = setting.setting_id;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await settingService.updateSetting(
            setting.setting_id,
            editValues[setting.setting_id],
        );

        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));

        editingId.value = null;
        successMessage.value = `Updated "${setting.setting_key}" successfully.`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update setting.');
    } finally {
        savingId.value = null;
    }
}

async function handleCreatePosition() {
    creatingPosition.value = true;
    newPositionError.value = '';
    error.value = '';

    try {
        const item = await lookupService.createLookup('personnel-position', {
            position_name: newPositionName.value.trim(),
        });

        await loadCreateOptions();
        createForm.position_id = item.id;
        personnelMode.value = 'position';
        newPositionName.value = '';
        successMessage.value = `Position "${item.label}" added successfully.`;
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        newPositionError.value = validationErrors.position_name
            ?? extractErrorMessage(err, 'Unable to add personnel position.');
    } finally {
        creatingPosition.value = false;
    }
}

async function handleCreateUser() {
    creatingUser.value = true;
    clearCreateErrors();
    error.value = '';
    successMessage.value = '';

    const payload = {
        username: createForm.username.trim(),
        role_id: Number(createForm.role_id),
    };

    if (personnelMode.value === 'available') {
        payload.personnel_id = Number(createForm.personnel_id);
    } else {
        payload.position_id = Number(createForm.position_id);
    }

    try {
        const created = await userService.createUser(payload);

        users.value = [...users.value, created].sort((a, b) => a.username.localeCompare(b.username));
        createForm.username = '';
        createForm.personnel_id = '';
        createForm.position_id = '';
        createForm.role_id = '';
        await loadCreateOptions();
        successMessage.value = `Account "${created.username}" created successfully.`;
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        createErrors.username = validationErrors.username ?? '';
        createErrors.personnel_id = validationErrors.personnel_id ?? '';
        createErrors.position_id = validationErrors.position_id ?? '';
        createErrors.role_id = validationErrors.role_id ?? '';
        error.value = extractErrorMessage(err, 'Unable to create user account.');
    } finally {
        creatingUser.value = false;
    }
}

async function handleStatusChange(account, event) {
    updatingStatusId.value = account.user_id;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await userService.updateUserStatus(
            account.user_id,
            Number(event.target.value),
        );

        users.value = users.value.map((row) => (
            row.user_id === updated.user_id ? updated : row
        ));
        successMessage.value = `Updated status for "${account.username}".`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update user status.');
        event.target.value = account.user_status_id;
    } finally {
        updatingStatusId.value = null;
    }
}

async function handleResetPassword(userId) {
    if (!window.confirm('Reset this account password to the system default?')) {
        return;
    }

    resettingId.value = userId;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await userService.resetUserPassword(userId);
        users.value = users.value.map((row) => (
            row.user_id === updated.user_id ? updated : row
        ));
        successMessage.value = 'Password reset successfully.';
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to reset password.');
    } finally {
        resettingId.value = null;
    }
}

async function handleDeleteUser(account) {
    if (!window.confirm(`Delete account "${account.username}"? This cannot be undone.`)) {
        return;
    }

    deletingId.value = account.user_id;
    error.value = '';
    successMessage.value = '';

    try {
        await userService.deleteUser(account.user_id);
        users.value = users.value.filter((row) => row.user_id !== account.user_id);
        await loadCreateOptions();
        successMessage.value = `Account "${account.username}" deleted successfully.`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to delete user account.');
    } finally {
        deletingId.value = null;
    }
}

watch(activeTab, (tab) => {
    if (tab === 'accounts' && !users.value.length) {
        loadUsers();
        loadCreateOptions();
        loadUserStatuses();
    }
});

onMounted(async () => {
    await loadSettings();

    if (canViewUsers.value) {
        await Promise.all([loadUsers(), loadCreateOptions(), loadUserStatuses()]);
    }
});
</script>
