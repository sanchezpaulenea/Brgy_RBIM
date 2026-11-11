<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage } from '@/services/http';
import * as lookupService from '@/services/lookupService';

const form = defineModel({ type: Object, required: true });

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
    idPrefix: {
        type: String,
        default: 'resident',
    },
});

const { hasPermission } = useAuth();

const provinces = ref([]);
const municipalities = ref([]);
const barangays = ref([]);
const loadingProvinces = ref(false);
const loadingMunicipalities = ref(false);
const loadingBarangays = ref(false);
const localError = ref('');

const canAdd = computed(() => hasPermission('resident.create')
    || hasPermission('resident.update')
    || hasPermission('household.create')
    || hasPermission('household.update'));

const hint = computed(() => (canAdd.value
    ? 'Choose from the list, or type a new name and press Enter to add it.'
    : 'Your role can choose a place from the list.'));

const countryName = computed(() => form.value.birth_country || 'Philippines');

const provinceOptions = computed(() => withCurrent(
    provinces.value,
    form.value.birth_province_id,
    form.value.birth_province,
));

const municipalityOptions = computed(() => withCurrent(
    municipalities.value,
    form.value.birth_city_municipality_id,
    form.value.birth_city_municipality,
));

const barangayOptions = computed(() => withCurrent(
    barangays.value,
    form.value.birth_barangay_id,
    form.value.birth_barangay,
));

function withCurrent(items, id, label) {
    const numericId = Number(id);

    if (!Number.isInteger(numericId) || numericId <= 0 || !label) {
        return items;
    }

    if (items.some((item) => Number(item.id) === numericId)) {
        return items;
    }

    return [{ id: numericId, label }, ...items];
}

function fieldError(key) {
    return props.errors?.[key] || '';
}

onMounted(loadProvinces);

watch(() => form.value.birth_province_id, (id) => {
    loadMunicipalities(id);
}, { immediate: true });

watch(() => form.value.birth_city_municipality_id, (id) => {
    loadBarangays(id);
}, { immediate: true });

async function loadProvinces() {
    loadingProvinces.value = true;

    try {
        provinces.value = await lookupService.fetchProvinces();
        rememberCountry(provinces.value.find((item) => Number(item.id) === Number(form.value.birth_province_id)));
    } catch (error) {
        localError.value = extractErrorMessage(error, 'Unable to load provinces.');
    } finally {
        loadingProvinces.value = false;
    }
}

async function loadMunicipalities(provinceId) {
    municipalities.value = [];

    if (!Number(provinceId)) {
        return;
    }

    loadingMunicipalities.value = true;

    try {
        municipalities.value = await lookupService.fetchMunicipalities(provinceId);
    } catch (error) {
        localError.value = extractErrorMessage(error, 'Unable to load cities and municipalities.');
    } finally {
        loadingMunicipalities.value = false;
    }
}

async function loadBarangays(municipalityId) {
    barangays.value = [];

    if (!Number(municipalityId)) {
        return;
    }

    loadingBarangays.value = true;

    try {
        barangays.value = await lookupService.fetchBarangays(municipalityId);
    } catch (error) {
        localError.value = extractErrorMessage(error, 'Unable to load barangays.');
    } finally {
        loadingBarangays.value = false;
    }
}

function selectProvince(provinceId) {
    const province = provinceOptions.value.find((item) => Number(item.id) === Number(provinceId));
    const nextId = province ? Number(province.id) : null;

    if (Number(form.value.birth_province_id) !== Number(nextId)) {
        clearMunicipality();
        clearBarangay();
    }

    form.value.birth_province_id = nextId;
    form.value.birth_province = province?.label || '';
    rememberCountry(province);
    localError.value = '';
}

function selectMunicipality(municipalityId) {
    const municipality = municipalityOptions.value.find((item) => Number(item.id) === Number(municipalityId));
    const nextId = municipality ? Number(municipality.id) : null;

    if (Number(form.value.birth_city_municipality_id) !== Number(nextId)) {
        clearBarangay();
    }

    form.value.birth_city_municipality_id = nextId;
    form.value.birth_city_municipality = municipality?.label || '';
    localError.value = '';
}

function selectBarangay(barangayId) {
    const barangay = barangayOptions.value.find((item) => Number(item.id) === Number(barangayId));

    form.value.birth_barangay_id = barangay ? Number(barangay.id) : null;
    form.value.birth_barangay = barangay?.label || '';
    localError.value = '';
}

