<template>
    <GuestLayout>
        <form class="rbim-card px-6 py-7 sm:px-8" @submit.prevent="handleSubmit">
            <p class="mb-6 text-center text-sm text-slate-500">
                Enter your credentials to continue.
            </p>

            <div v-if="generalError" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
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
                        required
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.username }"
                    >
                    <p v-if="errors.username" class="rbim-error text-left">{{ errors.username }}</p>
                </div>

                <div class="text-center">
                    <label for="password" class="rbim-label text-center">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.password }"
                    >
                    <p v-if="errors.password" class="rbim-error text-left">{{ errors.password }}</p>
                </div>
            </div>

            <button type="submit" class="rbim-btn mt-6 w-full" :disabled="loading">
                {{ loading ? 'Signing in...' : 'Login' }}
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';

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

onMounted(() => {
    if (route.query.reason === 'inactive') {
        generalError.value = 'You have been logged out due to inactivity.';
    }
});

function clearErrors() {
    errors.username = '';
    errors.password = '';
    generalError.value = '';
}

async function handleSubmit() {
    clearErrors();

    try {
        await login({
            username: form.username,
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

        if (!errors.username && !errors.password) {
            generalError.value = extractErrorMessage(error, 'Unable to sign in. Please check your credentials.');
        }
    }
}
</script>
