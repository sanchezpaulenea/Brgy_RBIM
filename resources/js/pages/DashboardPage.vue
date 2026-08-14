<template>
    <AppLayout title="Dashboard">
        <section class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900">
                    Welcome back, {{ user?.username }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">
                    You are signed in to the Barangay Resident and Barangay Information Management System.
                </p>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Account status
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">
                            {{ user?.user_status ?? 'Active' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Password status
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">
                            {{ user?.must_change_password ? 'Change required' : 'Up to date' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-wrap gap-3">
                    <RouterLink
                        to="/lookups"
                        class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800"
                    >
                        Browse lookups
                    </RouterLink>
                    <RouterLink
                        v-if="isSystemAdministrator"
                        to="/settings"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Manage settings
                    </RouterLink>
                </div>
            </article>

            <aside v-if="isSystemAdministrator" class="space-y-6">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Roles
                    </h3>
                    <ul class="mt-3 space-y-2">
                        <li
                            v-for="role in roles"
                            :key="role"
                            class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-800"
                        >
                            {{ role }}
                        </li>
                        <li v-if="!roles.length" class="text-sm text-slate-500">
                            No roles assigned.
                        </li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Permissions
                    </h3>
                    <ul class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                        <li
                            v-for="permission in permissions"
                            :key="permission"
                            class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700"
                        >
                            {{ permission }}
                        </li>
                        <li v-if="!permissions.length" class="text-sm text-slate-500">
                            No permissions assigned.
                        </li>
                    </ul>
                </article>
            </aside>
        </section>
    </AppLayout>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';

const { user, roles, permissions, isSystemAdministrator } = useAuth();
</script>
