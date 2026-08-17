import http from '@/services/http';
import { personnelDisplayName } from '@/utils/format';
import * as lookupService from '@/services/lookupService';
import * as userService from '@/services/userService';

function asPersonnelRecord(person, extra = {}) {
    return {
        personnel_id: person.personnel_id,
        position_id: person.position_id,
        position_name: person.position_name ?? person.label ?? extra.position_name ?? '—',
        personnel_last_name: person.personnel_last_name ?? '',
        personnel_first_name: person.personnel_first_name ?? '',
        personnel_middle_name: person.personnel_middle_name ?? '',
        personnel_suffix: person.personnel_suffix ?? '',
        personnel_status_id: person.personnel_status_id ?? extra.personnel_status_id ?? 1,
        personnel_date_of_birth: person.personnel_date_of_birth ?? '',
        username: extra.username ?? person.username ?? null,
        linked: Boolean(extra.username ?? person.username),
        label: personnelDisplayName({
            ...person,
            label: person.label ?? person.position_name,
        }),
    };
}

export async function fetchPersonnel() {
    try {
        const { data } = await http.get('/barangay-personnel');

        return (data.items ?? []).map((item) => asPersonnelRecord(item, {
            username: item.username,
            position_name: item.position_name,
        }));
    } catch (error) {
        if (error.response?.status !== 404) {
            throw error;
        }
    }

    const [users, options] = await Promise.all([
        userService.fetchUsers(),
        userService.fetchCreateOptions().catch(() => ({ personnel: [] })),
    ]);

    const linked = users
        .filter((account) => account.personnel_id)
        .map((account) => asPersonnelRecord(account.personnel ?? {
            personnel_id: account.personnel_id,
            position_name: account.personnel?.position_name,
        }, { username: account.username }));

    const unlinked = (options.personnel ?? []).map((person) => asPersonnelRecord(person));

    return [...unlinked, ...linked];
}

export async function fetchPersonnelSearchOptions() {
    const items = await fetchPersonnel();

    return items
        .filter((item) => !item.linked)
        .map((item) => ({
            personnel_id: item.personnel_id,
            position_id: item.position_id,
            label: item.label === `Personnel #${item.personnel_id}`
                ? `${item.label} — ${item.position_name}`
                : `${item.label} (${item.position_name})`,
        }));
}

export async function createPersonnel(payload) {
    let positionId = payload.position_id;

    if (!positionId && payload.position_name) {
        const position = await lookupService.createPersonnelPosition({
            position_name: payload.position_name,
        });
        positionId = position.id;
    }

    const body = {
        position_id: Number(positionId),
        personnel_last_name: payload.personnel_last_name,
        personnel_first_name: payload.personnel_first_name,
        personnel_middle_name: payload.personnel_middle_name || null,
        personnel_suffix: payload.personnel_suffix || null,
        personnel_date_of_birth: payload.personnel_date_of_birth,
        personnel_status_id: Number(payload.personnel_status_id || 1),
    };

    try {
        const { data } = await http.post('/barangay-personnel', body);

        return data.item;
    } catch (error) {
        if (error.response?.status === 404) {
            error.response.data = {
                message: 'The personnel record could not be saved. Create the position first, then try again.',
            };
        }

        throw error;
    }
}

export async function updatePersonnel(personnelId, payload) {
    try {
        const { data } = await http.patch(`/barangay-personnel/${personnelId}`, payload);

        return data.item;
    } catch (error) {
        if (error.response?.status === 404) {
            error.response.data = {
                message: 'The personnel record could not be updated.',
            };
        }

        throw error;
    }
}
