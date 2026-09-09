import { computed, reactive, ref, watch } from 'vue';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import {
    clearFormDraft,
    readFormDraft,
    residentSectionDraftKey,
    writeFormDraft,
} from '@/utils/formDraft';
import { applyValidationErrors } from '@/utils/residentForm';
import {
    emptySectionForm,
    persistSectionRecord,
    prepareSectionPayload,
    profilingStepsFor,
    PROFILING_SECTION_LABELS,
    validateSectionForm,
} from '@/utils/residentSectionForm';

export function useResidentSectionWizard({
    getResident,
    getLookups,
    getLocation,
} = {}) {
    const index = ref(0);
    const form = ref(emptySectionForm('education'));
    const errors = reactive({});
    const saving = ref(false);
    const error = ref('');
    const records = reactive({});
    const skipped = reactive({});
    const localForms = reactive({});
    const startedResidentId = ref(null);

    const resident = computed(() => getResident?.() ?? null);

    const steps = computed(() => profilingStepsFor(resident.value, {
        healthSaved: Boolean(records.health),
    }));

    const currentKey = computed(() => steps.value[index.value] ?? null);

    const currentLabel = computed(() => (
        currentKey.value ? (PROFILING_SECTION_LABELS[currentKey.value] ?? currentKey.value) : ''
    ));

    const isFirstStep = computed(() => index.value <= 0);

    const isLastStep = computed(() => (
        steps.value.length === 0 || index.value >= steps.value.length - 1
    ));

    const progressLabel = computed(() => {
        if (!steps.value.length || !currentKey.value) {
            return '';
        }

        return `Section ${index.value + 1} of ${steps.value.length}`;
    });

    const draftKey = computed(() => {
        const id = resident.value?.resident_id ?? startedResidentId.value;

        return id ? residentSectionDraftKey(id) : null;
    });

    function clearErrors() {
        Object.keys(errors).forEach((field) => {
            delete errors[field];
        });
        error.value = '';
    }

    function persistLocal() {
        if (!draftKey.value) {
            return;
        }

        writeFormDraft(draftKey.value, {
            localForms: { ...localForms },
            records: { ...records },
            skipped: { ...skipped },
            index: index.value,
        });
    }

    function loadFormForStep(key) {
        form.value = {
            ...emptySectionForm(key),
            ...(localForms[key] ?? {}),
        };
        clearErrors();
    }

    function rememberCurrentForm() {
        if (!currentKey.value) {
            return;
        }

        localForms[currentKey.value] = { ...form.value };
    }

    function restoreDraft(residentId) {
        const draft = readFormDraft(residentSectionDraftKey(residentId));

        if (!draft) {
            return;
        }

        Object.keys(localForms).forEach((key) => {
            delete localForms[key];
        });
        Object.assign(localForms, draft.localForms ?? {});

        Object.keys(records).forEach((key) => {
            delete records[key];
        });
        Object.assign(records, draft.records ?? {});

        Object.keys(skipped).forEach((key) => {
            delete skipped[key];
        });
        Object.assign(skipped, draft.skipped ?? {});

        index.value = Number.isInteger(draft.index) ? draft.index : 0;
    }

    function resetState() {
        index.value = 0;
        startedResidentId.value = null;
        Object.keys(localForms).forEach((key) => {
            delete localForms[key];
        });
        Object.keys(records).forEach((key) => {
            delete records[key];
        });
        Object.keys(skipped).forEach((key) => {
            delete skipped[key];
        });
        form.value = emptySectionForm('education');
        clearErrors();
    }

    function start() {
        resetState();
        startedResidentId.value = resident.value?.resident_id ?? null;

        if (startedResidentId.value) {
            restoreDraft(startedResidentId.value);
        }

        if (!steps.value.length) {
            return false;
        }

        if (index.value >= steps.value.length) {
            index.value = 0;
        }

        loadFormForStep(currentKey.value);
        persistLocal();

        return true;
    }

    function finish() {
        if (draftKey.value) {
            clearFormDraft(draftKey.value);
        }

        resetState();
    }

    function goNext() {
        if (isLastStep.value) {
            return 'finished';
        }

        index.value += 1;
        loadFormForStep(currentKey.value);
        persistLocal();

        return 'next';
    }

    function handleSkip() {
        if (!currentKey.value || saving.value) {
            return 'idle';
        }

        skipped[currentKey.value] = true;
        delete localForms[currentKey.value];
        persistLocal();

        return goNext();
    }

    function handleBack() {
        if (isFirstStep.value || saving.value) {
            return false;
        }

        rememberCurrentForm();
        index.value -= 1;
        loadFormForStep(currentKey.value);
        persistLocal();

        return true;
    }

    async function handleContinue() {
        if (!currentKey.value || saving.value) {
            return 'idle';
        }

        clearErrors();
        rememberCurrentForm();
        persistLocal();

        const lookups = getLookups?.() ?? {};
        const location = getLocation?.() ?? lookups.location ?? {};
        const valid = validateSectionForm(currentKey.value, form.value, errors, {
            lookups,
            resident: resident.value,
            location,
        });

        if (!valid) {
            return 'invalid';
        }

        saving.value = true;

        try {
            const payload = prepareSectionPayload(currentKey.value, form.value, {
                lookups,
                resident: resident.value,
                location,
            });
            const saved = await persistSectionRecord(
                currentKey.value,
                resident.value.resident_id,
                payload,
                records[currentKey.value] ?? null,
            );

            records[currentKey.value] = saved;
            skipped[currentKey.value] = false;
            persistLocal();

            return goNext();
        } catch (err) {
            const validationErrors = extractValidationErrors(err);

            if (Object.keys(validationErrors).length) {
                applyValidationErrors(errors, validationErrors);
                error.value = '';
            } else {
                error.value = extractErrorMessage(err, `Unable to save ${currentLabel.value.toLowerCase()}.`);
            }

            return 'error';
        } finally {
            saving.value = false;
        }
    }

    watch(currentKey, (key) => {
        if (key) {
            loadFormForStep(key);
        }
    });

    return {
        index,
        form,
        errors,
        saving,
        error,
        steps,
        currentKey,
        currentLabel,
        isFirstStep,
        isLastStep,
        progressLabel,
        start,
        finish,
        handleSkip,
        handleBack,
        handleContinue,
        resetState,
    };
}
