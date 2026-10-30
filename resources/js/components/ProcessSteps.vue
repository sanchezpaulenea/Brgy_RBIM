<template>
    <ol class="flex items-center gap-y-3" :class="nowrap ? 'flex-nowrap' : 'flex-wrap'" :aria-label="ariaLabel">
        <li
            v-for="(label, index) in labels"
            :key="label"
            class="flex items-center"
        >
            <span
                class="inline-flex rounded-full px-4 py-2 text-sm font-medium"
                :class="stepClass(index)"
                :aria-current="index === current ? 'step' : undefined"
            >
                {{ index + 1 }}. {{ label }}
            </span>
            <span
                v-if="index < labels.length - 1"
                class="-mx-0.5 h-0.5 w-10 shrink-0 sm:w-14"
                :class="index < current ? 'bg-brand' : 'bg-slate-300'"
                aria-hidden="true"
            />
        </li>
    </ol>
</template>

<script setup>
const props = defineProps({
    labels: {
        type: Array,
        required: true,
    },
    current: {
        type: Number,
        required: true,
    },
    ariaLabel: {
        type: String,
        default: 'Progress',
    },
    nowrap: {
        type: Boolean,
        default: false,
    },
});

function stepClass(index) {
    if (index < props.current) {
        return 'bg-brand text-white';
    }

    if (index === props.current) {
        return 'bg-[#c5ead4] text-[#143326]';
    }

    return 'bg-white text-slate-500 ring-1 ring-slate-300';
}
</script>
