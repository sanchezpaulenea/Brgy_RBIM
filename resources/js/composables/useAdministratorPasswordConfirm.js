import { reactive } from 'vue';
import { extractValidationErrors } from '@/services/http';

export function useAdministratorPasswordConfirm() {
    const passwordPrompt = reactive({
        open: false,
        title: 'Confirm with your password',
        message: '',
        confirmLabel: 'Confirm',
        variant: 'primary',
        error: '',
        submitting: false,
    });

    let resolveAsk = null;
    let rejectAsk = null;
    let runAction = null;

    function resetPrompt() {
        passwordPrompt.open = false;
        passwordPrompt.error = '';
        passwordPrompt.submitting = false;
        resolveAsk = null;
        rejectAsk = null;
        runAction = null;
    }

    function askAdministratorPassword({
        title,
        message,
        confirmLabel = 'Confirm',
        variant = 'primary',
        run,
    }) {
        if (passwordPrompt.open) {
            return Promise.resolve(null);
        }

        passwordPrompt.open = true;
        passwordPrompt.title = title;
        passwordPrompt.message = message;
        passwordPrompt.confirmLabel = confirmLabel;
        passwordPrompt.variant = variant;
        passwordPrompt.error = '';
        passwordPrompt.submitting = false;
        runAction = run;

        return new Promise((resolve, reject) => {
            resolveAsk = resolve;
            rejectAsk = reject;
        });
    }

    function cancelAdministratorPassword() {
        if (passwordPrompt.submitting) {
            return;
        }

        const resolve = resolveAsk;
        resetPrompt();
        resolve?.(null);
    }

    async function confirmAdministratorPassword(password) {
        if (passwordPrompt.submitting || !runAction) {
            return;
        }

        if (!String(password ?? '')) {
            passwordPrompt.error = 'Enter your Admin or Super Admin password.';

            return;
        }

        passwordPrompt.submitting = true;
        passwordPrompt.error = '';

        try {
            const result = await runAction(password);
            const resolve = resolveAsk;
            resetPrompt();
            resolve?.(result ?? true);
        } catch (error) {
            passwordPrompt.submitting = false;
            const validationError = extractValidationErrors(error).admin_password;

            if (validationError) {
                passwordPrompt.error = validationError;

                return;
            }

            const reject = rejectAsk;
            resetPrompt();
            reject?.(error);
        }
    }

    return {
        passwordPrompt,
        askAdministratorPassword,
        confirmAdministratorPassword,
        cancelAdministratorPassword,
    };
}
