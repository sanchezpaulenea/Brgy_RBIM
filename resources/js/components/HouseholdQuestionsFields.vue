<template>
    <div class="space-y-4">
        <div>
            <label :for="`${idPrefix}-ownership_of_housing_unit_id`" class="rbim-label">
                Q1. Do you own or amortize this housing unit occupied by your household or do you rent it, do you occupy it rent-free with consent of owner or rent-free without consent of owner?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-ownership_of_housing_unit_id`"
                v-model="form.ownership_of_housing_unit_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.ownership_of_housing_unit_id }"
            >
                <option value="">Select ownership of housing unit</option>
                <option v-for="option in lookups.ownershipType" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.ownership_of_housing_unit_id" class="rbim-error">{{ errors.ownership_of_housing_unit_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-ownership_of_lot_id`" class="rbim-label">
                Q2. Do you own or amortize this lot occupied by your household or do you rent it, do you occupy it rent-free with consent of owner or rent-free without consent of owner?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-ownership_of_lot_id`"
                v-model="form.ownership_of_lot_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.ownership_of_lot_id }"
            >
                <option value="">Select ownership of lot</option>
                <option v-for="option in lookups.ownershipType" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.ownership_of_lot_id" class="rbim-error">{{ errors.ownership_of_lot_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-fuel_type_for_lighting_id`" class="rbim-label">
                Q3. What type of fuel does this household use for lighting?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-fuel_type_for_lighting_id`"
                v-model="form.fuel_type_for_lighting_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.fuel_type_for_lighting_id }"
            >
                <option value="">Select fuel for lighting</option>
                <option v-for="option in lookups.fuelType" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.fuel_type_for_lighting_id" class="rbim-error">{{ errors.fuel_type_for_lighting_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-fuel_type_for_cooking_id`" class="rbim-label">
                Q4. What kind of fuel does this household use most of the time for cooking?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-fuel_type_for_cooking_id`"
                v-model="form.fuel_type_for_cooking_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.fuel_type_for_cooking_id }"
            >
                <option value="">Select fuel for cooking</option>
                <option v-for="option in lookups.fuelType" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.fuel_type_for_cooking_id" class="rbim-error">{{ errors.fuel_type_for_cooking_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-main_source_drinking_water_id`" class="rbim-label">
                Q5. What is the household's main source of drinking water?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-main_source_drinking_water_id`"
                v-model="form.main_source_drinking_water_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.main_source_drinking_water_id }"
            >
                <option value="">Select source of drinking water</option>
                <option v-for="option in lookups.waterSource" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.main_source_drinking_water_id" class="rbim-error">{{ errors.main_source_drinking_water_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-kitchen_garbage_disposal_id`" class="rbim-label">
                Q6a. How does your household usually dispose of your kitchen garbage such as leftover food, peeling of fruits and vegetables, fish and chicken entrails, and others?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-kitchen_garbage_disposal_id`"
                v-model="form.kitchen_garbage_disposal_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.kitchen_garbage_disposal_id }"
            >
                <option value="">Select kitchen garbage disposal</option>
                <option v-for="option in lookups.kitchenGarbageDisposal" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.kitchen_garbage_disposal_id" class="rbim-error">{{ errors.kitchen_garbage_disposal_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-perform_garbage_seggragation`" class="rbim-label">
                Q6b. Do you segregate your garbage?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-perform_garbage_seggragation`"
                :value="booleanSelectValue(form.perform_garbage_seggragation)"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.perform_garbage_seggragation }"
                @change="form.perform_garbage_seggragation = parseBooleanSelect($event.target.value)"
            >
                <option value="">Select</option>
                <option value="true">Yes</option>
                <option value="false">No</option>
            </select>
            <p v-if="errors.perform_garbage_seggragation" class="rbim-error">{{ errors.perform_garbage_seggragation }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-toilet_facility_type_id`" class="rbim-label">
                Q7. What type of toilet facility does this household use?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-toilet_facility_type_id`"
                v-model="form.toilet_facility_type_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.toilet_facility_type_id }"
            >
                <option value="">Select toilet facility</option>
                <option v-for="option in lookups.toiletFacilityType" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p v-if="errors.toilet_facility_type_id" class="rbim-error">{{ errors.toilet_facility_type_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-type_of_building_house_id`" class="rbim-label">
                Q8. Type of building/house<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-type_of_building_house_id`"
                v-model="form.type_of_building_house_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.type_of_building_house_id }"
            >
                <option value="">Select type of building/house</option>
                <option v-for="option in lookups.buildingHouseType" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Do not ask, observation only.</p>
            <p v-if="errors.type_of_building_house_id" class="rbim-error">{{ errors.type_of_building_house_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-construction_material_outer_wall_id`" class="rbim-label">
                Q9. Construction materials of the outer wall<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-construction_material_outer_wall_id`"
                v-model="form.construction_material_outer_wall_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.construction_material_outer_wall_id }"
            >
                <option value="">Select construction material</option>
                <option v-for="option in lookups.constructionMaterialOuterWall" :key="option.id" :value="option.id">{{ option.label }}</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Do not ask, observation only.</p>
            <p v-if="errors.construction_material_outer_wall_id" class="rbim-error">{{ errors.construction_material_outer_wall_id }}</p>
        </div>

        <div>
            <label :for="`${idPrefix}-female_hhm_died_past_12mos`" class="rbim-label">
                Q10. Do you have any female HH member who died in the past 12 months?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-female_hhm_died_past_12mos`"
                :value="booleanSelectValue(form.female_hhm_died_past_12mos)"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.female_hhm_died_past_12mos }"
                @change="onFemaleDiedChange($event.target.value)"
            >
                <option value="">Select</option>
                <option value="true">Yes</option>
                <option value="false">No</option>
            </select>
            <p v-if="errors.female_hhm_died_past_12mos" class="rbim-error">{{ errors.female_hhm_died_past_12mos }}</p>
            <p v-if="errors.female_deaths" class="rbim-error">{{ errors.female_deaths }}</p>
            <div v-if="isYes(form.female_hhm_died_past_12mos)" class="mt-3 space-y-3">
                <div
                    v-for="(death, index) in form.female_deaths"
                    :key="`female-death-${index}`"
                    class="grid gap-3 sm:grid-cols-2"
                >
                    <div>
                        <label :for="`${idPrefix}-female_deaths_${index}_age`" class="rbim-label">Age</label>
                        <input
                            :id="`${idPrefix}-female_deaths_${index}_age`"
                            v-model="death.age"
                            type="number"
                            min="0"
                            max="120"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': errors[`female_deaths.${index}.age`] }"
                        >
                        <p v-if="errors[`female_deaths.${index}.age`]" class="rbim-error">{{ errors[`female_deaths.${index}.age`] }}</p>
                    </div>
                    <div>
                        <label :for="`${idPrefix}-female_deaths_${index}_cause`" class="rbim-label">Cause of death</label>
                        <input
                            :id="`${idPrefix}-female_deaths_${index}_cause`"
                            v-model="death.cause_of_death"
                            type="text"
                            maxlength="45"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': errors[`female_deaths.${index}.cause_of_death`] }"
                        >
                        <p v-if="errors[`female_deaths.${index}.cause_of_death`]" class="rbim-error">{{ errors[`female_deaths.${index}.cause_of_death`] }}</p>
                    </div>
                    <div v-if="form.female_deaths.length > 1" class="sm:col-span-2">
                        <button type="button" class="text-sm text-slate-600 underline" @click="removeFemaleDeath(index)">
                            Remove
                        </button>
                    </div>
                </div>
                <button type="button" class="text-sm font-medium text-brand" @click="addFemaleDeath">
                    Add another female household member
                </button>
            </div>
        </div>

        <div>
            <label :for="`${idPrefix}-child_hhm_died_past_12mos`" class="rbim-label">
                Q11. Do you have a child HH member below 5 years old who died in the past 12 months?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-child_hhm_died_past_12mos`"
                :value="booleanSelectValue(form.child_hhm_died_past_12mos)"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.child_hhm_died_past_12mos }"
                @change="onChildDiedChange($event.target.value)"
            >
                <option value="">Select</option>
                <option value="true">Yes</option>
                <option value="false">No</option>
            </select>
            <p v-if="errors.child_hhm_died_past_12mos" class="rbim-error">{{ errors.child_hhm_died_past_12mos }}</p>
            <p v-if="errors.child_deaths" class="rbim-error">{{ errors.child_deaths }}</p>
            <div v-if="isYes(form.child_hhm_died_past_12mos)" class="mt-3 space-y-3">
                <div
                    v-for="(death, index) in form.child_deaths"
                    :key="`child-death-${index}`"
                    class="grid gap-3 sm:grid-cols-3"
                >
                    <div>
                        <label :for="`${idPrefix}-child_deaths_${index}_age`" class="rbim-label">Age</label>
                        <input
                            :id="`${idPrefix}-child_deaths_${index}_age`"
                            v-model="death.age"
                            type="number"
                            min="0"
                            max="4"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': errors[`child_deaths.${index}.age`] }"
                        >
                        <p v-if="errors[`child_deaths.${index}.age`]" class="rbim-error">{{ errors[`child_deaths.${index}.age`] }}</p>
                    </div>
                    <div>
                        <label :for="`${idPrefix}-child_deaths_${index}_sex`" class="rbim-label">Sex</label>
                        <select
                            :id="`${idPrefix}-child_deaths_${index}_sex`"
                            v-model="death.sex_id"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': errors[`child_deaths.${index}.sex_id`] }"
                        >
                            <option value="">Select sex</option>
                            <option v-for="option in (lookups.sex ?? [])" :key="option.id" :value="option.id">{{ option.label }}</option>
                        </select>
                        <p v-if="errors[`child_deaths.${index}.sex_id`]" class="rbim-error">{{ errors[`child_deaths.${index}.sex_id`] }}</p>
                    </div>
                    <div>
                        <label :for="`${idPrefix}-child_deaths_${index}_cause`" class="rbim-label">Cause of death</label>
                        <input
                            :id="`${idPrefix}-child_deaths_${index}_cause`"
                            v-model="death.cause_of_death"
                            type="text"
                            maxlength="45"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': errors[`child_deaths.${index}.cause_of_death`] }"
                        >
                        <p v-if="errors[`child_deaths.${index}.cause_of_death`]" class="rbim-error">{{ errors[`child_deaths.${index}.cause_of_death`] }}</p>
                    </div>
                    <div v-if="form.child_deaths.length > 1" class="sm:col-span-3">
                        <button type="button" class="text-sm text-slate-600 underline" @click="removeChildDeath(index)">
                            Remove
                        </button>
                    </div>
                </div>
                <button type="button" class="text-sm font-medium text-brand" @click="addChildDeath">
                    Add another child household member
                </button>
            </div>
        </div>

        <div>
            <p class="rbim-label">Q12. What are the common diseases that causes death in this barangay?</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                <input
                    v-for="index in 3"
                    :id="`${idPrefix}-common_diseases_${index}`"
                    :key="`disease-${index}`"
                    v-model="form.common_diseases[index - 1]"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :placeholder="`${index}.`"
                >
            </div>
            <p v-if="errors.common_diseases" class="rbim-error">{{ errors.common_diseases }}</p>
        </div>

        <div>
            <p class="rbim-label">Q13. What do you think are the primary needs of this barangay?</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                <input
                    v-for="index in 3"
                    :id="`${idPrefix}-primary_needs_${index}`"
                    :key="`need-${index}`"
                    v-model="form.primary_needs[index - 1]"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :placeholder="`${index}.`"
                >
            </div>
            <p v-if="errors.primary_needs" class="rbim-error">{{ errors.primary_needs }}</p>
        </div>

        <div>
            <p class="rbim-label">
                Q14. Where does your household intend to stay five years from now?<span class="rbim-required" aria-hidden="true">*</span>
            </p>
            <div class="mt-2 grid gap-4 sm:grid-cols-3">
                <div>
                    <label :for="`${idPrefix}-intend_to_stay_brgy`" class="rbim-label">Barangay</label>
                    <input
                        :id="`${idPrefix}-intend_to_stay_brgy`"
                        v-model="form.intend_to_stay_brgy"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.intend_to_stay_brgy }"
                    >
                    <p v-if="errors.intend_to_stay_brgy" class="rbim-error">{{ errors.intend_to_stay_brgy }}</p>
                </div>
                <div>
                    <label :for="`${idPrefix}-intend_to_stay_municipality`" class="rbim-label">Municipality</label>
                    <input
                        :id="`${idPrefix}-intend_to_stay_municipality`"
                        v-model="form.intend_to_stay_municipality"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.intend_to_stay_municipality }"
                    >
                    <p v-if="errors.intend_to_stay_municipality" class="rbim-error">{{ errors.intend_to_stay_municipality }}</p>
                </div>
                <div>
                    <label :for="`${idPrefix}-intend_to_stay_province`" class="rbim-label">Province</label>
                    <input
                        :id="`${idPrefix}-intend_to_stay_province`"
                        v-model="form.intend_to_stay_province"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.intend_to_stay_province }"
                    >
                    <p v-if="errors.intend_to_stay_province" class="rbim-error">{{ errors.intend_to_stay_province }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    booleanSelectValue,
    emptyChildDeath,
    emptyFemaleDeath,
    parseBooleanSelect,
} from '@/utils/householdQuestions';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    lookups: {
        type: Object,
        default: () => ({}),
    },
    idPrefix: {
        type: String,
        default: 'household-questions',
    },
});

function isYes(value) {
    return value === true || value === 'true';
}

function onFemaleDiedChange(value) {
    props.form.female_hhm_died_past_12mos = parseBooleanSelect(value);

    if (isYes(props.form.female_hhm_died_past_12mos) && !(props.form.female_deaths ?? []).length) {
        props.form.female_deaths = [emptyFemaleDeath()];
    }

    if (!isYes(props.form.female_hhm_died_past_12mos)) {
        props.form.female_deaths = [];
    }
}

function onChildDiedChange(value) {
    props.form.child_hhm_died_past_12mos = parseBooleanSelect(value);

    if (isYes(props.form.child_hhm_died_past_12mos) && !(props.form.child_deaths ?? []).length) {
        props.form.child_deaths = [emptyChildDeath()];
    }

    if (!isYes(props.form.child_hhm_died_past_12mos)) {
        props.form.child_deaths = [];
    }
}

function addFemaleDeath() {
    props.form.female_deaths.push(emptyFemaleDeath());
}

function removeFemaleDeath(index) {
    props.form.female_deaths.splice(index, 1);
}

function addChildDeath() {
    props.form.child_deaths.push(emptyChildDeath());
}

function removeChildDeath(index) {
    props.form.child_deaths.splice(index, 1);
}
</script>
