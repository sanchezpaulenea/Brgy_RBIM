import { readonly, ref } from 'vue';

/*
 * Must match the key read by the inline script in app.blade.php, which applies
 * the theme before the bundle loads so the page never flashes light.
 */
const STORAGE_KEY = 'rbim-theme';

function readStoredTheme() {
    try {
        return localStorage.getItem(STORAGE_KEY) === 'dark' ? 'dark' : 'light';
    } catch {
        return 'light';
    }
}

const theme = ref(readStoredTheme());

function applyTheme(value) {
    document.documentElement.classList.toggle('dark', value === 'dark');
}

applyTheme(theme.value);

function setTheme(value) {
    theme.value = value === 'dark' ? 'dark' : 'light';
    applyTheme(theme.value);

    try {
        localStorage.setItem(STORAGE_KEY, theme.value);
    } catch {
        // Storage can be unavailable (private mode); the choice still lasts for this session.
    }
}

export function useTheme() {
    return {
        theme: readonly(theme),
        setTheme,
    };
}
