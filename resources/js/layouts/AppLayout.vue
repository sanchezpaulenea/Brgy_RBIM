<template>
    <div class="rbim-shell min-h-screen bg-[#f4f6f5] text-slate-900">
        <header class="fixed inset-x-0 top-0 z-40 h-20 bg-brand text-white shadow-md">
            <div class="flex h-full items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-white/90 hover:bg-white/10 md:hidden"
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
                    <UserProfileMenu />
                </div>
            </div>
        </header>

        <div class="pt-20 md:pl-[var(--rbim-sidebar)]">
            <button
                v-if="sidebarOpen"
                type="button"
                class="fixed inset-0 z-20 bg-black/40 md:hidden"
                aria-label="Close menu"
                @click="sidebarOpen = false"
            />
            <aside
                class="fixed bottom-0 left-0 top-20 z-30 w-[var(--rbim-sidebar)] max-w-[18.5rem] overflow-x-hidden overflow-y-auto bg-brand text-white shadow-lg transition-transform duration-200 md:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            >
                <nav class="flex flex-col gap-1 p-3">
                    <RouterLink
                        :to="{ name: 'dashboard' }"
                        class="flex items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="route.name === 'dashboard' ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 11h1v6a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-6h1a1 1 0 00.707-1.707l-7-7z" />
                        </svg>
                        <span>Dashboard</span>
                    </RouterLink>

                    <div v-if="canViewHouseholdMenu">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isHouseholdRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="openSection('household')"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1">Household Management</span>
                            <span class="text-xs">{{ openMenu === 'household' ? '▾' : '▸' }}</span>
                        </button>

                        <div v-show="openMenu === 'household'" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-for="tab in householdTabs"
                                :key="tab.name"
                                :to="{ name: tab.name }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="isSidebarTabActive(tab) ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                {{ tab.label }}
                            </RouterLink>
                        </div>
                    </div>

                    <div v-if="canViewResidentMenu">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isResidentRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="openSection('resident')"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                            <span class="flex-1">Resident Management</span>
                            <span class="text-xs">{{ openMenu === 'resident' ? '▾' : '▸' }}</span>
                        </button>

                        <div v-show="openMenu === 'resident'" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-for="tab in residentTabs"
                                :key="tab.name"
                                :to="{ name: tab.name }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="isSidebarTabActive(tab) ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                {{ tab.label }}
                            </RouterLink>
                        </div>
                    </div>

                    <RouterLink
                        v-if="personnelTabs.length === 1"
                        :to="{ name: personnelTabs[0].name }"
                        class="flex items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="isPersonnelRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18h4v-3a3 3 0 00-4.56-2.56A5.99 5.99 0 0116 15v3zM4.56 12.44A3 3 0 000 15v3h4v-3c0-.91.2-1.78.56-2.56z" />
                        </svg>
                        <span class="whitespace-nowrap">Barangay Personnel Management</span>
                    </RouterLink>
                    <div v-else-if="personnelTabs.length > 1">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isPersonnelRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="openSection('personnel')"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18h4v-3a3 3 0 00-4.56-2.56A5.99 5.99 0 0116 15v3zM4.56 12.44A3 3 0 000 15v3h4v-3c0-.91.2-1.78.56-2.56z" />
                            </svg>
                            <span class="flex-1 whitespace-nowrap">Barangay Personnel Management</span>
                            <span class="text-xs">{{ openMenu === 'personnel' ? '▾' : '▸' }}</span>
                        </button>
                        <div v-show="openMenu === 'personnel'" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-for="tab in personnelTabs"
                                :key="tab.name"
                                :to="{ name: tab.name }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="isSidebarTabActive(tab) ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                {{ tab.label }}
                            </RouterLink>
                        </div>
                    </div>

                    <RouterLink
                        v-if="userTabs.length === 1"
                        :to="{ name: userTabs[0].name }"
                        class="flex items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="isUsersRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        <span>User Account Management</span>
                    </RouterLink>
                    <div v-else-if="userTabs.length > 1">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isUsersRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="openSection('users')"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1">User Account Management</span>
                            <span class="text-xs">{{ openMenu === 'users' ? '▾' : '▸' }}</span>
                        </button>
                        <div v-show="openMenu === 'users'" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-for="tab in userTabs"
                                :key="tab.name"
                                :to="{ name: tab.name }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="isSidebarTabActive(tab) ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                {{ tab.label }}
                            </RouterLink>
                        </div>
                    </div>

                    <div v-if="canViewSettingsMenu">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isSettingsRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="openSection('settings')"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.53 1.53 0 01-2.29.95c-1.37-.84-2.94.73-2.1 2.1.54.88.1 2.03-.95 2.28-1.56.38-1.56 2.6 0 2.98a1.53 1.53 0 01.95 2.29c-.84 1.37.73 2.94 2.1 2.1a1.53 1.53 0 012.28.95c.38 1.56 2.6 1.56 2.98 0a1.53 1.53 0 012.29-.95c1.37.84 2.94-.73 2.1-2.1a1.53 1.53 0 01.95-2.28c1.56-.38 1.56-2.6 0-2.98a1.53 1.53 0 01-.95-2.29c.84-1.37-.73-2.94-2.1-2.1a1.53 1.53 0 01-2.28-.95zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1">Settings</span>
                            <span class="text-xs">{{ openMenu === 'settings' ? '▾' : '▸' }}</span>
                        </button>

                        <div v-show="openMenu === 'settings'" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-if="canViewAuditLogs"
                                :to="{ name: 'audit-logs' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'audit-logs' ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                Audit log
                            </RouterLink>
                            <RouterLink
                                v-if="canViewUserLogs"
                                :to="{ name: 'user-logs' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'user-logs' ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                User log
                            </RouterLink>
                            <RouterLink
                                v-if="canViewSystemSettings"
                                :to="{ name: 'settings' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'settings' ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
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
import { computed, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import BrandLogos from '@/components/BrandLogos.vue';
import UserProfileMenu from '@/components/UserProfileMenu.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const route = useRoute();
const router = useRouter();
const { hasPermission } = useAuth();
const { householdTabs, residentTabs, personnelTabs, userTabs } = useSectionTabs();

const sidebarOpen = ref(false);
const openMenu = ref(null);

const canViewHouseholdMenu = computed(() => householdTabs.value.length > 0);
const canViewResidentMenu = computed(() => residentTabs.value.length > 0);

const canViewSystemSettings = computed(() => (
    hasPermission('setting.view') || hasPermission('setting.update')
));

const canViewAuditLogs = computed(() => hasPermission('auditlog.view'));
const canViewUserLogs = computed(() => hasPermission('userlog.view'));
const canViewLogs = computed(() => canViewAuditLogs.value || canViewUserLogs.value);

const canViewSettingsMenu = computed(() => (
    canViewSystemSettings.value || canViewLogs.value
));

const isHouseholdRoute = computed(() => String(route.path).startsWith('/households'));
const isHouseholdViewRoute = computed(() => (
    route.name === 'households' || route.name === 'household-detail'
));
const isResidentRoute = computed(() => String(route.path).startsWith('/residents'));
const isSettingsRoute = computed(() => String(route.path).startsWith('/settings'));
const isPersonnelRoute = computed(() => String(route.path).startsWith('/personnel'));
const isUsersRoute = computed(() => String(route.path).startsWith('/users'));

function isSidebarTabActive(tab) {
    if (tab.name === 'households') {
        return isHouseholdViewRoute.value;
    }

    return route.name === tab.name;
}

function menuForRoute() {
    if (isHouseholdRoute.value) {
        return 'household';
    }

    if (isResidentRoute.value) {
        return 'resident';
    }

    if (isPersonnelRoute.value) {
        return 'personnel';
    }

    if (isUsersRoute.value) {
        return 'users';
    }

    if (isSettingsRoute.value) {
        return 'settings';
    }

    return null;
}

function defaultRouteFor(section) {
    const firstTab = {
        household: householdTabs.value[0]?.name,
        resident: residentTabs.value[0]?.name,
        personnel: personnelTabs.value[0]?.name,
        users: userTabs.value[0]?.name,
        settings: [
            canViewAuditLogs.value ? 'audit-logs' : null,
            canViewUserLogs.value ? 'user-logs' : null,
            canViewSystemSettings.value ? 'settings' : null,
        ].find(Boolean),
    }[section];

    return firstTab ?? null;
}

function toggleMenu(name) {
    openMenu.value = openMenu.value === name ? null : name;
}

function isCurrentSection(name) {
    return menuForRoute() === name;
}

function openSection(name) {
    const target = defaultRouteFor(name);

    if (target && !isCurrentSection(name)) {
        openMenu.value = name;
        router.push({ name: target });
        sidebarOpen.value = false;

        return;
    }

    toggleMenu(name);
}

watch(() => route.path, () => {
    openMenu.value = menuForRoute();
}, { immediate: true });
</script>
