<template>
    <article class="mx-auto max-w-3xl bg-white px-8 py-8 text-slate-900 shadow-sm" style="font-family: 'Times New Roman', Times, serif;">
        <div class="grid grid-cols-[4.5rem_4.5rem_1fr_4.5rem] items-center gap-3">
            <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center">
                <img v-if="document.logos?.bagong_pilipinas" :src="document.logos.bagong_pilipinas" alt="" class="h-[4.5rem] w-[4.5rem] object-contain">
                <div v-else class="h-[4.5rem] w-[4.5rem] border border-slate-400" />
            </div>
            <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center">
                <img v-if="document.logos?.city_of_baguio" :src="document.logos.city_of_baguio" alt="" class="h-[4.5rem] w-[4.5rem] object-contain">
                <div v-else class="h-[4.5rem] w-[4.5rem] border border-slate-400" />
            </div>
            <div class="text-center text-sm leading-snug">
                <p>Republic of the Philippines</p>
                <p>{{ document.header?.city_name }}</p>
                <p class="text-base font-bold tracking-wide">{{ document.header?.barangay_name }}</p>
                <p>Official email address: {{ document.header?.barangay_email }}</p>
                <p>Tel. No. {{ document.header?.barangay_contact_no }}</p>
            </div>
            <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center">
                <img v-if="document.logos?.happy_hallow" :src="document.logos.happy_hallow" alt="" class="h-[4.5rem] w-[4.5rem] object-contain">
                <div v-else class="h-[4.5rem] w-[4.5rem] border border-slate-400" />
            </div>
        </div>

        <h2 class="mt-5 text-center font-sans text-lg font-bold">{{ document.title }}</h2>
        <p class="mt-4 font-bold">To Whom It May Concern:</p>

        <p
            v-for="(paragraph, index) in document.paragraphs"
            :key="index"
            class="mt-3 text-justify indent-8 leading-relaxed"
        >
            <template v-for="(part, partIndex) in paragraph" :key="partIndex">
                <strong v-if="part.bold">{{ part.text }}</strong>
                <template v-else>{{ part.text }}</template>
            </template>
        </p>

        <div class="mt-6 flex justify-end">
            <div class="w-60 text-center text-sm">
                <img
                    v-if="attachSignature && signatureUrl"
                    :src="signatureUrl"
                    alt="Punong Barangay signature"
                    class="mx-auto h-14 object-contain"
                >
                <div v-else class="h-14" />
                <p class="font-bold underline">{{ document.fields?.punong_barangay_name }}</p>
                <p>Punong Barangay</p>
                <div class="mx-auto mt-3 w-56 border border-slate-900 px-3 py-2 text-left text-xs">
                    <p class="text-center font-bold">DOCUMENTARY STAMP TAX PAID</p>
                    <p class="mt-2">GOR SERIAL NUMBER: _______________</p>
                    <p class="mt-2">DATE: _______________</p>
                </div>
            </div>
        </div>

        <div class="mt-8 text-sm">
            <p>By The Authority of the Punong Barangay;</p>
            <div class="mt-6 h-7 w-52 border-b border-slate-900" />
            <p>{{ document.fields?.issuer_name }}</p>
            <p>Officer for the day</p>
            <p>{{ document.fields?.issuer_position }}</p>
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
