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
                    <div class="relative shrink-0">
                        <UserAvatar
                            :src="user?.avatar_url"
                            :preset="user?.avatar_preset"
                            size-class="h-14 w-14"
                            :alt="displayName"
                        />
                        <button
                            type="button"
                            class="absolute -bottom-0.5 -right-0.5 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border-2 border-[#f4f6f1] bg-white text-slate-700 shadow-sm transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2d4c39]"
                            aria-label="Change profile picture"
                            @click="openEditor"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                    </div>
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

            <div class="border-t border-[#e3e8e2] px-4 py-3.5">
                <p id="appearance-label" class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Appearance
                </p>
                <div
                    class="grid grid-cols-2 gap-1 rounded-xl bg-slate-200/70 p-1"
                    role="radiogroup"
                    aria-labelledby="appearance-label"
                >
                    <button
                        v-for="option in themeOptions"
                        :key="option.value"
                        type="button"
                        role="radio"
                        class="flex cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand"
                        :class="theme === option.value ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        :aria-checked="theme === option.value"
                        @click="setTheme(option.value)"
                    >
                        <svg v-if="option.value === 'light'" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                        {{ option.label }}
                    </button>
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

        <Teleport to="body">
            <div
                v-if="editingProfile"
                class="fixed inset-0 z-[80] flex items-end justify-center bg-black/60 p-4 sm:items-center"
                @click.self="closeEditor"
            >
                <div
                    class="w-full max-w-md overflow-hidden rounded-[1.75rem] bg-[#1f1f1f] text-white shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="edit-profile-title"
                >
                    <div class="relative flex h-14 items-center px-2">
                        <button
                            type="button"
                            class="cursor-pointer rounded-full p-2.5 text-white/90 transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-50"
                            aria-label="Close"
                            :disabled="uploading"
                            @click="closeEditor"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 01-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <h2 id="edit-profile-title" class="pointer-events-none absolute inset-x-14 text-center text-[1.05rem] font-medium tracking-tight">
                            Change profile picture
                        </h2>
                    </div>

                    <div class="flex justify-center px-6 pb-7 pt-3">
                        <div class="relative">
                            <UserAvatar
                                :src="user?.avatar_url"
                                :preset="user?.avatar_preset"
                                size-class="h-44 w-44 ring-1 ring-white/10"
                                :alt="displayName"
                            />
                            <button
                                type="button"
                                class="absolute bottom-1 right-1 flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border-[3px] border-[#1f1f1f] bg-[#3c4043] text-white shadow-md transition hover:bg-[#4e5256] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:cursor-not-allowed disabled:opacity-60"
                                aria-label="Upload photo"
                                :disabled="uploading"
                                @click="choosePhoto"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M9.5 6.5 8.2 8H6.5A2.5 2.5 0 0 0 4 10.5v7A2.5 2.5 0 0 0 6.5 20h11a2.5 2.5 0 0 0 2.5-2.5v-7A2.5 2.5 0 0 0 17.5 8h-1.7l-1.3-1.5h-5Zm2.5 10.25a3.25 3.25 0 1 1 0-6.5 3.25 3.25 0 0 1 0 6.5Z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-4 pb-5">
                        <input
                            id="profile-photo"
                            ref="photoInputRef"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif"
                            class="sr-only"
                            :disabled="uploading"
                            @change="onPhotoSelected"
                        >
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-4 rounded-2xl bg-[#2d2d2d] px-4 py-3.5 text-left transition hover:bg-[#383838] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="uploading"
                            @click="choosePhoto"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center text-white/90">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M6 4.75A2.75 2.75 0 0 0 3.25 7.5v9A2.75 2.75 0 0 0 6 19.25h12A2.75 2.75 0 0 0 20.75 16.5v-9A2.75 2.75 0 0 0 18 4.75H6Zm1.2 2.1a1.35 1.35 0 1 1 0 2.7 1.35 1.35 0 0 1 0-2.7ZM5.5 16.2l3.15-3.55a.9.9 0 0 1 1.32-.04l1.55 1.58 2.35-2.85a.9.9 0 0 1 1.4 0L18.5 15.4v1.1a1.25 1.25 0 0 1-1.25 1.25H6.75A1.25 1.25 0 0 1 5.5 16.5v-.3Z" />
                                </svg>
                            </span>
                            <span class="text-[15px] font-medium">
                                {{ uploading ? 'Uploading…' : 'Upload photo' }}
                            </span>
                        </button>
                        <p v-if="photoError" class="mt-3 px-1 text-sm text-red-300" role="alert">
                            {{ photoError }}
                        </p>
                        <p v-else class="mt-3 px-1 text-xs text-white/50">
                            JPEG, PNG, WebP, or GIF. Maximum 2 MB.
                        </p>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import UserAvatar from '@/components/UserAvatar.vue';
import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';
import { fetchLocationProfile } from '@/services/householdService';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import { formatDisplayName } from '@/utils/format';

const PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const PHOTO_MAX_BYTES = 2 * 1024 * 1024;

const router = useRouter();
const { user, roles, loading, logout, updateAvatar } = useAuth();
const { theme, setTheme } = useTheme();

const themeOptions = [
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
];

const open = ref(false);
const barangayName = ref('');
const rootRef = ref(null);
const photoInputRef = ref(null);
const uploading = ref(false);
const photoError = ref('');
const choosingPhoto = ref(false);
const editingProfile = ref(false);

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

function resetPhotoInput() {
    if (photoInputRef.value) {
        photoInputRef.value.value = '';
    }
}

function photoIsAllowed(file) {
    if (PHOTO_TYPES.includes(file.type)) {
        return true;
    }

    return /\.(jpe?g|png|webp|gif)$/i.test(file.name);
}

function choosePhoto() {
    if (uploading.value) {
        return;
    }

    choosingPhoto.value = true;
    photoInputRef.value?.click();
}

async function onPhotoSelected(event) {
    choosingPhoto.value = false;
    const file = event.target.files?.[0];
    resetPhotoInput();

    if (!file) {
        return;
    }

    if (!photoIsAllowed(file)) {
        photoError.value = 'The profile photo must be a JPEG, PNG, WebP, or GIF image.';

        return;
    }

    if (file.size > PHOTO_MAX_BYTES) {
        photoError.value = 'The profile photo must not be larger than 2 MB.';

        return;
    }

    photoError.value = '';
    uploading.value = true;

    try {
        await updateAvatar({ photo: file });
    } catch (error) {
        photoError.value = extractValidationErrors(error).photo
            || extractErrorMessage(error, 'Unable to upload the profile photo.');
    } finally {
        uploading.value = false;
    }
}

function openEditor() {
    photoError.value = '';
    editingProfile.value = true;
}

function closeEditor() {
    if (uploading.value) {
        return;
    }

    editingProfile.value = false;
    photoError.value = '';
}

function toggleMenu() {
    open.value = !open.value;

    if (open.value) {
        loadBarangayName();
    }
}

function closeMenu() {
    if (editingProfile.value || uploading.value) {
        return;
    }

    open.value = false;
}

async function handleLogout() {
    closeMenu();
    await logout();
    await router.push({ name: 'login' });
}

function onDocumentPointerDown(event) {
    if (!open.value || choosingPhoto.value || editingProfile.value) {
        return;
    }

    if (rootRef.value && !rootRef.value.contains(event.target)) {
        closeMenu();
    }
}

function onEscape(event) {
    if (event.key !== 'Escape' || choosingPhoto.value) {
        return;
    }

    if (editingProfile.value) {
        closeEditor();

        return;
    }

    closeMenu();
}

function onWindowFocus() {
    if (!choosingPhoto.value) {
        return;
    }

    window.setTimeout(() => {
        choosingPhoto.value = false;
    }, 400);
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
    window.addEventListener('focus', onWindowFocus);
});

onUnmounted(() => {
    document.removeEventListener('pointerdown', onDocumentPointerDown);
    document.removeEventListener('keydown', onEscape);
    window.removeEventListener('focus', onWindowFocus);
});
</script>
