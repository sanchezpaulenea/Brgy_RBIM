<template>
    <div v-if="category" class="space-y-4">
        <div
            v-for="filter in category.filters"
            v-show="filterIsVisible(filter, modelValue)"
            :key="filter.key"
            class="space-y-3 rounded-lg border border-slate-200 p-4"
        >
            <h3 class="text-sm font-semibold text-slate-900">{{ filter.label }}</h3>

            <fieldset v-if="filter.type === 'lookup'" class="space-y-3">
                <legend class="sr-only">{{ filter.label }}</legend>
                <label
                    v-for="mode in filter.modes"
                    :key="mode.value"
                    class="flex items-center gap-2 text-sm text-slate-700"
                >
                    <input
                        type="radio"
                        class="text-brand focus:ring-brand"
                        :name="`dashboard-filter-${filter.key}`"
                        :checked="modelValue[filter.key]?.mode === mode.value"
                        @change="setMode(filter, mode.value)"
                    >
                    {{ mode.label }}
                </label>

                <div v-if="modelValue[filter.key]?.mode === 'one'" class="max-w-md">
                    <LookupCombobox
                        :model-value="modelValue[filter.key].ids[0] ?? null"
                        :options="options[filter.key] || []"
                        :limit="5"
                        :label="`Search ${filter.label.toLowerCase()}`"
                        :placeholder="`Type to search ${filter.label.toLowerCase()}`"
                        :input-id="`dashboard-filter-${filter.key}-one`"
                        @update:model-value="(id) => setSingleId(filter.key, id)"
                        @update:query="(value) => $emit('search', filter.key, value)"
                    />
                </div>

                <div
                    v-else-if="modelValue[filter.key]?.mode === 'multiple'"
                    class="max-h-52 max-w-md space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3"
                >
                    <p v-if="!(options[filter.key] || []).length" class="text-sm text-slate-500">
                        No records are available.
                    </p>
                    <label
                        v-for="option in options[filter.key] || []"
                        :key="option.id"
                        class="flex items-center gap-2 text-sm text-slate-700"
                    >
                        <input
                            type="checkbox"
                            class="text-brand focus:ring-brand"
                            :checked="modelValue[filter.key].ids.includes(Number(option.id))"
                            @change="toggleChecked(filter.key, option.id)"
                        >
                        {{ option.label }}
                    </label>
                </div>
            </fieldset>

            <fieldset v-else-if="filter.type === 'checks'" class="space-y-3">
                <legend class="sr-only">{{ filter.label }}</legend>
                <label
                    v-for="choice in filter.choices"
                    :key="choice.value"
                    class="flex items-center gap-2 text-sm text-slate-700"
                >
                    <input
                        type="checkbox"
                        class="text-brand focus:ring-brand"
                        :checked="modelValue[filter.key]?.ids.includes(Number(choice.value))"
                        @change="toggleChecked(filter.key, choice.value)"
                    >
                    {{ choice.label }}
                </label>
            </fieldset>

            <fieldset v-else-if="filter.type === 'range'" class="space-y-3">
                <legend class="sr-only">{{ filter.label }}</legend>
                <div class="grid max-w-md grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="rbim-label" :for="`dashboard-filter-${filter.key}-min`">Minimum threshold</label>
                        <input
                            :id="`dashboard-filter-${filter.key}-min`"
                            :value="modelValue[filter.key]?.min"
                            type="number"
                            min="0"
                            class="rbim-input"
                            :max="filter.max_bound ?? undefined"
                            @input="setBound(filter.key, 'min', $event.target.value)"
                        >
                    </div>
                    <div>
                        <label class="rbim-label" :for="`dashboard-filter-${filter.key}-max`">Maximum threshold</label>
                        <input
                            :id="`dashboard-filter-${filter.key}-max`"
                            :value="modelValue[filter.key]?.max"
                            type="number"
                            min="0"
                            class="rbim-input"
                            :max="filter.max_bound ?? undefined"
                            @input="setBound(filter.key, 'max', $event.target.value)"
                        >
                    </div>
                </div>
            </fieldset>

            <fieldset v-else class="space-y-3">
                <legend class="sr-only">{{ filter.label }}</legend>
                <label
                    v-for="choice in filter.choices"
                    :key="choice.value"
                    class="flex items-center gap-2 text-sm text-slate-700"
                >
                    <input
                        :checked="modelValue[filter.key]?.mode === choice.value"
                        type="radio"
                        class="text-brand focus:ring-brand"
                        :name="`dashboard-filter-${filter.key}`"
                        :value="choice.value"
                        @change="setChoice(filter.key, choice.value)"
                    >
                    {{ choice.label }}
                </label>
                <div v-if="numericChoice(filter)" class="max-w-xs">
                    <label :for="`dashboard-filter-${filter.key}-number`" class="rbim-label">Number</label>
                    <input
                        :id="`dashboard-filter-${filter.key}-number`"
                        :value="modelValue[filter.key]?.value"
                        type="number"
                        class="rbim-input"
                        :min="numericChoice(filter).min"
                        :max="numericChoice(filter).max"
                        @input="setValue(filter.key, $event.target.value)"
                    >
                </div>
            </fieldset>
        </div>
    </div>
</template>

<script setup>
import LookupCombobox from '@/components/LookupCombobox.vue';
import { filterIsVisible } from '@/utils/dashboardAnalytics';

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
    modelValue: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'search']);

function patch(key, next) {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: { ...props.modelValue[key], ...next },
    });
}

function setMode(filter, mode) {
    patch(filter.key, { mode, ids: [] });

    if (mode === 'multiple') {
        emit('search', filter.key, '');
    }
}

function setSingleId(key, id) {
    patch(key, { ids: id == null || id === '' ? [] : [Number(id)] });
}

function toggleChecked(key, id) {
    const current = props.modelValue[key]?.ids ?? [];
    const numeric = Number(id);
    const ids = current.includes(numeric)
        ? current.filter((item) => item !== numeric)
        : [...current, numeric];

    patch(key, { ids });
}

function setBound(key, side, value) {
    patch(key, { [side]: value });
}

function setChoice(key, mode) {
    patch(key, { mode });
}

function setValue(key, value) {
    patch(key, { value });
}

function numericChoice(filter) {
    const state = props.modelValue[filter.key];

    return (filter.choices ?? []).find((choice) => choice.value === state?.mode && choice.numeric) ?? null;
}
</script>
