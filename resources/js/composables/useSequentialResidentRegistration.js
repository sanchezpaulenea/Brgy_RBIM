import { computed, reactive, ref } from 'vue';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as residentService from '@/services/residentService';
import {
    HEAD_RELATIONSHIP_ID,
    applyValidationErrors,
    assignResidentNameError,
    duplicateResidentMatch,
    emptyResidentForm,
    residentPayload,
    toId,
    validateResidentForm,
} from '@/utils/residentForm';

export const SEQUENTIAL_RESIDENT_MAX = 10;

export function parseResidentCount(value, { min = 1, max = SEQUENTIAL_RESIDENT_MAX, noun = 'residents' } = {}) {
    const count = Number.parseInt(String(value), 10);

    if (!Number.isInteger(count) || count < min) {
        return { count: null, error: `Enter how many ${noun} to add, at least ${min}.` };
    }

    if (count > max) {
        return { count: null, error: `You can add up to ${max} ${noun} in this flow.` };
    }

    return { count, error: '' };
}

/**
 * Shared count + sequential POST /residents logic for both the
 * post-household-registration flow and standalone Register Resident.
 */
export function useSequentialResidentRegistration({
    getHouseholdId,
    getExistingResidents,
    relationships,
    noun = 'residents',
    onMemberAdded,
    onFinished,
    ensureLookups,
} = {}) {
    const step = ref('count');
    const saving = ref(false);
    const error = ref('');
    const successMessage = ref('');
    const memberCountInput = ref('1');
    const countError = ref('');
    const totalMembers = ref(1);
    const currentMember = ref(1);
    const member = ref(emptyResidentForm());
    const memberErrors = reactive({});
    const createdResident = ref(null);
    const confirm = reactive({
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Confirm',
        variant: 'primary',
        onConfirm: null,
        onCancel: null,
    });

    const memberRelationships = computed(() => (
        (relationships?.value ?? []).filter((option) => Number(option.id) !== HEAD_RELATIONSHIP_ID)
    ));

    const isLastMember = computed(() => currentMember.value >= totalMembers.value);

    const saveButtonLabel = computed(() => (
        saving.value ? 'Saving...' : 'Continue'
    ));

    function resetMemberForm() {
        member.value = emptyResidentForm();
        createdResident.value = null;
        Object.keys(memberErrors).forEach((key) => {
            delete memberErrors[key];
        });
    }

    function startCount() {
        error.value = '';
        successMessage.value = '';
        memberCountInput.value = '1';
        countError.value = '';
        currentMember.value = 1;
        totalMembers.value = 1;
        resetMemberForm();
        step.value = 'count';
    }

    function confirmMemberCount() {
        const parsed = parseResidentCount(memberCountInput.value, { noun });

        countError.value = parsed.error;

        if (parsed.count === null) {
            return false;
        }

        totalMembers.value = parsed.count;
        currentMember.value = 1;
        resetMemberForm();
        error.value = '';
        successMessage.value = '';
        step.value = 'members';

        return true;
    }

    function handleConfirmCancel() {
        confirm.open = false;
        confirm.onCancel?.();
    }

    function askConfirm({ title, message, confirmLabel = 'Continue', variant = 'primary' }) {
        return new Promise((resolve) => {
            confirm.open = true;
            confirm.title = title;
            confirm.message = message;
            confirm.confirmLabel = confirmLabel;
            confirm.variant = variant;
            confirm.onConfirm = () => {
                confirm.open = false;
                resolve(true);
            };
            confirm.onCancel = () => resolve(false);
        });
    }

    function validateMemberName(field, label, required = false) {
        assignResidentNameError(member.value, memberErrors, field, label, required);
    }

    async function handleSaveMember() {
        error.value = '';
        successMessage.value = '';
        Object.keys(memberErrors).forEach((key) => {
            delete memberErrors[key];
        });

        const householdId = toId(getHouseholdId?.());

        if (!householdId) {
            error.value = 'Household is required.';
            return;
        }

        if (ensureLookups) {
            const lookupErrors = await ensureLookups(member.value) ?? {};

            Object.entries(lookupErrors).forEach(([field, message]) => {
                memberErrors[field] = message;
            });

            if (Object.keys(lookupErrors).length) {
                return;
            }
        }

        const valid = validateResidentForm(member.value, memberErrors, { requireRelationship: true });

        if (!valid) {
            return;
        }

        const existing = getExistingResidents?.() ?? [];
        const duplicate = duplicateResidentMatch(existing, member.value);

        if (duplicate) {
            const proceed = await askConfirm({
                title: 'Resident with the same name exists',
                message: `“${duplicate.full_name || `${duplicate.last_name}, ${duplicate.first_name}`}” is already recorded. Save this resident anyway?`,
                confirmLabel: 'Save anyway',
                variant: 'danger',
            });

            if (!proceed) {
                return;
            }
        }

        saving.value = true;

        try {
            createdResident.value = await residentService.createResident({
                ...residentPayload(member.value),
                household_id: householdId,
            });

            onMemberAdded?.();
            successMessage.value = '';
            step.value = 'sections';
        } catch (err) {
            const validationErrors = extractValidationErrors(err);

            if (Object.keys(validationErrors).length) {
                applyValidationErrors(memberErrors, validationErrors);
                error.value = '';
            } else {
                error.value = extractErrorMessage(err, 'Unable to register this resident.');
            }
        } finally {
            saving.value = false;
        }
    }

    function completeMemberSections() {
        const householdId = toId(getHouseholdId?.());

        if (isLastMember.value) {
            onFinished?.({ total: totalMembers.value, householdId });
            return;
        }

        const completed = currentMember.value;
        currentMember.value += 1;
        resetMemberForm();
        successMessage.value = `Member ${completed} of ${totalMembers.value} saved. Continue with the next member.`;
        step.value = 'members';
    }

    return {
        step,
        saving,
        error,
        successMessage,
        memberCountInput,
        countError,
        totalMembers,
        currentMember,
        member,
        memberErrors,
        createdResident,
        confirm,
        memberRelationships,
        isLastMember,
        saveButtonLabel,
        startCount,
        confirmMemberCount,
        handleConfirmCancel,
        validateMemberName,
        handleSaveMember,
        completeMemberSections,
        resetMemberForm,
    };
}