function rememberCountry(place) {
    if (!place?.country_name && !place?.country_id) {
        return;
    }

    form.value.birth_country = place.country_name || form.value.birth_country || 'Philippines';
    form.value.birth_country_id = place.country_id || form.value.birth_country_id || null;
}

function clearMunicipality() {
    form.value.birth_city_municipality_id = null;
    form.value.birth_city_municipality = '';
}

function clearBarangay() {
    form.value.birth_barangay_id = null;
    form.value.birth_barangay = '';
}

async function addProvince(name) {
    await addPlace(async () => {
        const item = await lookupService.createProvince(name);
        provinces.value = upsert(provinces.value, item);
        selectProvince(item.id);
    }, 'Unable to add that province.');
}

async function addMunicipality(name) {
    if (!Number(form.value.birth_province_id)) {
        localError.value = 'Select a province before adding a city or municipality.';

        return;
    }

    await addPlace(async () => {
        const item = await lookupService.createMunicipality(form.value.birth_province_id, name);
        municipalities.value = upsert(municipalities.value, item);
        selectMunicipality(item.id);
    }, 'Unable to add that city or municipality.');
}

async function addBarangay(name) {
    if (!Number(form.value.birth_city_municipality_id)) {
        localError.value = 'Select a city or municipality before adding a barangay.';

        return;
    }

    await addPlace(async () => {
        const item = await lookupService.createBarangay(form.value.birth_city_municipality_id, name);
        barangays.value = upsert(barangays.value, item);
        selectBarangay(item.id);
    }, 'Unable to add that barangay.');
}

async function addPlace(action, fallback) {
    localError.value = '';

    try {
        await action();
    } catch (error) {
        localError.value = extractErrorMessage(error, fallback);
    }
}

function upsert(items, item) {
    const rest = items.filter((current) => Number(current.id) !== Number(item.id));

    return [...rest, item].sort((left, right) => String(left.label).localeCompare(String(right.label)));
}
</script>

<template>
    <div class="contents">
        <div>
            <label class="rbim-label" :for="`${idPrefix}-birth-country`">Birth Country</label>
            <input
                :id="`${idPrefix}-birth-country`"
                :value="countryName"
                type="text"
                class="rbim-input bg-slate-50"
                disabled
            >
        </div>
        <div>
            <label class="rbim-label" :for="`${idPrefix}-birth-province`">Birth Province</label>
            <LookupCombobox
                :input-id="`${idPrefix}-birth-province`"
                :model-value="form.birth_province_id"
                :options="provinceOptions"
                :disabled="loadingProvinces"
                :can-create="canAdd"
                :limit="500"
                placeholder="Select province"
                @update:model-value="selectProvince"
                @create="addProvince"
            />
            <p v-if="fieldError('birth_province_id')" class="rbim-error">{{ fieldError('birth_province_id') }}</p>
        </div>
        <div>
            <label class="rbim-label" :for="`${idPrefix}-birth-city`">Birth City/Municipality</label>
            <LookupCombobox
                :input-id="`${idPrefix}-birth-city`"
                :model-value="form.birth_city_municipality_id"
                :options="municipalityOptions"
                :disabled="!form.birth_province_id || loadingMunicipalities"
                :can-create="canAdd && Boolean(form.birth_province_id)"
                :limit="500"
                placeholder="Select city or municipality"
                @update:model-value="selectMunicipality"
                @create="addMunicipality"
            />
            <p v-if="fieldError('birth_city_municipality_id')" class="rbim-error">{{ fieldError('birth_city_municipality_id') }}</p>
        </div>
        <div>
            <label class="rbim-label" :for="`${idPrefix}-birth-barangay`">Birth Barangay</label>
            <LookupCombobox
                :input-id="`${idPrefix}-birth-barangay`"
                :model-value="form.birth_barangay_id"
                :options="barangayOptions"
                :disabled="!form.birth_city_municipality_id || loadingBarangays"
                :can-create="canAdd && Boolean(form.birth_city_municipality_id)"
                :limit="500"
                placeholder="Select barangay"
                @update:model-value="selectBarangay"
                @create="addBarangay"
            />
            <p v-if="fieldError('birth_barangay_id')" class="rbim-error">{{ fieldError('birth_barangay_id') }}</p>
            <p v-if="localError" class="rbim-error">{{ localError }}</p>
            <p v-else class="rbim-hint">{{ hint }}</p>
        </div>
    </div>
</template>
