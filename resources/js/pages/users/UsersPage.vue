<template>
    <AppLayout title="User Account Management">
        <div class="space-y-6">
            <PageTabs :tabs="userTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <article v-if="canCreateUser" class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Create user account
                </h2>
                <form class="mt-4 grid gap-4 sm:grid-cols-2" novalidate @submit.prevent="handleCreateUser">
                    <div>
                        <label for="username" class="rbim-label">
                            Username<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="username"
                            v-model="createForm.username"
                            type="text"
                            maxlength="45"
                            required
                            class="rbim-input"
                            :class="{ 'rbim-input-error': createErrors.username }"
                        >
                        <p v-if="createErrors.username" class="rbim-error">{{ createErrors.username }}</p>
                    </div>
                    <PersonnelSearch
                        v-model="createForm.personnel_id"
                        :options="personnelOptions"
                        hint="Leave this blank to create a Guest account. Staff roles require a personnel record."
                        :error="createErrors.personnel_id"
                    />
                    <div>
                        <label for="role_id" class="rbim-label">
                            Role<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="role_id"
                            v-model="createForm.role_id"
                            required
                            class="rbim-input"
                            :class="{ 'rbim-input-error': createErrors.role_id }"
                        >
                            <option value="">Select role</option>
                            <option
                                v-for="role in createRoleOptions"
                                :key="role.role_id"
                                :value="role.role_id"
                            >
                                {{ role.role_name }}
                            </option>
                        </select>
                        <p v-if="createErrors.role_id" class="rbim-error">{{ createErrors.role_id }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rbim-btn" :disabled="creatingUser">
                            {{ creatingUser ? 'Creating...' : 'Create account' }}
                        </button>
                        <p class="mt-2 text-xs text-slate-500">
                            New accounts receive the default password and must change it on first login.
                            Additional roles are assigned in the User Role tab.
                        </p>
                    </div>
                </form>
            </article>

            <div v-if="loadingUsers" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading user accounts...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="border-b border-slate-200 p-4 sm:w-80">
                    <label for="user-filter-personnel" class="rbim-label">Search personnel name</label>
                    <input
                        id="user-filter-personnel"
                        v-model="search"
                        type="search"
                        class="rbim-input py-2"
                        placeholder="Search personnel name"
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Barangay personnel name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Roles</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">User Status</th>
                                <th v-if="canResetPassword" class="px-4 py-3 text-right font-semibold text-slate-600">Reset password</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="account in filteredUsers" :key="account.user_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ account.username }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ personnelName(account) }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ account.roles?.join(', ') || 'None' }}
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        v-if="canUpdateStatus"
                                        :value="account.user_status_id"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs outline-none focus:border-brand"
                                        :disabled="updatingStatusId === account.user_id"
                                        @change="handleStatusChange(account, $event)"
                                    >
                                        <option v-for="status in userStatuses" :key="status.id" :value="status.id">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <span v-else>{{ account.user_status }}</span>
                                </td>
                                <td v-if="canResetPassword" class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        class="rbim-btn-action"
                                        :disabled="resettingId === account.user_id"
                                        @click="handleResetPassword(account.user_id)"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M10 3a7 7 0 016.9 5.8.75.75 0 11-1.48.25A5.5 5.5 0 105.6 13.9l1.1-1.1H3.75a.75.75 0 010-1.5h4a.75.75 0 01.75.75v4a.75.75 0 01-1.5 0v-2.1l-1.36 1.36A7 7 0 1110 3z" />
                                        </svg>
                                        {{ resettingId === account.user_id ? 'Resetting...' : 'Reset password' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredUsers.length">
                                <td :colspan="canResetPassword ? 5 : 4" class="px-4 py-8 text-center text-slate-500">
                                    {{ users.length ? 'No accounts match the search.' : 'No user accounts found.' }}
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
import { computed, onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import PersonnelSearch from '@/components/PersonnelSearch.vue';
import { ROLES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as personnelService from '@/services/personnelService';
import * as userService from '@/services/userService';

const { hasPermission } = useAuth();
const { userTabs } = useSectionTabs();

const users = ref([]);
const userStatuses = ref([]);
const personnelOptions = ref([]);
const createOptions = reactive({ roles: [] });
const loadingUsers = ref(false);
const creatingUser = ref(false);
const resettingId = ref(null);
const updatingStatusId = ref(null);
const search = ref('');
const error = ref('');
const successMessage = ref('');

const createForm = reactive({
    username: '',
    personnel_id: null,
    role_id: '',
});

const createErrors = reactive({
    username: '',
    personnel_id: '',
    role_id: '',
});

const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canCreateUser = computed(() => hasPermission('user.create'));
const canUpdateStatus = computed(() => hasPermission('user.updatestatus'));
const canResetPassword = computed(() => hasPermission('user.resetpassword'));

const createRoleOptions = computed(() => {
    const hasPersonnel = Boolean(createForm.personnel_id);

    return createOptions.roles.filter((role) => (
        hasPersonnel ? role.role_name !== ROLES.GUEST : role.role_name === ROLES.GUEST
    ));
});

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return users.value;
    }

    return users.value.filter((account) => (
        personnelName(account).toLowerCase().includes(term)
        || String(account.username ?? '').toLowerCase().includes(term)
    ));
});

function personnelName(account) {
    return account.personnel?.full_name
        || account.personnel?.position_name
        || 'None';
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

watch(() => createForm.personnel_id, () => {
    const first = createRoleOptions.value[0];
    createForm.role_id = first?.role_id ?? '';
});

async function loadUsers() {
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

async function loadOptions() {
    try {
        const [options, statuses, personnel] = await Promise.all([
            userService.fetchCreateOptions(),
            lookupService.fetchUserStatuses(),
            personnelService.fetchPersonnelSearchOptions().catch(() => []),
        ]);

        createOptions.roles = options.roles ?? [];
        if (!createForm.personnel_id && createRoleOptions.value[0]) {
            createForm.role_id = createRoleOptions.value[0].role_id;
        }
        userStatuses.value = statuses.map((item) => ({ id: item.id, label: item.label }));
        personnelOptions.value = personnel.length
            ? personnel
            : (options.personnel ?? []).map((person) => ({
                personnel_id: person.personnel_id,
                position_id: person.position_id,
                label: person.label,
            }));
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load account options.');
    }
}

async function handleCreateUser() {
    creatingUser.value = true;
    createErrors.username = '';
    createErrors.personnel_id = '';
    createErrors.role_id = '';
    error.value = '';
    successMessage.value = '';

    const hasPersonnel = Boolean(createForm.personnel_id);
    const payload = {
        username: createForm.username.trim(),
        role_id: Number(createForm.role_id),
    };

    if (hasPersonnel) {
        payload.personnel_id = Number(createForm.personnel_id);
    }

    try {
        const created = await userService.createUser(payload);

        users.value = [...users.value, created].sort((a, b) => a.username.localeCompare(b.username));
        createForm.username = '';
        createForm.personnel_id = null;
        createForm.role_id = '';
        await loadOptions();
        successMessage.value = `Account "${created.username}" created.`;
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        createErrors.username = validationErrors.username ?? '';
        createErrors.personnel_id = validationErrors.personnel_id ?? validationErrors.position_id ?? '';
        createErrors.role_id = validationErrors.role_id ?? '';
        error.value = (createErrors.username || createErrors.personnel_id || createErrors.role_id)
            ? ''
            : extractErrorMessage(err, 'Unable to create user account.');
    } finally {
        creatingUser.value = false;
    }
}

/**
 * The selected id must be read before awaiting the dialog: opening it re-renders
 * this page, and Vue re-applies the `:value` binding, snapping the select back to
 * the account's current status.
 */
async function handleStatusChange(account, event) {
    const select = event.target;
    const nextStatusId = Number(select.value);

    if (!nextStatusId || nextStatusId === Number(account.user_status_id)) {
        select.value = account.user_status_id;

        return;
    }

    const nextStatusLabel = userStatuses.value
        .find((status) => Number(status.id) === nextStatusId)?.label ?? 'the selected status';

    const allowed = await askConfirm({
        title: 'Update account status',
        message: `Set "${account.username}" to ${nextStatusLabel}?`,
        confirmLabel: 'Update status',
    });

    if (!allowed) {
        select.value = account.user_status_id;

        return;
    }

    updatingStatusId.value = account.user_id;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await userService.updateUserStatus(account.user_id, nextStatusId);
        users.value = users.value.map((row) => (row.user_id === updated.user_id ? updated : row));
        successMessage.value = `Updated status for "${account.username}" to ${updated.user_status}.`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update user status.');
        select.value = account.user_status_id;
    } finally {
        updatingStatusId.value = null;
    }
}

async function handleResetPassword(userId) {
    const allowed = await askConfirm({
        title: 'Reset password',
        message: 'Reset this account password to the system default?',
        confirmLabel: 'Reset password',
        variant: 'danger',
    });

    if (!allowed) {
        return;
    }

    resettingId.value = userId;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await userService.resetUserPassword(userId);
        users.value = users.value.map((row) => (row.user_id === updated.user_id ? updated : row));
        successMessage.value = 'Password reset successfully.';
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to reset password.');
    } finally {
        resettingId.value = null;
    }
}

onMounted(async () => {
    await Promise.all([loadUsers(), loadOptions()]);
});
</script>
