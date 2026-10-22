<template>
    <article class="mx-auto max-w-3xl bg-white px-10 py-8 text-[13px] leading-snug text-slate-900 shadow-sm" style="font-family: 'Times New Roman', Times, serif;">
        <div class="flex items-start gap-3">
            <div class="flex shrink-0 gap-1">
                <img v-if="document.logos?.bagong_pilipinas" :src="document.logos.bagong_pilipinas" alt="" class="h-14 w-14 object-contain">
                <div v-else class="h-14 w-14 border border-slate-300" />
                <img v-if="document.logos?.city_of_baguio" :src="document.logos.city_of_baguio" alt="" class="h-14 w-14 object-contain">
                <div v-else class="h-14 w-14 border border-slate-300" />
            </div>
            <div class="min-w-0 flex-1 text-center leading-tight">
                <p class="text-sm font-bold">Republic of the Philippines</p>
                <p class="text-sm">{{ document.header?.city_name }}</p>
                <p class="text-sm font-bold">{{ document.header?.barangay_name }}</p>
                <p class="text-[11px] underline">Official email address: {{ document.header?.barangay_email }}</p>
                <p class="text-[11px] underline">Tel. No. {{ document.header?.barangay_contact_no }}</p>
            </div>
            <div class="shrink-0">
                <img v-if="document.logos?.happy_hallow" :src="document.logos.happy_hallow" alt="" class="h-14 w-14 object-contain">
                <div v-else class="h-14 w-14 border border-slate-300" />
            </div>
        </div>

        <p v-if="document.office" class="mt-1 text-center text-xs font-bold">{{ document.office }}</p>
        <p v-if="document.revised_note" class="text-center text-[11px]">{{ document.revised_note }}</p>
        <p v-if="document.show_certificate_number" class="text-center text-[11px]">
            Barangay Certificate Number: {{ document.certificate_number || '' }}
        </p>

        <h2 class="mt-4 mb-1 text-center font-bold tracking-wide" :class="document.title?.length > 40 ? 'text-sm' : 'text-lg'">
            {{ document.title }}
        </h2>
        <p v-if="document.subtitle" class="mb-2 text-center text-xs font-bold">{{ document.subtitle }}</p>
        <p v-if="document.salutation" class="font-bold">{{ document.salutation }}</p>

        <template v-for="(paragraph, index) in document.paragraphs" :key="index">
            <table v-if="document.children_after === index" class="mt-2 w-full border-collapse text-left text-[11px]">
                <thead>
                    <tr>
                        <th class="border border-slate-800 px-1">NAME</th>
                        <th class="border border-slate-800 px-1">DATE OF BIRTH</th>
                        <th class="border border-slate-800 px-1">AGE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(child, childIndex) in document.children" :key="childIndex">
                        <td class="border border-slate-800 px-1">{{ child.name }}</td>
                        <td class="border border-slate-800 px-1">{{ child.date_of_birth }}</td>
                        <td class="border border-slate-800 px-1">{{ child.age }}</td>
                    </tr>
                    <tr v-if="!document.children?.length">
                        <td class="border border-slate-800 px-1">&nbsp;</td>
                        <td class="border border-slate-800 px-1">&nbsp;</td>
                        <td class="border border-slate-800 px-1">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-justify leading-snug" :class="document.dense ? 'indent-4' : 'indent-8'">
                <template v-for="(part, partIndex) in paragraph" :key="partIndex">
                    <strong v-if="part.bold">{{ part.text }}</strong>
                    <template v-else>{{ part.text }}</template>
                </template>
            </p>
        </template>

        <div v-if="document.signature_style === 'punong'" class="mt-4 flex justify-end">
            <div class="w-60 text-center">
                <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                <div v-else class="h-12" />
                <p class="inline-block min-w-40 font-bold underline">{{ document.fields?.punong_barangay_name || ' ' }}</p>
                <p>Punong Barangay</p>
            </div>
        </div>

        <div v-else-if="document.signature_style === 'approved'" class="mt-4">
            <p class="font-bold">Approved by:</p>
            <div class="w-60 text-center">
                <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                <div v-else class="h-12" />
                <p class="font-bold underline">{{ document.fields?.punong_barangay_name || ' ' }}</p>
                <p>Punong Barangay</p>
            </div>
            <p class="mt-3 font-bold">Witnessed by:</p>
            <p>{{ document.fields?.issuer_name }}</p>
            <p>{{ document.fields?.issuer_position || 'Barangay Kagawad' }}</p>
            <p>Date: {{ document.fields?.witness_date }}</p>
        </div>

        <div v-else-if="document.signature_style === 'oath'" class="mt-4">
            <div class="h-8 w-64 border-b border-slate-900" />
            <p>{{ document.fields?.resident_name }}, FIRST TIME JOB SEEKER</p>
            <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mt-2 h-12 object-contain">
            <div v-else class="h-8" />
            <p>{{ document.fields?.punong_barangay_name }}, Punong Barangay</p>
            <p class="mt-2">Witnessed by: {{ document.fields?.issuer_name }}, {{ document.fields?.issuer_position || 'Barangay Kagawad' }}/Officer of the day</p>
            <p>DATE: {{ document.fields?.witness_date }}</p>
        </div>

        <div v-else-if="document.signature_style === 'affidavit'" class="mt-4 text-[12px]">
            <p>Barangay Captain: {{ document.fields?.punong_barangay_name }}</p>
            <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="h-12 object-contain">
            <div class="mt-3 grid grid-cols-2 gap-4">
                <div>
                    <p>Witness</p>
                    <p>Name: {{ document.affidavit?.president_name }}</p>
                    <p>Solo Parent President</p>
                    <p>Complete Address: {{ document.affidavit?.president_address }}</p>
                    <div class="mt-4 h-6 w-40 border-b border-slate-900" />
                    <p>Signature</p>
                </div>
                <div>
                    <p>NEIGHBOR:</p>
                    <p>Name: {{ document.affidavit?.neighbor_name }}</p>
                    <p>Complete Address: {{ document.affidavit?.neighbor_address }}</p>
                    <div class="mt-4 h-6 w-40 border-b border-slate-900" />
                    <p>Signature</p>
                </div>
            </div>
            <p class="mt-3">Name: {{ document.affidavit?.kagawad_name }}</p>
            <p>Kagawad on Social Services</p>
            <div class="mt-2 h-6 w-40 border-b border-slate-900" />
            <p>Signature</p>
        </div>

        <template v-else>
            <div class="mt-4 flex justify-end">
                <div class="w-60 text-center">
                    <img v-if="attachSignature && signatureUrl" :src="signatureUrl" alt="Punong Barangay signature" class="mx-auto h-12 object-contain">
                    <div v-else class="h-12" />
                    <p class="inline-block min-w-40 font-bold underline">{{ document.fields?.punong_barangay_name || ' ' }}</p>
                    <p>Punong Barangay</p>
                </div>
            </div>
            <div class="mt-3 flex items-start justify-between gap-6">
                <p>By The Authority of the Punong Barangay;</p>
                <div v-if="document.show_stamp !== false" class="w-60 shrink-0 rounded-lg border-2 border-[#9c3b32] p-0.5 text-left text-[#9c3b32]">
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
                <p class="mt-1">{{ document.fields?.issuer_name }}</p>
                <p>Officer for the day</p>
                <p>{{ document.fields?.issuer_position }}</p>
            </div>
        </template>

        <p v-for="(note, noteIndex) in document.footer_notes || []" :key="noteIndex" class="mt-1 text-center text-[11px] font-bold">
            {{ note }}
        </p>
    </article>
</template>

<script setup>
defineProps({
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
</script>
