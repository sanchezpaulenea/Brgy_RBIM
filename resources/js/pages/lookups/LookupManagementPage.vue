<template>
    <AppLayout :title="config.layoutTitle">
        <div class="space-y-6">
            <PageTabs :tabs="tabs" />

            <LookupTableManager
                :key="route.meta.lookupKey"
                :item-label="config.itemLabel"
                :field-label="config.fieldLabel"
                :field-name="config.fieldName"
                :hint="config.hint"
                :assigned-message="config.assignedMessage"
                :item-label-plural="config.itemLabelPlural"
                :can-create="hasPermission(config.createPermission)"
                :can-delete="hasPermission(config.deletePermission)"
                :fetch-items="config.fetchItems"
                :create-item="config.createItem"
                :delete-item="config.deleteItem"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import LookupTableManager from '@/components/LookupTableManager.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import * as lookupService from '@/services/lookupService';

const PLACE_NAME_HINT = 'Must start with a letter and may only contain letters, numbers, spaces, periods, commas, hyphens, and apostrophes.';

const LOOKUPS = {
    street: {
        layoutTitle: 'Household Management',
        tabs: 'household',
        itemLabel: 'street',
        itemLabelPlural: 'streets',
        fieldLabel: 'Street name',
        fieldName: 'street_name',
        hint: PLACE_NAME_HINT,
        assignedMessage: 'This street is assigned to one or more households and cannot be deleted.',
        createPermission: 'street.create',
        deletePermission: 'street.delete',
        fetchItems: lookupService.fetchStreets,
        createItem: (name) => lookupService.createStreet({ street_name: name }),
        deleteItem: lookupService.deleteStreet,
    },
    nationality: {
        layoutTitle: 'Resident Management',
        tabs: 'resident',
        itemLabel: 'nationality',
        itemLabelPlural: 'nationalities',
        fieldLabel: 'Nationality',
        fieldName: 'nationality',
        hint: PLACE_NAME_HINT,
        assignedMessage: 'This nationality is assigned to one or more residents and cannot be deleted.',
        createPermission: 'nationality.create',
        deletePermission: 'nationality.delete',
        fetchItems: lookupService.fetchNationalities,
        createItem: (name) => lookupService.createNationality({ nationality: name }),
        deleteItem: lookupService.deleteNationality,
    },
    ethnicity: {
        layoutTitle: 'Resident Management',
        tabs: 'resident',
        itemLabel: 'ethnicity',
        itemLabelPlural: 'ethnicities',
        fieldLabel: 'Ethnicity',
        fieldName: 'ethnicity',
        hint: PLACE_NAME_HINT,
        assignedMessage: 'This ethnicity is assigned to one or more residents and cannot be deleted.',
        createPermission: 'ethnicity.create',
        deletePermission: 'ethnicity.delete',
        fetchItems: lookupService.fetchEthnicities,
        createItem: (name) => lookupService.createEthnicity({ ethnicity: name }),
        deleteItem: lookupService.deleteEthnicity,
    },
    religion: {
        layoutTitle: 'Resident Management',
        tabs: 'resident',
        itemLabel: 'religion',
        itemLabelPlural: 'religions',
        fieldLabel: 'Religion',
        fieldName: 'religion',
        hint: PLACE_NAME_HINT,
        assignedMessage: 'This religion is assigned to one or more residents and cannot be deleted.',
        createPermission: 'religion.create',
        deletePermission: 'religion.delete',
        fetchItems: lookupService.fetchReligions,
        createItem: (name) => lookupService.createReligion({ religion: name }),
        deleteItem: lookupService.deleteReligion,
    },
};

const route = useRoute();
const { hasPermission } = useAuth();
const { householdTabs, residentTabs } = useSectionTabs();

const config = computed(() => LOOKUPS[route.meta.lookupKey] ?? LOOKUPS.street);
const tabs = computed(() => (
    config.value.tabs === 'resident' ? residentTabs.value : householdTabs.value
));
</script>
