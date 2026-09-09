import * as residentService from '@/services/residentService';
import { todayDate } from '@/utils/format';
import { classifyMigrationForm } from '@/utils/migration';
import { toId } from '@/utils/residentForm';
import {
    ECONOMIC_STATUS_NOT_APPLICABLE,
    ENROLLMENT_NOT_ENROLLED_ID,
    educationFieldRelevance,
    isEnrollmentStatusEnrolled,
    isFamilyPlanningNone,
    isNotApplicableSchoolLvl,
    lookupById,
    notApplicableHighestEducId,
    notApplicableSchoolLvlId,
    resolveApplicableSections,
    sociocivicFieldRelevance,
    sourceOfIncomeSkipsWorkDetails,
    titleCaseWords,
} from '@/utils/residentProfiling';
import { ncscRrnValidationError, placeNameValidationError } from '@/utils/validation';

export const PROFILING_SECTION_ORDER = [
    'education',
    'economic',
    'infant_health',
    'health',
    'women_health',
    'sociocivic',
    'migration',
    'ctc',
    'skills',
];

export const PROFILING_SECTION_LABELS = {
    education: 'Education',
    economic: 'Economic',
    infant_health: 'Infant health',
    health: 'Health',
    women_health: "Women's health",
    sociocivic: 'Sociocivic',
    migration: 'Migration',
    ctc: 'Community tax certificate',
    skills: 'Skills',
};

export function emptySectionForm(key) {
    const forms = {
        education: {
            highest_lvl_of_educ_id: '',
            current_enrollement_status_id: '',
            school_lvl_id: '',
            place_of_school_brgy: '',
            place_of_school_city_municipality: '',
        },
        economic: {
            monthly_income: '',
            source_of_income_id: '',
            status_of_work_business_id: '',
            place_of_work_business: '',
        },
        infant_health: {
            place_of_delivery_id: '',
            birth_attendant_id: '',
            immunization: '',
        },
        health: {
            health_insurance_id: '',
            facility_visited_past_12mos_id: '',
            facility_visit_reason_id: '',
            disability_id: '',
            disability_name: '',
            pwd_id_number: '',
        },
        women_health: {
            number_pregnancies: '',
            living_children: '',
            family_planning_method_id: '',
            source_of_fp_method_id: '',
            have_intention_to_use_fp: '',
        },
        sociocivic: {
            solo_parent_status_id: '',
            registered_sen_citizen: '',
            ncsc_rrn_id_number: '',
            osca_id_number: '',
            is_registered_barangay_voter: '',
            registered_barangay_voter: '',
        },
        migration: {
            previous_residence_6mos_brgy: '',
            previous_residence_6mos_city_municipality: '',
            previous_residence_5yrs_brgy: '',
            previous_residence_5yrs_city_municipality: '',
            date_of_transfer_in_brgy: '',
            reason_for_leaving_id: '',
            will_return_to_previous_residence: '',
            reason_for_transfer_id: '',
            duration_of_stay: '',
        },
        ctc: {
            has_valid_ctc: '',
            ctc_issued_here: '',
        },
        skills: {
            skills_development_training: '',
            skill_type_id: '',
        },
    };

    return { ...(forms[key] ?? {}) };
}

export function profilingStepsFor(resident, { healthSaved = false } = {}) {
    const applicable = resolveApplicableSections(resident);

    return PROFILING_SECTION_ORDER.filter((key) => {
        if (applicable[key] !== true) {
            return false;
        }

        if (key === 'women_health' && !healthSaved) {
            return false;
        }

        return true;
    });
}

export function sectionRecordId(key, record) {
    if (!record) {
        return null;
    }

    const ids = {
        education: record.education_id,
        economic: record.economic_id,
        infant_health: record.infant_health_id,
        health: record.health_id,
        women_health: record.women_health_id,
        sociocivic: record.sociocivic_id,
        migration: record.migration_id,
        ctc: record.community_tax_cert,
        skills: record.skills_development_id,
    };

    return toId(ids[key]);
}

function requiredSelect(form, errors, field, message) {
    if (!toId(form[field])) {
        errors[field] = message;
    } else {
        delete errors[field];
    }
}

function requiredBoolean(form, errors, field, message) {
    if (form[field] !== true && form[field] !== false) {
        errors[field] = message;
    } else {
        delete errors[field];
    }
}

function requiredInteger(form, errors, field, message, { min = 0 } = {}) {
    const raw = String(form[field] ?? '').trim();

    if (raw === '') {
        errors[field] = message;

        return;
    }

    if (!/^\d+$/.test(raw)) {
        errors[field] = message.replace('is required.', 'must be a whole number.');

        return;
    }

    if (Number(raw) < min) {
        errors[field] = message.replace('is required.', `must be at least ${min}.`);

        return;
    }

    delete errors[field];
}

