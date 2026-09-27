<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import BaseModal from '@/Components/base/BaseModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import CompanyTeamSection from '@/Components/company-team/CompanyTeamSection.vue';
import { useAuthStore } from '@/stores/auth';
import { useTranslate } from '@/composables/useTranslate';
import { computed, ref, watch } from 'vue';

const { t } = useTranslate();
const authStore = useAuthStore(); 
const page = usePage();

const props = defineProps({
    timezoneOptions: {
        type: Object,
        default: () => ({}),
    },
    canDeactivateAccount: {
        type: Boolean,
        default: false,
    },
    companyTeam: {
        type: Object,
        default: null,
    },
});

const availableLanguages = ['en', 'fr'];
const supportedTimezoneIds = Object.keys(props.timezoneOptions);
const savedTimezone = authStore.user.timezone ?? 'UTC';
const selectedTimezone = supportedTimezoneIds.includes(savedTimezone) ? savedTimezone : 'UTC';

const personalInfo = useForm({
    name: authStore.userName,
    email: authStore.user.email,
    phone: authStore.user.phone ?? '',
});

const companyName = authStore.user.company?.name ?? '';

const notifications = useForm({
    email: authStore.user.email_notifications ?? true,
    sms: authStore.user.sms_notifications ?? false,
    missionAlerts: authStore.user.mission_alerts ?? true,
    language: authStore.user.language ?? 'en',
    timezone: selectedTimezone,
});

const showDeactivationModal = ref(false);
const deactivationForm = useForm({
    password: '',
});

const showPasswordForm = ref(false);
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true },
);

function savePersonalInfo() {
    personalInfo.put(route('settings.personal.update'), {
        preserveScroll: true,
    });
}

function saveNotifications() {
    notifications.post(route('settings.notifications.update'), {
        preserveScroll: true,
    });
}

function showPasswordUpdateForm() {
    passwordForm.clearErrors();
    passwordForm.reset();
    showPasswordForm.value = true;
}

function hidePasswordUpdateForm() {
    showPasswordForm.value = false;
    passwordForm.clearErrors();
    passwordForm.reset();
}

function updatePassword() {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: hidePasswordUpdateForm,
        onError: () => {
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }

            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
        },
    });
}

function openDeactivationModal() {
    deactivationForm.clearErrors();
    deactivationForm.reset();
    showDeactivationModal.value = true;
}

function closeDeactivationModal() {
    showDeactivationModal.value = false;
    deactivationForm.clearErrors();
    deactivationForm.reset();
}

function deactivateAccount() {
    deactivationForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: closeDeactivationModal,
        onFinish: () => deactivationForm.reset('password'),
    });
}
</script>

