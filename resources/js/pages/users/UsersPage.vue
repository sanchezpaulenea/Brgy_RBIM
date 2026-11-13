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
                            minlength="2"
                            maxlength="45"
                            required
                            class="rbim-input"
                            :class="{ 'rbim-input-error': createErrors.username }"
                            @input="createErrors.username = ''"
                        >
                        <p v-if="createErrors.username" class="rbim-error"> {{ createErrors.username }} </p>
                    </div>
                    <PersonnelSearch
                        v-model="createForm.personnel_id"
                        :options="personnelOptions"
                        hint="Optional. A staff role still requires a linked personnel record. Guest cannot be linked to personnel."
                        :error="createErrors.personnel_id"
                    />
                    <div>
                        <label for="role_id" class="rbim-label">
                            Role
                        </label>
                        <select
                            id="role_id"
                            v-model="createForm.role_id"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': createErrors.role_id }"
                            @change="createErrors.role_id = ''"
                        >
                            <option value="">No role yet</option>
                            <option
                                v-for="role in createRoleOptions"
                                :key="role.role_id"
                                :value="role.role_id"
                            >
                                {{ role.role_name }}
                            </option>
                        </select>
                        <p v-if="createErrors.role_id" class="rbim-error">{{ createErrors.role_id }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Optional. Assigning a role asks for your Admin or Super Admin password.
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="flex justify-end">
                            <button type="submit" class="rbim-btn" :disabled="creatingUser">
                                {{ creatingUser ? 'Creating...' : 'Create account' }}
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            New accounts receive the default password and must change it on first login.
                            A role can be left blank and assigned later in the User Role tab.
                        </p>
                    </div>
                </form>
            </article>

            <div v-if="loadingUsers" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading user accounts...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                        User Account Overview
                    </h2>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="sm:w-72">
                            <label for="user-filter-user" class="sr-only">Search user</label>
                            <input
                                id="user-filter-user"
                                v-model="filters.username"
                                type="search"
                                class="rbim-input py-2"
                                placeholder="Search user"
                                aria-label="Search user"
                                @keydown.enter.prevent
                            >
                        </div>
                        <div class="sm:w-48">
                            <label for="user-filter-status" class="sr-only">User status</label>
                            <select
                                id="user-filter-status"
                                v-model="filters.statusId"
                                class="rbim-input rbim-filter py-2"
                                aria-label="User status"
                            >
                                <option value="all">All statuses</option>
                                <option v-for="status in userStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3 text-center font-semibold text-slate-600">User</th>
                                <th class="px-3 py-3 text-center font-semibold text-slate-600">Barangay personnel name</th>
                                <th class="px-3 py-3 text-center font-semibold text-slate-600">Roles</th>
                                <th class="px-3 py-3 text-center font-semibold text-slate-600">User Status</th>
                                <th v-if="canResetPassword" class="px-3 py-3 text-center font-semibold text-slate-600">Reset password</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="account in filteredUsers" :key="account.user_id">
                                <td class="px-3 py-3 text-center font-medium text-slate-900">{{ account.username }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ personnelName(account) }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ account.roles?.join(', ') || 'None' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <select
                                        v-if="canUpdateStatus"
                                        :value="account.user_status_id"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs outline-none focus:border-brand"
                                        :disabled="updatingStatusId === account.user_id"
                                        @change="handleStatusChange(account, $event)"
                                    >
                                        <option v-for="status in statusesFor(account)" :key="status.id" :value="status.id">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <span v-else class="text-slate-600">{{ account.user_status }}</span>
                                </td>
                                <td v-if="canResetPassword" class="px-3 py-3 text-center">
                                    <button
                                        type="button"
                                        class="rbim-btn-action"
                                        :disabled="resettingId === account.user_id"
                                        @click="handleResetPassword(account.user_id)"
                                    >
                                        {{ resettingId === account.user_id ? 'Resetting...' : 'Reset password' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredUsers.length">
                                <td :colspan="canResetPassword ? 5 : 4" class="px-4 py-8 text-center text-slate-500">
                                    {{ users.length ? 'No accounts match the current filters.' : 'No user accounts found.' }}
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
import { computed, onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PasswordConfirmDialog from '@/components/PasswordConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import PersonnelSearch from '@/components/PersonnelSearch.vue';
import { useAdministratorPasswordConfirm } from '@/composables/useAdministratorPasswordConfirm';
import { ROLES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as personnelService from '@/services/personnelService';
import * as userService from '@/services/userService';
import { matchesSearch } from '@/utils/format';

const { hasPermission } = useAuth();
const { userTabs } = useSectionTabs();
const {
    passwordPrompt,
    askAdministratorPassword,
    confirmAdministratorPassword,
    cancelAdministratorPassword,
} = useAdministratorPasswordConfirm();

const users = ref([]);
const userStatuses = ref([]);
const personnelOptions = ref([]);
const createOptions = reactive({ roles: [] });
const loadingUsers = ref(false);
const creatingUser = ref(false);
const resettingId = ref(null);
const updatingStatusId = ref(null);
const error = ref('');
const successMessage = ref('');
const filters = reactive({
    username: '',
    statusId: 'all',
});

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

const filteredUsers = computed(() => (
    users.value.filter((account) => {
        const matchesUser = matchesSearch(account.username, filters.username)
            || matchesSearch(personnelName(account), filters.username);
        const matchesStatus = filters.statusId === 'all'
            || Number(account.user_status_id) === Number(filters.statusId);

        return matchesUser && matchesStatus;
    })
));

function statusesFor(account) {
    const roles = Array.isArray(account.roles) ? account.roles : [];

    if (!roles.includes(ROLES.SUPER_ADMIN)) {
        return userStatuses.value;
    }

    return userStatuses.value.filter((status) => status.label !== 'Locked');
}

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
    const stillValid = createRoleOptions.value.some(
        (role) => Number(role.role_id) === Number(createForm.role_id),
    );

    if (!stillValid) {
        createForm.role_id = '';
    }
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
    // Reset previous validation errors
    createErrors.username = '';
    createErrors.personnel_id = '';
    createErrors.role_id = '';
    error.value = '';
    successMessage.value = '';

    // Normalize username
    const username = createForm.username.trim();

    // Frontend validation
    if (!username) {
        createErrors.username = 'Username is required.';
    } else if (username.length < 2) {
        createErrors.username = 'Username must be at least 2 characters.';
    } else if (username.length > 45) {
        createErrors.username = 'Username must not exceed 45 characters.';
    }

    // Stop if validation fails
    if (
        createErrors.username ||
        createErrors.personnel_id
    ) {
        return;
    }

    const hasPersonnel = Boolean(createForm.personnel_id);
    const roleId = createForm.role_id ? Number(createForm.role_id) : null;
    const payload = {
        username,
    };

    if (roleId) {
        payload.role_id = roleId;
    }

    if (hasPersonnel) {
        payload.personnel_id = Number(createForm.personnel_id);
    }

    creatingUser.value = true;

    try {
        if (roleId) {
            const roleName = createRoleOptions.value.find((role) => Number(role.role_id) === roleId)?.role_name ?? 'the selected role';
            const created = await askAdministratorPassword({
                title: 'Assign role',
                message: `Create "${username}" and assign the ${roleName} role. Enter your Admin or Super Admin password to continue.`,
                confirmLabel: 'Create account',
                run: (password) => userService.createUser({
                    ...payload,
                    admin_password: password,
                }),
            });

            if (!created) {
                return;
            }

            users.value = [...users.value, created].sort(
                (a, b) => a.username.localeCompare(b.username)
            );
            createForm.username = '';
            createForm.personnel_id = null;
            createForm.role_id = '';
            await loadOptions();
            successMessage.value = `Account "${created.username}" created.`;

            return;
        }

        const created = await userService.createUser(payload);

        users.value = [...users.value, created].sort(
            (a, b) => a.username.localeCompare(b.username)
        );
        createForm.username = '';
        createForm.personnel_id = null;
        createForm.role_id = '';
        await loadOptions();
        successMessage.value = `Account "${created.username}" created.`;
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        createErrors.username = validationErrors.username ?? '';
        createErrors.personnel_id =
            validationErrors.personnel_id ??
            validationErrors.position_id ?? '';
        createErrors.role_id = validationErrors.role_id ?? '';
        error.value = (
            createErrors.username ||
            createErrors.personnel_id ||
            createErrors.role_id
        )
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
