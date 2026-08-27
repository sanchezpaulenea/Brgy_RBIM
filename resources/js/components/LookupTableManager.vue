<template>
    <div class="space-y-6">
        <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>
        <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ successMessage }}
        </div>

        <article v-if="canCreate" class="rbim-card p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                Add {{ itemLabel }}
            </h2>
            <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-start" novalidate @submit.prevent="handleCreate">
                <div class="flex-1">
                    <label :for="inputId" class="rbim-label">
                        {{ fieldLabel }}<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="inputId"
                        v-model="newName"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': createError }"
                        @input="createError = ''"
                    >
                    <p v-if="createError" class="rbim-error">{{ createError }}</p>
                    <p v-else class="rbim-hint">{{ hint }}</p>
                </div>
                <button type="submit" class="rbim-btn mt-0 sm:mt-7" :disabled="creating">
                    {{ creating ? 'Adding...' : addButtonLabel }}
                </button>
            </form>
        </article>

        <div class="rbim-card overflow-hidden">
            <div class="border-b border-slate-200 p-4 sm:w-72">
                <label :for="`${inputId}-filter`" class="rbim-label">Search {{ itemLabel }}</label>
                <input
                    :id="`${inputId}-filter`"
                    v-model="search"
                    type="search"
                    class="rbim-input py-2"
                    :placeholder="`Search ${itemLabel} name`"
                >
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">{{ fieldLabel }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th v-if="canDelete" class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in filteredItems" :key="item.id">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ item.id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ item.label }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="isAssigned(item) ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ isAssigned(item) ? 'Assigned' : 'Available' }}
                                </span>
                            </td>
                            <td v-if="canDelete" class="px-4 py-3">
                                <button
                                    type="button"
                                    class="rbim-btn-danger"
                                    :disabled="deletingId === item.id || isAssigned(item)"
                                    :title="isAssigned(item) ? assignedMessage : `Delete this ${itemLabel}`"
                                    @click="handleDelete(item)"
                                >
                                    {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!filteredItems.length">
                            <td :colspan="canDelete ? 4 : 3" class="px-4 py-8 text-center text-slate-500">
                                {{ items.length ? `No ${pluralLabel} match the search.` : `No ${pluralLabel} found.` }}
                            </td>
                        </tr>
                    </tbody>
                </table>
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
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import { matchesSearch } from '@/utils/format';
import { placeNameValidationError } from '@/utils/validation';

const props = defineProps({
    itemLabel: {
        type: String,
        required: true,
    },
    fieldLabel: {
        type: String,
        required: true,
    },
    fieldName: {
        type: String,
        required: true,
    },
    hint: {
        type: String,
        required: true,
    },
    assignedMessage: {
        type: String,
        required: true,
    },
    itemLabelPlural: {
        type: String,
        default: '',
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
    canDelete: {
        type: Boolean,
        default: false,
    },
    fetchItems: {
        type: Function,
        required: true,
    },
    createItem: {
        type: Function,
        required: true,
    },
    deleteItem: {
        type: Function,
        required: true,
    },
});

const items = ref([]);
const search = ref('');
const creating = ref(false);
const deletingId = ref(null);
const newName = ref('');
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

const inputId = computed(() => `${props.fieldName}-input`);
const addButtonLabel = computed(() => `Add ${props.itemLabel}`);
const pluralLabel = computed(() => props.itemLabelPlural || `${props.itemLabel}s`);

const filteredItems = computed(() => (
    items.value.filter((item) => matchesSearch(item.label, search.value))
));

function isAssigned(item) {
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

async function loadItems() {
    error.value = '';

    try {
        items.value = await props.fetchItems();
    } catch (err) {
        error.value = extractErrorMessage(err, `Unable to load ${pluralLabel.value}.`);
    }
}

async function handleCreate() {
    creating.value = true;
    createError.value = '';
    successMessage.value = '';

    const name = newName.value.trim();
    const validationError = placeNameValidationError(name, props.fieldLabel);

    if (validationError) {
        createError.value = validationError;
        creating.value = false;

        return;
    }

    try {
        const item = await props.createItem(name);
        items.value = [...items.value, item].sort((a, b) => String(a.label).localeCompare(String(b.label)));
        newName.value = '';
        successMessage.value = `${capitalize(props.itemLabel)} added successfully.`;
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        createError.value = validationErrors[props.fieldName]
            ?? extractErrorMessage(err, `Unable to add ${props.itemLabel}.`);
    } finally {
        creating.value = false;
    }
}

async function handleDelete(item) {
    if (isAssigned(item)) {
        error.value = props.assignedMessage;
        successMessage.value = '';

        return;
    }

    const allowed = await askConfirm({
        title: `Delete ${props.itemLabel}`,
        message: `Delete this ${props.itemLabel}? This cannot be undone. Assigned ${pluralLabel.value} cannot be deleted.`,
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
        await props.deleteItem(item.id);
        items.value = items.value.filter((row) => row.id !== item.id);
        successMessage.value = `${capitalize(props.itemLabel)} deleted successfully.`;
    } catch (err) {
        error.value = extractErrorMessage(err, props.assignedMessage);
    } finally {
        deletingId.value = null;
    }
}

function capitalize(value) {
    const text = String(value ?? '');

    return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
}

onMounted(loadItems);
</script>
