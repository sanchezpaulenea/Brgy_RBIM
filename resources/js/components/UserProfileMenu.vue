<template>
    <div ref="rootRef" class="relative shrink-0">
        <button
            type="button"
            class="flex max-w-[12.5rem] cursor-pointer items-center gap-2.5 rounded-2xl bg-white/10 py-1.5 pl-1.5 pr-3 text-left text-white shadow-sm ring-1 ring-white/25 transition hover:bg-white/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:max-w-[16.5rem]"
            aria-haspopup="dialog"
            :aria-expanded="open"
            aria-label="Open account menu"
            @click="toggleMenu"
        >
            <UserAvatar
                :src="user?.avatar_url"
                :preset="user?.avatar_preset"
                size-class="h-10 w-10 ring-2 ring-white/80"
                decorative
            />
            <span class="min-w-0">
                <span class="block truncate text-xs font-semibold uppercase tracking-[0.08em]">
                    {{ pillName }}
                </span>
                <span class="block truncate text-[11px] text-white/75">
                    {{ rolesLabel }}
                </span>
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 top-full z-50 mt-3 w-[22.5rem] max-w-[calc(100vw-1.5rem)] rounded-[28px] bg-[#eef3ea] p-3 shadow-2xl"
            role="dialog"
            aria-label="Account"
        >
            <div class="relative rounded-full bg-[#f7faf4] px-8 py-2 text-center">
                <p class="truncate text-[13px] text-slate-500">
                    {{ managedByLabel }}
                </p>
                <button
                    type="button"
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 cursor-pointer rounded-full p-1 text-slate-500 transition hover:bg-white hover:text-slate-700"
                    aria-label="Close account menu"
                    @click="closeMenu"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 01-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="mt-2 overflow-hidden rounded-[22px] bg-white shadow-sm">
            <div class="px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        <UserAvatar
                            :src="user?.avatar_url"
                            :preset="user?.avatar_preset"
                            class="transition"
                            :class="{ 'opacity-60': saving }"
                            size-class="h-14 w-14"
                            :alt="displayName"
                        />
                        <button
                            type="button"
                            class="absolute -bottom-0.5 -right-0.5 inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50"
                            aria-label="Change profile photo"
                            :aria-expanded="pickerOpen"
                            @click.stop="pickerOpen = !pickerOpen"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[15px] font-semibold uppercase tracking-wide text-slate-900">
                            {{ displayName }}
                        </p>
                        <p class="truncate text-sm text-slate-500">
                            {{ positionLabel }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="pickerOpen"
                    class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 py-1"
                    role="menu"
                >
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-white disabled:cursor-not-allowed disabled:opacity-60"
                        role="menuitem"
                        :disabled="saving"
                        @click="chooseFile"
                    >
                        Upload photo
                    </button>
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-white disabled:cursor-not-allowed disabled:opacity-60"
                        role="menuitem"
                        :disabled="saving"
                        @click="choosePreset('male')"
                    >
                        Male avatar
                    </button>
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-white disabled:cursor-not-allowed disabled:opacity-60"
                        role="menuitem"
                        :disabled="saving"
                        @click="choosePreset('female')"
                    >
                        Female avatar
                    </button>
                </div>
                <p v-if="avatarError" class="mt-2 text-xs text-red-600">{{ avatarError }}</p>
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    class="sr-only"
                    @change="onFileSelected"
                >
            </div>

            <div class="flex items-center gap-3 border-t border-slate-200 px-4 py-3">
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center text-slate-700">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM3 18a7 7 0 1114 0H3z" />
                    </svg>
                </span>
                <p class="min-w-0 truncate text-sm font-medium text-slate-800">
                    {{ rolesLabel }}
                </p>
            </div>

            <div class="border-t border-slate-200">
                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-3 px-4 py-3.5 text-left text-sm font-medium text-slate-800 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading"
                    @click="handleLogout"
                >
                    <span class="inline-flex h-8 w-8 items-center justify-center text-slate-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </span>
                    Log out
                </button>
            </div>
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
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import { formatDisplayName } from '@/utils/format';

const router = useRouter();
const { user, roles, loading, logout, updateAvatar } = useAuth();

const open = ref(false);
const pickerOpen = ref(false);
const saving = ref(false);
const avatarError = ref('');
const barangayName = ref('');
const rootRef = ref(null);
const fileInput = ref(null);

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
    pickerOpen.value = false;
    avatarError.value = '';

    if (open.value) {
        loadBarangayName();
    }
}

function closeMenu() {
    open.value = false;
    pickerOpen.value = false;
    avatarError.value = '';
}

function chooseFile() {
    pickerOpen.value = false;
    fileInput.value?.click();
}

async function choosePreset(preset) {
    pickerOpen.value = false;
    avatarError.value = '';
    saving.value = true;

    try {
        await updateAvatar({ avatarPreset: preset });
    } catch (error) {
        avatarError.value = avatarFailureMessage(error, 'Could not update the avatar.');
    } finally {
        saving.value = false;
    }
}

async function onFileSelected(event) {
    const file = event.target.files?.[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        avatarError.value = 'The profile photo must not be larger than 2 MB.';

        return;
    }

    avatarError.value = '';
    saving.value = true;

    try {
        await updateAvatar({ photo: file });
    } catch (error) {
        avatarError.value = avatarFailureMessage(error, 'Could not upload the photo.');
    } finally {
        saving.value = false;
    }
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

function avatarFailureMessage(error, fallback) {
    const errors = extractValidationErrors(error);

    return errors.photo || errors.avatar_preset || extractErrorMessage(error, fallback);
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
