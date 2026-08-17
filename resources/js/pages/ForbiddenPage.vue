<template>
    <AppLayout title="Access denied">
        <section class="rbim-card p-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-red-600">
                403 Forbidden
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">
                Access denied
            </h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-slate-600">
                This module is restricted to Admin and Super Admin accounts.
            </p>
            <RouterLink to="/dashboard" class="rbim-btn mt-6 inline-flex">
                Back to home
            </RouterLink>
        </section>
    </AppLayout>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
</script>
