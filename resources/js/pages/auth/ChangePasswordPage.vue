<template>
    <GuestLayout>
        <form class="rbim-card px-6 py-7 sm:px-8" novalidate @submit.prevent="handleSubmit">
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
                <PasswordField
                    v-model="form.current_password"
                    input-id="current_password"
                    label="Current password"
                    autocomplete="current-password"
                    required
                    :error="errors.current_password"
                    @blur="validateCurrent"
                />

                <PasswordField
                    v-model="form.new_password"
                    input-id="new_password"
                    label="New password"
                    autocomplete="new-password"
                    required
                    :disabled="!canEditNewPassword"
                    :error="errors.new_password"
                    :hint="`Use at least ${minLength} characters.`"
                    @update:model-value="validateNew"
                    @blur="validateNew"
                />

                <PasswordField
                    v-model="form.new_password_confirmation"
                    input-id="new_password_confirmation"
                    label="Confirm new password"
                    autocomplete="new-password"
                    required
                    :disabled="!canEditConfirmation"
                    :error="errors.new_password_confirmation"
                    @update:model-value="validateConfirmation"
                    @blur="validateConfirmation"
                />
            </div>

            <button type="submit" class="rbim-btn mt-6 w-full" :disabled="loading || !canSubmit">
                {{ loading ? 'Updating password...' : 'Update password' }}
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import GuestLayout from '@/layouts/GuestLayout.vue';
import PasswordField from '@/components/PasswordField.vue';
import { useAuth } from '@/composables/useAuth';
import * as authService from '@/services/authService';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import { LOGIN_PASSWORD_MIN_LENGTH } from '@/utils/validation';

const router = useRouter();
const { changePassword, loading } = useAuth();

/**
 * Read from the system settings on every visit: the minimum length is
 * configurable, so a cached copy would keep reporting the previous value after
 * an administrator changes the password setting.
 */
const minLength = ref(LOGIN_PASSWORD_MIN_LENGTH);

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
    canEditNewPassword.value && form.new_password.length >= minLength.value
));
const canSubmit = computed(() => (
    canEditConfirmation.value
    && form.new_password_confirmation === form.new_password
    && !errors.current_password
    && !errors.new_password
    && !errors.new_password_confirmation
));

onMounted(async () => {
    try {
        const policy = await authService.fetchPasswordPolicy();
        const configured = Number(policy?.password_min_length);

        if (Number.isFinite(configured) && configured > 0) {
            minLength.value = configured;
        }
    } catch {
        // Keep the default minimum; the server still enforces the stored rule.
    }

    if (form.new_password) {
        validateNew();
    }
});

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

    if (form.new_password.length < minLength.value) {
        errors.new_password = `Password must be at least ${minLength.value} characters.`;

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
        successMessage.value = 'Password updated successfully. Redirecting to login...';

        setTimeout(async () => {
            await router.push({ name: 'login', query: { reason: 'password_changed' } });
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
