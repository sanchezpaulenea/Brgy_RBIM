<template>
    <AppLayout :title="pageTitle">
        <div class="space-y-6">
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <form v-if="isAudit" class="rbim-card grid gap-3 p-4 sm:grid-cols-5" @submit.prevent="loadAuditLogs">
                <div>
                    <label for="entity" class="rbim-label">Entity</label>
                    <input id="entity" v-model="filters.entity" type="text" class="rbim-input py-2" placeholder="user">
                </div>
                <div>
                    <label for="date_from" class="rbim-label">From</label>
                    <input id="date_from" v-model="filters.date_from" type="date" class="rbim-input py-2">
                </div>
                <div>
                    <label for="date_to" class="rbim-label">To</label>
                    <input id="date_to" v-model="filters.date_to" type="date" class="rbim-input py-2">
                </div>
                <div class="flex items-end sm:col-span-2 gap-2">
                    <button type="submit" class="rbim-btn flex-1" :disabled="loading">
                        Filter
                    </button>
                    <button type="button" class="rbim-btn-outline flex-1" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading logs...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table v-if="isAudit" class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">When</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Entity</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="log in auditLogs" :key="log.audit_id">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(log.performed_at) }}</td>
                                <td class="px-4 py-3 text-slate-900">{{ log.username || log.user_id }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.action }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.entity }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.description }}</td>
                            </tr>
                            <tr v-if="!auditLogs.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No audit logs found.</td>
                            </tr>
                        </tbody>
                    </table>

                    <table v-else class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Login</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Logout</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">IP / Device</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="log in loginLogs" :key="log.user_log_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ log.username || 'Unknown account' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(log.login_time) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(log.logout_time) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.login_status }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.ip_address }} · {{ log.device }}</td>
                            </tr>
                            <tr v-if="!loginLogs.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No login history found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import { extractErrorMessage } from '@/services/http';
import * as auditLogService from '@/services/auditLogService';
import * as userLogService from '@/services/userLogService';
import { formatDateTime } from '@/utils/format';

const route = useRoute();
const isAudit = computed(() => route.meta.logType !== 'login');
const pageTitle = computed(() => (isAudit.value ? 'Audit log' : 'User log'));

const loading = ref(false);
const error = ref('');
const auditLogs = ref([]);
const loginLogs = ref([]);
const filters = reactive({
    entity: '',
    date_from: '',
    date_to: '',
});

async function loadAuditLogs() {
    loading.value = true;
    error.value = '';

    try {
        const params = {};

        if (filters.entity) {
            params.entity = filters.entity;
        }

        if (filters.date_from) {
            params.date_from = filters.date_from;
        }

        if (filters.date_to) {
            params.date_to = filters.date_to;
        }

        auditLogs.value = await auditLogService.fetchAuditLogs(params);
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load audit logs.');
    } finally {
        loading.value = false;
    }
}

async function loadLoginLogs() {
    loading.value = true;
    error.value = '';

    try {
        loginLogs.value = await userLogService.fetchUserLogs();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load user logs.');
    } finally {
        loading.value = false;
    }
}

function clearFilters() {
    filters.entity = '';
    filters.date_from = '';
    filters.date_to = '';
    loadAuditLogs();
}

watch(() => route.meta.logType, () => {
    if (isAudit.value) {
        loadAuditLogs();
    } else {
        loadLoginLogs();
    }
}, { immediate: true });
</script>
