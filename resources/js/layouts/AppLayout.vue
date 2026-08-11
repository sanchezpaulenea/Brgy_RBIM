<template>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">
                        Barangay RBIM
                    </p>
                    <h1 class="text-lg font-semibold text-slate-900">
                        {{ title }}
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-slate-900">
                            {{ user?.username }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ rolesLabel }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="loading"
                        @click="handleLogout"
                    >
                        Log out
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const router = useRouter();
const { user, roles, loading, logout } = useAuth();

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
