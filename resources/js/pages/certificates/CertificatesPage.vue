<template>
    <AppLayout title="Certificate Generation">
        <div class="space-y-6">
            <PageTabs :tabs="certificateTabs" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <ol class="flex flex-wrap gap-2" aria-label="Certificate progress">
                    <li v-for="(label, index) in stepLabels" :key="label">
                        <span
                            class="inline-flex rounded-full px-4 py-2 text-sm font-medium"
                            :class="stepGuideClass(index + 1)"
                            :aria-current="step === index + 1 ? 'step' : undefined"
                        >
                            {{ index + 1 }}. {{ label }}
                        </span>
                    </li>
                </ol>
                <button
                    type="button"
                    class="rbim-btn py-2"
                    :disabled="busy"
                    title="Clear this certificate and start again from Certificate Type"
                    @click="refresh"
                >
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
                        <div class="relative min-w-0 flex-1">
                            <input
                                id="certificate-resident-search"
                                v-model="search"
                                type="text"
                                autocomplete="off"
                                class="rbim-input py-2 pr-10"
                                placeholder="Search by first, middle, or last name"
                                @keydown.enter.prevent="runSearch"
                            >
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 hover:text-slate-800"
                                aria-label="Clear requester"
                                @click="clearResidentSearch"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
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
                        :class="resident.eligible ? 'cursor-pointer' : ''"
                        @click="selectResident(resident)"
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
                            @click.stop="selectResident(resident)"
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
                    maxlength="255"
                    class="rbim-input py-2"
                    :class="{ 'rbim-input-error': purposeError }"
                    @input="purposeTouched = true"
                >
                <div class="flex items-start justify-between gap-3">
                    <p v-if="purposeTouched && purposeError" class="rbim-error">{{ purposeError }}</p>
                    <p v-else class="rbim-hint">Enter the reason for this certificate, from 3 to 255 characters.</p>
                    <p class="text-xs text-slate-500">{{ purpose.trim().length }}/255</p>
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
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="row in historyRows" :key="row.certificate_id">
                                    <td class="px-4 py-3">{{ row.type_of_certificate }}</td>
                                    <td class="px-4 py-3">{{ row.purpose_of_request }}</td>
                                    <td class="px-4 py-3">{{ formatDateTime(row.date_of_issue) }}</td>
                                    <td class="px-4 py-3">{{ row.issuer_name }}</td>
                                    <td class="px-4 py-3">
                                        <button
                                            type="button"
                                            class="rbim-btn-outline px-3 py-1.5 text-xs"
                                            :disabled="!canEdit"
                                            @click="editIssued(row)"
                                        >
                                            Edit
                                        </button>
                                        <p v-if="!canEdit" class="mt-1 text-xs text-slate-500">You do not have permission to edit certificates.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="historyMeta.last_page > 1" class="grid grid-cols-3 items-center border-t border-slate-100 px-4 py-3 text-sm">
                        <div>
                            <button v-if="historyPage > 1" type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" @click="changeHistoryPage(historyPage - 1)">
                                Previous
                            </button>
                        </div>
                        <span class="text-center">Page {{ historyMeta.current_page }} of {{ historyMeta.last_page }}</span>
                        <div class="flex justify-end">
                            <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="historyPage >= historyMeta.last_page" @click="changeHistoryPage(historyPage + 1)">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else class="space-y-4">
                <div v-if="issued" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ editingCertificateId ? 'Certificate updated.' : 'Certificate issued.' }} The PDF opened in a new tab for printing or download.
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
                                <input id="edit-purpose" v-model="purpose" type="text" maxlength="255" class="rbim-input py-2" :readonly="!canEdit">
                                <p v-if="purposeError" class="rbim-error">{{ purposeError }}</p>
                                <p class="text-xs text-slate-500">{{ purpose.trim().length }}/255</p>
                            </div>
                            <div>
                                <label class="rbim-label">Issue Date</label>
                                <input :value="preview.document.fields.issued_date" type="text" class="rbim-input py-2" readonly>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-4">
                            <label class="flex items-center gap-2 text-sm text-slate-800">
                                <input v-model="representativeRequested" type="checkbox">
                                Request made by a representative
                            </label>
                            <p v-if="selectedType?.kind === 'death'" class="rbim-hint mt-2">
                                A death certificate is requested by a representative.
                            </p>
                            <div v-if="representativeRequested" class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="representative-name" class="rbim-label">
                                        Representative Name<span class="rbim-required" aria-hidden="true">*</span>
                                    </label>
                                    <input id="representative-name" v-model="representativeName" type="text" maxlength="100" class="rbim-input py-2">
                                    <p v-if="personTextError(representativeName, 'Representative name', 100)" class="rbim-error">
                                        {{ personTextError(representativeName, 'Representative name', 100) }}
                                    </p>
                                </div>
                                <div>
                                    <label for="representative-relationship" class="rbim-label">
                                        Relationship to the Certificate Owner<span class="rbim-required" aria-hidden="true">*</span>
                                    </label>
                                    <input id="representative-relationship" v-model="representativeRelationship" type="text" maxlength="45" class="rbim-input py-2">
                                    <p v-if="personTextError(representativeRelationship, 'Relationship', 45)" class="rbim-error">
                                        {{ personTextError(representativeRelationship, 'Relationship', 45) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedType?.kind === 'death'" class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="date-of-death" class="rbim-label">
                                    Date of Death<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input id="date-of-death" v-model="dateOfDeath" type="date" class="rbim-input py-2">
                                <p v-if="dateOfDeathError" class="rbim-error">{{ dateOfDeathError }}</p>
                                <p class="rbim-hint">Kept on the certificate record. The barangay form prints the ethnic practice.</p>
                            </div>
                            <div>
                                <label for="ethnic-group" class="rbim-label">
                                    Ethnic Group<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input id="ethnic-group" v-model="ethnicGroup" type="text" maxlength="60" class="rbim-input py-2" placeholder="Kankanaey">
                                <p v-if="personTextError(ethnicGroup, 'Ethnic group', 60)" class="rbim-error">{{ personTextError(ethnicGroup, 'Ethnic group', 60) }}</p>
                            </div>
                        </div>

                        <div v-if="selectedType?.kind === 'low_income_amount'">
                            <label for="stated-income" class="rbim-label">
                                Annual Income Threshold<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <input id="stated-income" v-model="statedIncomeAmount" type="text" inputmode="decimal" class="rbim-input py-2 md:max-w-xs" placeholder="0.00">
                            <p class="rbim-hint">Printed in words and in figures as the regional annual threshold.</p>
                            <p v-if="incomeError" class="rbim-error">{{ incomeError }}</p>
                        </div>

                        <div v-if="selectedType?.kind === 'low_income'" class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="resident-since" class="rbim-label">
                                    Resident Since<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input id="resident-since" v-model="residentSinceYear" type="text" inputmode="numeric" maxlength="4" class="rbim-input py-2" placeholder="1998">
                                <p v-if="sinceYearError" class="rbim-error">{{ sinceYearError }}</p>
                            </div>
                            <div>
                                <label for="civil-status" class="rbim-label">
                                    Civil Status<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input id="civil-status" v-model="statedCivilStatus" type="text" maxlength="45" class="rbim-input py-2">
                                <p v-if="personTextError(statedCivilStatus, 'Civil status', 45)" class="rbim-error">{{ personTextError(statedCivilStatus, 'Civil status', 45) }}</p>
                            </div>
                        </div>

                        <div v-if="selectedType?.kind === 'good_moral'">
                            <label for="good-moral-civil-status" class="rbim-label">
                                Civil Status<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <input id="good-moral-civil-status" v-model="statedCivilStatus" type="text" maxlength="45" class="rbim-input py-2 md:max-w-xs">
                            <p v-if="personTextError(statedCivilStatus, 'Civil status', 45)" class="rbim-error">{{ personTextError(statedCivilStatus, 'Civil status', 45) }}</p>
                        </div>

                        <div v-if="selectedType?.kind === 'solo_parent'" class="space-y-4 rounded-lg border border-slate-200 p-4">
                            <div>
                                <label for="solo-circumstance" class="rbim-label">
                                    Solo Parent Circumstance<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input id="solo-circumstance" v-model="soloParentCircumstance" type="text" maxlength="120" class="rbim-input py-2" placeholder="abandonment, unmarried">
                                <p class="rbim-hint">Printed as (SOLO PARENT-circumstance).</p>
                                <p v-if="personTextError(soloParentCircumstance, 'Solo parent circumstance', 120)" class="rbim-error">
                                    {{ personTextError(soloParentCircumstance, 'Solo parent circumstance', 120) }}
                                </p>
                            </div>
                            <div>
                                <p class="rbim-label">Children under custody<span class="rbim-required" aria-hidden="true">*</span></p>
                                <p class="rbim-hint">Type a name, or choose a child from this household.</p>
                                <div v-for="(child, index) in children" :key="index" class="mt-2 grid gap-3 md:grid-cols-[1fr_180px_auto]">
                                    <HouseholdChildCombobox
                                        :model-value="child.child_name"
                                        :options="householdChildren"
                                        :input-id="`child-name-${index}`"
                                        @update:model-value="(name) => onChildNameInput(index, name)"
                                        @select="(option) => onChildSelected(index, option)"
                                    />
                                    <input v-model="child.date_of_birth" type="date" class="rbim-input py-2">
                                    <button type="button" class="rbim-btn-outline" @click="removeChild(index)">Remove</button>
                                </div>
                                <button type="button" class="rbim-btn-outline mt-3" :disabled="children.length >= 10" @click="addChild">
                                    Add Child
                                </button>
                                <p v-if="childrenError" class="rbim-error">{{ childrenError }}</p>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="president-name" class="rbim-label">Solo Parent President<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input id="president-name" :value="presidentName" type="text" maxlength="100" class="rbim-input py-2" readonly>
                                    <p class="rbim-hint">Filled from the active President of Solo Parent in Barangay Personnel.</p>
                                    <p v-if="officialNameError(presidentName, 'Register an active President of Solo Parent in Barangay Personnel.')" class="rbim-error">
                                        {{ officialNameError(presidentName, 'Register an active President of Solo Parent in Barangay Personnel.') }}
                                    </p>
                                </div>
                                <div>
                                    <label for="president-address" class="rbim-label">President Address<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input id="president-address" v-model="presidentAddress" type="text" maxlength="255" class="rbim-input py-2">
                                    <p v-if="addressError(presidentAddress, 'Solo Parent President address')" class="rbim-error">{{ addressError(presidentAddress, 'Solo Parent President address') }}</p>
                                </div>
                                <div>
                                    <label for="neighbor-name" class="rbim-label">Neighbor<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input id="neighbor-name" v-model="neighborName" type="text" maxlength="100" class="rbim-input py-2">
                                    <p v-if="personTextError(neighborName, 'Neighbor name', 100)" class="rbim-error">{{ personTextError(neighborName, 'Neighbor name', 100) }}</p>
                                </div>
                                <div>
                                    <label for="neighbor-address" class="rbim-label">Neighbor Address<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input id="neighbor-address" v-model="neighborAddress" type="text" maxlength="255" class="rbim-input py-2">
                                    <p v-if="addressError(neighborAddress, 'Neighbor address')" class="rbim-error">{{ addressError(neighborAddress, 'Neighbor address') }}</p>
                                </div>
                                <div>
                                    <label for="kagawad-name" class="rbim-label">Kagawad on Social Services<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input id="kagawad-name" :value="kagawadName" type="text" maxlength="100" class="rbim-input py-2" readonly>
                                    <p class="rbim-hint">Filled from the active Kagawad on Social Services in Barangay Personnel.</p>
                                    <p v-if="officialNameError(kagawadName, 'Register an active Kagawad on Social Services in Barangay Personnel.')" class="rbim-error">
                                        {{ officialNameError(kagawadName, 'Register an active Kagawad on Social Services in Barangay Personnel.') }}
                                    </p>
                                </div>
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
                            <p v-if="signatureReason || signatureWarning" class="rbim-hint">{{ signatureWarning || signatureReason }}</p>
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

            <div class="flex items-center justify-between gap-3">
                <button type="button" class="rbim-btn-outline" :disabled="step === 1 || generating" @click="goBack">
                    Back
                </button>
                <button v-if="step < 5 && !issued" type="button" class="rbim-btn" :disabled="!canContinue" @click="goNext">
                    Next
                </button>
            </div>
        </div>

        <ConfirmDialog
            :open="confirmRefreshOpen"
            title="Start over?"
            message="This clears the certificate type, requester, purpose, and any edits, then returns to step 1."
            confirm-label="Start Over"
            @confirm="confirmRefresh"
            @cancel="confirmRefreshOpen = false"
        />
    </AppLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CertificatePreview from '@/components/CertificatePreview.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import HouseholdChildCombobox from '@/components/HouseholdChildCombobox.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import {
    certificateErrorMessage,
    fetchCertificateHistory,
    fetchCertificateTypes,
    fetchSignatureBlob,
    issueCertificate,
    previewCertificate,
    searchCertificateResidents,
    updateCertificate,
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
const { certificateTabs } = useSectionTabs();
const confirmRefreshOpen = ref(false);
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
const editingCertificateId = ref(null);
const representativeRequested = ref(false);
const representativeName = ref('');
const representativeRelationship = ref('');
const dateOfDeath = ref('');
const placeOfDeath = ref('');
const causeOfDeath = ref('');
const ethnicGroup = ref('');
const statedIncomeAmount = ref('');
const residentSinceYear = ref('');
const statedCivilStatus = ref('');
const soloParentCircumstance = ref('');
const children = ref([{ child_name: '', date_of_birth: '' }]);
const householdChildren = ref([]);
const presidentName = ref('');
const presidentAddress = ref('');
const neighborName = ref('');
const neighborAddress = ref('');
const kagawadName = ref('');
const attachSignature = ref(false);
const signatureUrl = ref('');
const generating = ref(false);
const generateError = ref('');
const signatureWarning = ref('');
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
        return 'Not available for this certificate type.';
    }

    if (attachSignature.value && preview.value?.document && !preview.value.document.signature_available) {
        return 'No Punong Barangay signature file is on record. The certificate will show a blank signature line.';
    }

    return '';
});

