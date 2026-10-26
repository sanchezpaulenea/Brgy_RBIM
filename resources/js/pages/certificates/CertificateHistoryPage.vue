<template>
    <AppLayout title="Certificate Generation">
        <div class="space-y-6">
            <PageTabs :tabs="certificateTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3" @submit.prevent="applyFilters">
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <div class="flex-1">
                        <label for="certificate-history-search" class="rbim-label">Search</label>
                        <input
                            id="certificate-history-search"
                            v-model="search"
                            type="search"
                            name="certificate-history-search"
                            autocomplete="off"
                            autocapitalize="off"
                            spellcheck="false"
                            maxlength="100"
                            class="rbim-input py-2"
                            placeholder="Search resident name"
                            @keydown.enter.prevent="applyFilters"
                        >
                    </div>
                    <button type="button" class="rbim-btn py-2" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-slate-500">
                Loading certificate history...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Resident Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Latest Certificate</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Purpose of Request</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Latest Date of Issue</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Certificates Issued</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="resident in items"
                                :key="resident.resident_id"
                                class="cursor-pointer hover:bg-slate-50"
                                @click="openDetail(resident.resident_id)"
                            >
                                <td class="px-4 py-3 font-medium text-slate-900">{{ resident.name || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.resident_status || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.latest_type_of_certificate || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.latest_purpose_of_request || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.latest_date_of_issue ? formatDateTime(resident.latest_date_of_issue) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-brand-muted px-2 py-0.5 text-xs font-semibold text-brand">
                                        {{ resident.certificate_count }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    {{ search.trim() ? 'No residents with issued certificates match that name.' : 'No certificates have been issued yet.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-if="meta.last_page > 1"
                    class="grid grid-cols-3 items-center gap-3 border-t border-slate-200 px-4 py-3 text-sm"
                >
                    <div>
                        <button v-if="page > 1" type="button" class="rbim-btn-outline" @click="changePage(page - 1)">
                            Previous
                        </button>
                    </div>
                    <span class="text-center text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
                    <div class="flex justify-end">
                        <button type="button" class="rbim-btn-outline" :disabled="page >= meta.last_page" @click="changePage(page + 1)">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import { fetchCertificateHistoryResidents } from '@/services/certificateService';
import { formatDateTime } from '@/utils/format';

const router = useRouter();
const { certificateTabs } = useSectionTabs();

const items = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const page = ref(1);
const loading = ref(false);
const error = ref('');
let searchTimer = null;

function openDetail(residentId) {
    router.push({ name: 'certificate-history-detail', params: { residentId } });
}

async function loadResidents() {
    loading.value = true;
    error.value = '';

    try {
        const result = await fetchCertificateHistoryResidents({
            search: search.value.trim(),
            page: page.value,
        });
        items.value = result.data ?? [];
        meta.value = result;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load certificate history.');
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    clearTimeout(searchTimer);
    page.value = 1;
    loadResidents();
}

function clearFilters() {
    if (search.value) {
        search.value = '';

        return;
    }

    applyFilters();
}

function changePage(next) {
    page.value = next;
    loadResidents();
}

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        page.value = 1;
        loadResidents();
    }, 250);
});

onMounted(loadResidents);
onBeforeUnmount(() => clearTimeout(searchTimer));
</script>
