<template>
    <AppLayout title="Log Retention and Archiving">
        <div class="space-y-6">
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading log retention...
            </div>
            <div v-else class="grid gap-6 lg:grid-cols-2">
                <section v-for="card in cards" :key="card.key" class="rbim-card space-y-4 p-5">
                    <header>
                        <h2 class="text-base font-semibold text-slate-900">{{ card.title }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ card.description }}</p>
                    </header>

                    <dl class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Live rows</dt>
                            <dd class="mt-1 text-lg font-semibold text-slate-900">{{ summary[card.key].live_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Archived rows</dt>
                            <dd class="mt-1 text-lg font-semibold text-slate-900">{{ summary[card.key].archived_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Oldest record</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900">
                                {{ summary[card.key].oldest_record ? formatDateTime(summary[card.key].oldest_record) : 'None' }}
                            </dd>
                        </div>
                    </dl>

                    <div>
                        <label :for="`${card.key}-retention-days`" class="rbim-label">
                            Retention Period (Days)<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            :id="`${card.key}-retention-days`"
                            v-model="days[card.key]"
                            type="number"
                            inputmode="numeric"
                            min="30"
                            max="3650"
                            step="1"
                            class="rbim-input py-2"
                            :class="{ 'rbim-input-error': fieldErrors[card.key] }"
                            :disabled="!canUpdate || saving"
                            @input="onDaysInput(card.key)"
                        >
                        <p v-if="fieldErrors[card.key]" class="rbim-error">{{ fieldErrors[card.key] }}</p>
                        <p v-else class="rbim-hint">Whole number from 30 to 3650 days.</p>
                        <p v-if="!canUpdate" class="rbim-hint">You do not have permission to change the retention period.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rbim-btn"
                            :disabled="!canUpdate || saving"
                            @click="save(card.key)"
                        >
                            {{ saving === card.key ? 'Saving...' : 'Save' }}
                        </button>
                        <button
                            type="button"
                            class="rbim-btn-outline"
                            :disabled="!canArchive || previewing === card.key || archiving === card.key"
                            @click="preview(card.key)"
                        >
                            {{ previewing === card.key ? 'Previewing...' : 'Preview Archive' }}
                        </button>
                        <button
                            type="button"
                            class="rbim-btn"
                            :disabled="!canArchive || archiveDisabled(card.key)"
                            @click="askArchive(card.key)"
                        >
                            Archive Now
                        </button>
                    </div>

                    <p v-if="!canArchive" class="rbim-hint">You do not have permission to archive logs.</p>
                    <p v-else-if="previewCounts[card.key] === null" class="rbim-hint">
                        Preview the archive before archiving. Archive Now stays unavailable until a count is shown.
                    </p>
                    <p v-else-if="previewCounts[card.key] === 0" class="rbim-hint">
                        There are no records to archive. Archive Now is unavailable when the count is 0.
                    </p>
                    <p v-else class="text-sm text-slate-700">
                        {{ previewCounts[card.key] }} {{ previewCounts[card.key] === 1 ? 'record would be archived' : 'records would be archived' }}.
                    </p>
                </section>
            </div>

            <ConfirmDialog
                :open="confirmLog !== ''"
                title="Archive records"
                :message="confirmMessage"
                confirm-label="Archive Now"
                @cancel="confirmLog = ''"
                @confirm="runArchive"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as logRetentionService from '@/services/logRetentionService';
import { formatDateTime } from '@/utils/format';

const cards = [
    {
        key: 'audit',
        title: 'Audit Log',
        description: 'How long audit records stay in the active log before they can be archived.',
        field: 'audit_log_retention_days',
    },
    {
        key: 'user',
        title: 'User Log',
        description: 'How long login records that already have a logout time stay in the active log before they can be archived.',
        field: 'user_log_retention_days',
    },
];

const emptySummary = () => ({
    retention_days: null,
    live_count: 0,
    archived_count: 0,
    oldest_record: null,
});

const { hasPermission } = useAuth();
const canUpdate = computed(() => hasPermission('retention.update'));
const canArchive = computed(() => hasPermission('retention.archive'));

const loading = ref(false);
const saving = ref('');
const previewing = ref('');
const archiving = ref('');
const error = ref('');
const successMessage = ref('');
const confirmLog = ref('');
const summary = reactive({
    audit: emptySummary(),
    user: emptySummary(),
});
const days = reactive({ audit: '', user: '' });
const fieldErrors = reactive({ audit: '', user: '' });
const previewCounts = reactive({ audit: null, user: null });

const confirmMessage = computed(() => {
    const card = cards.find((item) => item.key === confirmLog.value);

    if (!card) {
        return '';
    }

    const count = previewCounts[card.key] ?? 0;

    return `Archive ${count} ${card.title} ${count === 1 ? 'record' : 'records'} that are outside the retention period? Archived records remain available in the archived view.`;
});

function retentionError(value) {
    const text = String(value ?? '').trim();

    if (text === '') {
        return 'Retention Period (Days) is required.';
    }

    if (!/^\d+$/.test(text)) {
        return 'Retention Period (Days) must be a whole number.';
    }

    const number = Number(text);

    if (number < 30 || number > 3650) {
        return 'Retention Period (Days) must be between 30 and 3650.';
    }

    return '';
}

function applySummary(payload) {
    cards.forEach((card) => {
        const row = payload?.[card.key] ?? emptySummary();
        summary[card.key] = {
            retention_days: row.retention_days,
            live_count: row.live_count ?? 0,
            archived_count: row.archived_count ?? 0,
            oldest_record: row.oldest_record ?? null,
        };
        days[card.key] = row.retention_days ?? '';
        previewCounts[card.key] = null;
    });
}

function payloadFromDays() {
    return {
        audit_log_retention_days: Number(days.audit),
        user_log_retention_days: Number(days.user),
    };
}

function onDaysInput(key) {
    fieldErrors[key] = '';
    previewCounts[key] = null;
    error.value = '';
    successMessage.value = '';
}

function archiveDisabled(key) {
    return previewCounts[key] === null || previewCounts[key] === 0 || archiving.value !== '';
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        applySummary(await logRetentionService.fetchLogRetention());
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load log retention.');
    } finally {
        loading.value = false;
    }
}

async function save(key) {
    error.value = '';
    successMessage.value = '';
    let invalid = false;

    cards.forEach((card) => {
        fieldErrors[card.key] = retentionError(days[card.key]);
        invalid = invalid || fieldErrors[card.key] !== '';
    });

    if (invalid) {
        return;
    }

    saving.value = key;

    try {
        const updated = await logRetentionService.updateLogRetention(payloadFromDays());
        applySummary(updated);
        successMessage.value = updated.message || 'Log retention updated.';
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        fieldErrors.audit = validationErrors.audit_log_retention_days || '';
        fieldErrors.user = validationErrors.user_log_retention_days || '';
        error.value = fieldErrors.audit || fieldErrors.user
            ? ''
            : extractErrorMessage(err, 'Unable to update log retention.');
    } finally {
        saving.value = '';
    }
}

async function preview(key) {
    error.value = '';
    successMessage.value = '';
    previewing.value = key;

    try {
        const result = await logRetentionService.previewArchive(key);
        previewCounts[key] = result.count;
    } catch (err) {
        previewCounts[key] = null;
        error.value = extractErrorMessage(err, 'Unable to preview the archive.');
    } finally {
        previewing.value = '';
    }
}

function askArchive(key) {
    if (archiveDisabled(key) || !canArchive.value) {
        return;
    }

    confirmLog.value = key;
}

async function runArchive() {
    const key = confirmLog.value;
    confirmLog.value = '';

    if (!key) {
        return;
    }

    error.value = '';
    successMessage.value = '';
    archiving.value = key;

    try {
        const result = await logRetentionService.archiveLogs(key);
        successMessage.value = result.message || 'Records archived.';
        await load();
        previewCounts[key] = 0;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to archive records.');
    } finally {
        archiving.value = '';
    }
}

onMounted(load);
</script>
