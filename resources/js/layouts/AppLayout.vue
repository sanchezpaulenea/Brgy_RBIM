<template>
    <div class="min-h-screen bg-[#f4f6f5] text-slate-900">
        <header class="bg-brand text-white">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
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
                    <BrandLogos size-class="h-10 w-10 sm:h-12 sm:w-12" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold sm:text-base">
                            Registry of Barangay Inhabitants and Migrants
                        </p>
                        <p v-if="title" class="truncate text-xs text-white/80">
                            {{ title }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium">{{ user?.username }}</p>
                        <p class="text-xs text-white/75">{{ rolesLabel }}</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-sm font-medium text-white transition hover:bg-white/20 disabled:opacity-60"
                        :disabled="loading"
                        @click="handleLogout"
                    >
                        Log out
                    </button>
                </div>
            </div>
        </header>

        <div class="flex">
            <aside
                class="bg-brand-dark text-white lg:sticky lg:top-0 lg:flex lg:h-[calc(100vh-4.5rem)] lg:w-64 lg:shrink-0 lg:flex-col"
                :class="sidebarOpen ? 'block' : 'hidden lg:flex'"
            >
                <nav class="flex flex-col gap-1 p-3">
                    <RouterLink
                        :to="{ name: 'dashboard' }"
                        class="flex items-center justify-center rounded-lg px-3 py-2 transition"
                        :class="route.name === 'dashboard' ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        aria-label="Home"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 11h1v6a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-6h1a1 1 0 00.707-1.707l-7-7z" />
                        </svg>
                    </RouterLink>

                    <RouterLink
                        v-if="canManagePersonnel"
                        :to="{ name: 'personnel' }"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="isPersonnelRoute ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        Barangay Personnel Management
                    </RouterLink>

                    <RouterLink
                        v-if="canManageUsers"
                        :to="{ name: 'users' }"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="isUsersRoute ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        User Account Management
                    </RouterLink>

                    <div v-if="canViewSettingsMenu">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                            :class="isSettingsRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="settingsOpen = !settingsOpen"
                        >
                            <span>Settings</span>
                            <span class="text-xs">{{ settingsOpen ? '▾' : '▸' }}</span>
                        </button>

                        <div v-show="settingsOpen || isSettingsRoute" class="ml-3 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-if="canViewAuditLogs"
                                :to="{ name: 'audit-logs' }"
                                class="block rounded-lg px-2 py-1.5 text-sm transition"
                                :class="route.name === 'audit-logs' ? 'bg-white text-brand' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                Audit log
                            </RouterLink>
                            <RouterLink
                                v-if="canViewUserLogs"
                                :to="{ name: 'user-logs' }"
                                class="block rounded-lg px-2 py-1.5 text-sm transition"
                                :class="route.name === 'user-logs' ? 'bg-white text-brand' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                User log
                            </RouterLink>
                            <RouterLink
                                v-if="canViewSystemSettings"
                                :to="{ name: 'settings' }"
                                class="block rounded-lg px-2 py-1.5 text-sm transition"
                                :class="route.name === 'settings' ? 'bg-white text-brand' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                System settings
                            </RouterLink>
                        </div>
                    </div>
                </nav>
            </aside>

            <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8">
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
const { user, roles, loading, logout, hasPermission, isSystemAdministrator } = useAuth();

const sidebarOpen = ref(false);
const settingsOpen = ref(true);

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

const isSettingsRoute = computed(() => String(route.path).startsWith('/settings'));
const isPersonnelRoute = computed(() => String(route.path).startsWith('/personnel'));
const isUsersRoute = computed(() => String(route.path).startsWith('/users'));

const rolesLabel = computed(() => {
    if (!roles.value.length) {
        return 'No role assigned';
    }

    return roles.value.join(', ');
});

async function handleLogout() {
    await logout();
    await router.push({ name: 'login' });
}
</script>
