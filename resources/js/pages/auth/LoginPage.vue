<template>
    <GuestLayout
        title="Sign in"
        subtitle="Enter your credentials to access the barangay information system."
    >
        <form
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
            @submit.prevent="handleSubmit"
        >
            <div v-if="generalError" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ generalError }}
            </div>

            <div class="space-y-5">
                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Username
                    </label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        autocomplete="username"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.username }"
                        placeholder="Enter your username"
                    >
                    <p v-if="errors.username" class="mt-1.5 text-sm text-red-600">
                        {{ errors.username }}
                    </p>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.password }"
                        placeholder="Enter your password"
                    >
                    <p v-if="errors.password" class="mt-1.5 text-sm text-red-600">
                        {{ errors.password }}
                    </p>
                </div>
            </div>

            <button
                type="submit"
                class="mt-6 w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="loading"
            >
                {{ loading ? 'Signing in...' : 'Sign in' }}
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
        generalError.value = extractErrorMessage(error, 'Unable to sign in. Please check your credentials.');
    }
}
</script>
