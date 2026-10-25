<template>
    <section class="space-y-4 print:block" aria-label="Matching records">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-900">{{ title }}</h3>
                <p v-if="subtitle" class="mt-0.5 text-sm text-slate-500">{{ subtitle }}</p>
                <p v-if="preview?.header" class="mt-2 text-sm font-semibold text-slate-900">
                    {{ preview.header }}: {{ preview.total }}
                </p>
            </div>
            <div v-if="canExport" class="flex flex-wrap gap-2 print:hidden">
                <button type="button" class="rbim-btn-outline" :disabled="busy" @click="$emit('export', 'excel')">
                    {{ busy === 'excel' ? 'Preparing Excel…' : 'Export Excel' }}
                </button>
                <button type="button" class="rbim-btn-outline" :disabled="busy" @click="$emit('export', 'csv')">
                    {{ busy === 'csv' ? 'Preparing CSV…' : 'Export CSV' }}
                </button>
                <button type="button" class="rbim-btn-outline" :disabled="busy" @click="$emit('export', 'pdf')">
                    {{ busy === 'pdf' ? 'Preparing PDF…' : 'Export PDF' }}
                </button>
                <button type="button" class="rbim-btn-outline" @click="print">Print</button>
            </div>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Loading matching records…</p>
        <p v-else-if="error" class="text-sm text-red-700">{{ error }}</p>

        <div v-else-if="preview" class="overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr v-if="!hasGroupColumns">
                            <th
                                v-for="column in preview.columns"
                                :key="column.key"
                                class="px-3 py-3 text-left font-semibold text-slate-600"
                            >
                                {{ column.label }}
                            </th>
                        </tr>
                        <template v-else>
                            <tr>
                                <th
                                    v-for="column in preview.columns"
                                    :key="column.key"
                                    class="px-3 py-3 text-left font-semibold text-slate-600"
                                    :colspan="column.is_group ? column.columns.length : 1"
                                    :rowspan="column.is_group ? 1 : 2"
                                >
                                    {{ column.label }}
                                </th>
                            </tr>
                            <tr>
                                <template v-for="column in preview.columns" :key="`${column.key}-children`">
                                    <th
                                        v-for="child in column.is_group ? column.columns : []"
                                        :key="`${column.key}-${child.key}`"
                                        class="px-3 py-2 text-left text-xs font-semibold text-slate-500"
                                    >
                                        {{ child.label }}
                                    </th>
                                </template>
                            </tr>
                        </template>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="!preview.rows.length">
                            <td class="px-3 py-6 text-center text-slate-500" :colspan="columnCount">
                                No records match this selection.
                            </td>
                        </tr>
                        <tr v-for="row in preview.rows" :key="row.id">
                            <template v-for="column in preview.columns" :key="`${row.id}-${column.key}`">
                                <td
                                    v-if="!column.is_group"
                                    class="whitespace-pre-line px-3 py-3 align-top text-slate-800"
                                >
                                    {{ displayValue(row.values[column.key]) }}
                                </td>
                                <template v-else>
                                    <td
                                        v-for="child in column.columns"
                                        :key="`${row.id}-${column.key}-${child.key}`"
                                        class="whitespace-pre-line px-3 py-3 align-top text-slate-800"
                                    >
                                        {{ groupDisplay(row.values[column.key], child.key) }}
                                    </td>
                                </template>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="preview.pagination && preview.pagination.last_page > 1"
                class="flex items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-sm print:hidden"
            >
                <span class="text-slate-500">
                    Page {{ preview.pagination.current_page }} of {{ preview.pagination.last_page }}
                </span>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rbim-btn-outline"
                        :disabled="preview.pagination.current_page <= 1 || loading"
                        @click="$emit('page', preview.pagination.current_page - 1)"
                    >
                        Previous
                    </button>
                    <button
                        type="button"
                        class="rbim-btn-outline"
                        :disabled="preview.pagination.current_page >= preview.pagination.last_page || loading"
                        @click="$emit('page', preview.pagination.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    preview: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    canExport: {
        type: Boolean,
        default: false,
    },
    busy: {
        type: String,
        default: '',
    },
});

defineEmits(['export', 'page']);

const hasGroupColumns = computed(() => (props.preview?.columns ?? []).some((column) => column.is_group));

const columnCount = computed(() => (props.preview?.columns ?? []).reduce(
    (count, column) => count + (column.is_group ? column.columns.length : 1),
    0,
));

function displayValue(value) {
    if (value == null || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        const lines = value
            .map((item) => (item == null || item === '' ? '' : String(item)))
            .filter(Boolean);

        return lines.length ? lines.join('\n') : '—';
    }

    return String(value);
}

function groupDisplay(groupValue, childKey) {
    if (groupValue == null) {
        return '—';
    }

    if (Array.isArray(groupValue)) {
        return displayValue(groupValue.map((item) => item?.[childKey]));
    }

    return displayValue(groupValue[childKey]);
}

function print() {
    window.print();
}
</script>
