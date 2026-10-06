<template>
    <div ref="rootRef" class="relative shrink-0">
        <button
            type="button"
            class="flex max-w-[11rem] cursor-pointer items-center gap-2.5 rounded-lg py-1 text-left text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:max-w-[16rem] sm:px-2"
            aria-haspopup="dialog"
            :aria-expanded="open"
            aria-label="Open account menu"
            @click="toggleMenu"
        >
            <UserAvatar
                :src="user?.avatar_url"
                :preset="user?.avatar_preset"
                size-class="h-11 w-11"
                decorative
            />
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold uppercase tracking-[0.06em]">
                    {{ pillName }}
                </span>
                <span class="block truncate text-xs font-medium text-white/80">
                    {{ rolesLabel }}
                </span>
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 top-full z-50 mt-2 w-[21.5rem] max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-3xl border border-[#e3e8e2] bg-[#f4f6f1] shadow-xl"
            role="dialog"
            aria-label="Account"
        >
            <div class="relative px-4 py-3 text-center">
                <p class="truncate px-6 text-[13px] text-black">
                    {{ managedByLabel }}
                </p>
                <button
                    type="button"
                    class="absolute right-3 top-2.5 cursor-pointer rounded-full p-1 text-slate-600 transition hover:bg-black/5 hover:text-slate-900"
                    aria-label="Close account menu"
                    @click="closeMenu"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 01-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="border-t border-[#e3e8e2] px-4 py-4">
                <div class="flex items-center gap-3">
                    <UserAvatar
                        :src="user?.avatar_url"
                        :preset="user?.avatar_preset"
                        size-class="h-14 w-14"
                        :alt="displayName"
                    />
                    <div class="min-w-0">
                        <p class="truncate text-[15px] font-semibold uppercase tracking-wide text-slate-900">
                            {{ displayName }}
                        </p>
                        <p class="truncate text-sm text-black">
                            {{ positionLabel }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#e3e8e2] px-4 py-3.5">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0 text-black" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM3 18a7 7 0 1114 0H3z" />
                    </svg>
                    <p class="min-w-0 truncate text-sm font-medium text-black">
                        {{ rolesLabel }}
                    </p>
                </div>
            </div>

            <div class="border-t border-[#e3e8e2]">
                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-3 px-4 py-3.5 text-left text-sm font-medium text-slate-800 transition hover:bg-black/[0.03] disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading"
                    @click="handleLogout"
                >
                    <svg class="h-5 w-5 shrink-0 text-slate-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m9 0l-3-3m3 3l-3 3" />
                    </svg>
                    Log out
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import UserAvatar from '@/components/UserAvatar.vue';
import { useAuth } from '@/composables/useAuth';
import { fetchLocationProfile } from '@/services/householdService';
import { formatDisplayName } from '@/utils/format';

const router = useRouter();
const { user, roles, loading, logout } = useAuth();

const open = ref(false);
const barangayName = ref('');
const rootRef = ref(null);

const displayName = computed(() => (
    user.value?.full_name
    || formatDisplayName(user.value?.username)
    || 'User'
));

const pillName = computed(() => {
    const first = String(user.value?.first_name ?? '').trim().split(/\s+/)[0];

    if (first) {
        return first.toUpperCase();
    }

    return displayName.value.toUpperCase().split(/\s+/)[0] || 'USER';
});

const positionLabel = computed(() => user.value?.position_name || 'No position assigned');

const rolesLabel = computed(() => (
    roles.value.length ? roles.value.join(', ') : 'No role assigned'
));

const managedByLabel = computed(() => {
    const barangay = barangayName.value.trim();

    if (!barangay) {
        return 'Managed by RBIM';
    }

    return `Managed by ${barangay}`;
});

function toggleMenu() {
    open.value = !open.value;

    if (open.value) {
        loadBarangayName();
    }
}

function closeMenu() {
    open.value = false;
}

async function handleLogout() {
    closeMenu();
    await logout();
    await router.push({ name: 'login' });
}

function onDocumentPointerDown(event) {
    if (!open.value) {
        return;
    }

    if (rootRef.value && !rootRef.value.contains(event.target)) {
        closeMenu();
    }
}

function onEscape(event) {
    if (event.key === 'Escape') {
        closeMenu();
    }
}

async function loadBarangayName() {
    if (barangayName.value) {
        return;
    }

    try {
        const location = await fetchLocationProfile();
        barangayName.value = location?.barangay ?? '';
    } catch {
        barangayName.value = '';
    }
}

onMounted(() => {
    document.addEventListener('pointerdown', onDocumentPointerDown);
    document.addEventListener('keydown', onEscape);
});

onUnmounted(() => {
    document.removeEventListener('pointerdown', onDocumentPointerDown);
    document.removeEventListener('keydown', onEscape);
});
</script>
