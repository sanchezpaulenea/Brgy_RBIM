<template>
    <RouterView />
</template>

<script setup>
import { RouterView } from 'vue-router';
import router from '@/router';
import { useAuth } from '@/composables/useAuth';
import { onMounted, onUnmounted, watch } from 'vue';

const { isAuthenticated, logout } = useAuth();

let inactivityTimeout = null;
const INACTIVITY_TIME = 5 * 60 * 1000;

function handleAutoLogout() {
    logout().then(() => {
        router.push({ name: 'login', query: { reason: 'inactive' } });
    });
}

function resetInactivityTimer() {
    if (inactivityTimeout) {
        clearTimeout(inactivityTimeout);
    }
    inactivityTimeout = setTimeout(handleAutoLogout, INACTIVITY_TIME);
}

const activityEvents = ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'];

function startTracking() {
    resetInactivityTimer();
    activityEvents.forEach((event) => {
        window.addEventListener(event, resetInactivityTimer, { passive: true });
    });
}

function stopTracking() {
    if (inactivityTimeout) {
        clearTimeout(inactivityTimeout);
        inactivityTimeout = null;
    }
    activityEvents.forEach((event) => {
        window.removeEventListener(event, resetInactivityTimer);
    });
}

function handleUnauthorized(event) {
    if (router.currentRoute.value.name === 'login') {
        return;
    }

    const message = typeof event.detail?.message === 'string' && event.detail.message !== ''
        ? event.detail.message
        : 'Your session is no longer valid. Please log in again.';

    logout().then(() => {
        router.push({ name: 'login', query: { reason: 'unauthorized', message } });
    });
}

watch(isAuthenticated, (authenticated) => {
    if (authenticated) {
        startTracking();
    } else {
        stopTracking();
    }
}, { immediate: true });

onMounted(() => {
    window.addEventListener('rbim:unauthorized', handleUnauthorized);
});

onUnmounted(() => {
    window.removeEventListener('rbim:unauthorized', handleUnauthorized);
    stopTracking();
});
</script>
