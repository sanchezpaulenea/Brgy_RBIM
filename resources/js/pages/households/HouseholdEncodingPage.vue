<template>
    <AppLayout title="Household encoding">
        <div class="space-y-6 pb-24">
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>
            <div v-if="loadingLookups" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading encoding form...
            </div>

            <template v-else>
                <section class="space-y-6">
                    <div class="space-y-3">
                        <h2 class="text-lg font-semibold text-slate-900">Identification</h2>
                        <HouseholdIdentificationForm
                            v-model="identification"
                            :location="location"
                            :streets="streets"
                            :clans="clans"
                            :can-create-street="canCreateStreet"
                            :total-members="members.length"
                            :errors="identificationErrors"
                        />
                    </div>

                    <div class="space-y-3">
                        <h2 class="text-lg font-semibold text-slate-900">Demographics</h2>
                        <div class="space-y-4">
                            <HouseholdMemberCard
                                v-for="(member, index) in members"
                                :key="member.key"
                                :model-value="member"
                                :title="memberTitle(member, index)"
                                :help="member.isHead
                                    ? 'This member is the household head. Relationship is set to Head.'
                                    : 'Complete the demographic characteristics for this household member.'"
                                :id-prefix="`member-${index}`"
                                :name-locked="member.isHead"
                                :sexes="sexes"
                                :relationships="relationships"
                                :nationalities="nationalities"
                                :marital-statuses="maritalStatuses"
                                :religions="religions"
                                :ethnicities="ethnicities"
                                :resident-types="residentTypes"
                                :errors="memberErrors[member.key] ?? {}"
                                @update:model-value="updateMember(index, $event)"
                                @remove="removeMember(index)"
                                @lookup-created="onLookupCreated"
                                @lookup-error="(payload) => onMemberLookupError(member.key, payload)"
                            />
                        </div>
                        <button type="button" class="rbim-btn-gold" @click="addMember">
                            + Add household member
                        </button>
                    </div>
                </section>
            </template>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-[#eef1f0] px-4 py-3 lg:pl-72">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-slate-700">Section 1 out of 3</p>
                <button
                    type="button"
                    class="rbim-btn-gold min-w-24"
                    :disabled="loadingLookups || saving"
                    @click="saveHousehold"
                >
                    {{ saving ? 'Saving...' : 'Save household' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import HouseholdIdentificationForm from '@/components/HouseholdIdentificationForm.vue';
import HouseholdMemberCard from '@/components/HouseholdMemberCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import { ageFromDateOfBirth, personDisplayName } from '@/utils/format';
import {
    applyLookupCreated,
    ensureResidentDemographicLookups,
} from '@/utils/demographicLookups';
import { optionalAddressText } from '@/utils/residentForm';
import { personnelNameValidationError } from '@/utils/validation';

const HEAD_RELATIONSHIP_ID = 1;
const DEFAULT_RESIDENT_TYPE_ID = 1;
const DEFAULT_BIRTH_COUNTRY = 'Philippines';

const { hasPermission } = useAuth();

const loadingLookups = ref(true);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');

const location = reactive({
    province: '',
    city: '',
    barangay: '',
});

const streets = ref([]);
const clans = ref([]);
const sexes = ref([]);
const relationships = ref([]);
const nationalities = ref([]);
const maritalStatuses = ref([]);
const religions = ref([]);
const ethnicities = ref([]);
const residentTypes = ref([]);

const identificationErrors = ref({});
const memberErrors = ref({});

const identification = reactive(blankIdentification());
const members = ref([createMember(true)]);

const canCreateStreet = computed(() => hasPermission('street.create'));

watch(() => identification.informant_is_head, (checked) => {
    if (checked) {
        identification.head = { ...identification.informant };
    }
});

watch(() => identification.informant, (informant) => {
    if (identification.informant_is_head) {
        identification.head = { ...informant };
    }
}, { deep: true });

watch(() => identification.head, (head) => {
    const first = members.value[0];

    if (!first?.isHead) {
        return;
    }

    first.last_name = head.last_name;
    first.first_name = head.first_name;
    first.middle_name = head.middle_name;
    first.suffix = head.suffix;
    first.relationship_to_hh_id = HEAD_RELATIONSHIP_ID;
}, { deep: true });

onMounted(loadLookups);

function blankName() {
    return {
        last_name: '',
        first_name: '',
        middle_name: '',
        suffix: '',
    };
}

function blankIdentification() {
    return {
        street_id: null,
        street_name: '',
        house_lot: '',
        block_num: '',
        unit_num: '',
        clan_id: '',
        informant_is_head: true,
        informant: blankName(),
        head: blankName(),
    };
}

function createMember(isHead) {
    return {
        key: `member-${Date.now()}-${Math.random().toString(16).slice(2)}`,
        isHead,
        last_name: isHead ? identification.head.last_name : '',
        first_name: isHead ? identification.head.first_name : '',
        middle_name: isHead ? identification.head.middle_name : '',
        suffix: isHead ? identification.head.suffix : '',
        relationship_to_hh_id: isHead ? HEAD_RELATIONSHIP_ID : '',
        sex_id: '',
        date_of_birth: '',
        nationality_id: '',
        nationality_name: '',
        marital_status_id: '',
        religion_id: '',
        religion_name: '',
        ethnicity_id: '',
        ethnicity_name: '',
        birth_city_municipality: '',
        birth_province: '',
        birth_country: DEFAULT_BIRTH_COUNTRY,
        resident_type_id: DEFAULT_RESIDENT_TYPE_ID,
        highest_education_attained: '',
        enrollment_status: '',
        school_level: '',
        school_location: '',
    };
}

function memberTitle(member, index) {
    const name = personDisplayName(member);

    if (member.isHead) {
        return name ? `Household head — ${name}` : 'Household head';
    }

    return name ? `Household member ${index + 1} — ${name}` : `Household member ${index + 1}`;
}

function updateMember(index, next) {
    members.value[index] = next;
}

function onLookupCreated(payload) {
    applyLookupCreated({
        nationality: nationalities,
        religion: religions,
        ethnicity: ethnicities,
    }, payload);
}

function onMemberLookupError(memberKey, { field, message } = {}) {
    if (!field) {
        return;
    }

    memberErrors.value = {
        ...memberErrors.value,
        [memberKey]: {
            ...(memberErrors.value[memberKey] ?? {}),
            [field]: message,
        },
    };
}

function addMember() {
    members.value.push(createMember(false));
}

function removeMember(index) {
    if (members.value[index]?.isHead) {
        return;
    }

    members.value.splice(index, 1);
}

async function loadLookups() {
    loadingLookups.value = true;
    error.value = '';

    try {
        const [
            locationProfile,
            streetItems,
            clanItems,
            sexItems,
            relationshipItems,
            nationalityItems,
            maritalItems,
            religionItems,
            ethnicityItems,
            residentTypeItems,
        ] = await Promise.all([
            householdService.fetchLocationProfile(),
            lookupService.fetchStreets(),
            lookupService.fetchLookup('clan'),
            lookupService.fetchLookup('sex'),
            lookupService.fetchLookup('relationship-to-hh'),
            lookupService.fetchNationalities(),
            lookupService.fetchLookup('marital-status'),
            lookupService.fetchReligions(),
            lookupService.fetchEthnicities(),
            lookupService.fetchLookup('resident-type'),
        ]);

        Object.assign(location, {
            province: locationProfile?.province ?? '',
            city: locationProfile?.city ?? '',
            barangay: locationProfile?.barangay ?? '',
        });

        streets.value = streetItems;
        clans.value = clanItems;
        sexes.value = sexItems;
        relationships.value = relationshipItems;
        nationalities.value = nationalityItems;
        maritalStatuses.value = maritalItems;
        religions.value = religionItems;
        ethnicities.value = ethnicityItems;
        residentTypes.value = residentTypeItems;
    } catch (loadError) {
        error.value = extractErrorMessage(loadError, 'Unable to load the encoding form.');
    } finally {
        loadingLookups.value = false;
    }
}

function validateIdentification() {
    const errors = {};

    if (!identification.street_id && !identification.street_name.trim()) {
        errors.street_id = 'Street is required.';
    }

    if (!identification.clan_id) {
        errors.clan_id = 'Clan is required.';
    }

    const informantLast = personnelNameValidationError(identification.informant.last_name, 'Last name', true);
    const informantFirst = personnelNameValidationError(identification.informant.first_name, 'First name', true);

    if (informantLast) {
        errors['informant.last_name'] = informantLast;
    }

    if (informantFirst) {
        errors['informant.first_name'] = informantFirst;
    }

    if (identification.informant_is_head) {
        identification.head = { ...identification.informant };
    }

    const headLast = personnelNameValidationError(identification.head.last_name, 'Last name', true);
    const headFirst = personnelNameValidationError(identification.head.first_name, 'First name', true);

    if (headLast) {
        errors['head.last_name'] = headLast;
    }

    if (headFirst) {
        errors['head.first_name'] = headFirst;
    }

    identificationErrors.value = errors;

    if (Object.keys(errors).length) {
        error.value = 'Please complete the identification section.';

        return false;
    }

    error.value = '';

    return true;
}

function validateMembers() {
    const nextErrors = {};
    let valid = true;

    members.value.forEach((member) => {
        const errors = {};

        const last = personnelNameValidationError(member.last_name, 'Last name', true);
        const first = personnelNameValidationError(member.first_name, 'First name', true);

        if (last) {
            errors.last_name = last;
        }

        if (first) {
            errors.first_name = first;
        }

        if (!member.relationship_to_hh_id) {
            errors.relationship_to_hh_id = 'Relationship to the household head is required.';
        }

        if (!member.sex_id) {
            errors.sex_id = 'Sex is required.';
        }

        if (!member.date_of_birth) {
            errors.date_of_birth = 'Date of birth is required.';
        }

        if (!member.nationality_id) {
            errors.nationality_id = 'Nationality is required.';
        }

        if (!member.marital_status_id) {
            errors.marital_status_id = 'Marital status is required.';
        }

        if (!member.religion_id) {
            errors.religion_id = 'Religion is required.';
        }

        if (!member.ethnicity_id) {
            errors.ethnicity_id = 'Ethnicity is required.';
        }

        if (!member.resident_type_id) {
            errors.resident_type_id = 'Resident type is required.';
        }

        if (!String(member.birth_city_municipality ?? '').trim()) {
            errors.birth_city_municipality = 'City / municipality of birth is required.';
        }

        if (!String(member.birth_province ?? '').trim()) {
            errors.birth_province = 'Province of birth is required.';
        }

        if (!String(member.birth_country ?? '').trim()) {
            errors.birth_country = 'Country of birth is required.';
        }

        if (Object.keys(errors).length) {
            valid = false;
            nextErrors[member.key] = errors;
        }
    });

    memberErrors.value = nextErrors;

    if (!valid) {
        error.value = 'Please complete the demographic fields for every household member.';

        return false;
    }

    error.value = '';

    return true;
}

function validateEducation() {
    const nextErrors = { ...memberErrors.value };
    let valid = true;

    educationMembers.value.forEach((member) => {
        const errors = { ...(nextErrors[member.key] ?? {}) };
        const age = ageFromDateOfBirth(member.date_of_birth);

        if (age !== null && age >= 5 && !String(member.highest_education_attained ?? '').trim()) {
            errors.highest_education_attained = 'Highest level of education attained is required.';
        }

        if (age !== null && age >= 3 && age <= 24) {
            if (!String(member.enrollment_status ?? '').trim()) {
                errors.enrollment_status = 'Enrollment status is required.';
            }

            if (member.enrollment_status === 'Currently enrolled') {
                if (!String(member.school_level ?? '').trim()) {
                    errors.school_level = 'School level is required.';
                }

                if (!String(member.school_location ?? '').trim()) {
                    errors.school_location = 'School location is required.';
                }
            }
        }

        if (Object.keys(errors).length) {
            valid = false;
            nextErrors[member.key] = errors;
        }
    });

    memberErrors.value = nextErrors;

    if (!valid) {
        error.value = 'Please complete the education fields for each eligible member.';

        return false;
    }

    error.value = '';

    return true;
}

function optionalText(value) {
    const text = String(value ?? '').trim();

    return text === '' ? null : text;
}

function toId(value) {
    const number = Number(value);

    return Number.isInteger(number) && number > 0 ? number : null;
}

function residentPayload(member) {
    return {
        last_name: member.last_name.trim(),
        first_name: member.first_name.trim(),
        middle_name: optionalText(member.middle_name),
        suffix: optionalText(member.suffix),
        relationship_to_hh_id: toId(member.relationship_to_hh_id),
        sex_id: toId(member.sex_id),
        date_of_birth: member.date_of_birth,
        birth_city_municipality: member.birth_city_municipality.trim(),
        birth_province: member.birth_province.trim(),
        birth_country: member.birth_country.trim(),
        nationality_id: toId(member.nationality_id),
        religion_id: toId(member.religion_id),
        ethnicity_id: toId(member.ethnicity_id),
        marital_status_id: toId(member.marital_status_id),
        resident_type_id: toId(member.resident_type_id),
        clan_id: toId(identification.clan_id),
    };
}

async function resolveStreetId() {
    if (identification.street_id) {
        return toId(identification.street_id);
    }

    const name = identification.street_name.trim();

    if (!name) {
        throw new Error('Street is required.');
    }

    const existing = streets.value.find((street) => street.label.toLowerCase() === name.toLowerCase());

    if (existing) {
        identification.street_id = existing.id;

        return toId(existing.id);
    }

    if (!canCreateStreet.value) {
        throw new Error('Choose an existing street.');
    }

    const created = await lookupService.createStreet({ street_name: name });
    streets.value = [...streets.value, created];
    identification.street_id = created.street_id ?? created.id;

    return toId(identification.street_id);
}

function mergeMemberLookupErrors(lookupErrorMap) {
    if (!Object.keys(lookupErrorMap).length) {
        return;
    }

    memberErrors.value = {
        ...memberErrors.value,
        ...Object.fromEntries(Object.entries(lookupErrorMap).map(([key, errors]) => [
            key,
            { ...(memberErrors.value[key] ?? {}), ...errors },
        ])),
    };
}

async function saveHousehold() {
    const lookupErrorMap = {};

    for (const member of members.value) {
        const lookupErrors = await ensureResidentDemographicLookups(member, {
            nationalities,
            religions,
            ethnicities,
            canCreateNationality: hasPermission('nationality.create'),
            canCreateReligion: hasPermission('religion.create'),
            canCreateEthnicity: hasPermission('ethnicity.create'),
        });

        if (Object.keys(lookupErrors).length) {
            lookupErrorMap[member.key] = lookupErrors;
        }
    }

    const identificationValid = validateIdentification();
    const membersValid = validateMembers();
    const educationValid = validateEducation();

    mergeMemberLookupErrors(lookupErrorMap);

    if (!identificationValid || !membersValid || !educationValid || Object.keys(lookupErrorMap).length) {
        return;
    }

    saving.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        const streetId = await resolveStreetId();
        const [head, ...others] = members.value;
        const household = await householdService.createHousehold({
            clan_id: toId(identification.clan_id),
            street_id: streetId,
            house_lot: optionalAddressText(identification.house_lot),
            block_num: optionalAddressText(identification.block_num),
            unit_num: optionalAddressText(identification.unit_num),
            head: residentPayload(head),
        });

        const householdId = household.household_id;

        for (const member of others) {
            await householdService.createResident({
                ...residentPayload(member),
                household_id: householdId,
            });
        }

        successMessage.value = 'Household registered successfully.';
        identification.street_id = streetId;
        identification.house_lot = '';
        identification.block_num = '';
        identification.unit_num = '';
        identification.clan_id = '';
        identification.informant_is_head = true;
        identification.informant = blankName();
        identification.head = blankName();
        members.value = [createMember(true)];
        identificationErrors.value = {};
        memberErrors.value = {};
    } catch (saveError) {
        const fieldErrors = extractValidationErrors(saveError);

        if (Object.keys(fieldErrors).length) {
            applyServerErrors(fieldErrors);
        }

        error.value = extractErrorMessage(saveError, 'Unable to register this household.');
    } finally {
        saving.value = false;
    }
}

function applyServerErrors(fieldErrors) {
    const identity = {};
    const membersMap = { ...(memberErrors.value ?? {}) };
    const headErrors = { ...(membersMap[members.value[0]?.key] ?? {}) };

    Object.entries(fieldErrors).forEach(([field, message]) => {
        if (field.startsWith('head.')) {
            const key = field.slice(5);
            headErrors[key] = message;
            identity[`head.${key}`] = message;

            return;
        }

        identity[field] = message;
    });

    if (members.value[0]) {
        membersMap[members.value[0].key] = headErrors;
    }

    identificationErrors.value = identity;
    memberErrors.value = membersMap;
}
</script>
