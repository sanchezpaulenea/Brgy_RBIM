<template>
    <AppLayout title="User Account Management">
        <div class="space-y-6">
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
                <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="handleCreateUser">
                    <div>
                        <label for="username" class="rbim-label">Username</label>
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
                        <label for="role_id" class="rbim-label">Role</label>
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
                        </p>
                    </div>
                </form>
            </article>

            <div v-if="loadingUsers" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading user accounts...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Username</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Personnel</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Roles</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                <th v-if="canManageAccountActions" class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="account in users" :key="account.user_id" class="align-top">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ account.username }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ account.personnel ? (account.personnel.position_name || 'Linked personnel') : 'None' }}
                                </td>
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
                                <td v-if="canManageAccountActions" class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            v-if="canAssignRoles"
                                            type="button"
                                            class="rbim-btn-outline px-3 py-1.5 text-xs"
                                            @click="toggleRolesPanel(account)"
                                        >
                                            {{ rolePanelUserId === account.user_id ? 'Close roles' : 'Assign roles' }}
                                        </button>
                                        <button
                                            v-if="canResetPassword"
                                            type="button"
                                            class="rbim-btn-outline px-3 py-1.5 text-xs"
                                            :disabled="resettingId === account.user_id"
                                            @click="handleResetPassword(account.user_id)"
                                        >
                                            {{ resettingId === account.user_id ? 'Resetting...' : 'Reset password' }}
                                        </button>
                                    </div>
                                    <div
                                        v-if="rolePanelUserId === account.user_id"
                                        class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-left"
                                    >
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Role assignments
                                        </p>
                                        <ul class="mt-2 space-y-2">
                                            <li
                                                v-for="assignment in roleAssignments"
                                                :key="assignment.user_role_id"
                                                class="flex items-center justify-between gap-2 text-xs"
                                            >
                                                <span>{{ assignment.role_name }}</span>
                                                <button
                                                    v-if="canUpdateRoleStatus"
                                                    type="button"
                                                    class="rounded border px-2 py-1"
                                                    :class="assignment.enable ? 'border-brand text-brand' : 'border-slate-300 text-slate-500'"
                                                    @click="handleRoleStatus(assignment)"
                                                >
                                                    {{ assignment.enable ? 'Enabled' : 'Disabled' }}
                                                </button>
                                            </li>
                                            <li v-if="!roleAssignments.length" class="text-xs text-slate-500">
                                                No roles assigned.
                                            </li>
                                        </ul>
                                        <div v-if="assignableRoles.length" class="mt-3 flex gap-2">
                                            <select v-model="selectedRoleId" class="rbim-input py-1.5 text-xs">
                                                <option value="">Select role</option>
                                                <option
                                                    v-for="role in assignableRoles"
                                                    :key="role.role_id"
                                                    :value="role.role_id"
                                                >
                                                    {{ role.role_name }}
                                                </option>
                                            </select>
                                            <button
                                                type="button"
                                                class="rbim-btn px-3 py-1.5 text-xs"
                                                :disabled="!selectedRoleId || assigningRole"
                                                @click="handleAssignRole(account)"
                                            >
                                                Assign
                                            </button>
                                        </div>
                                        <p v-else class="mt-2 text-xs text-slate-500">
                                            {{ account.personnel_id
                                                ? 'Guest is not offered for personnel-linked accounts.'
                                                : 'Staff roles require a linked personnel record.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.length">
                                <td :colspan="canManageAccountActions ? 5 : 4" class="px-4 py-8 text-center text-slate-500">
                                    No user accounts found.
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
import PersonnelSearch from '@/components/PersonnelSearch.vue';
import { ROLES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as personnelService from '@/services/personnelService';
import * as userService from '@/services/userService';

const { hasPermission } = useAuth();

const users = ref([]);
const userStatuses = ref([]);
const personnelOptions = ref([]);
const createOptions = reactive({ roles: [] });
const loadingUsers = ref(false);
const creatingUser = ref(false);
const resettingId = ref(null);
const updatingStatusId = ref(null);
const assigningRole = ref(false);
const rolePanelUserId = ref(null);
const roleAssignments = ref([]);
const selectedRoleId = ref('');
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
const canAssignRoles = computed(() => hasPermission('userrole.create'));
const canUpdateRoleStatus = computed(() => hasPermission('userrole.updatestatus'));
const canManageAccountActions = computed(() => canAssignRoles.value || canResetPassword.value);

const activeAccount = computed(() => (
    users.value.find((account) => account.user_id === rolePanelUserId.value) ?? null
));

const assignableRoles = computed(() => {
    const assignedIds = new Set(roleAssignments.value.map((item) => Number(item.role_id)));
    const hasPersonnel = Boolean(activeAccount.value?.personnel_id);

    return createOptions.roles.filter((role) => {
        if (assignedIds.has(Number(role.role_id))) {
            return false;
        }

        if (!hasPersonnel) {
            return role.role_name === ROLES.GUEST;
        }

        return role.role_name !== ROLES.GUEST;
    });
});

const createRoleOptions = computed(() => {
    const hasPersonnel = Boolean(createForm.personnel_id);

    return createOptions.roles.filter((role) => (
        hasPersonnel ? role.role_name !== ROLES.GUEST : role.role_name === ROLES.GUEST
    ));
});

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

async function toggleRolesPanel(account) {
    if (rolePanelUserId.value === account.user_id) {
        rolePanelUserId.value = null;
        roleAssignments.value = [];
        selectedRoleId.value = '';

        return;
    }

    rolePanelUserId.value = account.user_id;
    selectedRoleId.value = '';
    error.value = '';

    try {
        roleAssignments.value = await userService.fetchUserRoles(account.user_id);
    } catch (err) {
        roleAssignments.value = [];
        error.value = extractErrorMessage(err, 'Unable to load role assignments.');
    }
}

async function handleAssignRole(account) {
    assigningRole.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        await userService.assignUserRole(account.user_id, Number(selectedRoleId.value));
        roleAssignments.value = await userService.fetchUserRoles(account.user_id);
        selectedRoleId.value = '';
        const updated = await userService.fetchUser(account.user_id);
        users.value = users.value.map((row) => (row.user_id === updated.user_id ? updated : row));
        successMessage.value = `Role assigned to "${account.username}".`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to assign role.');
    } finally {
        assigningRole.value = false;
    }
}

async function handleRoleStatus(assignment) {
    const allowed = await askConfirm({
        title: assignment.enable ? 'Disable role' : 'Enable role',
        message: `${assignment.enable ? 'Disable' : 'Enable'} the ${assignment.role_name} role for this user?`,
        confirmLabel: assignment.enable ? 'Disable' : 'Enable',
        variant: assignment.enable ? 'danger' : 'primary',
    });

    if (!allowed) {
        return;
    }

    error.value = '';

    try {
        const updated = await userService.updateUserRoleStatus(assignment.user_role_id, !assignment.enable);
        roleAssignments.value = roleAssignments.value.map((row) => (
            row.user_role_id === updated.user_role_id ? updated : row
        ));
        if (rolePanelUserId.value) {
            const user = await userService.fetchUser(rolePanelUserId.value);
            users.value = users.value.map((row) => (row.user_id === user.user_id ? user : row));
        }
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update role status.');
    }
}

onMounted(async () => {
    await Promise.all([loadUsers(), loadOptions()]);
});
</script>
