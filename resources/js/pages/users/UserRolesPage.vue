<template>
    <AppLayout title="User Role">
        <div class="space-y-6">
            <PageTabs :tabs="userTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <p class="text-sm text-slate-600">
                Assign roles to an account and enable or disable each assignment.
            </p>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading user roles...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="border-b border-slate-200 p-4 sm:w-72">
                    <label for="user-role-search" class="rbim-label">Search user</label>
                    <input
                        id="user-role-search"
                        v-model="search"
                        type="search"
                        class="rbim-input py-2"
                        placeholder="Search username"
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full table-fixed divide-y divide-slate-200 text-sm">
                        <colgroup>
                            <col>
                            <col class="w-44">
                            <col class="w-32">
                            <col class="w-32">
                        </colgroup>
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Role</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template v-for="account in filteredUsers" :key="account.user_id">
                                <tr
                                    v-for="(assignment, index) in assignmentsOf(account)"
                                    :key="assignment.user_role_id"
                                >
                                    <td
                                        v-if="index === 0"
                                        :rowspan="userRowSpan(account)"
                                        class="px-4 py-3.5 align-top font-medium text-slate-900"
                                    >
                                        {{ account.username }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ assignment.role_name }}</td>
                                    <td class="px-4 py-3.5">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs"
                                            :class="assignment.enable ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                        >
                                            {{ assignment.enable ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-left">
                                        <button
                                            v-if="canUpdateRoleStatus"
                                            type="button"
                                            class="rbim-btn-action w-24"
                                            :disabled="updatingRoleId === assignment.user_role_id"
                                            @click="handleRoleStatus(account, assignment)"
                                        >
                                            {{ assignment.enable ? 'Disable' : 'Enable' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!assignmentsOf(account).length">
                                    <td class="px-4 py-3.5 align-top font-medium text-slate-900">
                                        {{ account.username }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500" colspan="3">
                                        <p>No roles assigned.</p>
                                        <div v-if="canAssignRoles" class="mt-3 flex flex-wrap items-center gap-2">
                                            <select
                                                v-model="selectedRoleByUser[account.user_id]"
                                                class="rbim-input w-40 py-1.5 text-xs"
                                                :disabled="!assignableRoles(account).length"
                                            >
                                                <option value="">
                                                    {{ assignableRoles(account).length ? 'Select role' : 'No role available' }}
                                                </option>
                                                <option
                                                    v-for="role in assignableRoles(account)"
                                                    :key="role.role_id"
                                                    :value="role.role_id"
                                                >
                                                    {{ role.role_name }}
                                                </option>
                                            </select>
                                            <button
                                                type="button"
                                                class="rbim-btn px-3 py-1.5 text-xs"
                                                :disabled="!selectedRoleByUser[account.user_id] || assigningUserId === account.user_id"
                                                @click="handleAssignRole(account)"
                                            >
                                                Assign
                                            </button>
                                        </div>
                                        <p v-if="canAssignRoles && !assignableRoles(account).length" class="mt-1 text-xs text-slate-500">
                                            {{ account.personnel_id
                                                ? 'Guest is not offered for personnel-linked accounts.'
                                                : 'Staff roles require a linked personnel record.' }}
                                        </p>
                                    </td>
                                </tr>
                                <tr v-else-if="canAssignRoles">
                                    <td class="px-4 py-3.5" colspan="3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <select
                                                v-model="selectedRoleByUser[account.user_id]"
                                                class="rbim-input w-40 py-1.5 text-xs"
                                                :disabled="!assignableRoles(account).length"
                                            >
                                                <option value="">
                                                    {{ assignableRoles(account).length ? 'Select role' : 'No role available' }}
                                                </option>
                                                <option
                                                    v-for="role in assignableRoles(account)"
                                                    :key="role.role_id"
                                                    :value="role.role_id"
                                                >
                                                    {{ role.role_name }}
                                                </option>
                                            </select>
                                            <button
                                                type="button"
                                                class="rbim-btn px-3 py-1.5 text-xs"
                                                :disabled="!selectedRoleByUser[account.user_id] || assigningUserId === account.user_id"
                                                @click="handleAssignRole(account)"
                                            >
                                                Assign
                                            </button>
                                        </div>
                                        <p v-if="!assignableRoles(account).length" class="mt-1 text-xs text-slate-500">
                                            {{ account.personnel_id
                                                ? 'Guest is not offered for personnel-linked accounts.'
                                                : 'Staff roles require a linked personnel record.' }}
                                        </p>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="!filteredUsers.length">
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    {{ users.length ? 'No users match the search.' : 'No user accounts found.' }}
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
import PageTabs from '@/components/PageTabs.vue';
import { ROLES } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as userService from '@/services/userService';
import { matchesSearch } from '@/utils/format';

const { hasPermission, user: currentUser, refreshSession } = useAuth();
const { userTabs } = useSectionTabs();

const users = ref([]);
const roles = ref([]);
const loading = ref(false);
const search = ref('');
const updatingRoleId = ref(null);
const assigningUserId = ref(null);
const selectedRoleByUser = reactive({});
const error = ref('');
const successMessage = ref('');
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canAssignRoles = computed(() => hasPermission('userrole.create'));
const canUpdateRoleStatus = computed(() => hasPermission('userrole.updatestatus'));

const filteredUsers = computed(() => (
    users.value.filter((account) => matchesSearch(account.username, search.value))
));

function assignmentsOf(account) {
    return account.role_assignments ?? [];
}

function userRowSpan(account) {
    const count = assignmentsOf(account).length;

    if (!count) {
        return 1;
    }

    return count + (canAssignRoles.value ? 1 : 0);
}

function assignableRoles(account) {
    const assignedIds = new Set(assignmentsOf(account).map((item) => Number(item.role_id)));
    const hasPersonnel = Boolean(account.personnel_id);

    return roles.value.filter((role) => {
        if (assignedIds.has(Number(role.role_id))) {
            return false;
        }

        return hasPersonnel
            ? role.role_name !== ROLES.GUEST
            : role.role_name === ROLES.GUEST;
    });
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

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const [accounts, options] = await Promise.all([
            userService.fetchUsers(),
            userService.fetchCreateOptions().catch(() => ({ roles: [] })),
        ]);

        users.value = accounts;
        roles.value = options.roles ?? [];
        accounts.forEach((account) => {
            selectedRoleByUser[account.user_id] = '';
        });
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load user roles.');
    } finally {
        loading.value = false;
    }
}

function replaceUser(updated) {
    users.value = users.value.map((row) => (row.user_id === updated.user_id ? updated : row));
}

async function syncCurrentUserSession(account) {
    if (Number(account.user_id) !== Number(currentUser.value?.user_id)) {
        return;
    }

    await refreshSession();
}

async function handleAssignRole(account) {
    const roleId = Number(selectedRoleByUser[account.user_id]);

    if (!roleId) {
        return;
    }

    const roleName = roles.value.find((role) => Number(role.role_id) === roleId)?.role_name ?? 'this role';

    const allowed = await askConfirm({
        title: 'Assign role',
        message: `Assign the ${roleName} role to ${account.username}?`,
        confirmLabel: 'Assign role',
    });

    if (!allowed) {
        return;
    }

    assigningUserId.value = account.user_id;
    error.value = '';
    successMessage.value = '';

    try {
        await userService.assignUserRole(account.user_id, roleId);
        replaceUser(await userService.fetchUser(account.user_id));
        selectedRoleByUser[account.user_id] = '';
        successMessage.value = `Assigned ${roleName} to "${account.username}".`;
        await syncCurrentUserSession(account);
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to assign role.');
    } finally {
        assigningUserId.value = null;
    }
}

async function handleRoleStatus(account, assignment) {
    const allowed = await askConfirm({
        title: assignment.enable ? 'Disable role' : 'Enable role',
        message: `${assignment.enable ? 'Disable' : 'Enable'} the ${assignment.role_name} role for "${account.username}"?`,
        confirmLabel: assignment.enable ? 'Disable' : 'Enable',
        variant: assignment.enable ? 'danger' : 'primary',
    });

    if (!allowed) {
        return;
    }

    updatingRoleId.value = assignment.user_role_id;
    error.value = '';
    successMessage.value = '';

    try {
        await userService.updateUserRoleStatus(assignment.user_role_id, !assignment.enable);
        replaceUser(await userService.fetchUser(account.user_id));
        successMessage.value = `${assignment.enable ? 'Disabled' : 'Enabled'} ${assignment.role_name} for "${account.username}".`;
        await syncCurrentUserSession(account);
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update role status.');
    } finally {
        updatingRoleId.value = null;
    }
}

onMounted(load);
</script>
