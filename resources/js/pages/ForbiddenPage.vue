<template>
    <AppLayout title="Forbidden">
        <section class="rounded-2xl border border-red-200 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wider text-red-600">
                403 Forbidden
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">
                Access denied
            </h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-slate-600">
                System administrator modules are restricted to Admin and Super Admin accounts.
                Super Admin access applies even when a Guest role is also assigned.
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <RouterLink
                    to="/dashboard"
                    class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800"
                >
                    Back to dashboard
                </RouterLink>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
</script>
