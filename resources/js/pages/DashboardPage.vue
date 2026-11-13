<template>
    <AppLayout title="Dashboard">
        <div class="space-y-10">
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-slate-900">
                        Welcome back, {{ displayName }}!
                    </h2>
                    <p class="mt-0.5 text-sm text-slate-600">
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

                <section aria-label="Barangay Overview" class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                        Barangay Overview
                    </h3>
                    <div class="grid grid-cols-1 gap-x-7 gap-y-5 sm:grid-cols-2 lg:grid-cols-4" :aria-busy="loading">
                        <template v-if="loading && !tiles.length">
                            <div
                                v-for="item in 12"
                                :key="item"
                                class="h-[6.5rem] animate-pulse rounded-[1.75rem] border border-[#2f3e33] bg-white"
                            />
                        </template>
                        <template v-else>
                            <StatTile v-for="tile in overviewTiles" :key="tile.id" :tile="tile" />
                            <CategoryCard
                                v-for="card in OVERVIEW_CATEGORY_CARDS"
                                :key="card.key"
                                :card="card"
                                layout="tile"
                            />
                        </template>
                    </div>
                </section>

                <section
                    v-for="section in DASHBOARD_CATEGORY_SECTIONS"
                    :key="section.key"
                    :aria-label="section.title"
                    class="space-y-4"
                >
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                        {{ section.title }}
                    </h3>
                    <div class="grid grid-cols-1 gap-x-7 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
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
import { DASHBOARD_CATEGORY_SECTIONS, OVERVIEW_CATEGORY_CARDS, OVERVIEW_TILE_LABELS, OVERVIEW_TILE_ORDER } from '@/constants/dashboardCharts';
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

const overviewTiles = computed(() => {
    const byId = new Map(tiles.value.map((tile) => [tile.id, tile]));

    return OVERVIEW_TILE_ORDER
        .map((id) => byId.get(id))
        .filter(Boolean)
        .map((tile) => ({
            ...tile,
            label: OVERVIEW_TILE_LABELS[tile.id] ?? tile.label,
        }));
});

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
