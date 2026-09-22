import * as lookupService from '@/services/lookupService';
import { placeNameValidationError } from '@/utils/validation';

export function emptyHouseholdQuestionLookups() {
    return {
        ownershipType: [],
        fuelType: [],
        waterSource: [],
        kitchenGarbageDisposal: [],
        toiletFacilityType: [],
        buildingHouseType: [],
        constructionMaterialOuterWall: [],
        sex: [],
    };
}

export async function fetchHouseholdQuestionLookups() {
    const [
        ownershipType,
        fuelType,
        waterSource,
        kitchenGarbageDisposal,
        toiletFacilityType,
        buildingHouseType,
        constructionMaterialOuterWall,
        sex,
    ] = await Promise.all([
        lookupService.fetchLookup('ownership-type'),
        lookupService.fetchLookup('fuel-type'),
        lookupService.fetchLookup('water-source'),
        lookupService.fetchLookup('kitchen-garbage-disposal'),
        lookupService.fetchLookup('toilet-facility-type'),
        lookupService.fetchLookup('building-house-type'),
        lookupService.fetchLookup('construction-material-outer-wall'),
        lookupService.fetchLookup('sex'),
    ]);

    return {
        ownershipType,
        fuelType,
        waterSource,
        kitchenGarbageDisposal,
        toiletFacilityType,
        buildingHouseType,
        constructionMaterialOuterWall,
        sex,
    };
}

function femaleDeathsFromRecord(record) {
    const deaths = (record?.female_deaths ?? []).map((death) => ({
        age: death.age ?? '',
        cause_of_death: death.cause_of_death ?? '',
    }));

    if (record?.female_hhm_died_past_12mos && !deaths.length) {
        return [emptyFemaleDeath()];
    }

    return deaths;
}

function childDeathsFromRecord(record) {
    const deaths = (record?.child_deaths ?? []).map((death) => ({
        age: death.age ?? '',
        cause_of_death: death.cause_of_death ?? '',
        sex_id: death.sex_id ?? '',
    }));

    if (record?.child_hhm_died_past_12mos && !deaths.length) {
        return [emptyChildDeath()];
    }

    return deaths;
}

export function emptyFemaleDeath() {
    return {
        age: '',
        cause_of_death: '',
    };
}

export function emptyChildDeath() {
    return {
        age: '',
        cause_of_death: '',
        sex_id: '',
    };
}

export function emptyHouseholdQuestionsForm(defaults = {}) {
    return {
        ownership_of_housing_unit_id: '',
        ownership_of_lot_id: '',
        fuel_type_for_lighting_id: '',
        fuel_type_for_cooking_id: '',
        main_source_drinking_water_id: '',
        kitchen_garbage_disposal_id: '',
        perform_garbage_seggragation: '',
        toilet_facility_type_id: '',
        type_of_building_house_id: '',
        construction_material_outer_wall_id: '',
        female_hhm_died_past_12mos: '',
        female_deaths: [],
        child_hhm_died_past_12mos: '',
        child_deaths: [],
        common_diseases: ['', '', ''],
        primary_needs: ['', '', ''],
        intend_to_stay_brgy: defaults.barangay ?? '',
        intend_to_stay_municipality: defaults.city ?? '',
        intend_to_stay_province: defaults.province ?? '',
    };
}

