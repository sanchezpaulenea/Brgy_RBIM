<template>
    <span
        class="inline-flex items-center justify-center overflow-hidden rounded-full bg-[#e4e4e4]"
        :class="sizeClass"
        :aria-hidden="decorative"
    >
        <img
            v-if="imageSrc"
            :src="imageSrc"
            :alt="decorative ? '' : alt"
            class="h-full w-full object-cover"
        >
        <svg
            v-else
            class="h-full w-full"
            viewBox="0 0 40 40"
            fill="none"
            aria-hidden="true"
        >
            <rect width="40" height="40" fill="#c5d4ea" />
            <circle cx="20" cy="15" r="7" fill="#7b90b0" />
            <path fill="#7b90b0" d="M8.5 36.5c2-9.2 7.4-13 11.5-13s9.5 3.8 11.5 13H8.5Z" />
        </svg>
    </span>
</template>

<script setup>
import { computed } from 'vue';
import { AVATAR_PRESET_IMAGES } from '@/constants/avatars';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    preset: {
        type: String,
        default: null,
    },
    sizeClass: {
        type: String,
        default: 'h-10 w-10',
    },
    alt: {
        type: String,
        default: 'Profile photo',
    },
    decorative: {
        type: Boolean,
        default: false,
    },
});

const imageSrc = computed(() => props.src || AVATAR_PRESET_IMAGES[props.preset] || null);
</script>
