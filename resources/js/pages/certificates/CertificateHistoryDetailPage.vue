<template>
    <AppLayout title="Certificate Generation">
        <div class="space-y-6">
            <PageTabs :tabs="certificateTabs" active-name="certificate-history" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <RouterLink :to="{ name: 'certificate-history' }" class="text-sm font-medium text-brand hover:underline">
                    ← Back to certificate history
                </RouterLink>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <div v-if="loading && !payload" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading issued certificates...
            </div>

            <template v-else-if="payload">
                <article class="rbim-card p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Resident</h2>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ payload.resident?.name || '—' }}</p>
                    <p class="text-sm text-slate-600">{{ payload.resident?.resident_status || 'No resident status recorded' }}</p>

                    <div class="mt-4">
                        <p class="text-sm text-slate-700">
                            {{ totalLabel }}
                        </p>
                        <ul v-if="counts.length" class="mt-3 flex flex-wrap gap-2">
                            <li
                                v-for="count in counts"
                                :key="count.type_of_certificate_id"
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700"
                            >
                                {{ count.type_of_certificate }}: {{ count.count }}
                            </li>
                        </ul>
                    </div>
                </article>

                <div class="rbim-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Certificate Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Purpose of Request</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Requested By</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Date of Issue</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Issuer</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="row in rows" :key="row.certificate_id">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ row.type_of_certificate || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ row.purpose_of_request || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ requestedBy(row) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ formatDateTime(row.date_of_issue) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ row.issuer_name || '—' }}</td>
                                </tr>
                                <tr v-if="!rows.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                        No certificates have been issued to this resident yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-if="meta.last_page > 1"
                        class="flex items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-sm"
                    >
                        <span class="text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
                        <div class="flex gap-2">
                            <button type="button" class="rbim-btn-outline" :disabled="loading || page <= 1" @click="changePage(page - 1)">
                                Previous
                            </button>
                            <button type="button" class="rbim-btn-outline" :disabled="loading || page >= meta.last_page" @click="changePage(page + 1)">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { certificateErrorMessage, fetchCertificateHistory } from '@/services/certificateService';
import { formatDateTime } from '@/utils/format';

const route = useRoute();
const { certificateTabs } = useSectionTabs();

const payload = ref(null);
const page = ref(1);
const loading = ref(false);
const error = ref('');

const rows = computed(() => payload.value?.certificates?.data ?? []);
const meta = computed(() => payload.value?.certificates ?? { current_page: 1, last_page: 1, total: 0 });
const counts = computed(() => payload.value?.counts_by_type ?? []);

const totalLabel = computed(() => {
    const total = Number(meta.value.total ?? 0);

    return total === 1 ? '1 certificate issued.' : `${total} certificates issued.`;
});

function requestedBy(row) {
    if (!row.representative_name) {
        return 'Resident';
    }

    return row.representative_relationship
        ? `${row.representative_name} (${row.representative_relationship})`
        : row.representative_name;
}

async function loadHistory() {
    loading.value = true;
    error.value = '';

    try {
        payload.value = await fetchCertificateHistory(route.params.residentId, page.value);
    } catch (err) {
        error.value = await certificateErrorMessage(err, 'Unable to load issued certificates.');
    } finally {
        loading.value = false;
    }
}

function changePage(next) {
    page.value = next;
    loadHistory();
}

watch(() => route.params.residentId, (residentId) => {
    if (!residentId) {
        return;
    }

    page.value = 1;
    payload.value = null;
    loadHistory();
}, { immediate: true });
</script>
