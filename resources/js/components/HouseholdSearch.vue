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
            @focus="open = true"
            @input="open = true"
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
                class="cursor-pointer px-3 py-2 text-sm"
                :class="index === highlightedIndex ? 'bg-brand text-white' : 'text-slate-700 hover:bg-brand-muted'"
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
import { householdDisplayLabel, matchesSearch } from '@/utils/format';

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
        default: 'Search street or head resident name',
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
const highlightedIndex = ref(0);
const root = ref(null);

const selected = computed(() => (
    props.options.find((option) => Number(option.household_id) === Number(props.modelValue)) ?? null
));

const selectedLabel = computed(() => (selected.value ? householdDisplayLabel(selected.value) : ''));

const filtered = computed(() => {
    const list = props.options.filter((option) => matchesSearch(optionLabel(option), query.value));

    return list.slice(0, 8);
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

    highlightedIndex.value = 0;
});

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

    const next = highlightedIndex.value + step;
    highlightedIndex.value = (next + filtered.value.length) % filtered.value.length;
}

function selectHighlighted() {
    const option = filtered.value[highlightedIndex.value];

    if (option) {
        select(option);
    }
}
</script>
