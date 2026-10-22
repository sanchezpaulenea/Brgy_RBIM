<template>
    <article class="mx-auto max-w-3xl bg-white px-10 py-8 text-[13px] leading-snug text-slate-900 shadow-sm" style="font-family: 'Times New Roman', Times, serif;">
        <template v-for="(sheet, sheetIndex) in sheets" :key="sheetIndex">
        <div :class="sheetIndex > 0 ? 'mt-10 border-t border-slate-300 pt-8' : ''">
        <div class="flex items-start gap-3">
            <div class="flex shrink-0 gap-1">
                <img v-if="sheet.logos?.bagong_pilipinas" :src="sheet.logos.bagong_pilipinas" alt="" class="h-14 w-14 object-contain">
                <div v-else class="h-14 w-14 border border-slate-300" />
                <img v-if="sheet.logos?.city_of_baguio" :src="sheet.logos.city_of_baguio" alt="" class="h-14 w-14 object-contain">
                <div v-else class="h-14 w-14 border border-slate-300" />
            </div>
            <div class="min-w-0 flex-1 text-center leading-tight">
                <p class="text-sm font-bold">Republic of the Philippines</p>
                <p class="text-sm">{{ sheet.header?.city_name }}</p>
                <p class="text-sm font-bold">{{ sheet.header?.barangay_name }}</p>
                <p class="text-[11px] underline">Official email address: {{ sheet.header?.barangay_email }}</p>
                <p class="text-[11px] underline">Tel. No. {{ sheet.header?.barangay_contact_no }}</p>
            </div>
            <div class="shrink-0">
                <img v-if="sheet.logos?.happy_hallow" :src="sheet.logos.happy_hallow" alt="" class="h-14 w-14 object-contain">
                <div v-else class="h-14 w-14 border border-slate-300" />
            </div>
        </div>

        <p v-if="sheet.office" class="mt-1 text-center text-xs font-bold">{{ sheet.office }}</p>
        <p v-if="sheet.revised_note" class="text-[11px]" :class="sheet.meta_right ? 'text-right italic' : 'text-center'">{{ sheet.revised_note }}</p>
        <p v-if="sheet.show_certificate_number" class="text-right text-[11px] italic underline">
            Barangay Certificate Number: {{ sheet.certificate_number || '' }}
        </p>

        <h2 class="mt-4 mb-1 text-center font-bold tracking-wide" :class="sheet.title?.length > 40 ? 'text-sm' : 'text-lg'">
            {{ sheet.title }}
        </h2>
        <p v-if="sheet.subtitle" class="mb-2 text-center text-xs font-bold">{{ sheet.subtitle }}</p>
        <p v-if="sheet.salutation" class="font-bold">{{ sheet.salutation }}</p>

        <template v-for="(paragraph, index) in sheet.paragraphs" :key="index">
            <table v-if="sheet.children_after === index" class="mt-2 w-full border-collapse text-left text-[11px]">
                <thead>
                    <tr>
                        <th class="border border-slate-800 px-1">NAME</th>
                        <th class="border border-slate-800 px-1">DATE OF BIRTH</th>
                        <th class="border border-slate-800 px-1">AGE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(child, childIndex) in sheet.children" :key="childIndex">
                        <td class="border border-slate-800 px-1">{{ child.name }}</td>
                        <td class="border border-slate-800 px-1">{{ child.date_of_birth }}</td>
                        <td class="border border-slate-800 px-1">{{ child.age }}</td>
                    </tr>
                    <tr v-if="!sheet.children?.length">
                        <td class="border border-slate-800 px-1">&nbsp;</td>
                        <td class="border border-slate-800 px-1">&nbsp;</td>
                        <td class="border border-slate-800 px-1">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-justify leading-snug" :class="paragraph.flush ? '' : (sheet.dense ? 'indent-4' : 'indent-8')">
                <template v-for="(part, partIndex) in (paragraph.parts || paragraph)" :key="partIndex">
                    <span
                        v-if="part.bold || part.italic || part.underline"
                        :class="{ 'font-bold': part.bold, italic: part.italic, underline: part.underline }"
                    >{{ part.text }}</span>
                    <template v-else>{{ part.text }}</template>
                </template>
            </p>
        </template>

        <div v-if="sheet.signature_style === 'punong'" class="mt-4 flex justify-end">
            <div class="w-60 text-center">
                <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                <div v-else class="h-12" />
                <p class="inline-block min-w-40 font-bold underline">{{ sheet.fields?.punong_barangay_name || ' ' }}</p>
                <p>Punong Barangay</p>
            </div>
        </div>

        <div v-else-if="sheet.signature_style === 'approved'" class="mt-4">
            <p class="font-bold">Approved by:</p>
            <div class="w-60 text-center">
                <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                <div v-else class="h-12" />
                <p class="font-bold underline">{{ sheet.fields?.punong_barangay_name || ' ' }}</p>
                <p>Punong Barangay</p>
            </div>
            <p class="mt-3 font-bold">Witnessed by:</p>
            <p class="font-bold">{{ sheet.fields?.issuer_name }}</p>
            <p>{{ sheet.fields?.issuer_position || 'Barangay Kagawad' }}</p>
            <p>Date: <span class="font-bold">{{ sheet.fields?.witness_date }}</span></p>
        </div>

        <div v-else-if="sheet.signature_style === 'oath'" class="mt-4">
            <div class="flex justify-end">
                <div class="w-60 text-center">
                    <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                    <div v-else class="h-8" />
                    <p>{{ sheet.fields?.punong_barangay_name }}, Punong Barangay</p>
                </div>
            </div>
            <p class="font-bold">{{ sheet.fields?.resident_name }}, FIRST TIME JOB SEEKER</p>
            <p class="mt-2">Witnessed by:<span class="font-bold">{{ sheet.fields?.issuer_name }}</span>, {{ sheet.fields?.issuer_position || 'Barangay Kagawad' }}/Officer of the day</p>
            <p>DATE: {{ sheet.fields?.witness_date }}</p>
        </div>
        <div v-else-if="sheet.signature_style === 'income'" class="mt-4">
            <p>{{ sheet.authority_line }}</p>
            <p class="font-bold">{{ sheet.fields?.issuer_name }}</p>
            <p>Officer for the day</p>
            <p class="font-bold">{{ sheet.fields?.issuer_position || 'Barangay Kagawad' }}</p>
            <div class="mt-3 flex justify-end">
                <div class="w-60 text-center">
                    <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                    <div v-else class="h-12" />
                    <p class="font-bold underline">{{ sheet.fields?.punong_barangay_name || ' ' }}</p>
                    <p>Punong Barangay</p>
                </div>
            </div>
        </div>

        <div v-else-if="sheet.signature_style === 'affidavit'" class="mt-4 text-[12px]">
            <div class="text-right">
                <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="ml-auto h-12 object-contain">
                <p>Barangay Captain: <span class="font-bold">{{ sheet.fields?.punong_barangay_name }}</span></p>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-4">
                <div>
                    <p class="font-bold">Witness</p>
                    <p>Name: {{ sheet.affidavit?.president_name }}</p>
                    <p>Solo Parent President</p>
                    <p>Complete Address: {{ sheet.affidavit?.president_address }}</p>
                    <div class="mt-4 h-6 w-40 border-b border-slate-900" />
                    <p>Signature</p>
                </div>
                <div>
                    <p class="font-bold">NEIGHBOR:</p>
                    <p>Name: {{ sheet.affidavit?.neighbor_name }}</p>
                    <p>Complete Address: {{ sheet.affidavit?.neighbor_address }}</p>
                    <div class="mt-4 h-6 w-40 border-b border-slate-900" />
                    <p>Signature</p>
                </div>
            </div>
            <p class="mt-3">Name: {{ sheet.affidavit?.kagawad_name }}</p>
            <p>Kagawad on Social Services</p>
            <div class="mt-2 h-6 w-40 border-b border-slate-900" />
            <p>Signature</p>
        </div>

        <template v-else>
            <div class="mt-4 flex justify-end">
                <div class="w-60 text-center">
                    <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                    <div v-else class="h-12" />
                    <p class="inline-block min-w-40 font-bold underline">{{ sheet.fields?.punong_barangay_name || ' ' }}</p>
                    <p>Punong Barangay</p>
                </div>
            </div>
            <div class="mt-3 flex items-start justify-between gap-6">
                <p>{{ sheet.authority_line || 'By The Authority of the Punong Barangay;' }}</p>
                <div v-if="sheet.show_stamp !== false" class="w-60 shrink-0 rounded-lg border-2 border-[#9c3b32] p-0.5 text-left text-[#9c3b32]">
                    <div class="rounded-md border border-[#9c3b32] px-2 py-1.5 text-[10px] leading-tight">
                        <p class="text-center font-bold">DOCUMENTARY STAMP TAX PAID</p>
                        <div class="mt-1.5 grid grid-cols-[1.3fr_1fr]">
                            <div class="pr-2">
                                <p>GOR SERIAL NUMBER:</p>
                                <div class="mt-2 border-b border-[#9c3b32]" />
                            </div>
                            <div class="border-l border-[#9c3b32] pl-2">
                                <p>DATE:</p>
                                <div class="mt-2 border-b border-[#9c3b32]" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="h-8 w-48 border-b border-slate-900" />
                <p class="mt-1" :class="{ 'font-bold': sheet.bold_issuer }">{{ sheet.fields?.issuer_name }}</p>
                <p>Officer for the day</p>
                <p>{{ sheet.fields?.issuer_position }}</p>
            </div>
        </template>

        <div v-if="sheet.footer_split" class="mt-4 flex items-end justify-between gap-4">
            <div>
                <p v-for="(note, noteIndex) in sheet.footer_notes || []" :key="noteIndex" class="text-[11px] font-bold">{{ note }}</p>
            </div>
            <p class="text-right text-[11px] italic">{{ sheet.revised_note }}</p>
        </div>
        <div v-else>
            <p v-for="(note, noteIndex) in sheet.footer_notes || []" :key="noteIndex" class="mt-1 text-[11px] font-bold">
                {{ note }}
            </p>
        </div>
        </div>
        </template>
    </article>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    document: {
        type: Object,
        required: true,
    },
    attachSignature: {
        type: Boolean,
        default: false,
    },
    signatureUrl: {
        type: String,
        default: '',
    },
});

const sheets = computed(() => (props.document?.pages?.length ? props.document.pages : [props.document]));
</script>
