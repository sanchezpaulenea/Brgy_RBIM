<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" />

            <article class="rbim-card p-8 text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Pet Census
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Pet census recording will be added in a later update.
                </p>
            </article>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';

const { householdTabs } = useSectionTabs();
</script>
