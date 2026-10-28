import http from '@/services/http';

export async function fetchSettings() {
    const { data } = await http.get('/settings');

    return data.settings;
}

export async function updateSetting(settingId, settingValue, adminPassword) {
    const payload = {
        setting_value: settingValue,
    };

    if (adminPassword) {
        payload.admin_password = adminPassword;
    }

    const { data } = await http.patch(`/settings/${settingId}`, payload);

    return data.setting;
}

export async function uploadPunongBarangaySignature(file) {
    const formData = new FormData();
    formData.append('signature', file);

    const { data } = await http.post('/settings/punong-barangay-signature', formData, {
        transformRequest: [
            (value, headers) => {
                if (value instanceof FormData && headers) {
                    if (typeof headers.delete === 'function') {
                        headers.delete('Content-Type');
                    } else {
                        delete headers['Content-Type'];
                    }
                }

                return value;
            },
        ],
    });

    return data.setting;
}

export async function removePunongBarangaySignature() {
    const { data } = await http.delete('/settings/punong-barangay-signature');

    return data.setting;
}