<template>
    <Head :title="t('settings_page.title')" />

    <SidebarLayout>
        <BaseToast
            :key="'success-' + toastKey"
            :message="flashSuccess"
            type="success"
        />

        <BaseToast
            :key="'error-' + toastKey"
            :message="flashError"
            type="error"
        />

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
                            <p v-if="personalInfo.errors.name" class="text-red-600 text-sm">
                                {{ personalInfo.errors.name }}
                            </p>
                        </div>
                        <div class="form-field">
                            <label>{{ t('settings_page.personal.email') }}</label>
                            <input v-model="personalInfo.email" />
                            <p v-if="personalInfo.errors.email" class="text-red-600 text-sm">
                                {{ personalInfo.errors.email }}
                            </p>
                        </div>
                        <div class="form-field">
                            <label>{{ t('settings_page.personal.phone') }}</label>
                            <input v-model="personalInfo.phone" />
                            <p v-if="personalInfo.errors.phone" class="text-red-600 text-sm">
                                {{ personalInfo.errors.phone }}
                            </p>
                        </div>
                        <div v-if="companyName" class="form-field">
                            <label>{{ t('settings_page.personal.company') }}</label>
                            <input :value="companyName" disabled class="disabled-field" />
                        </div>
                    </div>
                    <button
                        class="btn-secondary mt-4"
                        :disabled="personalInfo.processing"
                        @click="savePersonalInfo"
                    >
                        {{ t('settings_page.personal.save_changes') }}
                    </button>
                </div>
            </div>

            <!-- Password & Security -->
            <div class="card">
                <h3 class="card-title">{{ t('settings_page.security.title') }}</h3>
                <p class="card-subtitle">{{ t('settings_page.security.subtitle') }}</p>
                <div class="card-body">
                    <button
                        v-if="!showPasswordForm"
                        class="btn-thirdary"
                        @click="showPasswordUpdateForm"
                    >
                        {{ t('settings_page.security.change_password') }}
                    </button>

                    <form v-else class="password-form" @submit.prevent="updatePassword">
                        <div class="form-field">
                            <label for="current-password">
                                {{ t('settings_page.security.current_password') }}
                            </label>
                            <input
                                id="current-password"
                                v-model="passwordForm.current_password"
                                type="password"
                                autocomplete="current-password"
                            />
                            <p v-if="passwordForm.errors.current_password" class="text-red-600 text-sm">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>

                        <div class="form-field">
                            <label for="new-password">
                                {{ t('settings_page.security.new_password') }}
                            </label>
                            <input
                                id="new-password"
                                v-model="passwordForm.password"
                                type="password"
                                autocomplete="new-password"
                            />
                            <p v-if="passwordForm.errors.password" class="text-red-600 text-sm">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>

                        <div class="form-field">
                            <label for="new-password-confirmation">
                                {{ t('settings_page.security.confirm_new_password') }}
                            </label>
                            <input
                                id="new-password-confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                            />
                            <p v-if="passwordForm.errors.password_confirmation" class="text-red-600 text-sm">
                                {{ passwordForm.errors.password_confirmation }}
                            </p>
                        </div>

                        <div class="password-actions">
                            <button
                                class="btn-secondary"
                                :disabled="passwordForm.processing"
                                type="submit"
                            >
                                {{ t('settings_page.security.update_password') }}
                            </button>
                            <button
                                class="btn-thirdary"
                                type="button"
                                @click="hidePasswordUpdateForm"
                            >
                                {{ t('settings_page.security.hide') }}
                            </button>
                        </div>
                    </form>
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
                            <option
                                v-for="(labelKey, timezone) in timezoneOptions"
                                :key="timezone"
                                :value="timezone"
                            >
                                {{ t(`settings_page.notifications.timezone_options.${labelKey}`) }}
                            </option>
                        </select>
                    </div>

                </div>
                    <button
                        class="btn-secondary mt-2"
                        :disabled="notifications.processing"
                        @click="saveNotifications"
                    >
                        {{ t('settings_page.notifications.save') }}
                    </button>
                </div>
            </div>

            <CompanyTeamSection
                v-if="companyTeam"
                :team="companyTeam"
            />

            <!-- Danger Zone -->
            <div class="card border-red-400">
                <h3 class="card-title text-red-600">{{ t('settings_page.danger_zone.title') }}</h3>
                <p class="card-subtitle">{{ t('settings_page.danger_zone.subtitle') }}</p>
                <div class="card-body">
                    <template v-if="canDeactivateAccount">
                        <button @click="openDeactivationModal" class="btn-danger">
                            {{ t('settings_page.danger_zone.deactivate_account') }}
                        </button>
                    </template>
                    <p v-else class="setting-desc">
                        {{ t('settings_page.danger_zone.owner_cannot_deactivate') }}
                    </p>
                </div>
            </div>
        </div>

        <BaseModal
            v-model="showDeactivationModal"
            :title="t('settings_page.danger_zone.deactivate_modal_title')"
            max-width="480px"
            @close="closeDeactivationModal"
        >
            <p>{{ t('settings_page.danger_zone.deactivate_modal_description') }}</p>
            <label for="deactivation-password">
                {{ t('settings_page.danger_zone.password') }}
            </label>
            <input
                id="deactivation-password"
                v-model="deactivationForm.password"
                type="password"
                :placeholder="t('settings_page.danger_zone.password')"
                @keyup.enter="deactivateAccount"
            />
            <p v-if="deactivationForm.errors.password" class="text-red-600 text-sm">
                {{ deactivationForm.errors.password }}
            </p>
            <p v-if="deactivationForm.errors.account" class="text-red-600 text-sm">
                {{ deactivationForm.errors.account }}
            </p>
            <template #footer>
                <button class="btn-secondary" @click="closeDeactivationModal">
                    {{ t('common.cancel') }}
                </button>
                <button
                    class="btn-danger"
                    :disabled="deactivationForm.processing"
                    @click="deactivateAccount"
                >
                    {{ t('settings_page.danger_zone.deactivate_account') }}
                </button>
            </template>
        </BaseModal>
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

.password-form {
    display: grid;
    gap: 16px;
}

.password-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
</style>
