<template>
    <Teleport to="body">
        <Transition name="rbim-notification">
            <div
                v-if="notification"
                :key="notification.id"
                class="pointer-events-none fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <div
                    class="pointer-events-auto flex w-full max-w-xl items-start gap-4 rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-8 py-7 shadow-2xl"
                    role="alert"
                    aria-live="polite"
                >
                    <span
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white"
                        aria-hidden="true"
                    >
                        <svg
                            class="h-8 w-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xl font-semibold text-emerald-900">Success</p>
                        <p class="mt-1 text-base text-emerald-800">{{ notification.message }}</p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-lg p-1 text-2xl leading-none text-emerald-700 hover:bg-emerald-100"
                        aria-label="Dismiss notification"
                        @click="dismiss"
                    >
                        &times;
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { useAppNotifications } from '@/composables/useAppNotifications';

const { notification, dismiss } = useAppNotifications();
</script>

<style scoped>
.rbim-notification-enter-active,
.rbim-notification-leave-active {
    transition: opacity 180ms ease, transform 180ms ease;
}

.rbim-notification-enter-from,
.rbim-notification-leave-to {
    opacity: 0;
    transform: scale(0.94);
}
</style>
