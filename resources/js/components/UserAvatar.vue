<template>
    <span
        class="inline-flex items-center justify-center overflow-hidden rounded-full bg-[#e7edf2]"
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
            viewBox="0 0 80 80"
            aria-hidden="true"
        >
            <rect width="80" height="80" fill="#e7edf2" />
            <path fill="#1a1a1a" d="M8 82c7-20 16-28 32-28s25 8 32 28H8z" />
            <path fill="#f6eee6" d="M33 48c.4 7 2.2 14 7 14s6.6-7 7-14H33z" />
            <path fill="#1a1a1a" d="M20 36c.4-16 8.6-26 20-26s19.6 10 20 26c.6 7 .8 15-2 24-5-8-10-12-18-12s-13 4-18 12c-2.8-9-2.6-17-2-24z" />
            <ellipse cx="40" cy="34" rx="13" ry="14.5" fill="#f6eee6" />
            <path fill="#1a1a1a" d="M26 31c1.4-11 6.4-17 14-17s12.6 6 14 17c-2.4-7.2-7-11.2-14-11.2S28.4 23.8 26 31z" />
            <path fill="#1a1a1a" d="M23 30c.8 8 1.6 16 4.2 24 1.6-7 1.4-14 .2-22-1.6-.8-3-1.4-4.4-2zm34 0c-.8 8-1.6 16-4.2 24-1.6-7-1.4-14-.2-22 1.6-.8 3-1.4 4.4-2z" />
        </svg>
        <svg
            v-else-if="preset === 'male'"
            class="h-full w-full"
            viewBox="0 0 80 80"
            aria-hidden="true"
        >
            <rect width="80" height="80" fill="#e7edf2" />
            <path fill="#1a1a1a" d="M8 82c7-20 16-28 32-28s25 8 32 28H8z" />
            <ellipse cx="40" cy="34" rx="14" ry="15" fill="#f6eee6" />
            <path fill="#1a1a1a" d="M24 32c1.2-12 7.2-20 16-20s14.8 8 16 20c-2.2-8-7.4-13-16-13s-13.8 5-16 13z" />
        </svg>
        <svg
            v-else
            class="h-full w-full"
            viewBox="0 0 40 40"
            fill="none"
            aria-hidden="true"
        >
            <rect width="40" height="40" fill="#e7edf2" />
            <circle cx="20" cy="15" r="6.4" fill="#1c1c1c" />
            <path fill="#1c1c1c" d="M9 37.2c1.8-8.6 6.4-12.4 11-12.4s9.2 3.8 11 12.4H9Z" />
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
