<template>
    <GuestLayout>
        <form class="rbim-card px-6 py-7 sm:px-8" @submit.prevent="handleSubmit">
            <p class="mb-6 text-center text-sm text-slate-500">
                Set a new password before continuing.
            </p>

            <div v-if="successMessage" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="generalError" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ generalError }}
            </div>

            <div class="space-y-5">
                <div>
                    <label for="current_password" class="rbim-label">Current password</label>
                    <input
                        id="current_password"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.current_password }"
                        @blur="validateCurrent"
                    >
                    <p v-if="errors.current_password" class="rbim-error">{{ errors.current_password }}</p>
                </div>

                <div>
                    <label for="new_password" class="rbim-label">New password</label>
                    <input
                        id="new_password"
                        v-model="form.new_password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.new_password }"
                        :disabled="!canEditNewPassword"
                        @blur="validateNew"
                        @input="validateNew"
                    >
                    <p v-if="errors.new_password" class="rbim-error">{{ errors.new_password }}</p>
                </div>

                <div>
                    <label for="new_password_confirmation" class="rbim-label">Confirm new password</label>
                    <input
                        id="new_password_confirmation"
                        v-model="form.new_password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.new_password_confirmation }"
                        :disabled="!canEditConfirmation"
                        @blur="validateConfirmation"
                        @input="validateConfirmation"
                    >
                    <p v-if="errors.new_password_confirmation" class="rbim-error">{{ errors.new_password_confirmation }}</p>
                </div>
            </div>

            <button type="submit" class="rbim-btn mt-6 w-full" :disabled="loading || !canSubmit">
                {{ loading ? 'Updating password...' : 'Update password' }}
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';

const PASSWORD_MIN_LENGTH = 8;

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
    new_password_confirmation: '',
});

const generalError = ref('');
const successMessage = ref('');

const canEditNewPassword = computed(() => form.current_password.trim().length > 0);
const canEditConfirmation = computed(() => (
    canEditNewPassword.value && form.new_password.length >= PASSWORD_MIN_LENGTH
));
const canSubmit = computed(() => (
    canEditConfirmation.value
    && form.new_password_confirmation === form.new_password
    && !errors.current_password
    && !errors.new_password
    && !errors.new_password_confirmation
));

function validateCurrent() {
    errors.current_password = form.current_password.trim()
        ? ''
        : 'Current password is required.';
}

function validateNew() {
    if (!canEditNewPassword.value) {
        return;
    }

    if (!form.new_password) {
        errors.new_password = 'New password is required.';

        return;
    }

    if (form.new_password.length < PASSWORD_MIN_LENGTH) {
        errors.new_password = `Password must be at least ${PASSWORD_MIN_LENGTH} characters.`;

        return;
    }

    errors.new_password = '';

    if (form.new_password_confirmation) {
        validateConfirmation();
    }
}

function validateConfirmation() {
    if (!canEditConfirmation.value) {
        return;
    }

    if (!form.new_password_confirmation) {
        errors.new_password_confirmation = 'Please confirm the new password.';

        return;
    }

    errors.new_password_confirmation = form.new_password_confirmation === form.new_password
        ? ''
        : 'New password confirmation does not match.';
}

async function handleSubmit() {
    generalError.value = '';
    successMessage.value = '';
    validateCurrent();
    validateNew();
    validateConfirmation();

    if (!canSubmit.value) {
        return;
    }

    try {
        await changePassword({ ...form });
        successMessage.value = 'Password updated successfully. Redirecting...';

        setTimeout(async () => {
            await router.push({ name: 'dashboard' });
        }, 800);
    } catch (error) {
        const validationErrors = extractValidationErrors(error);

        errors.current_password = validationErrors.current_password ?? errors.current_password;
        errors.new_password = validationErrors.new_password ?? errors.new_password;
        errors.new_password_confirmation = validationErrors.new_password_confirmation ?? errors.new_password_confirmation;

        const hasFieldError = Boolean(
            errors.current_password || errors.new_password || errors.new_password_confirmation,
        );

        generalError.value = hasFieldError
            ? ''
            : extractErrorMessage(error, 'Unable to update password.');
    }
}
</script>
