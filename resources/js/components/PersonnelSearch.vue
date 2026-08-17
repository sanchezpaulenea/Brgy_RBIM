<template>
    <div class="relative">
        <label v-if="label" :for="inputId" class="rbim-label">
            {{ label }}
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
            @keydown.down.prevent="move(1)"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="selectHighlighted"
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
                :key="option.personnel_id"
                class="cursor-pointer px-3 py-2 text-sm"
                :class="index === highlightedIndex ? 'bg-brand text-white' : 'text-slate-700 hover:bg-brand-muted'"
                @mousedown.prevent="select(option)"
            >
                {{ option.label }}
            </li>
        </ul>
        <p v-else-if="open && query.trim() && !filtered.length" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg">
            No matching personnel. Create the record in Barangay Personnel Management first.
        </p>
        <p v-if="error" class="rbim-error">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

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
        default: 'Barangay personnel name',
    },
    placeholder: {
        type: String,
        default: 'Search personnel name',
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    inputId: {
        type: String,
        default: 'personnel-search',
    },
});

const emit = defineEmits(['update:modelValue']);

const query = ref('');
const open = ref(false);
const highlightedIndex = ref(0);

const selected = computed(() => (
    props.options.find((option) => Number(option.personnel_id) === Number(props.modelValue)) ?? null
));

const selectedLabel = computed(() => selected.value?.label ?? '');

const filtered = computed(() => {
    const term = query.value.trim().toLowerCase();

    if (!term) {
        return props.options.slice(0, 8);
    }

    return props.options
        .filter((option) => option.label.toLowerCase().includes(term))
        .slice(0, 8);
});

watch(selected, (person) => {
    if (person) {
        query.value = person.label;
    } else if (!open.value) {
        query.value = '';
    }
}, { immediate: true });

watch(query, (value) => {
    if (selected.value && value !== selected.value.label) {
        emit('update:modelValue', null);
    }

    highlightedIndex.value = 0;
});

function select(option) {
    emit('update:modelValue', option.personnel_id);
    query.value = option.label;
    open.value = false;
}

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
