<template>
    <AppLayout :title="pageTitle">
        <div class="space-y-6">
            <div v-if="tabs.length > 1" class="flex flex-wrap gap-2">
                <RouterLink
                    v-for="tab in tabs"
                    :key="tab.name"
                    :to="{ name: tab.name }"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition"
                    :class="route.name === tab.name ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
                >
                    {{ tab.label }}
                </RouterLink>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <section v-if="lookup === 'personnel-positions'" class="space-y-6">
                <article v-if="canCreatePosition" class="rbim-card p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Add personnel position
                    </h2>
                    <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="handleCreatePosition">
                        <div class="flex-1">
                            <label for="position-name" class="rbim-label">Position name</label>
                            <input id="position-name" v-model="newPositionName" type="text" maxlength="45" required class="rbim-input">
                            <p v-if="createError" class="rbim-error">{{ createError }}</p>
                        </div>
                        <button type="submit" class="rbim-btn" :disabled="creating">
                            {{ creating ? 'Adding...' : 'Add position' }}
                        </button>
                    </form>
                </article>

                <div class="rbim-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">ID</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Position</th>
                                    <th v-if="canDeletePosition" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in positions" :key="item.id">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ item.id }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ item.label }}</td>
                                    <td v-if="canDeletePosition" class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            class="rbim-btn-danger"
                                            :disabled="deletingId === item.id"
                                            @click="handleDeletePosition(item.id)"
                                        >
                                            {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!positions.length">
                                    <td :colspan="canDeletePosition ? 3 : 2" class="px-4 py-8 text-center text-slate-500">
                                        No personnel positions found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-else-if="lookup === 'user-roles'" class="space-y-6">
                <p class="text-sm text-slate-600">
                    Review each account’s roles and disable an assignment when needed.
                </p>
                <div class="rbim-card overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Roles</th>
                                <th v-if="canUpdateRoleStatus" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="account in users" :key="account.user_id" class="align-top">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ account.username }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    <ul class="space-y-2">
                                        <li
                                            v-for="assignment in (account.role_assignments ?? [])"
                                            :key="assignment.user_role_id"
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <span>{{ assignment.role_name }}</span>
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs"
                                                :class="assignment.enable ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                            >
                                                {{ assignment.enable ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </li>
                                        <li v-if="!(account.role_assignments ?? []).length" class="text-slate-500">
                                            No roles assigned.
                                        </li>
                                    </ul>
                                </td>
                                <td v-if="canUpdateRoleStatus" class="px-4 py-3 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        <button
                                            v-for="assignment in (account.role_assignments ?? [])"
                                            :key="`toggle-${assignment.user_role_id}`"
                                            type="button"
                                            class="rbim-btn-outline px-3 py-1.5 text-xs"
                                            @click="handleRoleStatus(account, assignment)"
                                        >
                                            {{ assignment.enable ? 'Disable' : 'Enable' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.length">
                                <td :colspan="canUpdateRoleStatus ? 3 : 2" class="px-4 py-8 text-center text-slate-500">
                                    No user accounts found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-else class="space-y-4">
                <p class="text-sm text-slate-600">
                    Role permissions are read-only. Encoder and Guest have the same permissions.
                </p>
                <div class="rbim-card overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Role</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Permission</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(row, index) in ROLE_PERMISSION_ROWS" :key="`${row.role}-${index}`">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ row.role }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ row.permission }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
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
import { computed, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { ROLE_PERMISSION_ROWS } from '@/constants/roles';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as userService from '@/services/userService';

const route = useRoute();
const { hasPermission, isSuperAdmin } = useAuth();

const tabs = computed(() => {
    if (!isSuperAdmin.value) {
        return [];
    }

    return [
        hasPermission('pposition.view')
            ? { name: 'lookup-personnel-positions', label: 'Personnel Position' }
            : null,
        hasPermission('userrole.view')
            ? { name: 'lookup-user-roles', label: 'User Role' }
            : null,
        hasPermission('userrole.view')
            ? { name: 'lookup-role-permissions', label: 'Role Permission' }
            : null,
    ].filter(Boolean);
});

const lookup = computed(() => route.meta.lookup);
const pageTitle = computed(() => {
    if (lookup.value === 'user-roles') {
        return 'User Role';
    }

    if (lookup.value === 'role-permissions') {
        return 'Role Permission';
    }

    return 'Personnel Position';
});

const positions = ref([]);
const users = ref([]);
const creating = ref(false);
const deletingId = ref(null);
const newPositionName = ref('');
const createError = ref('');
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

const canCreatePosition = computed(() => hasPermission('pposition.create'));
const canDeletePosition = computed(() => hasPermission('pposition.delete'));
const canUpdateRoleStatus = computed(() => hasPermission('userrole.updatestatus'));

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

async function loadPositions() {
    error.value = '';

    try {
        positions.value = await lookupService.fetchPersonnelPositions();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load personnel positions.');
    }
}

async function loadUsers() {
    error.value = '';

    try {
        users.value = await userService.fetchUsers();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load user roles.');
    }
}

async function handleCreatePosition() {
    creating.value = true;
    createError.value = '';
    successMessage.value = '';

    try {
        const item = await lookupService.createPersonnelPosition({
            position_name: newPositionName.value.trim(),
        });
        positions.value = [...positions.value, item].sort((a, b) => a.label.localeCompare(b.label));
        newPositionName.value = '';
        successMessage.value = 'Position added successfully.';
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        createError.value = validationErrors.position_name
            ?? extractErrorMessage(err, 'Unable to add position.');
    } finally {
        creating.value = false;
    }
}

async function handleDeletePosition(id) {
    const allowed = await askConfirm({
        title: 'Delete position',
        message: 'Delete this personnel position? This cannot be undone.',
        confirmLabel: 'Delete',
        variant: 'danger',
    });

    if (!allowed) {
        return;
    }

    deletingId.value = id;
    error.value = '';
    successMessage.value = '';

    try {
        await lookupService.deletePersonnelPosition(id);
        positions.value = positions.value.filter((item) => item.id !== id);
        successMessage.value = 'Position deleted successfully.';
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to delete position.');
    } finally {
        deletingId.value = null;
    }
}

watch(lookup, (value) => {
    error.value = '';
    successMessage.value = '';

    if (value === 'personnel-positions') {
        loadPositions();
    }

    if (value === 'user-roles') {
        loadUsers();
    }
}, { immediate: true });

async function handleRoleStatus(account, assignment) {
    const allowed = await askConfirm({
        title: assignment.enable ? 'Disable role' : 'Enable role',
        message: `${assignment.enable ? 'Disable' : 'Enable'} ${assignment.role_name} for ${account.username}?`,
        confirmLabel: assignment.enable ? 'Disable' : 'Enable',
        variant: assignment.enable ? 'danger' : 'primary',
    });

    if (!allowed) {
        return;
    }

    error.value = '';

    try {
        const updated = await userService.updateUserRoleStatus(assignment.user_role_id, !assignment.enable);
        users.value = users.value.map((row) => {
            if (row.user_id !== account.user_id) {
                return row;
            }

            return {
                ...row,
                role_assignments: (row.role_assignments ?? []).map((item) => (
                    item.user_role_id === updated.user_role_id ? updated : item
                )),
                roles: (row.role_assignments ?? [])
                    .map((item) => (item.user_role_id === updated.user_role_id ? updated : item))
                    .filter((item) => item.enable)
                    .map((item) => item.role_name),
            };
        });
        successMessage.value = `Updated ${assignment.role_name} for ${account.username}.`;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to update role status.');
    }
}

</script>
