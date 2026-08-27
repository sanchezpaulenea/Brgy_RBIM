<template>
    <AppLayout title="Barangay Personnel Management">
        <div class="space-y-6">
            <PageTabs :tabs="personnelTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <article v-if="canCreatePosition" class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Add personnel position
                </h2>
                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-start" novalidate @submit.prevent="handleCreatePosition">
                    <div class="flex-1">
                        <label for="position-name" class="rbim-label">
                            Position name<span class="rbim-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="position-name"
                            v-model="newPositionName"
                            type="text"
                            maxlength="45"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': createError }"
                            @input="createError = ''"
                        >
                        <p v-if="createError" class="rbim-error">{{ createError }}</p>
                        <p v-else class="rbim-hint">Letters and spaces only, for example “Barangay Kagawad”.</p>
                    </div>
                    <button type="submit" class="rbim-btn mt-0 sm:mt-7" :disabled="creating">
                        {{ creating ? 'Adding...' : 'Add position' }}
                    </button>
                </form>
            </article>

            <div class="rbim-card overflow-hidden">
                <div class="border-b border-slate-200 p-4 sm:w-72">
                    <label for="position-filter" class="rbim-label">Search position</label>
                    <input
                        id="position-filter"
                        v-model="search"
                        type="search"
                        class="rbim-input py-2"
                        placeholder="Search position name"
                    >
                </div>
                <div class="w-full overflow-x-auto" role="table">
                    <div
                        class="grid w-full min-w-[36rem] items-center gap-x-[7.5rem] bg-slate-50 px-4 text-sm font-semibold text-slate-600"
                        :style="{ gridTemplateColumns: positionColumnTemplate }"
                        role="row"
                    >
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">ID</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Position</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Status</div>
                        <div v-if="canDeletePosition" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="columnheader">Action</div>
                    </div>
                    <div
                        v-for="item in filteredPositions"
                        :key="item.id"
                        class="grid w-full min-w-[36rem] items-center gap-x-[7.5rem] border-t border-slate-100 px-4 text-sm"
                        :style="{ gridTemplateColumns: positionColumnTemplate }"
                        role="row"
                    >
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left font-mono text-xs text-slate-700" role="cell">{{ item.id }}</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left font-medium break-words text-slate-900" role="cell">{{ item.label }}</div>
                        <div class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="cell">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="isPositionAssigned(item) ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-500'"
                            >
                                {{ isPositionAssigned(item) ? 'Assigned' : 'Available' }}
                            </span>
                        </div>
                        <div v-if="canDeletePosition" class="mx-auto w-[58%] translate-x-[2rem] py-3 text-left" role="cell">
                            <button
                                type="button"
                                class="rbim-btn-danger"
                                :disabled="deletingId === item.id || isPositionAssigned(item)"
                                :title="isPositionAssigned(item) ? assignedPositionMessage : 'Delete this personnel position'"
                                @click="handleDeletePosition(item)"
                            >
                                {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                    <div v-if="!filteredPositions.length" class="border-t border-slate-100 px-4 py-8 text-center text-sm text-slate-500">
                        {{ positions.length ? 'No positions match the search.' : 'No personnel positions found.' }}
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="confirm.open"
            :title="confirm.title"
            :message="confirm.message"
            :confirm-label="confirm.confirmLabel"
            :variant="confirm.variant"
            @confirm="confirm.onConfirm?.()"
            @cancel="handleConfirmCancel"
        />
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import { matchesSearch } from '@/utils/format';
import { positionNameValidationError } from '@/utils/validation';

const { hasPermission } = useAuth();
const { personnelTabs } = useSectionTabs();

const positions = ref([]);
const search = ref('');
const creating = ref(false);
const deletingId = ref(null);
const newPositionName = ref('');
const createError = ref('');
const error = ref('');
const successMessage = ref('');
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canCreatePosition = computed(() => hasPermission('pposition.create'));
const canDeletePosition = computed(() => hasPermission('pposition.delete'));
const positionColumnTemplate = computed(() => (
    canDeletePosition.value ? 'repeat(4, minmax(0, 1fr))' : 'repeat(3, minmax(0, 1fr))'
));
const assignedPositionMessage = 'This personnel position is assigned to one or more personnel records and cannot be deleted.';

const filteredPositions = computed(() => (
    positions.value.filter((item) => matchesSearch(item.label, search.value))
));

function isPositionAssigned(item) {
    return Boolean(item?.in_use || item?.occupied);
}

function handleConfirmCancel() {
    confirm.open = false;
    confirm.onCancel?.();
}

function askConfirm({ title, message, confirmLabel = 'Continue', variant = 'primary' }) {
    return new Promise((resolve) => {
        confirm.open = true;
        confirm.title = title;
        confirm.message = message;
        confirm.confirmLabel = confirmLabel;
        confirm.variant = variant;
        confirm.onConfirm = () => {
            confirm.open = false;
            resolve(true);
        };
        confirm.onCancel = () => resolve(false);
    });
}

async function loadPositions() {
    error.value = '';

    try {
        positions.value = await lookupService.fetchPersonnelPositions();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load personnel positions.');
    }
}

async function handleCreatePosition() {
    creating.value = true;
    createError.value = '';
    successMessage.value = '';

    const positionName = newPositionName.value.trim();
    const validationError = positionNameValidationError(positionName);

    if (validationError) {
        createError.value = validationError;
        creating.value = false;

        return;
    }

    try {
        const item = await lookupService.createPersonnelPosition({
            position_name: positionName,
        });
        positions.value = [...positions.value, item].sort((a, b) => a.label.localeCompare(b.label));
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

async function handleDeletePosition(item) {
    if (isPositionAssigned(item)) {
        error.value = assignedPositionMessage;
        successMessage.value = '';

        return;
    }

    const allowed = await askConfirm({
        title: 'Delete position',
        message: 'Delete this personnel position? This cannot be undone. Assigned positions cannot be deleted.',
        confirmLabel: 'Delete',
        variant: 'danger',
    });

    if (!allowed) {
        return;
    }

    deletingId.value = item.id;
    error.value = '';
    successMessage.value = '';

    try {
        await lookupService.deletePersonnelPosition(item.id);
        positions.value = positions.value.filter((row) => row.id !== item.id);
        successMessage.value = 'Position deleted successfully.';
    } catch (err) {
        error.value = extractErrorMessage(err, assignedPositionMessage);
    } finally {
        deletingId.value = null;
    }
}

onMounted(loadPositions);
</script>
