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
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="open"
            :placeholder="placeholder"
            :disabled="disabled"
            class="rbim-input"
            :class="{ 'rbim-input-error': error }"
            @focus="open = true"
            @input="onInput"
            @blur="onBlur"
            @keydown.down.prevent="onArrowDown"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="onEnter"
            @keydown.tab="open = false"
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
        <p
            v-else-if="open && canCreate && newPositionName"
            class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg"
        >
            Press Enter to add “{{ newPositionName }}” as a new position.
        </p>
        <p v-if="error" class="rbim-error">{{ error }}</p>
        <p v-else-if="acceptedNewName" class="mt-1.5 text-xs font-medium text-brand">
            “{{ acceptedNewName }}” will be added as a new position when you save.
        </p>
        <p v-else-if="hint" class="rbim-hint">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

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
    hint: {
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
        default: 'position-search',
    },
});

const emit = defineEmits(['update:modelValue', 'update:query', 'create']);

const query = ref('');
const open = ref(false);
const highlightedIndex = ref(0);
const acceptedNewName = ref('');
const root = ref(null);

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

/**
 * The typed name when it does not already exist, which is what pressing Enter
 * would add. An exact match is treated as a selection instead.
 */
const newPositionName = computed(() => {
    const term = query.value.trim();

    if (!term) {
        return '';
    }

    const exists = props.options.some((option) => option.label.toLowerCase() === term.toLowerCase());

    return exists ? '' : term;
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
    acceptedNewName.value = '';
    emit('update:query', query.value);
    emit('update:modelValue', null);
    highlightedIndex.value = 0;
}

function onBlur() {
    open.value = false;
}

function closeOnOutsidePointer(event) {
    if (open.value && root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('pointerdown', closeOnOutsidePointer, true));
onBeforeUnmount(() => document.removeEventListener('pointerdown', closeOnOutsidePointer, true));

function onArrowDown() {
    if (!open.value) {
        open.value = true;

        return;
    }

    move(1);
}

function select(option) {
    if (optionDisabled(option)) {
        return;
    }

    acceptedNewName.value = '';
    emit('update:modelValue', option.id);
    emit('update:query', option.label);
    query.value = option.label;
    highlightedIndex.value = 0;
    open.value = false;
}

function move(step) {
    if (!filtered.value.length) {
        return;
    }

    const next = highlightedIndex.value + step;
    highlightedIndex.value = (next + filtered.value.length) % filtered.value.length;
}

/**
 * Enter either takes the highlighted suggestion or, when nothing matches the
 * typed name, keeps it as a new position for the parent form to create.
 */
function onEnter() {
    const option = filtered.value[highlightedIndex.value];

    if (open.value && option && !optionDisabled(option)) {
        select(option);

        return;
    }

    if (props.canCreate && newPositionName.value) {
        acceptedNewName.value = newPositionName.value;
        emit('update:modelValue', null);
        emit('update:query', newPositionName.value);
        emit('create', newPositionName.value);
        open.value = false;
    }
}
</script>
