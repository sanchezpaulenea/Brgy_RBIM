<template>
    <div ref="root" class="relative">
        <label v-if="label" :for="inputId" class="rbim-label">
            {{ label }}
            <span v-if="required" class="rbim-required" aria-hidden="true">*</span>
        </label>
        <input
            :id="inputId"
            v-model="query"
            type="text"
            autocomplete="off"
            :placeholder="placeholder"
            :disabled="disabled"
            class="rbim-input"
            :class="{ 'rbim-input-error': error }"
            @focus="openDropdown"
            @input="openDropdown"
            @blur="open = false"
            @keydown.down.prevent="move(1)"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="selectHighlighted"
            @keydown.tab="open = false"
            @keydown.escape="open = false"
        >
        <p v-if="selectedLabel && !query" class="mt-1 text-xs text-slate-500">
            Selected: {{ selectedLabel }}
        </p>
        <ul
            v-if="open && filtered.length"
            class="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
        >
            <li
                v-for="(option, index) in filtered"
                :key="option.household_id"
                class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-brand-muted"
                :class="index === highlightedIndex ? 'bg-brand-muted' : ''"
                @mouseenter="highlightedIndex = index"
                @mousedown.prevent="select(option)"
            >
                {{ optionLabel(option) }}
            </li>
        </ul>
        <p
            v-else-if="open && query.trim() && !filtered.length"
            class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg"
        >
            No matching household. Register the household first.
        </p>
        <p v-if="error" class="rbim-error">{{ error }}</p>
        <p v-else-if="hint" class="rbim-hint">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { householdDisplayLabel, householdIdentitySearchText, matchesSearch } from '@/utils/format';

const props = defineProps({
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    options: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: 'Household',
    },
    placeholder: {
        type: String,
        default: 'Search household head, household ID, street, or house/lot number',
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    inputId: {
        type: String,
        default: 'household-search',
    },
});

const emit = defineEmits(['update:modelValue']);

const query = ref('');
const open = ref(false);
const highlightedIndex = ref(-1);
const root = ref(null);

const HOUSEHOLD_SEARCH_LIMIT = 5;

const selected = computed(() => (
    props.options.find((option) => Number(option.household_id) === Number(props.modelValue)) ?? null
));

const selectedLabel = computed(() => (selected.value ? householdDisplayLabel(selected.value) : ''));

const filtered = computed(() => {
    if (selected.value && query.value === householdDisplayLabel(selected.value)) {
        return [selected.value];
    }

    const list = props.options.filter((option) => (
        matchesSearch(householdIdentitySearchText(option, true), query.value)
    ));

    return list.slice(0, HOUSEHOLD_SEARCH_LIMIT);
});

watch(selected, (household) => {
    if (household) {
        query.value = householdDisplayLabel(household);
    } else if (!open.value) {
        query.value = '';
    }
}, { immediate: true });

watch(query, (value) => {
    if (props.disabled) {
        return;
    }

    if (selected.value && value !== householdDisplayLabel(selected.value)) {
        emit('update:modelValue', null);
    }

    highlightedIndex.value = -1;
});

function openDropdown() {
    open.value = true;
    highlightedIndex.value = -1;
}

function optionLabel(option) {
    return householdDisplayLabel(option);
}

function select(option) {
    emit('update:modelValue', option.household_id);
    query.value = householdDisplayLabel(option);
    open.value = false;
}

function closeOnOutsidePointer(event) {
    if (open.value && root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('pointerdown', closeOnOutsidePointer, true));
onBeforeUnmount(() => document.removeEventListener('pointerdown', closeOnOutsidePointer, true));

function move(step) {
    if (!filtered.value.length) {
        return;
    }

    if (highlightedIndex.value < 0) {
        highlightedIndex.value = step > 0 ? 0 : filtered.value.length - 1;
        return;
    }

    const next = highlightedIndex.value + step;
    highlightedIndex.value = (next + filtered.value.length) % filtered.value.length;
}

function selectHighlighted() {
    if (highlightedIndex.value < 0) {
        return;
    }

    const option = filtered.value[highlightedIndex.value];

    if (option) {
        select(option);
    }
}
</script>
