<template>
    <FormSectionCard
        title="Informant and household head"
        help="Province, city, and barangay come from system settings. Street and house details identify this household."
    >
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="rbim-label" for="location_province">Province</label>
                <input
                    id="location_province"
                    :value="location.province"
                    type="text"
                    readonly
                    class="rbim-input"
                >
            </div>
            <div>
                <label class="rbim-label" for="location_city">City/Municipality</label>
                <input
                    id="location_city"
                    :value="location.city"
                    type="text"
                    readonly
                    class="rbim-input"
                >
            </div>
            <div>
                <label class="rbim-label" for="location_barangay">Barangay</label>
                <input
                    id="location_barangay"
                    :value="location.barangay"
                    type="text"
                    readonly
                    class="rbim-input"
                >
            </div>
            <LookupCombobox
                v-model="form.street_id"
                v-model:query="form.street_name"
                :options="streets"
                input-id="street_name"
                label="Street Name"
                placeholder="Search or type a street"
                required
                :can-create="canCreateStreet"
                :error="errors.street_id"
                :hint="canCreateStreet ? 'Choose an existing street or type a new name and press Enter.' : ''"
            />
            <div>
                <label class="rbim-label" for="house_lot">House Lot</label>
                <input
                    id="house_lot"
                    v-model="form.house_lot"
                    type="text"
                    maxlength="45"
                    placeholder="N/A if not applicable"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.house_lot }"
                >
            </div>
            <div>
                <label class="rbim-label" for="block_num">Block No.</label>
                <input
                    id="block_num"
                    v-model="form.block_num"
                    type="text"
                    maxlength="45"
                    placeholder="N/A if not applicable"
                    class="rbim-input"
                >
            </div>
            <div class="sm:col-span-2">
                <label class="rbim-label" for="unit_num">Floor/Room/Unit</label>
                <input
                    id="unit_num"
                    v-model="form.unit_num"
                    type="text"
                    maxlength="45"
                    placeholder="N/A if not applicable"
                    class="rbim-input"
                >
            </div>
            <div>
                <label class="rbim-label" for="clan_id">
                    Clan<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    id="clan_id"
                    v-model="form.clan_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.clan_id }"
                >
                    <option value="">Select clan</option>
                    <option v-for="clan in clans" :key="clan.id" :value="clan.id">{{ clan.label }}</option>
                </select>
                <p v-if="errors.clan_id" class="rbim-error">{{ errors.clan_id }}</p>
            </div>
        </div>

        <label class="mt-4 flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3">
            <input
                v-model="form.informant_is_head"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-400 text-brand focus:ring-brand"
            >
            <span class="text-sm font-medium text-slate-800">Informant is household head</span>
            <HelpTip text="Check this when the person answering the interview is also the household head. The head name is then copied from the informant." />
        </label>

        <fieldset class="mt-5">
            <legend class="mb-3 text-sm font-semibold text-slate-700">Respondent name</legend>
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="rbim-label" for="informant_last_name">
                        Last Name<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="informant_last_name"
                        v-model="form.informant.last_name"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors['informant.last_name'] }"
                    >
                    <p v-if="errors['informant.last_name']" class="rbim-error">{{ errors['informant.last_name'] }}</p>
                </div>
                <div>
                    <label class="rbim-label" for="informant_first_name">
                        First Name<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="informant_first_name"
                        v-model="form.informant.first_name"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors['informant.first_name'] }"
                    >
                    <p v-if="errors['informant.first_name']" class="rbim-error">{{ errors['informant.first_name'] }}</p>
                </div>
                <div>
                    <label class="rbim-label" for="informant_middle_name">Middle Name</label>
                    <input
                        id="informant_middle_name"
                        v-model="form.informant.middle_name"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                    >
                </div>
                <div>
                    <label class="rbim-label" for="informant_suffix">Suffix (optional)</label>
                    <input
                        id="informant_suffix"
                        v-model="form.informant.suffix"
                        type="text"
                        maxlength="45"
                        placeholder="Jr., Sr., III"
                        class="rbim-input"
                    >
                </div>
            </div>
        </fieldset>

        <fieldset class="mt-5" :disabled="form.informant_is_head">
            <legend class="mb-3 text-sm font-semibold text-slate-700">Household head name</legend>
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="rbim-label" for="head_last_name">
                        Last Name<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="head_last_name"
                        v-model="form.head.last_name"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors['head.last_name'] }"
                    >
                    <p v-if="errors['head.last_name']" class="rbim-error">{{ errors['head.last_name'] }}</p>
                </div>
                <div>
                    <label class="rbim-label" for="head_first_name">
                        First Name<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="head_first_name"
                        v-model="form.head.first_name"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors['head.first_name'] }"
                    >
                    <p v-if="errors['head.first_name']" class="rbim-error">{{ errors['head.first_name'] }}</p>
                </div>
                <div>
                    <label class="rbim-label" for="head_middle_name">Middle Name</label>
                    <input
                        id="head_middle_name"
                        v-model="form.head.middle_name"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                    >
                </div>
                <div>
                    <label class="rbim-label" for="head_suffix">Suffix (optional)</label>
                    <input
                        id="head_suffix"
                        v-model="form.head.suffix"
                        type="text"
                        maxlength="45"
                        placeholder="Jr., Sr., III"
                        class="rbim-input"
                    >
                </div>
            </div>
        </fieldset>

        <div class="mt-5 max-w-xs">
            <label class="rbim-label" for="total_household_members">Total Household Members</label>
            <input
                id="total_household_members"
                :value="totalMembers"
                type="text"
                readonly
                class="rbim-input max-w-24"
            >
            <p class="rbim-hint">Updates when member rows are added or removed below.</p>
        </div>
    </FormSectionCard>
</template>

<script setup>
import FormSectionCard from '@/components/FormSectionCard.vue';
import HelpTip from '@/components/HelpTip.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';

const form = defineModel({ type: Object, required: true });

defineProps({
    location: {
        type: Object,
        default: () => ({ province: '', city: '', barangay: '' }),
    },
    streets: {
        type: Array,
        default: () => [],
    },
    clans: {
        type: Array,
        default: () => [],
    },
    canCreateStreet: {
        type: Boolean,
        default: false,
    },
    totalMembers: {
        type: Number,
        default: 1,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});
</script>
