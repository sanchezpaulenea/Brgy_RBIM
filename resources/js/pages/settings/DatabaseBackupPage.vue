<template>
    <AppLayout title="Database Backup and Restore">
        <div class="space-y-6">
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <section v-if="canExport" class="rbim-card space-y-4 p-5">
                <header>
                    <h2 class="text-base font-semibold text-slate-900">Download a backup</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Save a copy of the database, then copy the SQL file to an external hard drive.
                        Print the PDF, or open the Excel file and print it, when you need a paper copy.
                        This page cannot write directly to a USB drive.
                    </p>
                </header>

                <dl class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-slate-600">SQL</dt>
                        <dd class="mt-1 text-sm text-slate-700">Full backup. Use this file to restore the system.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-600">Excel</dt>
                        <dd class="mt-1 text-sm text-slate-700">Every record in a workbook, one sheet per table.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-600">PDF</dt>
                        <dd class="mt-1 text-sm text-slate-700">A printable copy. Long tables are shortened.</dd>
                    </div>
                </dl>

                <p class="rbim-hint">
                    Excel and PDF leave out password hashes. The SQL file keeps them so accounts can be restored.
                </p>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rbim-btn"
                        :disabled="exporting !== ''"
                        @click="download('sql')"
                    >
                        {{ exporting === 'sql' ? 'Preparing SQL...' : 'Download SQL' }}
                    </button>
                    <button
                        type="button"
                        class="rbim-btn-outline"
                        :disabled="exporting !== ''"
                        @click="download('excel')"
                    >
                        {{ exporting === 'excel' ? 'Preparing Excel...' : 'Download Excel' }}
                    </button>
                    <button
                        type="button"
                        class="rbim-btn-outline"
                        :disabled="exporting !== ''"
                        @click="download('pdf')"
                    >
                        {{ exporting === 'pdf' ? 'Preparing PDF...' : 'Download PDF' }}
                    </button>
                </div>
            </section>

            <section v-if="canRestore" class="rbim-card space-y-4 p-5">
                <header>
                    <h2 class="text-base font-semibold text-slate-900">Restore from a backup</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Super Admin only. Choose a SQL file downloaded from this page. The restore replaces current records with that backup.
                        Excel, PDF, and printed pages cannot be restored.
                    </p>
                </header>

                <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    A safety copy is saved on the server before the restore starts. If the selected file is not a backup from this system, nothing is changed.
                </p>

                <form class="space-y-4" @submit.prevent="askRestore">
                    <div>
                        <label for="backup-file" class="rbim-label">
                            SQL backup file<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="backup-file"
                            ref="backupInput"
                            type="file"
                            accept=".sql,text/plain"
                            class="rbim-input py-2"
                            :disabled="restoring"
                            @change="onFileChange"
                        >
                        <p v-if="fileError" class="rbim-error">{{ fileError }}</p>
                        <p v-else class="rbim-hint">.sql file, 32 MB or smaller.</p>
                    </div>

                    <div>
                        <label for="restore-confirmation" class="rbim-label">
                            Type RESTORE to confirm<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="restore-confirmation"
                            v-model="confirmation"
                            type="text"
                            autocomplete="off"
                            spellcheck="false"
                            class="rbim-input py-2"
                            :class="{ 'rbim-input-error': confirmationError }"
                            :disabled="restoring"
                            @input="confirmationError = ''"
                        >
                        <p v-if="confirmationError" class="rbim-error">{{ confirmationError }}</p>
                    </div>

                    <button type="submit" class="rbim-btn-danger px-4 py-2.5 text-sm" :disabled="restoring">
                        {{ restoring ? 'Restoring...' : 'Restore Database' }}
                    </button>
                </form>
            </section>

            <p v-if="!canExport && !canRestore" class="text-sm text-slate-600">
                You do not have permission to back up or restore the database.
            </p>

            <PasswordConfirmDialog
                :open="passwordPrompt.open"
                :title="passwordPrompt.title"
                :message="passwordPrompt.message"
                :confirm-label="passwordPrompt.confirmLabel"
                :variant="passwordPrompt.variant"
                :error="passwordPrompt.error"
                :submitting="passwordPrompt.submitting"
                @confirm="confirmAdministratorPassword"
                @cancel="cancelAdministratorPassword"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import PasswordConfirmDialog from '@/components/PasswordConfirmDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAdministratorPasswordConfirm } from '@/composables/useAdministratorPasswordConfirm';
import { useAuth } from '@/composables/useAuth';
import { backupErrorMessage, exportDatabase, restoreDatabase } from '@/services/databaseBackupService';

const { hasPermission, isSuperAdmin } = useAuth();
const {
    passwordPrompt,
    askAdministratorPassword,
    confirmAdministratorPassword,
    cancelAdministratorPassword,
} = useAdministratorPasswordConfirm();
const canExport = computed(() => hasPermission('backup.export'));
const canRestore = computed(() => isSuperAdmin.value && hasPermission('backup.restore'));

const exporting = ref('');
const restoring = ref(false);
const error = ref('');
const successMessage = ref('');
const fileError = ref('');
const confirmationError = ref('');
const confirmation = ref('');
const backupFile = ref(null);
const backupInput = ref(null);

function onFileChange(event) {
    fileError.value = '';
    error.value = '';
    successMessage.value = '';
    const file = event.target.files?.[0] ?? null;

    if (file && !file.name.toLowerCase().endsWith('.sql')) {
        backupFile.value = null;
        fileError.value = 'The backup must be a .sql file.';
        event.target.value = '';

        return;
    }

    backupFile.value = file;
}

async function download(format) {
    error.value = '';
    successMessage.value = '';
    exporting.value = format;

    try {
        const filename = await exportDatabase(format);
        successMessage.value = format === 'sql'
            ? `Downloaded ${filename}. Copy this file to an external hard drive if you are keeping a manual backup.`
            : `Downloaded ${filename}. Print this file if you need a paper copy.`;
    } catch (err) {
        error.value = await backupErrorMessage(err, 'Unable to download the backup.');
    } finally {
        exporting.value = '';
    }
}

async function askRestore() {
    if (restoring.value || passwordPrompt.open) {
        return;
    }

    error.value = '';
    successMessage.value = '';
    fileError.value = backupFile.value ? '' : 'Choose a SQL backup file.';
    confirmationError.value = confirmation.value === 'RESTORE' ? '' : 'Type RESTORE to confirm.';

    if (fileError.value || confirmationError.value) {
        return;
    }

    const file = backupFile.value;
    const confirmationText = confirmation.value;
    restoring.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        const result = await askAdministratorPassword({
            title: 'Restore database',
            message: 'Replace the current database records with the selected SQL backup? This cannot be undone from this page except by restoring another backup. Enter your Admin or Super Admin password to continue.',
            confirmLabel: 'Restore Database',
            variant: 'danger',
            run: (password) => restoreDatabase(file, confirmationText, password),
        });

        if (!result) {
            return;
        }

        successMessage.value = result.message || 'Database restored from the selected backup.';
        confirmation.value = '';
        backupFile.value = null;

        if (backupInput.value) {
            backupInput.value.value = '';
        }
    } catch (err) {
        error.value = await backupErrorMessage(err, 'Unable to restore the database.');
    } finally {
        restoring.value = false;
    }
}
</script>
