import http from '@/services/http';

export async function fetchSettings() {
    const { data } = await http.get('/settings');

    return data.settings;
}

export async function updateSetting(settingId, settingValue) {
    const { data } = await http.patch(`/settings/${settingId}`, {
        setting_value: settingValue,
    });

    return data.setting;
}