const signatureDisabled = computed(() => selectedType.value?.allows_signature === false);

const dateOfDeathError = computed(() => {
    if (selectedType.value?.kind !== 'death') {
        return '';
    }

    if (!dateOfDeath.value) {
        return 'Date of death is required.';
    }

    const chosen = new Date(`${dateOfDeath.value}T00:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (Number.isNaN(chosen.getTime()) || chosen > today) {
        return 'Date of death cannot be in the future.';
    }

    return '';
});

const incomeError = computed(() => {
    if (selectedType.value?.kind !== 'low_income_amount') {
        return '';
    }

    const text = String(statedIncomeAmount.value ?? '').trim();

    if (!/^\d+(\.\d{1,2})?$/.test(text) || Number(text) <= 0) {
        return 'The annual income threshold must be a number greater than zero, with at most 2 decimal places.';
    }

    return '';
});

const sinceYearError = computed(() => {
    if (selectedType.value?.kind !== 'low_income') {
        return '';
    }

    if (!/^\d{4}$/.test(String(residentSinceYear.value).trim())) {
        return 'Resident since must be a 4-digit year.';
    }

    const year = Number(residentSinceYear.value);
    const current = new Date().getFullYear();

    if (year < 1900 || year > current) {
        return 'Resident since year must be from 1900 through the current year.';
    }

    return '';
});

const needsCivilStatus = computed(() => selectedType.value?.kind === 'good_moral'
    || selectedType.value?.kind === 'low_income');

const civilStatusError = computed(() => {
    if (!needsCivilStatus.value) {
        return '';
    }

    return personTextError(statedCivilStatus.value, 'Civil status', 45);
});

const childrenError = computed(() => {
    if (selectedType.value?.kind !== 'solo_parent') {
        return '';
    }

    const rows = children.value.filter((child) => String(child.child_name).trim() || child.date_of_birth);

    if (rows.length === 0) {
        return 'Add at least one child.';
    }

    for (const child of rows) {
        const nameError = personTextError(child.child_name, 'Child name', 100);

        if (nameError) {
            return nameError;
        }

        if (!child.date_of_birth) {
            return 'Child date of birth is required.';
        }
    }

    return '';
});

const soloParentError = computed(() => {
    if (selectedType.value?.kind !== 'solo_parent') {
        return '';
    }

    return personTextError(soloParentCircumstance.value, 'Solo parent circumstance', 120)
        || childrenError.value
        || officialNameError(presidentName.value, 'Register an active President of Solo Parent in Barangay Personnel.')
        || addressError(presidentAddress.value, 'Solo Parent President address')
        || personTextError(neighborName.value, 'Neighbor name', 100)
        || addressError(neighborAddress.value, 'Neighbor address')
        || officialNameError(kagawadName.value, 'Register an active Kagawad on Social Services in Barangay Personnel.');
});

const deathDetailError = computed(() => {
    if (selectedType.value?.kind !== 'death') {
        return '';
    }

    return personTextError(ethnicGroup.value, 'Ethnic group', 60);
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

    if (editingCertificateId.value && !canEdit.value) {
        return 'You do not have permission to edit certificates.';
    }

    if (
        purposeError.value
        || overrideError(edits.resident_name, 'Resident name')
        || overrideError(edits.address, 'Address')
        || overrideError(edits.requested_by, 'Requester')
        || representativeError()
        || dateOfDeathError.value
        || deathDetailError.value
        || incomeError.value
        || sinceYearError.value
        || civilStatusError.value
        || soloParentError.value
    ) {
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

    if (text.length > 255) {
        return 'Purpose of request must not exceed 255 characters.';
    }

    if (!/\p{L}/u.test(text)) {
        return 'Purpose of request must contain at least one letter.';
    }

    return '';
}

function personTextError(value, label, max) {
    const text = String(value ?? '').trim();

    if (!text) {
        return `${label} is required.`;
    }

    if (text.length > max) {
        return `${label} must not exceed ${max} characters.`;
    }

    if (!/\p{L}/u.test(text) || /\p{N}/u.test(text) || !/^[\p{L}\s.'’\-,]+$/u.test(text)) {
        return `${label} may contain only letters, spaces, periods, hyphens, apostrophes, and commas.`;
    }

    return '';
}

function addressError(value, label) {
    const text = String(value ?? '').trim();

    if (!text) {
        return `${label} is required.`;
    }

    if (text.length > 255) {
        return `${label} must not exceed 255 characters.`;
    }

    if (!/\p{L}/u.test(text)) {
        return `${label} must contain at least one letter.`;
    }

    return '';
}

function officialNameError(value, missingMessage) {
    const text = String(value ?? '').trim();

    if (!text) {
        return missingMessage;
    }

    return personTextError(text, 'Name', 100);
}

function onChildNameInput(index, name) {
    const child = children.value[index];

    if (!child) {
        return;
    }

    child.child_name = name;

    if (child.resident_id && householdChildren.value.every((option) => option.name !== name)) {
        child.resident_id = null;
    }
}

function onChildSelected(index, option) {
    const child = children.value[index];

    if (!child || !option) {
        return;
    }

    child.child_name = option.name ?? '';
    child.date_of_birth = option.date_of_birth ?? '';
    child.resident_id = option.resident_id ?? null;
}

function addChild() {
    if (children.value.length >= 10) {
        return;
    }

    children.value.push({ child_name: '', date_of_birth: '', resident_id: null });
}

function removeChild(index) {
    if (children.value.length === 1) {
        children.value[0] = { child_name: '', date_of_birth: '', resident_id: null };

        return;
    }

    children.value.splice(index, 1);
}

function representativeError() {
    if (!representativeRequested.value) {
        return '';
    }

    return personTextError(representativeName.value, 'Representative name', 100)
        || personTextError(representativeRelationship.value, 'Relationship', 45);
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

function clearIssuanceFields() {
    representativeRequested.value = selectedType.value?.kind === 'death';
    representativeName.value = '';
    representativeRelationship.value = '';
    dateOfDeath.value = '';
    placeOfDeath.value = '';
    causeOfDeath.value = '';
    ethnicGroup.value = '';
    statedIncomeAmount.value = '';
    residentSinceYear.value = '';
    statedCivilStatus.value = '';
    soloParentCircumstance.value = '';
    children.value = [{ child_name: '', date_of_birth: '', resident_id: null }];
    householdChildren.value = [];
    presidentName.value = '';
    presidentAddress.value = '';
    neighborName.value = '';
    neighborAddress.value = '';
    kagawadName.value = '';
    editingCertificateId.value = null;
}

function clearReview() {
    preview.value = null;
    editsReady.value = false;
    attachSignature.value = false;
    issued.value = false;
    generateError.value = '';
    clearIssuanceFields();
    clearSignature();
}

function selectType(type) {
    if (selectedType.value?.type_of_certificate_id === type.type_of_certificate_id) {
        return;
    }

    selectedType.value = type;
    resetAfterType();
}

function clearResidentSearch() {
    search.value = '';
    searchError.value = '';
    matches.value = [];
    searched.value = false;
    selectedResident.value = null;
    purpose.value = '';
    purposeTouched.value = false;
    historyPayload.value = null;
    historyPage.value = 1;
    clearReview();
}

function selectResident(resident) {
    if (!resident.eligible) {
        return;
    }

    search.value = resident.name;

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

function stepGuideClass(number) {
    if (number < step.value) {
        return 'bg-brand text-white';
    }

    if (number === step.value) {
        return 'bg-brand-light text-white';
    }

    return 'bg-white text-slate-500 ring-1 ring-slate-300';
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
        matches.value = await searchCertificateResidents(term, selectedType.value?.type_of_certificate_id);
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

    const payload = certificatePayload();

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

            if (!editingCertificateId.value) {
                if (!ethnicGroup.value && data.document?.suggestions?.ethnic_group) {
                    ethnicGroup.value = data.document.suggestions.ethnic_group;
                }

                if (!statedCivilStatus.value && data.document?.suggestions?.stated_civil_status) {
                    statedCivilStatus.value = data.document.suggestions.stated_civil_status;
                }

                if (!soloParentCircumstance.value && data.document?.suggestions?.solo_parent_circumstance) {
                    soloParentCircumstance.value = data.document.suggestions.solo_parent_circumstance;
                }
            }

            if (selectedType.value?.kind === 'solo_parent') {
                const officials = data.document?.suggestions?.solo_parent_officials ?? {};
                presidentName.value = officials.president_name ?? '';
                kagawadName.value = officials.kagawad_name ?? '';
                householdChildren.value = Array.isArray(data.document?.suggestions?.household_children)
                    ? data.document.suggestions.household_children
                    : [];
            }

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

function schedulePreview(useOverrides = true) {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(() => {
        loadPreview(useOverrides);
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
        ...certificatePayload(),
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
        const result = editingCertificateId.value
            ? await updateCertificate(editingCertificateId.value, payload)
            : await issueCertificate(payload);

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
        signatureWarning.value = result.warning || '';
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

function refresh() {
    if (step.value > 1 || selectedType.value) {
        confirmRefreshOpen.value = true;

        return;
    }

    confirmRefresh();
}

async function confirmRefresh() {
    confirmRefreshOpen.value = false;
    clearTimeout(previewTimer);
    pageError.value = '';
    searchError.value = '';
    selectedType.value = null;
    resetAfterType();
    step.value = 1;
    await loadTypes();
}

async function editIssued(row) {
    if (!canEdit.value) {
        return;
    }

    const type = types.value.find((item) => item.type_of_certificate_id === row.type_of_certificate_id);

    if (type) {
        selectedType.value = type;
    }

    editingCertificateId.value = row.certificate_id;
    purpose.value = row.purpose_of_request ?? '';
    representativeRequested.value = type?.kind === 'death' || Boolean(row.representative_name);
    representativeName.value = row.representative_name ?? '';
    representativeRelationship.value = row.representative_relationship ?? '';
    dateOfDeath.value = row.date_of_death ?? '';
    placeOfDeath.value = row.place_of_death ?? '';
    causeOfDeath.value = row.cause_of_death ?? '';
    ethnicGroup.value = row.ethnic_group ?? '';
    statedIncomeAmount.value = row.stated_income_amount ?? '';
    residentSinceYear.value = row.resident_since_year ?? '';
    statedCivilStatus.value = row.stated_civil_status ?? '';
    soloParentCircumstance.value = row.solo_parent_circumstance ?? '';
    presidentName.value = row.solo_parent_president_name ?? '';
    presidentAddress.value = row.solo_parent_president_address ?? '';
    neighborName.value = row.solo_parent_neighbor_name ?? '';
    neighborAddress.value = row.solo_parent_neighbor_address ?? '';
    kagawadName.value = row.solo_parent_kagawad_name ?? '';
    children.value = Array.isArray(row.children) && row.children.length
        ? row.children.map((child) => ({
            child_name: child.child_name ?? '',
            date_of_birth: child.date_of_birth ?? '',
            resident_id: null,
        }))
        : [{ child_name: '', date_of_birth: '', resident_id: null }];
    editsReady.value = false;
    issued.value = false;
    step.value = 5;
    await loadPreview(false);
}

function certificatePayload() {
    return {
        type_of_certificate_id: selectedType.value.type_of_certificate_id,
        resident_id: selectedResident.value.resident_id,
        purpose_of_request: purpose.value.trim(),
        representative_requested: representativeRequested.value,
        representative_name: representativeRequested.value ? representativeName.value.trim() : null,
        representative_relationship: representativeRequested.value ? representativeRelationship.value.trim() : null,
        date_of_death: selectedType.value?.kind === 'death' ? dateOfDeath.value : null,
        place_of_death: selectedType.value?.kind === 'death' ? placeOfDeath.value.trim() : null,
        cause_of_death: selectedType.value?.kind === 'death' ? causeOfDeath.value.trim() : null,
        ethnic_group: selectedType.value?.kind === 'death' ? ethnicGroup.value.trim() : null,
        include_income_amount: selectedType.value?.kind === 'low_income_amount',
        stated_income_amount: selectedType.value?.kind === 'low_income_amount'
            ? statedIncomeAmount.value.trim()
            : null,
        resident_since_year: selectedType.value?.kind === 'low_income'
            ? residentSinceYear.value.trim()
            : null,
        stated_civil_status: needsCivilStatus.value ? statedCivilStatus.value.trim() : null,
        solo_parent_circumstance: selectedType.value?.kind === 'solo_parent' ? soloParentCircumstance.value.trim() : null,
        solo_parent_president_name: selectedType.value?.kind === 'solo_parent' ? presidentName.value.trim() : null,
        solo_parent_president_address: selectedType.value?.kind === 'solo_parent' ? presidentAddress.value.trim() : null,
        solo_parent_neighbor_name: selectedType.value?.kind === 'solo_parent' ? neighborName.value.trim() : null,
        solo_parent_neighbor_address: selectedType.value?.kind === 'solo_parent' ? neighborAddress.value.trim() : null,
        solo_parent_kagawad_name: selectedType.value?.kind === 'solo_parent' ? kagawadName.value.trim() : null,
        children: selectedType.value?.kind === 'solo_parent'
            ? children.value
                .map((child) => ({
                    child_name: String(child.child_name ?? '').trim(),
                    date_of_birth: child.date_of_birth || null,
                }))
                .filter((child) => child.child_name || child.date_of_birth)
            : null,
    };
}

watch(attachSignature, async (enabled) => {
    if (!enabled || preview.value?.document?.signature_available === false) {
        return;
    }

    try {
        await ensureSignature();
    } catch (err) {
        generateError.value = await certificateErrorMessage(err, 'No Punong Barangay signature file is on record. The certificate will show a blank signature line.');
    }
});

watch(
    () => [
        purpose.value,
        edits.resident_name,
        edits.address,
        edits.requested_by,
        representativeRequested.value,
        representativeName.value,
        representativeRelationship.value,
        dateOfDeath.value,
        placeOfDeath.value,
        causeOfDeath.value,
        ethnicGroup.value,
        statedIncomeAmount.value,
        residentSinceYear.value,
        statedCivilStatus.value,
        soloParentCircumstance.value,
        JSON.stringify(children.value),
        presidentName.value,
        presidentAddress.value,
        neighborName.value,
        neighborAddress.value,
        kagawadName.value,
    ],
    () => {
        if (step.value !== 5 || !editsReady.value) {
            return;
        }

        if (!canEdit.value) {
            schedulePreview(false);

            return;
        }

        schedulePreview(true);
    },
);

onMounted(loadTypes);
onBeforeUnmount(() => {
    clearTimeout(previewTimer);
    clearSignature();
});
</script>
