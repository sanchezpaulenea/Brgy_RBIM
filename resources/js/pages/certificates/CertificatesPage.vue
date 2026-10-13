<template>
    <AppLayout title="Certificates">
        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <ol class="flex flex-wrap gap-2">
                    <li v-for="(label, index) in stepLabels" :key="label">
                        <button
                            type="button"
                            class="rounded-full px-3 py-1.5 text-xs font-medium"
                            :class="step === index + 1 ? 'bg-brand text-white' : 'bg-white text-slate-600'"
                            :disabled="index + 1 > step"
                            @click="goToStep(index + 1)"
                        >
                            {{ index + 1 }}. {{ label }}
                        </button>
                    </li>
                </ol>
                <button type="button" class="rbim-btn py-2" :disabled="busy" @click="refresh">
                    Refresh
                </button>
            </div>

            <div v-if="pageError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ pageError }}
            </div>

            <section v-if="step === 1" class="rbim-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">
                    Certificate Type<span class="rbim-required" aria-hidden="true">*</span>
                </h2>
                <div v-if="loadingTypes" class="text-sm text-slate-500">Loading certificate types...</div>
                <div v-else class="grid gap-3 sm:grid-cols-2">
                    <button
                        v-for="type in types"
                        :key="type.type_of_certificate_id"
                        type="button"
                        class="rounded-lg border px-4 py-3 text-left text-sm"
                        :class="selectedType?.type_of_certificate_id === type.type_of_certificate_id ? 'border-brand bg-brand-muted' : 'border-slate-200 bg-white'"
                        @click="selectType(type)"
                    >
                        {{ type.type_of_certificate }}
                    </button>
                </div>
                <p v-if="typeError" class="rbim-error">{{ typeError }}</p>
            </section>

            <section v-else-if="step === 2" class="rbim-card space-y-4 p-5">
                <div>
                    <label for="certificate-resident-search" class="rbim-label">
                        Requester<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input
                            id="certificate-resident-search"
                            v-model="search"
                            type="search"
                            class="rbim-input py-2"
                            placeholder="Search by first, middle, or last name"
                            @keyup.enter="runSearch"
                        >
                        <button type="button" class="rbim-btn py-2" :disabled="searching" @click="runSearch">
                            Search
                        </button>
                    </div>
                    <p v-if="searchError" class="rbim-error">{{ searchError }}</p>
                </div>
                <p v-if="searching" class="text-sm text-slate-500">Searching residents...</p>
                <ul v-else class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                    <li v-if="searched && !matches.length" class="px-4 py-6 text-center text-sm text-slate-500">
                        No residents match that name.
                    </li>
                    <li
                        v-for="resident in matches"
                        :key="resident.resident_id"
                        class="flex flex-wrap items-center justify-between gap-3 px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ resident.name }}</p>
                            <p v-if="resident.warnings?.length" class="mt-1 text-xs text-amber-700">
                                {{ resident.warnings.join(' ') }}
                            </p>
                            <p v-if="!resident.eligible" class="mt-1 text-xs text-red-700">{{ resident.reason }}</p>
                        </div>
                        <button
                            type="button"
                            class="rbim-btn-outline px-3 py-1.5 text-xs"
                            :disabled="!resident.eligible"
                            @click="selectResident(resident)"
                        >
                            {{ selectedResident?.resident_id === resident.resident_id ? 'Selected' : 'Select' }}
                        </button>
                    </li>
                </ul>
            </section>

            <section v-else-if="step === 3" class="rbim-card space-y-3 p-5">
                <label for="purpose-of-request" class="rbim-label">
                    Purpose of Request<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    id="purpose-of-request"
                    v-model="purpose"
                    type="text"
                    maxlength="45"
                    class="rbim-input py-2"
                    :class="{ 'rbim-input-error': purposeError }"
                    @input="purposeTouched = true"
                >
                <div class="flex items-start justify-between gap-3">
                    <p v-if="purposeTouched && purposeError" class="rbim-error">{{ purposeError }}</p>
                    <p v-else class="rbim-hint">Enter the reason for this certificate, from 3 to 45 characters.</p>
                    <p class="text-xs text-slate-500">{{ purpose.trim().length }}/45</p>
                </div>
            </section>

            <section v-else-if="step === 4" class="space-y-4">
                <div class="rbim-card p-5">
                    <p class="text-sm text-slate-700">{{ nextIssueLine }}</p>
                    <ul v-if="counts.length" class="mt-3 flex flex-wrap gap-2">
                        <li
                            v-for="count in counts"
                            :key="count.type_of_certificate_id"
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700"
                        >
                            {{ count.type_of_certificate }}: {{ count.count }}
                        </li>
                    </ul>
                </div>
                <div v-if="historyLoading" class="rbim-card p-8 text-center text-sm text-slate-500">
                    Loading issued certificates...
                </div>
                <div v-else class="rbim-card overflow-hidden">
                    <div v-if="!historyRows.length" class="px-4 py-8 text-center text-sm text-slate-500">
                        No certificates have been issued to this resident yet.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Certificate Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Purpose of Request</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Date of Issue</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Issuer</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="row in historyRows" :key="row.certificate_id">
                                    <td class="px-4 py-3">{{ row.type_of_certificate }}</td>
                                    <td class="px-4 py-3">{{ row.purpose_of_request }}</td>
                                    <td class="px-4 py-3">{{ formatDateTime(row.date_of_issue) }}</td>
                                    <td class="px-4 py-3">{{ row.issuer_name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="historyMeta.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-sm">
                        <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="historyPage <= 1" @click="changeHistoryPage(historyPage - 1)">
                            Previous
                        </button>
                        <span>Page {{ historyMeta.current_page }} of {{ historyMeta.last_page }}</span>
                        <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="historyPage >= historyMeta.last_page" @click="changeHistoryPage(historyPage + 1)">
                            Next
                        </button>
                    </div>
                </div>
            </section>

            <section v-else class="space-y-4">
                <div v-if="issued" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Certificate issued. The PDF opened in a new tab for printing or download.
                    <button type="button" class="rbim-btn ml-3 py-2" @click="issueAnother">
                        Issue Another Certificate
                    </button>
                </div>

                <div v-if="previewWarnings.length" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p v-for="warning in previewWarnings" :key="warning">{{ warning }}</p>
                </div>
                <div v-if="preview?.missing_logos?.length || preview?.document?.missing_logos?.length" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Logo file missing: {{ (preview.document?.missing_logos || []).join(', ') }}. An empty box is shown in its place.
                </div>

                <div v-if="previewLoading && !preview?.document" class="rbim-card p-8 text-center text-sm text-slate-500">
                    Preparing the certificate preview...
                </div>
                <template v-else-if="preview?.document">
                    <div class="rbim-card space-y-4 p-5">
                        <p v-if="!canEdit" class="text-sm text-slate-600">
                            You do not have permission to edit certificate text. These fields are read-only.
                        </p>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="edit-resident-name" class="rbim-label">Resident Name</label>
                                <input id="edit-resident-name" v-model="edits.resident_name" type="text" maxlength="150" class="rbim-input py-2" :readonly="!canEdit">
                                <p v-if="overrideError(edits.resident_name, 'Resident name')" class="rbim-error">{{ overrideError(edits.resident_name, 'Resident name') }}</p>
                            </div>
                            <div>
                                <label for="edit-address" class="rbim-label">Address</label>
                                <input id="edit-address" v-model="edits.address" type="text" maxlength="150" class="rbim-input py-2" :readonly="!canEdit">
                                <p v-if="overrideError(edits.address, 'Address')" class="rbim-error">{{ overrideError(edits.address, 'Address') }}</p>
                            </div>
                            <div>
                                <label for="edit-requester" class="rbim-label">Requester</label>
                                <input id="edit-requester" v-model="edits.requested_by" type="text" maxlength="150" class="rbim-input py-2" :readonly="!canEdit">
                                <p v-if="overrideError(edits.requested_by, 'Requester')" class="rbim-error">{{ overrideError(edits.requested_by, 'Requester') }}</p>
                            </div>
                            <div>
                                <label for="edit-purpose" class="rbim-label">Purpose of Request</label>
                                <input id="edit-purpose" v-model="purpose" type="text" maxlength="45" class="rbim-input py-2" :readonly="!canEdit">
                                <p v-if="purposeError" class="rbim-error">{{ purposeError }}</p>
                                <p class="text-xs text-slate-500">{{ purpose.trim().length }}/45</p>
                            </div>
                            <div>
                                <label class="rbim-label">Issue Date</label>
                                <input :value="preview.document.fields.issued_date" type="text" class="rbim-input py-2" readonly>
                            </div>
                            <div>
                                <label class="rbim-label">Issuer</label>
                                <input :value="issuerLine" type="text" class="rbim-input py-2" readonly>
                            </div>
                            <div class="md:col-span-2">
                                <label class="rbim-label">Punong Barangay Name</label>
                                <input :value="preview.document.fields.punong_barangay_name" type="text" class="rbim-input py-2" readonly>
                                <p class="rbim-hint">This name is changed in Settings, under System settings, in Barangay Information. Only a Super Admin can edit it.</p>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-2 text-sm text-slate-800">
                                <input
                                    v-model="attachSignature"
                                    type="checkbox"
                                    :disabled="signatureDisabled"
                                >
                                Attach Punong Barangay digital signature
                            </label>
                            <p v-if="signatureReason" class="rbim-hint">{{ signatureReason }}</p>
                        </div>
                    </div>

                    <CertificatePreview
                        :document="preview.document"
                        :attach-signature="attachSignature"
                        :signature-url="signatureUrl"
                    />

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" class="rbim-btn" :disabled="generateDisabled" @click="generate">
                            {{ generating ? 'Generating...' : 'Generate PDF' }}
                        </button>
                        <p v-if="generateReason" class="text-sm text-slate-600">{{ generateReason }}</p>
                        <p v-if="generateError" class="rbim-error">{{ generateError }}</p>
                    </div>
                </template>
            </section>

            <div class="flex gap-2">
                <button type="button" class="rbim-btn-outline" :disabled="step === 1 || generating" @click="goBack">
                    Back
                </button>
                <button v-if="step < 5 && !issued" type="button" class="rbim-btn" :disabled="!canContinue" @click="goNext">
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CertificatePreview from '@/components/CertificatePreview.vue';
import { useAuth } from '@/composables/useAuth';
import {
    certificateErrorMessage,
    fetchCertificateHistory,
    fetchCertificateTypes,
    fetchSignatureBlob,
    issueCertificate,
    previewCertificate,
    searchCertificateResidents,
} from '@/services/certificateService';
import { formatDateTime } from '@/utils/format';

const stepLabels = [
    'Certificate Type',
    'Requester',
    'Purpose of Request',
    'Issued Certificates',
    'Review and Edit',
];

const { hasPermission } = useAuth();
const canEdit = computed(() => hasPermission('certificate.edit'));
const canCreate = computed(() => hasPermission('certificate.create'));

const step = ref(1);
const types = ref([]);
const loadingTypes = ref(false);
const typeError = ref('');
const selectedType = ref(null);
const search = ref('');
const searchError = ref('');
const searching = ref(false);
const searched = ref(false);
const matches = ref([]);
const selectedResident = ref(null);
const purpose = ref('');
const purposeTouched = ref(false);
const historyLoading = ref(false);
const historyPage = ref(1);
const historyPayload = ref(null);
const preview = ref(null);
const previewLoading = ref(false);
const pageError = ref('');
const edits = reactive({
    resident_name: '',
    address: '',
    requested_by: '',
});
const editsReady = ref(false);
const attachSignature = ref(false);
const signatureUrl = ref('');
const generating = ref(false);
const generateError = ref('');
const issued = ref(false);
let previewTimer = null;

const busy = computed(() => loadingTypes.value || searching.value || historyLoading.value || previewLoading.value || generating.value);
const historyRows = computed(() => historyPayload.value?.certificates?.data ?? []);
const historyMeta = computed(() => historyPayload.value?.certificates ?? { current_page: 1, last_page: 1 });
const counts = computed(() => historyPayload.value?.counts_by_type ?? []);
const previewWarnings = computed(() => preview.value?.warnings ?? []);

const purposeError = computed(() => purposeValidationError(purpose.value));

const selectedCount = computed(() => {
    const match = counts.value.find((row) => row.type_of_certificate_id === selectedType.value?.type_of_certificate_id);

    return match?.count ?? 0;
});

const nextIssueLine = computed(() => {
    const next = selectedCount.value + 1;
    const name = selectedType.value?.type_of_certificate ?? 'certificate';

    return `This will be the ${ordinal(next)} ${name} issued to this resident.`;
});

const signatureReason = computed(() => {
    if (!selectedType.value?.allows_signature) {
        return 'Not available for this certificate type';
    }

    if (preview.value?.document && !preview.value.document.signature_available) {
        return 'No Punong Barangay signature uploaded. Ask a Super Admin to upload it in Settings.';
    }

    return '';
});

const signatureDisabled = computed(() => signatureReason.value !== '');

const issuerLine = computed(() => {
    const fields = preview.value?.document?.fields;

    if (!fields) {
        return '';
    }

    return [fields.issuer_name, fields.issuer_position].filter(Boolean).join(', ');
});

const canContinue = computed(() => {
    if (step.value === 1) {
        return selectedType.value !== null;
    }

    if (step.value === 2) {
        return selectedResident.value !== null;
    }

    if (step.value === 3) {
        return purposeError.value === '';
    }

    return true;
});

const generateReason = computed(() => {
    if (!canCreate.value) {
        return 'You do not have permission to generate certificates.';
    }

    if (preview.value?.issue_block) {
        return preview.value.issue_block;
    }

    if (preview.value && preview.value.eligible === false) {
        return preview.value.reason;
    }

    if (purposeError.value || overrideError(edits.resident_name, 'Resident name') || overrideError(edits.address, 'Address') || overrideError(edits.requested_by, 'Requester')) {
        return 'Fix the highlighted fields before generating.';
    }

    return '';
});

const generateDisabled = computed(() => generating.value || issued.value || generateReason.value !== '');

function purposeValidationError(value) {
    const text = String(value ?? '').trim();

    if (!text) {
        return 'Purpose of request is required.';
    }

    if (/[\u0000-\u001F\u007F]/.test(text)) {
        return 'Purpose of request cannot contain control characters.';
    }

    if (text.length < 3) {
        return 'Purpose of request must be at least 3 characters.';
    }

    if (text.length > 45) {
        return 'Purpose of request must not exceed 45 characters.';
    }

    if (!/\p{L}/u.test(text)) {
        return 'Purpose of request must contain at least one letter.';
    }

    return '';
}

function overrideError(value, label) {
    const text = String(value ?? '').replace(/[\u0000-\u001F\u007F]/g, '').trim();

    if (!text) {
        return '';
    }

    if (text.length > 150) {
        return `${label} must not exceed 150 characters.`;
    }

    if (!/\p{L}/u.test(text)) {
        return `${label} must contain at least one letter.`;
    }

    return '';
}

function ordinal(number) {
    const mod100 = number % 100;

    if (mod100 >= 11 && mod100 <= 13) {
        return `${number}th`;
    }

    return `${number}${['th', 'st', 'nd', 'rd'][number % 10] || 'th'}`;
}

function resetAfterType() {
    selectedResident.value = null;
    search.value = '';
    matches.value = [];
    searched.value = false;
    purpose.value = '';
    purposeTouched.value = false;
    historyPayload.value = null;
    historyPage.value = 1;
    clearReview();
}

function clearReview() {
    preview.value = null;
    editsReady.value = false;
    attachSignature.value = false;
    issued.value = false;
    generateError.value = '';
    clearSignature();
}

function selectType(type) {
    if (selectedType.value?.type_of_certificate_id === type.type_of_certificate_id) {
        return;
    }

    selectedType.value = type;
    resetAfterType();
}

function selectResident(resident) {
    if (!resident.eligible) {
        return;
    }

    if (selectedResident.value?.resident_id === resident.resident_id) {
        return;
    }

    selectedResident.value = resident;
    purpose.value = '';
    purposeTouched.value = false;
    historyPayload.value = null;
    historyPage.value = 1;
    clearReview();
}

function goToStep(next) {
    if (next < step.value) {
        step.value = next;
    }
}

function goBack() {
    if (step.value > 1) {
        step.value -= 1;
    }
}

async function goNext() {
    if (!canContinue.value) {
        if (step.value === 3) {
            purposeTouched.value = true;
        }

        return;
    }

    step.value += 1;

    if (step.value === 4) {
        await loadHistory();
    }

    if (step.value === 5) {
        await loadPreview(canEdit.value && editsReady.value);
    }
}

async function loadTypes() {
    loadingTypes.value = true;
    typeError.value = '';

    try {
        types.value = await fetchCertificateTypes();
    } catch (err) {
        typeError.value = await certificateErrorMessage(err, 'Unable to load certificate types.');
    } finally {
        loadingTypes.value = false;
    }
}

async function runSearch() {
    const term = search.value.trim();
    searchError.value = term.length < 2 ? 'Enter at least 2 characters to search.' : '';
    searched.value = false;

    if (searchError.value) {
        matches.value = [];

        return;
    }

    searching.value = true;
    pageError.value = '';

    try {
        matches.value = await searchCertificateResidents(term);
        searched.value = true;
    } catch (err) {
        pageError.value = await certificateErrorMessage(err, 'Unable to search residents.');
    } finally {
        searching.value = false;
    }
}

async function loadHistory() {
    if (!selectedResident.value) {
        return;
    }

    historyLoading.value = true;
    pageError.value = '';

    try {
        historyPayload.value = await fetchCertificateHistory(selectedResident.value.resident_id, historyPage.value);
    } catch (err) {
        pageError.value = await certificateErrorMessage(err, 'Unable to load issued certificates.');
    } finally {
        historyLoading.value = false;
    }
}

async function changeHistoryPage(page) {
    historyPage.value = page;
    await loadHistory();
}

async function loadPreview(useOverrides) {
    if (!selectedType.value || !selectedResident.value) {
        return;
    }

    previewLoading.value = true;
    pageError.value = '';

    const payload = {
        type_of_certificate_id: selectedType.value.type_of_certificate_id,
        resident_id: selectedResident.value.resident_id,
        purpose_of_request: purpose.value.trim(),
    };

    if (useOverrides && canEdit.value) {
        payload.overrides = {
            resident_name: edits.resident_name,
            address: edits.address,
            requested_by: edits.requested_by,
        };
    }

    try {
        const data = await previewCertificate(payload);
        preview.value = data;

        if (!editsReady.value) {
            edits.resident_name = data.document?.fields?.resident_name ?? '';
            edits.address = data.document?.fields?.address ?? '';
            edits.requested_by = data.document?.fields?.requested_by ?? '';
            editsReady.value = true;
        }

        if (!data.document?.allows_signature) {
            attachSignature.value = false;
        }
    } catch (err) {
        pageError.value = await certificateErrorMessage(err, 'Unable to prepare the certificate preview.');
    } finally {
        previewLoading.value = false;
    }
}

function schedulePreview() {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(() => {
        loadPreview(true);
    }, 400);
}

async function ensureSignature() {
    if (signatureUrl.value) {
        return;
    }

    const blob = await fetchSignatureBlob();
    signatureUrl.value = URL.createObjectURL(blob);
}

function clearSignature() {
    if (signatureUrl.value) {
        URL.revokeObjectURL(signatureUrl.value);
        signatureUrl.value = '';
    }
}

async function generate() {
    if (generateDisabled.value) {
        return;
    }

    const popup = window.open('', '_blank');
    generating.value = true;
    generateError.value = '';

    const payload = {
        type_of_certificate_id: selectedType.value.type_of_certificate_id,
        resident_id: selectedResident.value.resident_id,
        purpose_of_request: purpose.value.trim(),
        attach_pb_signature: attachSignature.value,
    };

    if (canEdit.value) {
        payload.overrides = {
            resident_name: edits.resident_name,
            address: edits.address,
            requested_by: edits.requested_by,
        };
    }

    try {
        const result = await issueCertificate(payload);

        if (!(result.blob instanceof Blob) || result.blob.type.includes('json') || result.blob.type.includes('html')) {
            throw new Error('The server did not return a PDF.');
        }

        const url = URL.createObjectURL(result.blob);

        if (popup && !popup.closed) {
            popup.location = url;
        } else {
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.download = result.filename || 'certificate.pdf';
            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        issued.value = true;
    } catch (err) {
        popup?.close();
        generateError.value = await certificateErrorMessage(err, 'Unable to generate the certificate.');
    } finally {
        generating.value = false;
    }
}

function issueAnother() {
    selectedType.value = null;
    resetAfterType();
    step.value = 1;
}

async function refresh() {
    pageError.value = '';

    if (step.value === 1) {
        await loadTypes();
    }

    if (step.value === 2 && search.value.trim().length >= 2) {
        await runSearch();
    }

    if (step.value === 4) {
        await loadHistory();
    }

    if (step.value === 5) {
        await loadPreview(canEdit.value && editsReady.value);
    }
}

watch(attachSignature, async (enabled) => {
    if (!enabled) {
        return;
    }

    try {
        await ensureSignature();
    } catch (err) {
        attachSignature.value = false;
        generateError.value = await certificateErrorMessage(err, 'Unable to load the Punong Barangay signature.');
    }
});

watch(
    () => [purpose.value, edits.resident_name, edits.address, edits.requested_by],
    () => {
        if (step.value !== 5 || !editsReady.value || !canEdit.value) {
            return;
        }

        schedulePreview();
    },
);

onMounted(loadTypes);
onBeforeUnmount(() => {
    clearTimeout(previewTimer);
    clearSignature();
});
</script>
