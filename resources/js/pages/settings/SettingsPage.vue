<template>
    <AppLayout title="System settings">
        <div class="space-y-6">
            <p class="text-sm text-slate-600">
                Values stored in the system settings table, including barangay profile, password rules, session timeout, and audit retention.
            </p>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading settings...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Setting</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Value</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                                <th v-if="canUpdateSettings" class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="setting in settings" :key="setting.setting_id">
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ formatSettingLabel(setting.setting_key) }}
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-if="canUpdateSettings && editingId === setting.setting_id"
                                        v-model="editValues[setting.setting_id]"
                                        :type="setting.data_type === 'int' ? 'number' : 'text'"
                                        class="rbim-input min-w-32 py-1.5"
                                    >
                                    <span v-else class="font-medium text-slate-900">
                                        {{ setting.typed_value ?? setting.setting_value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ setting.description }}</td>
                                <td v-if="canUpdateSettings" class="px-4 py-3 text-right">
                                    <div v-if="editingId === setting.setting_id" class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rbim-btn px-3 py-1.5 text-xs"
                                            :disabled="savingId === setting.setting_id"
                                            @click="saveSetting(setting)"
                                        >
                                            Save
                                        </button>
                                        <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" @click="editingId = null">
                                            Cancel
                                        </button>
                                    </div>
                                    <button
                                        v-else
                                        type="button"
                                        class="rbim-btn-outline px-3 py-1.5 text-xs"
                                        @click="startEdit(setting)"
                                    >
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!settings.length">
                                <td :colspan="canUpdateSettings ? 4 : 3" class="px-4 py-8 text-center text-slate-500">
                                    No settings found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as settingService from '@/services/settingService';
import { formatSettingLabel } from '@/utils/format';

const { hasPermission } = useAuth();

const settings = ref([]);
const loading = ref(false);
const savingId = ref(null);
const editingId = ref(null);
const editValues = reactive({});
const error = ref('');
const successMessage = ref('');

const canUpdateSettings = computed(() => hasPermission('setting.update'));

function startEdit(setting) {
    editingId.value = setting.setting_id;
    editValues[setting.setting_id] = setting.data_type === 'int'
        ? setting.typed_value
        : setting.setting_value;
    error.value = '';
    successMessage.value = '';
}

async function loadSettings() {
    loading.value = true;
    error.value = '';

    try {
        settings.value = await settingService.fetchSettings();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load settings.');
    } finally {
        loading.value = false;
    }
}

async function saveSetting(setting) {
    savingId.value = setting.setting_id;
    error.value = '';
    successMessage.value = '';

    try {
        const updated = await settingService.updateSetting(
            setting.setting_id,
            editValues[setting.setting_id],
        );
        settings.value = settings.value.map((row) => (
            row.setting_id === updated.setting_id ? updated : row
        ));
        editingId.value = null;
        successMessage.value = `Updated "${formatSettingLabel(setting.setting_key)}" successfully.`;
    } catch (err) {
        error.value = extractValidationErrors(err).setting_value
            ?? extractErrorMessage(err, 'Unable to update setting.');
    } finally {
        savingId.value = null;
    }
}

onMounted(loadSettings);
</script>
