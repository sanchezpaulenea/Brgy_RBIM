import { readonly, ref } from 'vue';

const VISIBLE_DURATION = 3200;

const notification = ref(null);

let sequence = 0;
let dismissTimer = null;

export function notifySuccess(message) {
    const text = String(message ?? '').trim();

    if (!text) {
        return;
    }

    if (dismissTimer) {
        clearTimeout(dismissTimer);
    }

    sequence += 1;
    notification.value = { id: sequence, message: text };
    dismissTimer = setTimeout(dismiss, VISIBLE_DURATION);
}

export function dismiss() {
    if (dismissTimer) {
        clearTimeout(dismissTimer);
        dismissTimer = null;
    }

    notification.value = null;
}

export function useAppNotifications() {
    return {
        notification: readonly(notification),
        notifySuccess,
        dismiss,
    };
}
