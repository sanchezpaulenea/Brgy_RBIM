<template>
    <article class="mx-auto max-w-3xl bg-white px-10 py-8 text-[13px] leading-snug text-slate-900 shadow-sm" style="font-family: 'Times New Roman', Times, serif;">
        <div class="grid grid-cols-[5.25rem_5.25rem_1fr_5.25rem] items-center gap-2">
            <div class="flex h-[5.25rem] w-[5.25rem] items-center justify-center">
                <img v-if="document.logos?.bagong_pilipinas" :src="document.logos.bagong_pilipinas" alt="" class="h-[5.25rem] w-[5.25rem] object-contain">
                <div v-else class="h-[5.25rem] w-[5.25rem] border border-slate-300" />
            </div>
            <div class="flex h-[5.25rem] w-[5.25rem] items-center justify-center">
                <img v-if="document.logos?.city_of_baguio" :src="document.logos.city_of_baguio" alt="" class="h-[5.25rem] w-[5.25rem] object-contain">
                <div v-else class="h-[5.25rem] w-[5.25rem] border border-slate-300" />
            </div>
            <div class="text-center leading-tight">
                <p class="font-bold">Republic of the Philippines</p>
                <p>{{ document.header?.city_name }}</p>
                <p class="text-base font-bold">{{ document.header?.barangay_name }}</p>
                <p class="text-xs underline">Official email address: {{ document.header?.barangay_email }}</p>
                <p class="text-xs underline">Tel. No. {{ document.header?.barangay_contact_no }}</p>
            </div>
            <div class="flex h-[5.25rem] w-[5.25rem] items-center justify-center">
                <img v-if="document.logos?.happy_hallow" :src="document.logos.happy_hallow" alt="" class="h-[5.25rem] w-[5.25rem] object-contain">
                <div v-else class="h-[5.25rem] w-[5.25rem] border border-slate-300" />
            </div>
        </div>

        <h2 class="mt-6 mb-4 text-center text-xl font-bold tracking-wide">{{ document.title }}</h2>
        <p class="font-bold">To Whom It May Concern:</p>

        <p
            v-for="(paragraph, index) in document.paragraphs"
            :key="index"
            class="mt-3 indent-10 text-justify leading-relaxed"
        >
            <template v-for="(part, partIndex) in paragraph" :key="partIndex">
                <strong v-if="part.bold">{{ part.text }}</strong>
                <template v-else>{{ part.text }}</template>
            </template>
        </p>

        <div class="mt-8 flex justify-end">
            <div class="w-56 text-center">
                <img
                    v-if="attachSignature && signatureUrl"
                    :src="signatureUrl"
                    alt="Punong Barangay signature"
                    class="mx-auto h-12 object-contain"
                >
                <div v-else class="h-12" />
                <p class="inline-block min-w-40 font-bold underline">{{ document.fields?.punong_barangay_name || ' ' }}</p>
                <p>Punong Barangay</p>
            </div>
        </div>

        <div class="mt-4 flex items-start justify-between gap-6">
            <div>
                <p>By The Authority of the Punong Barangay;</p>
                <div class="mt-3 h-8 w-48 border-b border-slate-900" />
                <p class="mt-1">{{ document.fields?.issuer_name }}</p>
                <p>Officer for the day</p>
                <p>{{ document.fields?.issuer_position }}</p>
            </div>
            <div class="w-60 shrink-0 rounded-lg border-2 border-[#9c3b32] p-0.5 text-[#9c3b32]">
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
