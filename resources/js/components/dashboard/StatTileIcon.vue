<template>
    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <template v-if="name === 'home'">
            <path d="M4 10.5 12 4l8 6.5" />
            <path d="M6.5 9.5V20h11V9.5" />
            <path d="M10 20v-5h4v5" />
        </template>
        <template v-else-if="name === 'clan'">
            <circle cx="7" cy="8" r="2" />
            <circle cx="17" cy="8" r="2" />
            <circle cx="12" cy="6.5" r="2.2" />
            <path d="M3.5 18c.4-2.2 1.8-3.4 3.5-3.4s3.1 1.2 3.5 3.4" />
            <path d="M13.5 18c.4-2.2 1.8-3.4 3.5-3.4s3.1 1.2 3.5 3.4" />
            <path d="M9.2 18c.3-2.4 1.4-3.8 2.8-3.8s2.5 1.4 2.8 3.8" />
        </template>
        <template v-else-if="name === 'map'">
            <path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10z" />
            <circle cx="12" cy="11" r="2" />
        </template>
        <template v-else-if="name === 'senior'">
            <circle cx="12" cy="6.5" r="2.2" />
            <path d="M8 19c.4-2.6 1.8-4 4-4s3.6 1.4 4 4" />
            <path d="M15 12.5 18.5 19" />
            <path d="M17.2 19h2.6" />
        </template>
        <template v-else-if="name === 'voter'">
            <path d="M6 4.5h12v15H6z" />
            <path d="M9 12.2 11 14l4-4.2" />
        </template>
        <template v-else-if="name === 'solo'">
            <circle cx="10" cy="7" r="2.2" />
            <path d="M5.5 18c.4-2.5 2-4 4.5-4s4.1 1.5 4.5 4" />
            <circle cx="17.2" cy="15.2" r="1.3" />
            <path d="M15.2 19.5c.2-1.2.9-1.8 2-1.8s1.8.6 2 1.8" />
        </template>
        <template v-else-if="name === 'pwd'">
            <circle cx="12" cy="5.5" r="2" />
            <circle cx="8.5" cy="16.5" r="2.3" />
            <circle cx="16.2" cy="16.5" r="2.3" />
            <path d="M12 8.2 9.2 14.2h7.2" />
        </template>
        <template v-else-if="name === 'youth'">
            <circle cx="12" cy="7" r="2.4" />
            <path d="M8 19.5c.4-2.8 1.8-4.3 4-4.3s3.6 1.5 4 4.3" />
            <path d="M15.2 4.2 16.4 6l2 .3-1.5 1.3.4 1.9-1.9-1-1.9 1 .4-1.9L12.4 6.3l2-.3z" />
        </template>
        <template v-else>
            <circle cx="12" cy="8" r="3" />
            <path d="M6 19.5c.6-3 2.7-4.8 6-4.8s5.4 1.8 6 4.8" />
        </template>
    </svg>
</template>

<script setup>
defineProps({
    name: {
        type: String,
        default: 'users',
    },
});
</script>
