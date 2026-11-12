<template>
    <select
        :id="id"
        :value="selected"
        class="rbim-input"
        :class="{ 'rbim-input-error': invalid }"
        :disabled="disabled"
        @change="onChange"
    >
        <option value="">{{ notApplicableLabel }}</option>
        <option
            v-for="option in namedSuffixes"
            :key="option.id"
            :value="suffixFormValue(option)"
        >
            {{ option.label }}
        </option>
        <option v-if="unmatched" :value="selected">{{ selected }}</option>
    </select>
    <p v-if="loadError" class="rbim-error">{{ loadError }}</p>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { isNotApplicableSuffix, loadSuffixOptions, suffixFormValue } from '@/utils/suffixOptions';

const model = defineModel({ default: '' });

defineProps({
    id: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    invalid: {
        type: Boolean,
        default: false,
    },
});

const options = ref([]);
const loadError = ref('');

const selected = computed(() => (model.value == null ? '' : String(model.value)));

const namedSuffixes = computed(() => options.value.filter((option) => suffixFormValue(option) !== ''));

const notApplicableLabel = computed(() => {
    const match = options.value.find((option) => suffixFormValue(option) === '');

    return match?.label || 'Not Applicable';
});

const unmatched = computed(() => {
    if (selected.value === '') {
        return false;
    }

    return !options.value.some((option) => suffixFormValue(option) === selected.value);
});

function normalizeCurrentValue() {
    const current = selected.value.trim();

    if (isNotApplicableSuffix(current)) {
        if (model.value !== '') {
            model.value = '';
        }

        return;
    }

    const match = options.value.find(
        (option) => suffixFormValue(option).toLowerCase() === current.toLowerCase(),
    );

    if (match && suffixFormValue(match) !== model.value) {
        model.value = suffixFormValue(match);
    }
}

function onChange(event) {
    model.value = event.target.value;
}

onMounted(async () => {
    try {
        options.value = await loadSuffixOptions();
        normalizeCurrentValue();
    } catch {
        loadError.value = 'Unable to load suffixes.';
    }
});
</script>
