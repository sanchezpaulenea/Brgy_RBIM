<template>
    <div class="flex items-center justify-center gap-3" :class="wrapperClass">
        <img
            :src="cityLogo"
            alt="City of Baguio seal"
            :class="sizeClass"
            class="rounded-full bg-white object-cover"
            width="96"
            height="96"
        >
        <img
            :src="brgyLogo"
            alt="Barangay Happy Hallow seal"
            :class="sizeClass"
            class="rounded-full bg-white object-cover"
            width="96"
            height="96"
        >
    </div>
</template>

<script setup>
const cityLogo = '/img/city-of-baguio.png';
const brgyLogo = '/img/brgy-happy-hallow.png';

defineProps({
    sizeClass: {
        type: String,
        default: 'h-16 w-16 sm:h-20 sm:w-20',
    },
    wrapperClass: {
        type: String,
        default: '',
    },
});
</script>
