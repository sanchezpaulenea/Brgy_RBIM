<template>
    <AppLayout>
        <section class="grid gap-6 lg:grid-cols-3">
            <article class="rbim-card p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900">
                    Welcome back, {{ user?.username }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">
                    You are signed in to the Registry of Barangay Inhabitants and Migrants.
                </p>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-brand-muted p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Account status
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">
                            {{ user?.user_status ?? 'Active' }}
                        </dd>
                    </div>
                    <div class="rounded-xl bg-brand-muted p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Assigned roles
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">
                            {{ rolesLabel }}
                        </dd>
                    </div>
                </dl>
            </article>
        </section>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';

const { user, roles } = useAuth();

const rolesLabel = computed(() => (
    roles.value.length ? roles.value.join(', ') : 'No role assigned'
));
</script>
