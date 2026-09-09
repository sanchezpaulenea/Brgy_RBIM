import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';

export function emptyProfilingLookups() {
    return {
        highestLvlOfEduc: [],
        currentEnrollmentStatus: [],
        schoolLvl: [],
        sourceOfIncome: [],
        statusOfWorkBusiness: [],
        placeOfDelivery: [],
        birthAttendant: [],
        healthInsurance: [],
        facilityVisited: [],
        facilityVisitReason: [],
        familyPlanningMethod: [],
        sourceOfFpMethod: [],
        soloParentStatus: [],
        reasonForLeaving: [],
        reasonForTransfer: [],
        skillType: [],
        location: {
            barangay: '',
            city: '',
        },
    };
}

export async function fetchProfilingLookups() {
    const [
        highestLvlOfEduc,
        currentEnrollmentStatus,
        schoolLvl,
        sourceOfIncome,
        statusOfWorkBusiness,
        placeOfDelivery,
        birthAttendant,
        healthInsurance,
        facilityVisited,
        facilityVisitReason,
        familyPlanningMethod,
        sourceOfFpMethod,
        soloParentStatus,
        reasonForLeaving,
        reasonForTransfer,
        skillType,
        location,
    ] = await Promise.all([
        lookupService.fetchLookup('highest-lvl-of-educ'),
        lookupService.fetchLookup('current-enrollment-status'),
        lookupService.fetchLookup('school-lvl'),
        lookupService.fetchLookup('source-of-income'),
        lookupService.fetchLookup('status-of-work-business'),
        lookupService.fetchLookup('place-of-delivery'),
        lookupService.fetchLookup('birth-attendant'),
        lookupService.fetchLookup('health-insurance'),
        lookupService.fetchLookup('facility-visited-past-12mos'),
        lookupService.fetchLookup('facility-visit-reason'),
        lookupService.fetchLookup('family-planning-method'),
        lookupService.fetchLookup('source-of-fp-method'),
        lookupService.fetchLookup('solo-parent-status'),
        lookupService.fetchLookup('reason-for-leaving'),
        lookupService.fetchLookup('reason-for-transfer'),
        lookupService.fetchLookup('skill-type'),
        householdService.fetchLocationProfile(),
    ]);

    return {
        highestLvlOfEduc,
        currentEnrollmentStatus,
        schoolLvl,
        sourceOfIncome,
        statusOfWorkBusiness,
        placeOfDelivery,
        birthAttendant,
        healthInsurance,
        facilityVisited,
        facilityVisitReason,
        familyPlanningMethod,
        sourceOfFpMethod,
        soloParentStatus,
        reasonForLeaving,
        reasonForTransfer,
        skillType,
        location: location ?? { barangay: '', city: '' },
    };
}
