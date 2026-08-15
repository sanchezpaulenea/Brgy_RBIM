<template>
    <div class="relative">
        <label v-if="label" :for="inputId" class="rbim-label">{{ label }}</label>
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
            @input="onInput"
            @keydown.down.prevent="move(1)"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="selectHighlighted"
            @keydown.escape="open = false"
        >
        <ul
            v-if="open && filtered.length"
            class="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
        >
            <li
                v-for="(option, index) in filtered"
                :key="option.id"
                class="px-3 py-2 text-sm"
                :class="optionDisabled(option)
                    ? 'cursor-not-allowed text-slate-400'
                    : (index === highlightedIndex ? 'cursor-pointer bg-brand text-white' : 'cursor-pointer text-slate-700 hover:bg-brand-muted')"
                @mousedown.prevent="select(option)"
            >
                <span>{{ option.label }}</span>
                <span v-if="optionDisabled(option)" class="ml-2 text-xs">Held by active personnel</span>
            </li>
        </ul>
        <p v-else-if="open && query.trim() && !filtered.length && canCreate" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg">
            Press Enter to add “{{ query.trim() }}” as a new position.
        </p>
        <p v-if="error" class="rbim-error">{{ error }}</p>
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
    excludeOccupiedForId: {
        type: [Number, String, null],
        default: null,
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: 'Position',
    },
    placeholder: {
        type: String,
        default: 'Search or select a position',
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
        default: 'position-search',
    },
});

const emit = defineEmits(['update:modelValue', 'update:query']);

const query = ref('');
const open = ref(false);
const highlightedIndex = ref(0);

const selected = computed(() => (
    props.options.find((option) => Number(option.id) === Number(props.modelValue)) ?? null
));

const filtered = computed(() => {
    const term = query.value.trim().toLowerCase();
    const list = term
        ? props.options.filter((option) => option.label.toLowerCase().includes(term))
        : props.options;

    return list.slice(0, 12);
});

watch(selected, (option) => {
    if (option) {
        query.value = option.label;
    }
}, { immediate: true });

function optionDisabled(option) {
    if (!option.occupied) {
        return false;
    }

    return Number(option.occupied_by_personnel_id) !== Number(props.excludeOccupiedForId);
}

function onInput() {
    open.value = true;
    emit('update:query', query.value);
    emit('update:modelValue', null);
    highlightedIndex.value = 0;
}

function select(option) {
    if (optionDisabled(option)) {
        return;
    }

    emit('update:modelValue', option.id);
    emit('update:query', option.label);
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

    if (option && !optionDisabled(option)) {
        select(option);
    }
}
</script>
