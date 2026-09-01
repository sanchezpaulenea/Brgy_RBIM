import { extractErrorMessage } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import { toId } from '@/utils/residentForm';

export async function ensureLookupId({
    id,
    name,
    options = [],
    canCreate = false,
    create,
    requiredMessage,
}) {
    const existingId = toId(id);

    if (existingId) {
        return existingId;
    }

    const term = String(name ?? '').trim();

    if (!term) {
        throw new Error(requiredMessage);
    }

    const existing = options.find((option) => (
        String(option.label ?? '').toLowerCase() === term.toLowerCase()
    ));

    if (existing) {
        return toId(existing.id);
    }

    if (!canCreate || typeof create !== 'function') {
        throw new Error(requiredMessage);
    }

    const created = await create(term);

    return toId(created?.id);
}

export function appendLookupOption(list, item) {
    if (!item?.id || list.value.some((option) => Number(option.id) === Number(item.id))) {
        return item;
    }

    list.value = [...list.value, item];

    return item;
}

export function applyLookupCreated(lists, payload) {
    const kind = payload?.kind;
    const item = payload?.item;
    const list = lists[kind];

    if (!list || !item) {
        return;
    }

    appendLookupOption(list, item);
}

export async function ensureResidentDemographicLookups(form, {
    nationalities,
    religions,
    ethnicities,
    canCreateNationality,
    canCreateReligion,
    canCreateEthnicity,
}) {
    const errors = {};

    try {
        form.nationality_id = await ensureLookupId({
            id: form.nationality_id,
            name: form.nationality_name,
            options: nationalities.value,
            canCreate: canCreateNationality,
            create: async (name) => appendLookupOption(
                nationalities,
                await lookupService.createNationality({ nationality: name }),
            ),
            requiredMessage: 'Nationality is required.',
        });
    } catch (error) {
        errors.nationality_id = error.response
            ? extractErrorMessage(error, 'Unable to add this nationality.')
            : error.message;
    }

    try {
        form.religion_id = await ensureLookupId({
            id: form.religion_id,
            name: form.religion_name,
            options: religions.value,
            canCreate: canCreateReligion,
            create: async (name) => appendLookupOption(
                religions,
                await lookupService.createReligion({ religion: name }),
            ),
            requiredMessage: 'Religion is required.',
        });
    } catch (error) {
        errors.religion_id = error.response
            ? extractErrorMessage(error, 'Unable to add this religion.')
            : error.message;
    }

    try {
        form.ethnicity_id = await ensureLookupId({
            id: form.ethnicity_id,
            name: form.ethnicity_name,
            options: ethnicities.value,
            canCreate: canCreateEthnicity,
            create: async (name) => appendLookupOption(
                ethnicities,
                await lookupService.createEthnicity({ ethnicity: name }),
            ),
            requiredMessage: 'Ethnicity is required.',
        });
    } catch (error) {
        errors.ethnicity_id = error.response
            ? extractErrorMessage(error, 'Unable to add this ethnicity.')
            : error.message;
    }

    return errors;
}
