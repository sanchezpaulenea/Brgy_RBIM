<template>
    <GuestLayout>
        <form class="rbim-card px-6 py-7 sm:px-8" novalidate @submit.prevent="handleSubmit">
            <p class="mb-6 text-center text-sm text-slate-500">
                Enter your credentials to continue.
            </p>

            <div v-if="successMessage" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>
            <div v-else-if="lockoutSeconds > 0" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ lockoutMessage }}
                <span class="mt-1 block font-semibold">
                    You can try again in {{ lockoutLabel }}.
                </span>
            </div>
            <div v-else-if="generalError" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ generalError }}
            </div>

            <div class="space-y-5">
                <div class="text-center">
                    <label for="username" class="rbim-label text-center">Username</label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        autocomplete="username"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.username }"
                    >
                    <p v-if="errors.username" class="rbim-error text-left">{{ errors.username }}</p>
                </div>

                <PasswordField
                    v-model="form.password"
                    input-id="password"
                    label="Password"
                    autocomplete="current-password"
                    center-label
                    :error="errors.password"
                />
            </div>

            <button type="submit" class="rbim-btn mt-6 w-full" :disabled="loading || lockoutSeconds > 0">
                {{ loading ? 'Signing in...' : 'Login' }}
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import GuestLayout from '@/layouts/GuestLayout.vue';
import PasswordField from '@/components/PasswordField.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import {
    INVALID_CREDENTIALS_MESSAGE,
    INVALID_PASSWORD_MESSAGE,
    INVALID_USERNAME_MESSAGE,
    isWellFormedPassword,
    isWellFormedUsername,
} from '@/utils/validation';

const route = useRoute();
const router = useRouter();
const { login, loading, mustChangePassword } = useAuth();

const form = reactive({
    username: '',
    password: '',
});

const errors = reactive({
    username: '',
    password: '',
});

const generalError = ref('');
const successMessage = ref('');
const lockoutMessage = ref('');
const lockoutSeconds = ref(0);
let lockoutTimer = null;
let lockoutEndsAt = 0;

const lockoutLabel = computed(() => {
    const minutes = Math.floor(lockoutSeconds.value / 60);
    const seconds = String(lockoutSeconds.value % 60).padStart(2, '0');

    return `${minutes}:${seconds}`;
});

function stopLockoutTimer() {
    if (lockoutTimer) {
        clearInterval(lockoutTimer);
        lockoutTimer = null;
    }
}

/**
 * Derives the remaining time from a fixed deadline rather than decrementing a
 * counter, so a throttled or backgrounded tab does not fall behind the server.
 */
function tickLockout() {
    lockoutSeconds.value = Math.max(0, Math.ceil((lockoutEndsAt - Date.now()) / 1000));

    if (lockoutSeconds.value <= 0) {
        stopLockoutTimer();
    }
}

function startLockoutTimer(seconds) {
    stopLockoutTimer();

    const totalSeconds = Math.max(0, Number.parseInt(seconds, 10) || 0);

    if (totalSeconds <= 0) {
        lockoutSeconds.value = 0;

        return;
    }

    lockoutEndsAt = Date.now() + totalSeconds * 1000;
    tickLockout();
    lockoutTimer = setInterval(tickLockout, 1000);
}

onMounted(() => {
    if (route.query.reason === 'inactive') {
        generalError.value = 'You have been logged out due to inactivity.';
    } else if (route.query.reason === 'unauthorized') {
        generalError.value = typeof route.query.message === 'string' && route.query.message !== ''
            ? route.query.message
            : 'Your session is no longer valid. Please log in again.';
    } else if (route.query.reason === 'password_changed') {
        successMessage.value = 'Password updated successfully. Please log in with your new password.';
    }
});

onUnmounted(stopLockoutTimer);

function clearErrors() {
    errors.username = '';
    errors.password = '';
    generalError.value = '';
    successMessage.value = '';
    lockoutMessage.value = '';
}

/**
 * Reports which credential the user needs to correct: one message per field, or
 * the combined message when neither entry is usable.
 */
function hasCredentialFormatErrors() {
    const usernameInvalid = !isWellFormedUsername(form.username);
    const passwordInvalid = !isWellFormedPassword(form.password);

    if (usernameInvalid && passwordInvalid) {
        generalError.value = INVALID_CREDENTIALS_MESSAGE;

        return true;
    }

    if (usernameInvalid) {
        errors.username = INVALID_USERNAME_MESSAGE;

        return true;
    }

    if (passwordInvalid) {
        errors.password = INVALID_PASSWORD_MESSAGE;

        return true;
    }

    return false;
}

async function handleSubmit() {
    if (lockoutSeconds.value > 0) {
        return;
    }

    clearErrors();

    if (hasCredentialFormatErrors()) {
        return;
    }

    try {
        await login({
            username: form.username.trim(),
            password: form.password,
        });

        if (mustChangePassword.value) {
            await router.push({ name: 'change-password' });

            return;
        }

        const redirect = typeof route.query.redirect === 'string'
            ? route.query.redirect
            : '/dashboard';

        await router.push(redirect);
    } catch (error) {
        const validationErrors = extractValidationErrors(error);

        errors.username = validationErrors.username ?? '';
        errors.password = validationErrors.password ?? '';

        if (validationErrors.lockout_remaining_seconds) {
            startLockoutTimer(validationErrors.lockout_remaining_seconds);
        }

        if (lockoutSeconds.value > 0) {
            lockoutMessage.value = errors.username || 'Your account is locked.';
            errors.username = '';
            form.password = '';

            return;
        }

        if (!errors.username && !errors.password) {
            generalError.value = extractErrorMessage(error, INVALID_CREDENTIALS_MESSAGE);
        }
    }
}
</script>
