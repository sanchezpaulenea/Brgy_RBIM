<template>
    <GuestLayout
        title="Change password"
        subtitle="You must set a new password before continuing."
    >
        <form
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
            @submit.prevent="handleSubmit"
        >
            <div v-if="successMessage" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ successMessage }}
            </div>

            <div v-if="generalError" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ generalError }}
            </div>

            <div class="space-y-5">
                <div>
                    <label for="current_password" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Current password
                    </label>
                    <input
                        id="current_password"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.current_password }"
                    >
                    <p v-if="errors.current_password" class="mt-1.5 text-sm text-red-600">
                        {{ errors.current_password }}
                    </p>
                </div>

                <div>
                    <label for="new_password" class="mb-1.5 block text-sm font-medium text-slate-700">
                        New password
                    </label>
                    <input
                        id="new_password"
                        v-model="form.new_password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.new_password }"
                    >
                    <p v-if="errors.new_password" class="mt-1.5 text-sm text-red-600">
                        {{ errors.new_password }}
                    </p>
                </div>

                <div>
                    <label for="new_password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Confirm new password
                    </label>
                    <input
                        id="new_password_confirmation"
                        v-model="form.new_password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                </div>
            </div>

            <button
                type="submit"
                class="mt-6 w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="loading"
            >
                {{ loading ? 'Updating password...' : 'Update password' }}
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';

const router = useRouter();
const { changePassword, loading } = useAuth();

const form = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const errors = reactive({
    current_password: '',
    new_password: '',
});

const generalError = ref('');
const successMessage = ref('');

function clearErrors() {
    errors.current_password = '';
    errors.new_password = '';
    generalError.value = '';
    successMessage.value = '';
}

async function handleSubmit() {
    clearErrors();

    try {
        await changePassword({ ...form });
        successMessage.value = 'Password updated successfully. Redirecting to dashboard...';

        setTimeout(async () => {
            await router.push({ name: 'dashboard' });
        }, 800);
    } catch (error) {
        const validationErrors = extractValidationErrors(error);

        errors.current_password = validationErrors.current_password ?? '';
        errors.new_password = validationErrors.new_password ?? '';
        generalError.value = extractErrorMessage(error, 'Unable to update password.');
    }
}
</script>
