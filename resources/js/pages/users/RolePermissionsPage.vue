<template>
    <AppLayout title="Role Permission">
        <div class="space-y-6">
            <PageTabs :tabs="userTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <p class="text-sm text-slate-600">
                Role permissions are read-only. Each role below lists everything an account with that
                role is allowed to do.
            </p>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading role permissions...
            </div>
            <div v-else class="space-y-6">
                <article v-for="role in roles" :key="role.role_id" class="rbim-card p-6">
                    <header class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-base font-semibold text-slate-900">{{ role.role_name }}</h2>
                        <span class="rounded-full bg-brand-muted px-3 py-1 text-xs font-medium text-brand">
                            {{ role.permissions.length }}
                            {{ role.permissions.length === 1 ? 'permission' : 'permissions' }}
                        </span>
                    </header>

                    <div v-if="role.permissions.length" class="mt-4 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                        <section v-for="group in groupPermissions(role.permissions)" :key="`${role.role_id}-${group.group}`">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ group.group }}
                            </h3>
                            <ul class="mt-2 space-y-1.5">
                                <li
                                    v-for="permission in group.items"
                                    :key="permission"
                                    class="flex items-start gap-2 text-sm text-slate-700"
                                >
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.29 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ permission }}</span>
                                </li>
                            </ul>
                        </section>
                    </div>
                    <p v-else class="mt-3 text-sm text-slate-500">
                        No permissions assigned to this role.
                    </p>
                </article>

                <div v-if="!roles.length" class="rbim-card p-8 text-center text-sm text-slate-500">
                    No roles found.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { ROLE_PRIVILEGE_ORDER } from '@/constants/roles';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as rolePermissionService from '@/services/rolePermissionService';
import { groupPermissions } from '@/utils/permissions';

const { userTabs } = useSectionTabs();

const roles = ref([]);
const loading = ref(false);
const error = ref('');

function sortRolesByPrivilege(items) {
    return [...items].sort((left, right) => {
        const leftIndex = ROLE_PRIVILEGE_ORDER.indexOf(left.role_name);
        const rightIndex = ROLE_PRIVILEGE_ORDER.indexOf(right.role_name);

        return (leftIndex === -1 ? ROLE_PRIVILEGE_ORDER.length : leftIndex)
            - (rightIndex === -1 ? ROLE_PRIVILEGE_ORDER.length : rightIndex);
    });
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        roles.value = sortRolesByPrivilege(await rolePermissionService.fetchRolePermissions());
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load role permissions.');
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
