<template>
    <AppLayout title="Dashboard">
        <div class="space-y-8">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-slate-900">
                        Welcome back, {{ displayName }}!
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Choose a statistic to see its chart and matching records.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <p v-if="updatedLabel" class="text-xs text-slate-500">
                        Updated {{ updatedLabel }}
                    </p>
                    <button
                        type="button"
                        class="rbim-btn-outline py-2"
                        :disabled="loading"
                        @click="load"
                    >
                        Refresh
                    </button>
                </div>
            </header>

            <div
                v-if="error && !tiles.length"
                class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-slate-200"
                role="alert"
            >
                <h3 class="text-base font-semibold text-slate-900">Statistics could not be loaded</h3>
                <p class="mt-1 text-sm text-slate-600">{{ error }}</p>
                <button type="button" class="rbim-btn mt-5" @click="load">
                    Try again
                </button>
            </div>

            <template v-else>
                <p
                    v-if="error"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    role="alert"
                >
                    Refresh failed: {{ error }} Showing the last loaded statistics.
                </p>

                <section aria-label="Summary">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" :aria-busy="loading">
                        <template v-if="loading && !tiles.length">
                            <div
                                v-for="item in 8"
                                :key="item"
                                class="h-40 animate-pulse rounded-2xl bg-white ring-1 ring-slate-200"
                            />
                        </template>
                        <StatTile v-for="tile in tiles" v-else :key="tile.id" :tile="tile" />
                    </div>
                </section>

                <section
                    v-for="section in DASHBOARD_CATEGORY_SECTIONS"
                    :key="section.key"
                    :aria-label="section.title"
                    class="space-y-3"
                >
                    <h3 class="text-base font-semibold text-slate-900">
                        {{ section.title }}
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <CategoryCard v-for="card in section.cards" :key="card.key" :card="card" />
                    </div>
                </section>
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import CategoryCard from '@/components/dashboard/CategoryCard.vue';
import StatTile from '@/components/dashboard/StatTile.vue';
import { useAuth } from '@/composables/useAuth';
import { DASHBOARD_CATEGORY_SECTIONS } from '@/constants/dashboardCharts';
import AppLayout from '@/layouts/AppLayout.vue';
import { fetchCensusTiles } from '@/services/censusStatisticService';
import { extractErrorMessage } from '@/services/http';
import { formatDateTime, formatDisplayName } from '@/utils/format';

const { user } = useAuth();
const tiles = ref([]);
const loading = ref(true);
const error = ref('');
const generatedAt = ref('');

const displayName = computed(() => (
    user.value?.full_name || formatDisplayName(user.value?.username) || 'User'
));

const updatedLabel = computed(() => (generatedAt.value ? formatDateTime(generatedAt.value) : ''));

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const data = await fetchCensusTiles();
        tiles.value = data.tiles ?? [];
        generatedAt.value = data.generated_at || '';
    } catch (exception) {
        error.value = extractErrorMessage(exception, 'Please check your connection and try again.');
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