function requiredText(form, errors, field, message, { max = 45 } = {}) {
    const text = String(form[field] ?? '').trim();

    if (!text) {
        errors[field] = message;

        return;
    }

    if (text.length > max) {
        errors[field] = message.replace('is required.', `may not be longer than ${max} characters.`);

        return;
    }

    delete errors[field];
}

function optionalPlace(form, errors, field, label) {
    const message = placeNameValidationError(form[field], label, false);

    if (message) {
        errors[field] = message;
    } else {
        delete errors[field];
    }
}

function requiredPlace(form, errors, field, label) {
    const message = placeNameValidationError(form[field], label, true);

    if (message) {
        errors[field] = message;
    } else {
        delete errors[field];
    }
}

function nonSoloParentId(lookups) {
    const match = (lookups.soloParentStatus ?? []).find((option) => (
        /non[- ]solo/i.test(String(option.label ?? ''))
    ));

    return match?.id ?? 2;
}

export function validateSectionForm(key, form, errors, { lookups = {}, resident = {}, location = {} } = {}) {
    Object.keys(errors).forEach((field) => {
        delete errors[field];
    });

    if (key === 'education') {
        const relevance = educationFieldRelevance(resident);

        if (relevance.highest_level) {
            requiredSelect(form, errors, 'highest_lvl_of_educ_id', 'Highest level of education is required.');
        }

        if (relevance.enrollment) {
            requiredSelect(form, errors, 'current_enrollement_status_id', 'Current enrollment status is required.');

            const enrolled = isEnrollmentStatusEnrolled(
                lookupById(lookups.currentEnrollmentStatus, form.current_enrollement_status_id),
            );

            if (enrolled) {
                requiredSelect(form, errors, 'school_lvl_id', 'School level is required.');
                requiredPlace(form, errors, 'place_of_school_brgy', 'School barangay');
                requiredPlace(form, errors, 'place_of_school_city_municipality', 'School city / municipality');

                if (isNotApplicableSchoolLvl(lookupById(lookups.schoolLvl, form.school_lvl_id))) {
                    errors.school_lvl_id = 'School level is required.';
                }
            }
        }
    }

    if (key === 'economic') {
        requiredInteger(form, errors, 'monthly_income', 'Monthly income is required.');
        requiredSelect(form, errors, 'source_of_income_id', 'Source of income is required.');

        if (!sourceOfIncomeSkipsWorkDetails(form.source_of_income_id, lookups.sourceOfIncome)) {
            requiredSelect(form, errors, 'status_of_work_business_id', 'Status of work / business is required.');
            requiredPlace(form, errors, 'place_of_work_business', 'Place of work / business');
        }
    }

    if (key === 'infant_health') {
        requiredSelect(form, errors, 'place_of_delivery_id', 'Place of delivery is required.');
        requiredSelect(form, errors, 'birth_attendant_id', 'Birth attendant is required.');
        requiredText(form, errors, 'immunization', 'Immunization is required.');
    }

    if (key === 'health') {
        const disabilityLabel = String(
            lookupById(lookups.disability ?? [], form.disability_id)?.label
                ?? form.disability_name
                ?? form.disability
                ?? '',
        ).trim();

        if (!form.disability_id && !disabilityLabel) {
            errors.disability_id = 'Disability is required.';
        } else {
            delete errors.disability_id;
        }

        if (disabilityLabel.length > 45) {
            errors.disability_id = 'Disability may not be longer than 45 characters.';
        }

        const pwdId = String(form.pwd_id_number ?? '').trim();

        if (pwdId === '') {
            delete errors.pwd_id_number;
        } else if (!/^\d+$/.test(pwdId) || Number(pwdId) < 1) {
            errors.pwd_id_number = 'PWD ID number must be a positive whole number.';
        } else {
            delete errors.pwd_id_number;
        }
    }

    if (key === 'women_health') {
        requiredInteger(form, errors, 'number_pregnancies', 'Number of pregnancies is required.');
        requiredInteger(form, errors, 'living_children', 'Living children is required.');
    }

    if (key === 'sociocivic') {
        const relevance = sociocivicFieldRelevance(resident);

        if (relevance.solo_parent) {
            requiredSelect(form, errors, 'solo_parent_status_id', 'Solo parent status is required.');
        }

        if (relevance.senior_citizen) {
            requiredBoolean(form, errors, 'registered_sen_citizen', 'Registered senior citizen is required.');

            if (form.registered_sen_citizen === true) {
                const ncscError = ncscRrnValidationError(form.ncsc_rrn_id_number, false);

                if (ncscError) {
                    errors.ncsc_rrn_id_number = ncscError;
                } else {
                    delete errors.ncsc_rrn_id_number;
                }

                const osca = String(form.osca_id_number ?? '').trim();

                if (osca.length > 45) {
                    errors.osca_id_number = 'OSCA ID number may not be longer than 45 characters.';
                } else {
                    delete errors.osca_id_number;
                }
            }
        }

        if (relevance.barangay_voter) {
            if (!['Yes', 'No'].includes(String(form.is_registered_barangay_voter ?? '').trim())) {
                errors.is_registered_barangay_voter = 'Registered barangay voter is required.';
            } else {
                delete errors.is_registered_barangay_voter;
            }

            if (form.is_registered_barangay_voter === 'Yes') {
                requiredPlace(form, errors, 'registered_barangay_voter', 'Registered voter barangay');
            }
        }
    }

    if (key === 'migration') {
        requiredPlace(form, errors, 'previous_residence_6mos_brgy', 'Previous residence (6 months) barangay');
        requiredPlace(form, errors, 'previous_residence_6mos_city_municipality', 'Previous residence (6 months) city / municipality');
        requiredPlace(form, errors, 'previous_residence_5yrs_brgy', 'Previous residence (5 years) barangay');
        requiredPlace(form, errors, 'previous_residence_5yrs_city_municipality', 'Previous residence (5 years) city / municipality');

        const classification = classifyMigrationForm(form, location);

        if (!classification.nonMigrant) {
            if (!form.date_of_transfer_in_brgy) {
                errors.date_of_transfer_in_brgy = 'Date of transfer is required for migrants and transients.';
            } else if (String(form.date_of_transfer_in_brgy) > todayDate()) {
                errors.date_of_transfer_in_brgy = 'Date of transfer cannot be in the future.';
            } else {
                delete errors.date_of_transfer_in_brgy;
            }

            requiredSelect(form, errors, 'reason_for_leaving_id', 'Reason for leaving is required for migrants and transients.');
            requiredSelect(form, errors, 'reason_for_transfer_id', 'Reason for transfer is required for migrants and transients.');
            requiredBoolean(
                form,
                errors,
                'will_return_to_previous_residence',
                'Indicate whether the resident plans to return to the previous residence.',
            );
        }
    }

    if (key === 'ctc') {
        requiredBoolean(form, errors, 'has_valid_ctc', 'Has valid community tax certificate is required.');

        if (form.has_valid_ctc === true) {
            requiredBoolean(form, errors, 'ctc_issued_here', 'Community tax certificate issued here is required.');
        }
    }

    if (key === 'skills') {
        requiredText(form, errors, 'skills_development_training', 'Skills development training is required.');
        requiredSelect(form, errors, 'skill_type_id', 'Skill type is required.');
    }

    return Object.keys(errors).length === 0;
}

