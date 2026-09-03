<script setup>
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import BaseFilters from '@/Components/base/BaseFilters.vue';
import BasePagination from '@/Components/base/BasePagination.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import { useTranslate } from '@/composables/useTranslate';
import { useFilters } from '@/composables/useFilters';
import { jobOptions } from '@/constants/jobs';
import {
    Building2,
    MapPin,
    // Briefcase,
    CalendarDays,
    DollarSign,
    Clock3,
    Send,
    Info
} from 'lucide-vue-next';

/* ============================= */
/* GLOBAL / PROPS */
/* ============================= */
const { t } = useTranslate();
const page = usePage();
const authStore = useAuthStore();

const props = defineProps({
    missions: Object,
    locations: Array,
    filters: Object,
    workers: Array,
    selectedMission: Object,
});

/* ============================= */
/* STATE */
/* ============================= */
const pagination = computed(() => props.missions);

const {
    search,
    job,
    location,
    apply
} = useFilters('/find-missions', props.filters);

let timeout;

watch(search, () => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        apply();
    }, 300);
});

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true }
);

const showRequestModal = ref(false);
const selectedMission = ref(null);
const selectedMissionDetails = ref(null);
const showMissionDetails = ref(false);

watch(
    () => props.selectedMission,
    (mission) => {
        if (mission) {
            selectedMissionDetails.value = mission;
            showMissionDetails.value = true;
        }
    },
    { immediate: true }
);

const form = useForm({
    worker_profile_id: '',
    message: '',
});

const filteredWorkers = computed(() => {
    if (!selectedMission.value) return [];

    return props.workers.filter(w =>
        w.job === selectedMission.value.job_type
    );
});

function openRequestModal(mission) {
    selectedMission.value = mission;

    form.reset();

    // Auto-select if self-employed
    if (!authStore.user.company) {
        const worker = props.workers.find(
            w => w.user_id === authStore.user.id
        );

        form.worker_profile_id = worker?.id ?? '';
    }

    showRequestModal.value = true;
}

