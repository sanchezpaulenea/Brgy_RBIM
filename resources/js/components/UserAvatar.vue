<template>
    <span
        class="inline-flex items-center justify-center overflow-hidden rounded-full bg-[#d7e3f4]"
        :class="sizeClass"
        :aria-hidden="decorative"
    >
        <img
            v-if="src"
            :src="src"
            :alt="decorative ? '' : alt"
            class="h-full w-full object-cover"
        >
        <svg
            v-else-if="preset === 'female'"
            class="h-full w-full"
            viewBox="0 0 40 40"
            fill="none"
            aria-hidden="true"
        >
            <rect width="40" height="40" fill="#c5d4ea" />
            <path fill="#e8c39e" d="M12.2 34.5c.6-7.4 4.2-11.2 7.8-11.2s7.2 3.8 7.8 11.2H12.2Z" />
            <circle cx="20" cy="15.2" r="7.4" fill="#e8c39e" />
            <path fill="#3d2a1c" d="M11.4 16.2c.6-8.2 4.6-13.2 8.6-13.2 4.1 0 8 5 8.6 13.2-1.1-5.6-4.1-9.2-8.6-9.2s-7.5 3.6-8.6 9.2Z" />
            <path fill="#3d2a1c" d="M10.8 18c1.2 6.4 3.8 9.8 9.2 9.8 5.3 0 8-3.4 9.2-9.8-.8 4.6-3.8 7.6-9.2 7.6S11.6 22.6 10.8 18Z" />
            <circle cx="17.4" cy="15.8" r="1.05" fill="#3a2a1a" />
            <circle cx="22.6" cy="15.8" r="1.05" fill="#3a2a1a" />
            <path stroke="#3a2a1a" stroke-linecap="round" stroke-width="1" d="M17.8 19.2c1.3 1.4 3.1 1.4 4.4 0" />
            <path fill="#6b3f6a" d="M11.6 34.8c1.8-6.4 4.8-8.8 8.4-8.8s6.6 2.4 8.4 8.8H11.6Z" />
        </svg>
        <svg
            v-else-if="preset === 'male'"
            class="h-full w-full"
            viewBox="0 0 40 40"
            fill="none"
            aria-hidden="true"
        >
            <rect width="40" height="40" fill="#c5d4ea" />
            <path fill="#4a6fa5" d="M10.8 40c1.6-9.4 5.8-13.8 9.2-13.8S28.6 30.6 30.2 40H10.8Z" />
            <circle cx="20" cy="15.4" r="7.6" fill="#e8c39e" />
            <path fill="#6b4423" d="M12.4 15.6c.4-6.6 4-10.6 7.6-10.6 3.7 0 7.2 4 7.6 10.6-.8-4.7-3.3-7.6-7.6-7.6s-6.8 2.9-7.6 7.6Z" />
            <circle cx="17.2" cy="15.6" r="1.1" fill="#3a2a1a" />
            <circle cx="22.8" cy="15.6" r="1.1" fill="#3a2a1a" />
            <path stroke="#3a2a1a" stroke-linecap="round" stroke-width="1" d="M17.6 19.2c1.4 1.5 3.4 1.5 4.8 0" />
        </svg>
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
defineProps({
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
</script>
