<template>
    <RouterView />
</template>

<script setup>
import { RouterView } from 'vue-router';
import router from '@/router';
import { useAuth } from '@/composables/useAuth';
import { onUnmounted, watch } from 'vue';

const { isAuthenticated, logout } = useAuth();

let inactivityTimeout = null;
const INACTIVITY_TIME = 5 * 60 * 1000; // 5 minutes

function handleAutoLogout() {
    console.log('[App.vue] Inactivity timeout reached. Logging out...');
    logout().then(() => {
        router.push({ name: 'login', query: { reason: 'inactive' } });
    });
}

function resetInactivityTimer() {
    console.log('[App.vue] Resetting inactivity timer');
    if (inactivityTimeout) {
        clearTimeout(inactivityTimeout);
    }
    inactivityTimeout = setTimeout(handleAutoLogout, INACTIVITY_TIME);
}

const activityEvents = ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'];

function startTracking() {
    console.log('[App.vue] Starting activity tracking...');
    resetInactivityTimer();
    activityEvents.forEach(event => {
        window.addEventListener(event, resetInactivityTimer, { passive: true });
    });
}

function stopTracking() {
    console.log('[App.vue] Stopping activity tracking...');
    if (inactivityTimeout) {
        clearTimeout(inactivityTimeout);
        inactivityTimeout = null;
    }
    activityEvents.forEach(event => {
        window.removeEventListener(event, resetInactivityTimer);
    });
}

watch(isAuthenticated, (authenticated) => {
    console.log('[App.vue] Auth state watcher:', authenticated);
    if (authenticated) {
        startTracking();
    } else {
        stopTracking();
    }
}, { immediate: true });

onUnmounted(() => {
    stopTracking();
});
</script>