function toPayloadValue(value) {
    if (value === '') {
        return null;
    }

    return value;
}

export function prepareSectionPayload(key, form, { lookups = {}, resident = {}, location = {} } = {}) {
    const payload = {};

    Object.entries(form).forEach(([field, value]) => {
        payload[field] = toPayloadValue(value);
    });

    if (key === 'education') {
        const relevance = educationFieldRelevance(resident);

        if (!relevance.highest_level) {
            payload.highest_lvl_of_educ_id = notApplicableHighestEducId(lookups.highestLvlOfEduc);
        }

        if (!relevance.enrollment || !isEnrollmentStatusEnrolled(
            lookupById(lookups.currentEnrollmentStatus, form.current_enrollement_status_id),
        )) {
            payload.place_of_school_brgy = null;
            payload.place_of_school_city_municipality = null;
            payload.school_lvl_id = notApplicableSchoolLvlId(lookups.schoolLvl);
        }

        if (!relevance.enrollment) {
            payload.current_enrollement_status_id = ENROLLMENT_NOT_ENROLLED_ID;
        }
    }

    if (key === 'economic') {
        if (sourceOfIncomeSkipsWorkDetails(form.source_of_income_id, lookups.sourceOfIncome)) {
            payload.status_of_work_business_id = ECONOMIC_STATUS_NOT_APPLICABLE;
            payload.place_of_work_business = null;
        }
    }

    if (key === 'health') {
        payload.health_insurance_id = payload.health_insurance_id || 0;
        payload.facility_visited_past_12mos_id = payload.facility_visited_past_12mos_id || 0;
        payload.facility_visit_reason_id = payload.facility_visit_reason_id || 0;
        payload.pwd_id_number = form.pwd_id_number ? Number(form.pwd_id_number) : 0;

        if (form.disability_id) {
            payload.disability_id = Number(form.disability_id);
            delete payload.disability;
        } else if (String(form.disability_name ?? form.disability ?? '').trim()) {
            payload.disability = String(form.disability_name ?? form.disability).trim();
            delete payload.disability_id;
        }

        delete payload.disability_name;
    }

    if (key === 'women_health') {
        if (isFamilyPlanningNone(lookupById(lookups.familyPlanningMethod, form.family_planning_method_id))) {
            payload.family_planning_method_id = 0;
            payload.source_of_fp_method_id = 0;
            payload.have_intention_to_use_fp = false;
        } else {
            payload.family_planning_method_id = Number(form.family_planning_method_id);
            payload.source_of_fp_method_id = form.source_of_fp_method_id ? Number(form.source_of_fp_method_id) : 0;
            payload.have_intention_to_use_fp = form.have_intention_to_use_fp === true;
        }
    }

    if (key === 'sociocivic') {
        const relevance = sociocivicFieldRelevance(resident);

        if (!relevance.solo_parent) {
            payload.solo_parent_status_id = nonSoloParentId(lookups);
        }

        if (!relevance.senior_citizen) {
            payload.registered_sen_citizen = false;
            payload.ncsc_rrn_id_number = null;
            payload.osca_id_number = null;
        } else if (form.registered_sen_citizen !== true) {
            payload.registered_sen_citizen = false;
            payload.ncsc_rrn_id_number = null;
            payload.osca_id_number = null;
        }

        if (!relevance.barangay_voter || form.is_registered_barangay_voter !== 'Yes') {
            payload.registered_barangay_voter = null;
        }

        delete payload.is_registered_barangay_voter;
    }

    if (key === 'ctc') {
        payload.has_valid_ctc = form.has_valid_ctc === true;
        payload.ctc_issued_here = form.has_valid_ctc === true
            ? form.ctc_issued_here === true
            : false;
    }

    if (key === 'migration' && classifyMigrationForm(form, location).nonMigrant) {
        payload.date_of_transfer_in_brgy = null;
        payload.reason_for_leaving_id = null;
        payload.will_return_to_previous_residence = null;
        payload.reason_for_transfer_id = null;
        payload.duration_of_stay = null;
    }

    if (key === 'skills' && payload.skills_development_training) {
        payload.skills_development_training = titleCaseWords(payload.skills_development_training);
    }

    if (key === 'economic' && payload.monthly_income !== null) {
        payload.monthly_income = Number(payload.monthly_income);
    }

    if (key === 'women_health') {
        if (payload.number_pregnancies !== null) {
            payload.number_pregnancies = Number(payload.number_pregnancies);
        }

        if (payload.living_children !== null) {
            payload.living_children = Number(payload.living_children);
        }
    }

    return payload;
}

