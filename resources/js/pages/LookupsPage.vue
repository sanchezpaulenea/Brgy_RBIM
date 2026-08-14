<template>
    <AppLayout title="Personnel Positions">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-slate-900">Personnel Positions</h2>
                    <p class="text-sm text-slate-600">Manage barangay personnel position lookup values.</p>
                </div>

                <button
                    type="button"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-60"
                    :disabled="loading"
                    @click="loadPositions"
                >
                    Refresh
                </button>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <article
                v-if="canCreatePosition"
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Add Personnel Position
                </h2>
                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="handleCreate">
                    <div class="flex-1">
                        <label for="position-name" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Position name
                        </label>
                        <input
                            id="position-name"
                            v-model="newPositionName"
                            type="text"
                            maxlength="45"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="e.g. Barangay Treasurer"
                        >
                        <p v-if="createError" class="mt-1.5 text-sm text-red-600">
                            {{ createError }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
                        :disabled="creating"
                    >
                        {{ creating ? 'Adding...' : 'Add position' }}
                    </button>
                </form>
            </article>

            <div v-if="loading && !items.length" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                Loading personnel positions...
            </div>

            <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <p class="text-sm text-slate-600">
                        {{ items.length }} record{{ items.length === 1 ? '' : 's' }}
                        <span class="text-slate-400">· writable</span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Label</th>
                                <th v-if="canDeletePosition" class="px-4 py-3 text-right font-semibold text-slate-600">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in items" :key="item.id">
                                <td class="px-4 py-3 font-mono text-xs text-slate-700">
                                    {{ item.id }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ item.label }}
                                </td>
                                <td v-if="canDeletePosition" class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-60"
                                        :disabled="deletingId === item.id"
                                        @click="handleDelete(item.id)"
                                    >
                                        {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td
                                    :colspan="canDeletePosition ? 3 : 2"
                                    class="px-4 py-8 text-center text-slate-500"
                                >
                                    No records found.
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
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';

const { hasPermission, isSystemAdministrator } = useAuth();

const items = ref([]);
const loading = ref(false);
const creating = ref(false);
const deletingId = ref(null);
const newPositionName = ref('');
const createError = ref('');
const error = ref('');
const successMessage = ref('');

const canCreatePosition = computed(() => (
    isSystemAdministrator.value
    && hasPermission('pposition.create')
));

const canDeletePosition = computed(() => (
    isSystemAdministrator.value
    && hasPermission('pposition.delete')
));

async function loadPositions() {
    loading.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        items.value = await lookupService.fetchPersonnelPositions();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load personnel positions.');
        items.value = [];
    } finally {
        loading.value = false;
    }
}

async function handleCreate() {
    creating.value = true;
    createError.value = '';
    successMessage.value = '';

    try {
        const item = await lookupService.createPersonnelPosition({
            position_name: newPositionName.value.trim(),
        });

        items.value = [...items.value, item].sort((a, b) => a.label.localeCompare(b.label));
        newPositionName.value = '';
        successMessage.value = 'Position added successfully.';
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        createError.value = validationErrors.position_name
            ?? extractErrorMessage(err, 'Unable to add position.');
    } finally {
        creating.value = false;
    }
}

async function handleDelete(id) {
    if (!window.confirm('Delete this personnel position?')) {
        return;
    }

    deletingId.value = id;
    error.value = '';
    successMessage.value = '';

    try {
        await lookupService.deletePersonnelPosition(id);
        items.value = items.value.filter((item) => item.id !== id);
        successMessage.value = 'Position deleted successfully.';
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to delete position.');
    } finally {
        deletingId.value = null;
    }
}

onMounted(loadPositions);
</script>
