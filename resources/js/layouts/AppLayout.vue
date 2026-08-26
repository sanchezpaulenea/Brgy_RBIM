<template>
    <div class="min-h-screen bg-[#f4f6f5] text-slate-900">
        <header class="fixed inset-x-0 top-0 z-40 h-20 bg-brand text-white shadow-md">
            <div class="flex h-full items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-white/90 hover:bg-white/10 lg:hidden"
                        aria-label="Toggle menu"
                        @click="sidebarOpen = !sidebarOpen"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 5h14a1 1 0 010 2H3a1 1 0 010-2zm0 4h14a1 1 0 010 2H3a1 1 0 010-2zm0 4h14a1 1 0 010 2H3a1 1 0 010-2z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <BrandLogos size-class="h-11 w-11 sm:h-14 sm:w-14" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold sm:text-base">
                            Registry of Barangay Inhabitants and Migrants
                        </p>
                        <p v-if="title" class="truncate text-xs text-white/80">
                            {{ title }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-md bg-white"
                        aria-hidden="true"
                    >
                        <svg class="h-9 w-9" viewBox="0 0 40 40" fill="none">
                            <path fill="#e07a3d" d="M4 40c2.2-11 9.4-16.5 16-16.5S33.8 29 36 40H4Z" />
                            <path fill="#fff" d="M16.5 24.2 20 29.5l3.5-5.3H16.5Z" />
                            <circle cx="20" cy="14.5" r="8.2" fill="#e8c39e" />
                            <path fill="#6b4423" d="M12.2 14.8c.4-6.4 4-10.3 7.8-10.3 3.9 0 7.4 3.9 7.8 10.3-.8-4.6-3.4-7.4-7.8-7.4s-7 2.8-7.8 7.4Z" />
                            <circle cx="17.2" cy="15.2" r="1.1" fill="#3a2a1a" />
                            <circle cx="22.8" cy="15.2" r="1.1" fill="#3a2a1a" />
                            <path stroke="#3a2a1a" stroke-linecap="round" stroke-width="1" d="M17.6 18.8c1.4 1.6 3.4 1.6 4.8 0" />
                        </svg>
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-sm font-semibold uppercase tracking-wide text-[#f0c14b]">
                            {{ user?.username }}
                        </p>
                        <button
                            type="button"
                            class="cursor-pointer text-sm text-[#7ec8e3] transition hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="loading"
                            @click="handleLogout"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="pt-20 lg:pl-72">
            <aside
                class="fixed inset-x-0 top-20 bottom-0 z-30 overflow-y-auto bg-brand text-white shadow-lg lg:right-auto lg:w-72"
                :class="sidebarOpen ? 'block' : 'hidden lg:block'"
            >
                <nav class="flex flex-col gap-1 p-3">
                    <RouterLink
                        :to="{ name: 'dashboard' }"
                        class="flex items-center gap-2.5 whitespace-nowrap rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="route.name === 'dashboard' ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 11h1v6a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-6h1a1 1 0 00.707-1.707l-7-7z" />
                        </svg>
                        <span>Dashboard</span>
                    </RouterLink>

                    <RouterLink
                        v-if="canEncodeHouseholds"
                        :to="{ name: 'household-encoding' }"
                        class="flex items-center gap-2.5 whitespace-nowrap rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="isHouseholdRoute ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm4 1.414L14.586 8H11a1 1 0 01-1-1V3.414zM7 11a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm1 3a1 1 0 100 2h4a1 1 0 100-2H8z" clip-rule="evenodd" />
                        </svg>
                        <span>Household Encoding</span>
                    </RouterLink>

                    <RouterLink
                        v-if="canManagePersonnel"
                        :to="{ name: 'personnel' }"
                        class="flex items-center gap-2.5 whitespace-nowrap rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="isPersonnelRoute ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18h4v-3a3 3 0 00-4.56-2.56A5.99 5.99 0 0116 15v3zM4.56 12.44A3 3 0 000 15v3h4v-3c0-.91.2-1.78.56-2.56z" />
                        </svg>
                        <span>Barangay Personnel Management</span>
                    </RouterLink>

                    <RouterLink
                        v-if="canManageUsers"
                        :to="{ name: 'users' }"
                        class="flex items-center gap-2.5 whitespace-nowrap rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="isUsersRoute ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        <span>User Account Management</span>
                    </RouterLink>

                    <div v-if="canViewSettingsMenu">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 whitespace-nowrap rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isSettingsRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="settingsOpen = !settingsOpen"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.53 1.53 0 01-2.29.95c-1.37-.84-2.94.73-2.1 2.1.54.88.1 2.03-.95 2.28-1.56.38-1.56 2.6 0 2.98a1.53 1.53 0 01.95 2.29c-.84 1.37.73 2.94 2.1 2.1a1.53 1.53 0 012.28.95c.38 1.56 2.6 1.56 2.98 0a1.53 1.53 0 012.29-.95c1.37.84 2.94-.73 2.1-2.1a1.53 1.53 0 01.95-2.28c1.56-.38 1.56-2.6 0-2.98a1.53 1.53 0 01-.95-2.29c.84-1.37-.73-2.94-2.1-2.1a1.53 1.53 0 01-2.28-.95zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1">Settings</span>
                            <span class="text-xs">{{ settingsOpen ? '▾' : '▸' }}</span>
                        </button>

                        <div v-show="settingsOpen || isSettingsRoute" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-if="canViewAuditLogs"
                                :to="{ name: 'audit-logs' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'audit-logs' ? 'bg-white text-brand' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                Audit log
                            </RouterLink>
                            <RouterLink
                                v-if="canViewUserLogs"
                                :to="{ name: 'user-logs' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'user-logs' ? 'bg-white text-brand' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                User log
                            </RouterLink>
                            <RouterLink
                                v-if="canViewSystemSettings"
                                :to="{ name: 'settings' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'settings' ? 'bg-white text-brand' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                System settings
                            </RouterLink>
                        </div>
                    </div>
                </nav>
            </aside>

            <main class="min-w-0 px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import BrandLogos from '@/components/BrandLogos.vue';
import { useAuth } from '@/composables/useAuth';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const route = useRoute();
const router = useRouter();
const { user, loading, logout, hasPermission, isSystemAdministrator, isEncoder } = useAuth();

const sidebarOpen = ref(false);
const settingsOpen = ref(true);

const canEncodeHouseholds = computed(() => isEncoder.value);

const canManagePersonnel = computed(() => (
    hasPermission('personnel.view')
    || hasPermission('personnel.create')
    || hasPermission('personnel.update')
));

const canManageUsers = computed(() => hasPermission('user.view'));

const canViewSystemSettings = computed(() => (
    hasPermission('setting.view') || hasPermission('setting.update')
));

const canViewAuditLogs = computed(() => isSystemAdministrator.value);
const canViewUserLogs = computed(() => hasPermission('userlog.view'));
const canViewLogs = computed(() => canViewAuditLogs.value || canViewUserLogs.value);

const canViewSettingsMenu = computed(() => (
    canViewSystemSettings.value || canViewLogs.value
));

const isHouseholdRoute = computed(() => String(route.path).startsWith('/households'));
const isSettingsRoute = computed(() => String(route.path).startsWith('/settings'));
const isPersonnelRoute = computed(() => String(route.path).startsWith('/personnel'));
const isUsersRoute = computed(() => String(route.path).startsWith('/users'));

async function handleLogout() {
    await logout();
    await router.push({ name: 'login' });
}
</script>
