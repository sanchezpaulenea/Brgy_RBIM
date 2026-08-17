<template>
    <div class="min-h-screen bg-brand text-white">
        <div class="mx-auto flex min-h-screen max-w-lg flex-col px-4 py-10 sm:px-6">
            <header class="mb-8 text-center">
                <BrandLogos />
                <h1 class="mt-5 text-xl font-semibold tracking-wide sm:text-2xl">
                    Registry of Barangay Inhabitants and Migrants
                </h1>
            </header>

            <main class="flex flex-1 items-start justify-center">
                <div class="w-full">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import BrandLogos from '@/components/BrandLogos.vue';
</script>
