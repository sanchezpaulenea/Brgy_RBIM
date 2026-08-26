<template>
    <div class="flex min-h-dvh min-h-screen items-center justify-center bg-brand px-4 py-10 text-white sm:px-6">
        <div class="w-full max-w-lg">
            <header class="mb-8 text-center">
                <BrandLogos />
                <h1 class="mt-5 text-xl font-semibold tracking-wide sm:text-2xl">
                    Registry of Barangay Inhabitants and Migrants
                </h1>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import BrandLogos from '@/components/BrandLogos.vue';
</script>
