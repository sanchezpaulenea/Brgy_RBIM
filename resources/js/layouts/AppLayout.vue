<template>
    <div class="rbim-shell min-h-screen bg-[#f4f6f5] text-slate-900">
        <header class="fixed inset-x-0 top-0 z-40 h-28 bg-brand text-white shadow-md">
            <div class="flex h-full items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-4">
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
                    <BrandLogos size-class="h-16 w-16 sm:h-20 sm:w-20" />
                    <div class="min-w-0">
                        <p class="truncate text-lg font-semibold sm:text-xl">
                            Barangay Happy Hallow Census
                        </p>
                        <p v-if="title" class="truncate text-sm text-white/80 sm:text-base">
                            {{ title }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2.5">
                    <UserProfileMenu />
                </div>
            </div>
        </header>

        <div class="pt-28 md:pl-[var(--rbim-sidebar)]">
            <button
                v-if="sidebarOpen"
                type="button"
                class="fixed inset-0 z-20 bg-black/40 md:hidden"
                aria-label="Close menu"
                @click="sidebarOpen = false"
            />
            <aside
                class="fixed bottom-0 left-0 top-28 z-30 w-[var(--rbim-sidebar)] max-w-[22rem] overflow-x-hidden overflow-y-auto bg-brand text-white shadow-lg transition-transform duration-200 md:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            >
                <nav class="flex flex-col gap-1 p-3">
                    <RouterLink
                        :to="{ name: 'dashboard' }"
                        class="flex items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="String(route.path).startsWith('/dashboard') ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
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
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Household Management</span>
                            <span class="ml-auto inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs leading-none">{{ openMenu === 'household' ? '▾' : '▸' }}</span>
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
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Resident Management</span>
                            <span class="ml-auto inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs leading-none">{{ openMenu === 'resident' ? '▾' : '▸' }}</span>
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
                        v-if="canViewReports"
                        :to="{ name: 'reports' }"
                        class="flex items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-[13px] font-medium transition"
                        :class="isReportsRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span>Report Generation</span>
                    </RouterLink>

                    <div v-if="canViewCertificates">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 min-w-0 rounded-lg px-3 py-2 text-left text-[13px] font-medium transition"
                            :class="isCertificatesRoute ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10'"
                            @click="openSection('certificates')"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Certificate Generation</span>
                            <span class="ml-auto inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs leading-none">{{ openMenu === 'certificates' ? '▾' : '▸' }}</span>
                        </button>

                        <div v-show="openMenu === 'certificates'" class="ml-6 mt-1 space-y-1 border-l border-white/20 pl-3">
                            <RouterLink
                                v-for="tab in certificateTabs"
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
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
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
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Barangay Personnel Management</span>
                            <span class="ml-auto inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs leading-none">{{ openMenu === 'personnel' ? '▾' : '▸' }}</span>
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
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
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
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">User Account Management</span>
                            <span class="ml-auto inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs leading-none">{{ openMenu === 'users' ? '▾' : '▸' }}</span>
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
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Settings</span>
                            <span class="ml-auto inline-flex h-4 w-4 shrink-0 items-center justify-center text-xs leading-none">{{ openMenu === 'settings' ? '▾' : '▸' }}</span>
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
                                v-if="canViewLogRetention"
                                :to="{ name: 'log-retention' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'log-retention' ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                Log Retention and Archiving
                            </RouterLink>
                            <RouterLink
                                v-if="canViewDatabaseBackup"
                                :to="{ name: 'database-backup' }"
                                class="block rounded-lg px-2 py-1.5 text-[13px] transition"
                                :class="route.name === 'database-backup' ? 'bg-white/15 text-white' : 'text-white/85 hover:bg-white/10'"
                                @click="sidebarOpen = false"
                            >
                                Database Backup and Restore
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
const { hasPermission, isSystemAdministrator } = useAuth();
const { householdTabs, residentTabs, personnelTabs, userTabs, certificateTabs } = useSectionTabs();

const sidebarOpen = ref(false);
const openMenu = ref(null);

const canViewHouseholdMenu = computed(() => householdTabs.value.length > 0);
const canViewResidentMenu = computed(() => residentTabs.value.length > 0);
const canViewReports = computed(() => hasPermission('report.view'));
const canViewCertificates = computed(() => hasPermission('certificate.view'));

const canViewSystemSettings = computed(() => (
    isSystemAdministrator.value && hasPermission('setting.view')
));

const canViewAuditLogs = computed(() => hasPermission('auditlog.view'));
const canViewUserLogs = computed(() => hasPermission('userlog.view'));
const canViewLogRetention = computed(() => hasPermission('retention.view'));
const canViewDatabaseBackup = computed(() => (
    hasPermission('backup.export') || hasPermission('backup.restore')
));
const canViewLogs = computed(() => canViewAuditLogs.value || canViewUserLogs.value);

const canViewSettingsMenu = computed(() => (
    canViewSystemSettings.value || canViewLogs.value || canViewLogRetention.value || canViewDatabaseBackup.value
));

const isHouseholdRoute = computed(() => String(route.path).startsWith('/households'));
const isHouseholdViewRoute = computed(() => (
    route.name === 'households' || route.name === 'household-detail'
));
const isResidentRoute = computed(() => String(route.path).startsWith('/residents'));
const isReportsRoute = computed(() => route.name === 'reports');
const isCertificatesRoute = computed(() => String(route.path).startsWith('/certificates'));
const isSettingsRoute = computed(() => String(route.path).startsWith('/settings'));
const isPersonnelRoute = computed(() => String(route.path).startsWith('/personnel'));
const isUsersRoute = computed(() => String(route.path).startsWith('/users'));

function isSidebarTabActive(tab) {
    if (tab.name === 'households') {
        return isHouseholdViewRoute.value;
    }

    if (tab.name === 'residents') {
        return route.name === 'residents' || route.name === 'resident-detail';
    }

    if (tab.name === 'certificate-history') {
        return route.name === 'certificate-history' || route.name === 'certificate-history-detail';
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

    if (isCertificatesRoute.value) {
        return 'certificates';
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
        certificates: certificateTabs.value[0]?.name,
        personnel: personnelTabs.value[0]?.name,
        users: userTabs.value[0]?.name,
        settings: [
            canViewAuditLogs.value ? 'audit-logs' : null,
            canViewUserLogs.value ? 'user-logs' : null,
            canViewLogRetention.value ? 'log-retention' : null,
            canViewDatabaseBackup.value ? 'database-backup' : null,
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
