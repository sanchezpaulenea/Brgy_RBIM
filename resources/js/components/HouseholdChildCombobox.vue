<template>
    <div ref="root" class="relative">
        <input
            :id="inputId"
            v-model="query"
            type="text"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="open"
            maxlength="100"
            placeholder="Search or select a child"
            class="rbim-input py-2"
            @focus="open = true"
            @input="onInput"
            @blur="onBlur"
            @keydown.down.prevent="onArrowDown"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="onEnter"
            @keydown.escape="open = false"
        >
        <ul
            v-if="open && filtered.length"
            class="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
        >
            <li
                v-for="(option, index) in filtered"
                :key="option.resident_id"
                class="cursor-pointer px-3 py-2 text-sm"
                :class="index === highlightedIndex ? 'bg-brand text-white' : 'text-slate-700 hover:bg-brand-muted'"
                @mousedown.prevent="select(option)"
            >
                {{ option.label }}
            </li>
        </ul>
        <p
            v-else-if="open && !options.length"
            class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg"
        >
            No children in this household. The name you type will be used.
        </p>
        <p
            v-else-if="open && query.trim() && !filtered.length"
            class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg"
        >
            No matching child. The name you type will be used.
        </p>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { matchesSearch } from '@/utils/format';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    inputId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue', 'select']);

const query = ref(props.modelValue ?? '');
const open = ref(false);
const highlightedIndex = ref(0);
const root = ref(null);

const filtered = computed(() => props.options
    .filter((option) => matchesSearch(option.label, query.value) || matchesSearch(option.name, query.value))
    .slice(0, 12));

watch(() => props.modelValue, (value) => {
    if (root.value?.contains(document.activeElement)) {
        return;
    }

    query.value = value ?? '';
});

function onInput() {
    open.value = true;
    highlightedIndex.value = 0;
    emit('update:modelValue', query.value);
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
    query.value = option.name ?? '';
    emit('update:modelValue', query.value);
    emit('select', option);
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

function onEnter() {
    const option = filtered.value[highlightedIndex.value];

    if (open.value && option) {
        select(option);
    }
}
</script>