export async function persistSectionRecord(key, residentId, payload, existingRecord = null) {
    const recordId = sectionRecordId(key, existingRecord);

    if (key === 'education') {
        return recordId
            ? residentService.updateEducation(recordId, payload)
            : residentService.createEducation(residentId, payload);
    }

    if (key === 'economic') {
        return recordId
            ? residentService.updateEconomic(recordId, payload)
            : residentService.createEconomic(residentId, payload);
    }

    if (key === 'infant_health') {
        return recordId
            ? residentService.updateInfantHealth(recordId, payload)
            : residentService.createInfantHealth(residentId, payload);
    }

    if (key === 'health') {
        return recordId
            ? residentService.updateHealth(recordId, payload)
            : residentService.createHealth(residentId, payload);
    }

    if (key === 'women_health') {
        return recordId
            ? residentService.updateWomenHealth(recordId, payload)
            : residentService.createWomenHealth(residentId, payload);
    }

    if (key === 'sociocivic') {
        return recordId
            ? residentService.updateSociocivic(recordId, payload)
            : residentService.createSociocivic(residentId, payload);
    }

    if (key === 'migration') {
        return recordId
            ? residentService.updateMigration(recordId, payload)
            : residentService.createMigration(residentId, payload);
    }

    if (key === 'ctc') {
        return recordId
            ? residentService.updateCtc(recordId, payload)
            : residentService.createCtc(residentId, payload);
    }

    if (key === 'skills') {
        return recordId
            ? residentService.updateSkills(recordId, payload)
            : residentService.createSkills(residentId, payload);
    }

    throw new Error(`Unknown profiling section: ${key}`);
}
