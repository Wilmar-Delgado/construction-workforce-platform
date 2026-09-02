<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import { useTranslate } from '@/composables/useTranslate';
import { ref } from 'vue';
import axios from 'axios';

const { t } = useTranslate();
const authStore = useAuthStore(); 

const availableLanguages = ['en', 'fr'];

const personalInfo = ref({
    name: authStore.userName,
    email: authStore.user.email,
    phone: authStore.user.phone ?? '',
    company: authStore.user.company?.name ?? '',
});

const notifications = ref({
    email: authStore.user.notification_email ?? true,
    sms: authStore.user.notification_sms ?? false,
    missionAlerts: authStore.user.mission_alerts ?? true,
    language: authStore.user.language ?? 'en',
    timezone: authStore.user.timezone ?? 'UTC',
});

async function savePersonalInfo() {
    try {
        await axios.post('/settings/personal', personalInfo.value);
        alert(t('settings_page.personal.success'));
    } catch (error) {
        console.error('Error saving personal info:', error);
    }
}

async function saveNotifications() {
    try {
        await axios.post('/settings/notifications', notifications.value);
        alert(t('settings_page.notifications.success'));
    } catch (error) {
        console.error('Error saving notification preferences:', error);
    }
}

async function changePassword() {
    console.log('Redirect to change password');
}

async function deleteAccount() {
    if (confirm(t('settings_page.danger_zone.confirm'))) {
        try {
            await axios.post('/settings/delete-account');
            alert(t('settings_page.danger_zone.deleted_alert'));
            window.location.href = '/';
        } catch (error) {
            console.error('Error deleting account:', error);
        }
    }
}
</script>

<template>
    <Head :title="t('settings_page.title')" />

    <SidebarLayout>
        <template #title>
            {{ t('settings_page.title') }}
        </template>

        <div class="page-container space-y-8">
            <!-- Personal Information -->
            <div class="card">
                <h3 class="card-title">{{ t('settings_page.personal.title') }}</h3>
                <p class="card-subtitle">{{ t('settings_page.personal.subtitle') }}</p>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label>{{ t('settings_page.personal.name') }}</label>
                            <input v-model="personalInfo.name" />
                        </div>
                        <div class="form-field">
                            <label>{{ t('settings_page.personal.email') }}</label>
                            <input v-model="personalInfo.email" />
                        </div>
                        <div class="form-field">
                            <label>{{ t('settings_page.personal.phone') }}</label>
                            <input v-model="personalInfo.phone" />
                        </div>
                        <div v-if="personalInfo.company" class="form-field">
                            <label>{{ t('settings_page.personal.company') }}</label>
                            <input :value="personalInfo.company" disabled class="disabled-field" />
                        </div>
                    </div>
                    <button @click="savePersonalInfo" class="btn-secondary mt-4">{{ t('settings_page.personal.save_changes') }}</button>
                </div>
            </div>

            <!-- Password & Security -->
            <div class="card">
                <h3 class="card-title">{{ t('settings_page.security.title') }}</h3>
                <p class="card-subtitle">{{ t('settings_page.security.subtitle') }}</p>
                <div class="card-body">
                    <button @click="changePassword" class="btn-thirdary">{{ t('settings_page.security.change_password') }}</button>
                </div>
            </div>

            <!-- Notifications & Preferences -->
            <div class="card">
                <h3 class="card-title">{{ t('settings_page.notifications.title') }}</h3>
                <p class="card-subtitle">{{ t('settings_page.notifications.subtitle') }}</p>
                <div class="card-body space-y-3">
                    <div class="setting-row">
                        <div>
                            <p class="setting-title">
                                {{ t('settings_page.notifications.email') }}
                            </p>
                            <p class="setting-desc">
                                {{ t('settings_page.notifications.email_description') }}
                            </p>
                        </div>

                        <input type="checkbox" v-model="notifications.email" />
                    </div>
                    <div class="setting-row">
                        <div>
                            <p class="setting-title">
                                {{ t('settings_page.notifications.sms') }}
                            </p>
                            <p class="setting-desc">
                                {{ t('settings_page.notifications.sms_description') }}
                            </p>
                        </div>

                        <input type="checkbox" v-model="notifications.sms" />
                    </div>
                    <div class="setting-row">
                        <div>
                            <p class="setting-title">
                                {{ t('settings_page.notifications.missions') }}
                            </p>
                            <p class="setting-desc">
                                {{ t('settings_page.notifications.missions_description') }}
                            </p>
                        </div>
                        <input type="checkbox" v-model="notifications.missionAlerts" />
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                    <!-- Language -->
                    <div class="space-y-1">
                        <label>
                            {{ t('settings_page.notifications.language') }}
                        </label>
                        <select v-model="notifications.language">
                            <option v-for="lang in availableLanguages" :key="lang" :value="lang">
                                {{ t(`settings_page.common.languages.${lang}`) }}
                            </option>
                        </select>
                    </div>
                    <!-- Timezone -->
                    <div class="space-y-1">
                        <label>
                            {{ t('settings_page.notifications.timezone') }}
                        </label>
                        <select v-model="notifications.timezone">
                            <option value="Europe/Paris">{{ t('settings_page.notifications.timezone_options.paris') }}</option>
                            <option value="UTC">{{ t('settings_page.notifications.timezone_options.utc') }}</option>
                        </select>
                    </div>

                </div>
                    <button @click="saveNotifications" class="btn-secondary mt-2">{{ t('settings_page.notifications.save') }}</button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-red-400">
                <h3 class="card-title text-red-600">{{ t('settings_page.danger_zone.title') }}</h3>
                <p class="card-subtitle">{{ t('settings_page.danger_zone.subtitle') }}</p>
                <div class="card-body">
                    <button @click="deleteAccount" class="btn-danger">{{ t('settings_page.danger_zone.delete_account') }}</button>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>

<style scoped>
/* Cards */
.card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.card-title {
    font-weight: 600;
    font-size: 1.25rem;
}

.card-subtitle {
    color: #6b7280;
    margin-bottom: 1rem;
}

/* Inputs */

.card-body input:not([type="checkbox"]),
.card-body select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
}

.card-body input:focus,
.card-body select:focus {
    outline: none;
    border-color: #111827;
}

.disabled-field {
    background: #f3f4f6;
    cursor: not-allowed;
}

.setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.setting-title {
    font-weight: 500;
    font-size: 14px;
}

.setting-desc {
    font-size: 12px;
    color: #6b7280;
}
</style>
