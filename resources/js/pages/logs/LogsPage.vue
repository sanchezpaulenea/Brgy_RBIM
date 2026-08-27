<template>
    <AppLayout :title="pageTitle">
        <div class="space-y-6">
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <form v-if="isAudit" class="rbim-card grid gap-3 p-4 sm:grid-cols-5">
                <div>
                    <label for="audit-entity" class="rbim-label">Affected Record</label>
                    <input
                        id="audit-entity"
                        v-model="filters.entity"
                        type="search"
                        name="audit-log-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search affected record"
                    >
                </div>
                <div>
                    <label for="date_from" class="rbim-label">From</label>
                    <input id="date_from" v-model="filters.date_from" type="date" class="rbim-input py-2">
                </div>
                <div>
                    <label for="date_to" class="rbim-label">To</label>
                    <input id="date_to" v-model="filters.date_to" type="date" class="rbim-input py-2">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2">
                    <button type="button" class="rbim-btn-outline" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <form v-else class="rbim-card grid gap-3 p-4 sm:grid-cols-5">
                <div>
                    <label for="user-name" class="rbim-label">User</label>
                    <input
                        id="user-name"
                        v-model="loginFilters.username"
                        type="search"
                        name="user-log-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search user"
                    >
                </div>
                <div>
                    <label for="login_from" class="rbim-label">From</label>
                    <input id="login_from" v-model="loginFilters.date_from" type="date" class="rbim-input py-2">
                </div>
                <div>
                    <label for="login_to" class="rbim-label">To</label>
                    <input id="login_to" v-model="loginFilters.date_to" type="date" class="rbim-input py-2">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2">
                    <button type="button" class="rbim-btn-outline" :disabled="loading" @click="clearLoginFilters">
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
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Affected Record</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Target</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Old value</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">New value</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="log in auditLogs" :key="log.audit_id">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(log.performed_at) }}</td>
                                <td class="px-4 py-3 text-slate-900">{{ log.username || log.user_id }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.action }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ formatRecordLabel(log.entity) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ formatRecordLabel(log.target) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.old_value || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.new_value || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.description }}</td>
                            </tr>
                            <tr v-if="!auditLogs.length">
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500">No audit logs found.</td>
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
                            <tr v-for="log in filteredLoginLogs" :key="log.user_log_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ log.username || 'Unknown account' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(log.login_time) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(log.logout_time) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.login_status }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ log.ip_address }} · {{ log.device }}</td>
                            </tr>
                            <tr v-if="!filteredLoginLogs.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    {{ loginLogs.length ? 'No login history matches the filters.' : 'No login history found.' }}
                                </td>
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
import { formatDateTime, formatRecordLabel, matchesSearch } from '@/utils/format';

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

/**
 * The user log endpoint returns the full history, so its search and date range
 * are applied here once the rows are loaded.
 */
const loginFilters = reactive({
    username: '',
    date_from: '',
    date_to: '',
});

const filteredLoginLogs = computed(() => (
    loginLogs.value.filter((log) => {
        if (!matchesSearch(log.username, loginFilters.username)) {
            return false;
        }

        const loggedOn = String(log.login_time ?? '').slice(0, 10);

        if (loginFilters.date_from && loggedOn && loggedOn < loginFilters.date_from) {
            return false;
        }

        if (loginFilters.date_to && loggedOn && loggedOn > loginFilters.date_to) {
            return false;
        }

        return true;
    })
));

async function loadAuditLogs() {
    loading.value = true;
    error.value = '';

    try {
        const params = {};

        if (filters.entity) {
            params.entity = filters.entity.trim();
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

function clearLoginFilters() {
    Object.assign(loginFilters, { username: '', date_from: '', date_to: '' });
    loadLoginLogs();
}

let auditFilterTimer = null;

watch(
    () => [filters.entity, filters.date_from, filters.date_to],
    () => {
        if (!isAudit.value) {
            return;
        }

        clearTimeout(auditFilterTimer);
        auditFilterTimer = setTimeout(() => {
            loadAuditLogs();
        }, 250);
    },
);

watch(() => route.meta.logType, () => {
    if (isAudit.value) {
        loadAuditLogs();
    } else {
        loadLoginLogs();
    }
}, { immediate: true });
</script>
