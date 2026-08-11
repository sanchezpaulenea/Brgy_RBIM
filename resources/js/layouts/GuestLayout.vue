<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">
                    Barangay RBIM
                </p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900 sm:text-3xl">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-2 text-sm text-slate-600">
                    {{ subtitle }}
                </p>
            </header>

            <main class="flex flex-1 items-center justify-center">
                <div class="w-full max-w-md">
                    <slot />
                </div>
            </main>

            <footer class="mt-8 text-center text-xs text-slate-500">
                Resident and Barangay Information Management System
            </footer>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});
</script>
