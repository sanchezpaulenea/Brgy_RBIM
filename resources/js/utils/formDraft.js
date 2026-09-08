const DRAFT_PREFIX = 'rbim.form-draft.';

export const HOUSEHOLD_REGISTER_DRAFT_KEY = 'household-register';

function storageKey(name) {
    return `${DRAFT_PREFIX}${name}`;
}

export function readFormDraft(name) {
    try {
        const raw = sessionStorage.getItem(storageKey(name));

        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);

        return parsed && typeof parsed === 'object' ? parsed : null;
    } catch {
        return null;
    }
}

export function writeFormDraft(name, payload) {
    try {
        sessionStorage.setItem(storageKey(name), JSON.stringify(payload));
    } catch {
        // Private mode or quota — skip rather than breaking the form.
    }
}

export function clearFormDraft(name) {
    try {
        sessionStorage.removeItem(storageKey(name));
    } catch {
        // Ignore storage failures.
    }
}

export function clearAllFormDrafts() {
    try {
        Object.keys(sessionStorage)
            .filter((key) => key.startsWith(DRAFT_PREFIX))
            .forEach((key) => sessionStorage.removeItem(key));
    } catch {
        // Ignore storage failures.
    }
}
