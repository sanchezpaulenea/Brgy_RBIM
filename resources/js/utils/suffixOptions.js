import * as lookupService from '@/services/lookupService';

let cachedSuffixes = null;
let suffixRequest = null;

export function isNotApplicableSuffix(value) {
    const text = String(value ?? '').trim().toLowerCase();

    return text === '' || text.startsWith('not applic');
}

export function suffixFormValue(option) {
    const raw = String(option?.suffix ?? '').trim();

    return isNotApplicableSuffix(raw) ? '' : raw;
}

export function loadSuffixOptions() {
    if (cachedSuffixes) {
        return Promise.resolve(cachedSuffixes);
    }

    if (!suffixRequest) {
        suffixRequest = lookupService.fetchLookup('suffix')
            .then((items) => {
                cachedSuffixes = items;

                return items;
            })
            .catch((error) => {
                suffixRequest = null;
                throw error;
            });
    }

    return suffixRequest;
}