export function householdQuestionsFromRecord(record, defaults = {}) {
    const diseases = [...(record?.common_diseases ?? [])];
    const needs = [...(record?.primary_needs ?? [])];

    return {
        ...emptyHouseholdQuestionsForm(defaults),
        ownership_of_housing_unit_id: record?.ownership_of_housing_unit_id ?? '',
        ownership_of_lot_id: record?.ownership_of_lot_id ?? '',
        fuel_type_for_lighting_id: record?.fuel_type_for_lighting_id ?? '',
        fuel_type_for_cooking_id: record?.fuel_type_for_cooking_id ?? '',
        main_source_drinking_water_id: record?.main_source_drinking_water_id ?? '',
        kitchen_garbage_disposal_id: record?.kitchen_garbage_disposal_id ?? '',
        perform_garbage_seggragation: booleanSelectValue(record?.perform_garbage_seggragation),
        toilet_facility_type_id: record?.toilet_facility_type_id ?? '',
        type_of_building_house_id: record?.type_of_building_house_id ?? '',
        construction_material_outer_wall_id: record?.construction_material_outer_wall_id ?? '',
        female_hhm_died_past_12mos: booleanSelectValue(record?.female_hhm_died_past_12mos),
        female_deaths: femaleDeathsFromRecord(record),
        child_hhm_died_past_12mos: booleanSelectValue(record?.child_hhm_died_past_12mos),
        child_deaths: childDeathsFromRecord(record),
        common_diseases: [diseases[0] ?? '', diseases[1] ?? '', diseases[2] ?? ''],
        primary_needs: [needs[0] ?? '', needs[1] ?? '', needs[2] ?? ''],
        intend_to_stay_brgy: record?.intend_to_stay_brgy ?? defaults.barangay ?? '',
        intend_to_stay_municipality: record?.intend_to_stay_municipality ?? defaults.city ?? '',
        intend_to_stay_province: record?.intend_to_stay_province ?? defaults.province ?? '',
    };
}

export function householdQuestionsPayload(form) {
    return {
        ownership_of_housing_unit_id: Number(form.ownership_of_housing_unit_id),
        ownership_of_lot_id: Number(form.ownership_of_lot_id),
        fuel_type_for_lighting_id: Number(form.fuel_type_for_lighting_id),
        fuel_type_for_cooking_id: Number(form.fuel_type_for_cooking_id),
        main_source_drinking_water_id: Number(form.main_source_drinking_water_id),
        kitchen_garbage_disposal_id: Number(form.kitchen_garbage_disposal_id),
        perform_garbage_seggragation: form.perform_garbage_seggragation === true || form.perform_garbage_seggragation === 'true',
        toilet_facility_type_id: Number(form.toilet_facility_type_id),
        type_of_building_house_id: Number(form.type_of_building_house_id),
        construction_material_outer_wall_id: Number(form.construction_material_outer_wall_id),
        female_hhm_died_past_12mos: isYes(form.female_hhm_died_past_12mos),
        female_deaths: isYes(form.female_hhm_died_past_12mos)
            ? (form.female_deaths ?? []).map((death) => ({
                age: Number(death.age),
                cause_of_death: String(death.cause_of_death ?? '').trim(),
            }))
            : [],
        child_hhm_died_past_12mos: isYes(form.child_hhm_died_past_12mos),
        child_deaths: isYes(form.child_hhm_died_past_12mos)
            ? (form.child_deaths ?? []).map((death) => ({
                age: Number(death.age),
                cause_of_death: String(death.cause_of_death ?? '').trim(),
                sex_id: Number(death.sex_id),
            }))
            : [],
        common_diseases: namedList(form.common_diseases),
        primary_needs: namedList(form.primary_needs),
        intend_to_stay_brgy: String(form.intend_to_stay_brgy ?? '').trim(),
        intend_to_stay_municipality: String(form.intend_to_stay_municipality ?? '').trim(),
        intend_to_stay_province: String(form.intend_to_stay_province ?? '').trim(),
    };
}