function formatDate(date) {
    if (!date) return '';

    return new Date(date).toLocaleDateString(page.props.locale === 'fr' ? 'fr-CA' : 'en-CA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function missionDuration(mission) {
    if (!mission.start_date || !mission.end_date) return '-';

    const start = new Date(mission.start_date);
    const end = new Date(mission.end_date);

    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

    return t(
        diff === 1
            ? 'find_missions_page.mission_card.duration_day'
            : 'find_missions_page.mission_card.duration_days',
        { count: diff }
    );
}

function submitRequest() {
    form.post(route('request-mission.store', selectedMission.value.id), {
        onSuccess: () => {
            showRequestModal.value = false;
            form.reset();
        }
    });
}

/* ============================= */
/* ACTIONS */
/* ============================= */
</script>

<template>
    <Head :title="t('find_missions_page.title')" />

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
            {{ t('find_missions_page.title') }}
        </template>

        <div class="page-container-lg">
            <!-- Header -->
            <div class="page-header">
                <h2>{{ t('find_missions_page.subtitle') }}</h2>
            </div>

            <!-- SEARCH + FILTER -->
            <BaseFilters
                :search="search"
                :job="job"
                :location="location"
                :jobs="jobOptions"
                :locations="locations"
                :showLocation="true"
                :t="t"

                @update:search="val => search = val"
                @update:job="val => job = val"
                @update:location="val => location = val"
                @change="apply"
            />

            <p class="results-count">
                {{ missions.total }} {{ t('find_missions_page.missions_found') }}
            </p>

            <!-- MISSIONS LIST -->
            <div class="missions-list">
                <div
                    v-for="mission in missions.data"
                    :key="mission.id"
                    class="mission-card"
                >
                    <!-- TOP -->
                    <div class="mission-header">
                        <div>
                            <h3 class="mission-title">{{ mission.title }}</h3>

                           <div class="mission-subtitle">
                                <div class="meta-inline">
                                    <Building2 class="mini-icon" />
                                    <span>{{ mission.hiring_company?.name }}</span>
                                </div>

                                <div class="meta-inline">
                                    <MapPin class="mini-icon" />
                                    <span>{{ mission.city }}, {{ mission.country }}</span>
                                </div>

                                <!-- <div class="meta-inline">
                                    <Briefcase class="mini-icon" />
                                    <span>{{ mission.job_type.charAt(0).toUpperCase() + mission.job_type.slice(1).replace('_', ' ') }}</span>
                                </div> -->
                            </div>
                        </div>

                        <div class="job-wrapper">
                            <span class="job-tag">
                                {{ t(`profiles_page.jobs.${mission.job_type}`) }}
                            </span>
                        </div>
                    </div>

                    <!-- STATS -->
                    <div class="mission-stats">
                        <div class="stat-box">
                            <div class="stat-top">
                                <CalendarDays class="mini-icon" />
                                <small>{{ t('find_missions_page.mission_card.duration') }}</small>
                            </div>

                            <strong>{{ missionDuration(mission) }}</strong>
                        </div>

                        <div class="stat-box">
                            <div class="stat-top">
                                <DollarSign class="mini-icon" />
                                <small>{{ t('find_missions_page.mission_card.rate') }}</small>
                            </div>

                            <strong>${{ mission.hourly_rate ?? '--' }} {{ t('common.per_hour') }}</strong>
                        </div>

                        <div class="stat-box">
                            <div class="stat-top">
                                <Clock3 class="mini-icon" />
                                <small>{{ t('find_missions_page.mission_card.starts') }}</small>
                            </div>

                            <strong>{{ formatDate(mission.start_date) }}</strong>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <p class="mission-description">
                        {{ mission.description }}
                    </p>

                    <!-- REQUIREMENTS -->
                    <div v-if="mission.requirements?.length" class="mission-requirements">
                        <p class="requirements-label">
                            {{ t('find_missions_page.mission_card.requirements') }}:
                        </p>

                        <div class="requirements-tags">
                            <span
                                v-for="req in mission.requirements"
                                :key="req.id"
                                class="requirement-tag"
                            >
                                {{ req.name }}
                            </span>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="mission-footer">
                        <small>
                            {{ t('find_missions_page.mission_card.posted_by') }} {{ mission.hiring_company?.name }} <span>• {{ formatDate(mission.created_at) }}</span>
                        </small>

                        <button class="btn-secondary" @click="openRequestModal(mission)">
                            <Send class="btn-icon" />
                            {{ t('find_missions_page.mission_card.request_join') }}
                        </button>
                    </div>
                </div>
            </div>

            <BasePagination :links="pagination.links" />

            <BaseModal
                v-model="showMissionDetails"
                :title="t('find_missions_page.details_modal.title')"
                max-width="900px"
            >
                <div v-if="selectedMissionDetails">
                    <div class="request-mission">
                        <h3><strong>{{ selectedMissionDetails.title }}</strong></h3>

                        <div class="meta-inline">
                            <Building2 class="mini-icon" />
                            <span>{{ selectedMissionDetails.hiring_company?.name }}</span>
                        </div>

                        <div class="meta-inline">
                            <MapPin class="mini-icon" />
                            <span>
                                {{ selectedMissionDetails.city }},
                                {{ selectedMissionDetails.province }}
                            </span>
                        </div>

                        <div class="meta-inline">
                            <CalendarDays class="mini-icon" />
                            <span>
                                {{ formatDate(selectedMissionDetails.start_date) }} -
                                {{ formatDate(selectedMissionDetails.end_date) }}
                            </span>
                        </div>

                        <div class="meta-inline">
                            <DollarSign class="mini-icon" />
                            <span>
                                {{ selectedMissionDetails.hourly_rate ?? '--' }}
                                {{ t('common.per_hour') }}
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ t('find_missions_page.details_modal.trade') }}</label>
                        <p>{{ t(`profiles_page.jobs.${selectedMissionDetails.job_type}`) }}</p>
                    </div>

                    <div class="form-group">
                        <label>{{ t('find_missions_page.details_modal.description') }}</label>
                        <p>{{ selectedMissionDetails.description }}</p>
                    </div>

                    <div v-if="selectedMissionDetails.requirements?.length" class="form-group">
                        <label>{{ t('find_missions_page.details_modal.requirements') }}</label>
                        <div class="requirements-tags">
                            <span
                                v-for="requirement in selectedMissionDetails.requirements"
                                :key="requirement.id"
                                class="requirement-tag"
                            >
                                {{ requirement.name }}
                            </span>
                        </div>
                    </div>

                    <div v-if="selectedMissionDetails.operational_details" class="form-group">
                        <label>{{ t('find_missions_page.details_modal.operational_details') }}</label>

                        <p v-if="selectedMissionDetails.operational_details.site_name">
                            <strong>{{ t('find_missions_page.details_modal.site_name') }}:</strong>
                            {{ selectedMissionDetails.operational_details.site_name }}
                        </p>

                        <p v-if="selectedMissionDetails.operational_details.address_line_1">
                            <strong>{{ t('find_missions_page.details_modal.address') }}:</strong>
                            {{ selectedMissionDetails.operational_details.address_line_1 }}
                            <template v-if="selectedMissionDetails.operational_details.address_line_2">
                                , {{ selectedMissionDetails.operational_details.address_line_2 }}
                            </template>
                            <template v-if="selectedMissionDetails.operational_details.postal_code">
                                , {{ selectedMissionDetails.operational_details.postal_code }}
                            </template>
                        </p>

                        <p v-if="selectedMissionDetails.operational_details.directions">
                            <strong>{{ t('find_missions_page.details_modal.directions') }}:</strong>
                            {{ selectedMissionDetails.operational_details.directions }}
                        </p>

                        <p v-if="selectedMissionDetails.operational_details.contact_name || selectedMissionDetails.operational_details.contact_phone">
                            <strong>{{ t('find_missions_page.details_modal.contact') }}:</strong>
                            {{ selectedMissionDetails.operational_details.contact_name }}
                            <template v-if="selectedMissionDetails.operational_details.contact_phone">
                                — {{ selectedMissionDetails.operational_details.contact_phone }}
                            </template>
                        </p>
                    </div>
                </div>

                <template #footer>
                    <button class="btn-thirdary" @click="showMissionDetails = false">
                        {{ t('common.close') }}
                    </button>
                </template>
            </BaseModal>

            <BaseModal
                v-model="showRequestModal"
                :title="t('find_missions_page.request_modal.title')"
            >
                <form id="request-form" @submit.prevent="submitRequest">
                    
                    <!-- Mission Summary -->
                    <div class="request-mission">
                        <h3><strong>{{ selectedMission?.title }}</strong></h3>
                        <!-- <p><Building2 class="mini-icon" />{{ selectedMission?.hiring_company?.name }}</p> -->
                        <div class="meta-inline">
                            <Building2 class="mini-icon" />
                            <span>{{ selectedMission?.hiring_company?.name }}</span>
                        </div>
                        <div class="meta-inline">
                            <CalendarDays class="mini-icon" />
                            <span>
                                {{ formatDate(selectedMission?.start_date) }} -
                                {{ formatDate(selectedMission?.end_date) }}
                            </span>
                        </div>
                        <div class="meta-inline">
                            <DollarSign class="mini-icon" />
                            <span>{{ selectedMission?.hourly_rate ?? '--' }} {{ t('common.per_hour') }}</span>
                        </div>
                    </div>

                    <!-- Worker Selection -->
                    <div class="form-group">
                        <label>{{ t('find_missions_page.request_modal.select_worker') }}</label>

                        <!-- Company -->
                        <select
                            v-if="authStore.user.company"
                            v-model="form.worker_profile_id"
                        >
                            <option disabled value="">
                                {{ t('find_missions_page.request_modal.worker') }}
                            </option>

                            <option
                                v-for="worker in filteredWorkers"
                                :key="worker.id"
                                :value="worker.id"
                            >
                                {{ worker.name }} ({{ t(`profiles_page.jobs.${worker.job}`) }})
                            </option>
                        </select>

                        <!-- Self-employed -->
                        <input
                            v-else
                            type="text"
                            :value="authStore.user.name"
                            disabled
                        />

                        <p v-if="authStore.user.company && !filteredWorkers.length" class="empty-text">
                            {{ t('find_missions_page.request_modal.no_matching_workers') }}
                        </p>

                        <p v-if="form.errors.worker_profile_id" class="error">
                            {{ form.errors.worker_profile_id }}
                        </p>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label>{{ t('find_missions_page.request_modal.message') }}</label>
                        <textarea
                            v-model="form.message"
                            rows="3"
                        />
                    </div>

                    <!-- Info box -->
                    <div class="info-box">
                        <Info class="mini-icon" /> 
                        <span>{{ t('find_missions_page.request_modal.info') }}</span>
                    </div>
                </form>

                <template #footer>
                    <button
                        type="button"
                        class="btn-thirdary"
                        @click="showRequestModal = false"
                    >
                        {{ t('find_missions_page.request_modal.cancel') }}
                    </button>

                    <button
                        type="submit"
                        form="request-form"
                        class="btn-primary"
                        :disabled="form.processing || !form.worker_profile_id"
                    >
                        <Send class="btn-icon" />
                        {{ form.processing ? t('find_missions_page.request_modal.sending') : t('find_missions_page.request_modal.send') }}
                    </button>
                </template>
            </BaseModal>
        </div>
    </SidebarLayout>
</template>

<style scoped src="../../css/pages/find-missions.css"></style>