export function validateHouseholdQuestions(form, errors) {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });

    requiredSelect(form, errors, 'ownership_of_housing_unit_id', 'Ownership of the housing unit is required.');
    requiredSelect(form, errors, 'ownership_of_lot_id', 'Ownership of the lot is required.');
    requiredSelect(form, errors, 'fuel_type_for_lighting_id', 'Fuel used for lighting is required.');
    requiredSelect(form, errors, 'fuel_type_for_cooking_id', 'Fuel used for cooking is required.');
    requiredSelect(form, errors, 'main_source_drinking_water_id', 'Main source of drinking water is required.');
    requiredSelect(form, errors, 'kitchen_garbage_disposal_id', 'Kitchen garbage disposal is required.');
    requiredBoolean(form, errors, 'perform_garbage_seggragation', 'Please indicate whether the household segregates garbage.');
    requiredSelect(form, errors, 'toilet_facility_type_id', 'Type of toilet facility is required.');
    requiredSelect(form, errors, 'type_of_building_house_id', 'Type of building/house is required.');
    requiredSelect(form, errors, 'construction_material_outer_wall_id', 'Construction material of the outer wall is required.');
    requiredBoolean(form, errors, 'female_hhm_died_past_12mos', 'Please indicate whether a female household member died in the past 12 months.');
    if (isYes(form.female_hhm_died_past_12mos)) {
        validateDeathRows(form.female_deaths, errors, 'female_deaths', {
            requireSex: false,
            maxAge: 120,
            emptyMessage: 'Add the age and cause of death for each female household member who died.',
        });
    }

    requiredBoolean(form, errors, 'child_hhm_died_past_12mos', 'Please indicate whether a child household member below 5 years old died in the past 12 months.');
    if (isYes(form.child_hhm_died_past_12mos)) {
        validateDeathRows(form.child_deaths, errors, 'child_deaths', {
            requireSex: true,
            maxAge: 4,
            emptyMessage: 'Add the age, sex, and cause of death for each child household member who died.',
            maxAgeMessage: 'The child must be below 5 years old.',
        });
    }

    const brgyError = placeNameValidationError(form.intend_to_stay_brgy, 'Intended barangay of stay', true);
    if (brgyError) {
        errors.intend_to_stay_brgy = brgyError;
    }

    const cityError = placeNameValidationError(form.intend_to_stay_municipality, 'Intended municipality of stay', true);
    if (cityError) {
        errors.intend_to_stay_municipality = cityError;
    }

    const provinceError = placeNameValidationError(form.intend_to_stay_province, 'Intended province of stay', true);
    if (provinceError) {
        errors.intend_to_stay_province = provinceError;
    }

    return Object.keys(errors).length === 0;
}

export function booleanSelectValue(value) {
    if (value === true) {
        return 'true';
    }

    if (value === false) {
        return 'false';
    }

    return '';
}

export function parseBooleanSelect(value) {
    if (value === 'true' || value === true) {
        return true;
    }

    if (value === 'false' || value === false) {
        return false;
    }

    return '';
}

export function deathListLabel(answeredYes, deaths, { includeSex = false } = {}) {
    if (!isYes(answeredYes) && !(deaths ?? []).length) {
        return yesNoLabel(false);
    }

    const details = (deaths ?? []).map((death) => {
        const parts = [
            includeSex ? (death.sex || '') : '',
            death.age !== '' && death.age != null ? `${death.age} yrs` : '',
            death.cause_of_death || '',
        ].filter(Boolean);

        return parts.join(', ');
    }).filter(Boolean);

    return details.length ? `Yes — ${details.join('; ')}` : yesNoLabel(true);
}

export function yesNoLabel(value) {
    if (value === true) {
        return 'Yes';
    }

    if (value === false) {
        return 'No';
    }

    return '—';
}

function isYes(value) {
    return value === true || value === 'true';
}

function validateDeathRows(rows, errors, prefix, {
    requireSex,
    maxAge,
    emptyMessage,
    maxAgeMessage,
}) {
    if (!Array.isArray(rows) || rows.length === 0) {
        errors[prefix] = emptyMessage;

        return;
    }

    rows.forEach((row, index) => {
        const age = Number(row?.age);

        if (!Number.isInteger(age) || age < 0) {
            errors[`${prefix}.${index}.age`] = 'Age is required.';
        } else if (age > maxAge) {
            errors[`${prefix}.${index}.age`] = maxAgeMessage || `Age must be at most ${maxAge}.`;
        }

        if (!String(row?.cause_of_death ?? '').trim()) {
            errors[`${prefix}.${index}.cause_of_death`] = 'Cause of death is required.';
        }

        if (requireSex && !Number(row?.sex_id)) {
            errors[`${prefix}.${index}.sex_id`] = 'Sex is required.';
        }
    });
}

function requiredSelect(form, errors, field, message) {
    if (!Number(form[field])) {
        errors[field] = message;
    }
}

function requiredBoolean(form, errors, field, message) {
    if (form[field] !== true && form[field] !== false && form[field] !== 'true' && form[field] !== 'false') {
        errors[field] = message;
    }
}

function namedList(values) {
    return (values ?? [])
        .map((value) => String(value ?? '').trim())
        .filter(Boolean)
        .slice(0, 3);
}
