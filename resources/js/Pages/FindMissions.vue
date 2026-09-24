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
import { useDateTime } from '@/composables/useDateTime';
import { useFilters } from '@/composables/useFilters';
import { usePermissions } from '@/composables/usePermissions';
import { jobOptions } from '@/constants/jobs';
import {
    Building2,
    MapPin,
    // Briefcase,
    CalendarDays,
    DollarSign,
    Clock3,
    Users,
    Send,
    Info
} from 'lucide-vue-next';

/* ============================= */
/* GLOBAL / PROPS */
/* ============================= */
const { t } = useTranslate();
const { can } = usePermissions();
const { calendarDayDifference, formatDateOnly, formatTimestamp } = useDateTime();
const page = usePage();
const authStore = useAuthStore();

const props = defineProps({
    missions: Object,
    locations: Array,
    filters: Object,
    workers: Array,
    existingRequests: Array,
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

function resetRequestForm() {
    form.reset();
    form.clearErrors();
}

watch(showRequestModal, (isOpen) => {
    if (!isOpen) {
        resetRequestForm();
        selectedMission.value = null;
    }
});

const filteredWorkers = computed(() => {
    if (!selectedMission.value) return [];

    return props.workers.filter(w =>
        w.job === selectedMission.value.job_type
    );
});

function workerAlreadyRequested(workerId, missionId = selectedMission.value?.id) {
    if (!missionId) return false;

    return props.existingRequests.some((request) =>
        String(request.mission_id) === String(missionId)
        && String(request.worker_profile_id) === String(workerId)
    );
}

const selectedWorkerAlreadyRequested = computed(() =>
    form.worker_profile_id !== ''
    && workerAlreadyRequested(form.worker_profile_id)
);

const isSelfEmployed = computed(() =>
    authStore.user.role?.name === 'self_employed'
    && !authStore.user.company
);

const selfEmployedWorker = computed(() =>
    props.workers.find((worker) => worker.user_id === authStore.user.id)
);

const selfEmployedNeedsActiveProfile = computed(() =>
    isSelfEmployed.value && !selfEmployedWorker.value
);

function selfEmployedRequestForMission(mission) {
    if (!isSelfEmployed.value || !selfEmployedWorker.value) {
        return null;
    }

    return props.existingRequests.find((request) =>
        String(request.mission_id) === String(mission.id)
        && String(request.worker_profile_id) === String(selfEmployedWorker.value.id)
    ) ?? null;
}

function selfEmployedRequestLabel(mission) {
    const request = selfEmployedRequestForMission(mission);

    if (!request) {
        return '';
    }

    if (request.status === 'pending') {
        return request.type === 'invite'
            ? t('find_missions_page.mission_card.invited')
            : t('find_missions_page.mission_card.applied');
    }

    return t(`common.statuses.${request.status}`);
}

function openRequestModal(mission) {
    resetRequestForm();
    selectedMission.value = mission;

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
    return formatDateOnly(date, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function missionDuration(mission) {
    if (!mission.start_date || !mission.end_date) return '-';

    const diff = calendarDayDifference(mission.start_date, mission.end_date);

    return t(
        diff === 1
            ? 'find_missions_page.mission_card.duration_day'
            : 'find_missions_page.mission_card.duration_days',
        { count: diff }
    );
}

function staffingProgress(mission) {
    return t('find_missions_page.mission_card.capacity', {
        committed: mission.committed_worker_count ?? 0,
        required: mission.workers ?? 0,
    });
}

function submitRequest() {
    form.post(route('request-mission.store', selectedMission.value.id), {
        onSuccess: () => {
            showRequestModal.value = false;
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

                        <div class="stat-box">
                            <div class="stat-top">
                                <Users class="mini-icon" />
                                <small>{{ t('find_missions_page.mission_card.workers') }}</small>
                            </div>

                            <strong>{{ staffingProgress(mission) }}</strong>
                            <span class="stat-secondary">
                                {{ t('find_missions_page.mission_card.remaining_capacity', { count: mission.remaining_capacity }) }}
                            </span>
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
                            {{ t('find_missions_page.mission_card.posted_by') }} {{ mission.hiring_company?.name }} <span>• {{ formatTimestamp(mission.created_at, { year: 'numeric', month: 'short', day: 'numeric' }) }}</span>
                        </small>

                        <span
                            v-if="selfEmployedRequestForMission(mission)"
                            class="btn-secondary mission-request-status"
                        >
                            {{ selfEmployedRequestLabel(mission) }}
                        </span>

                        <span
                            v-else-if="selfEmployedNeedsActiveProfile"
                            class="btn-secondary mission-request-status"
                        >
                            {{ t('find_missions_page.mission_card.active_profile_required') }}
                        </span>

                        <button
                            v-else-if="can('apply_to_missions')"
                            class="btn-secondary"
                            @click="openRequestModal(mission)"
                        >
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
                                :disabled="workerAlreadyRequested(worker.id)"
                            >
                                {{ worker.name }} ({{ t(`profiles_page.jobs.${worker.job}`) }}){{ workerAlreadyRequested(worker.id) ? ` — ${t('find_missions_page.request_modal.already_requested')}` : '' }}
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

                        <p v-else-if="selectedWorkerAlreadyRequested" class="error">
                            {{ t('find_missions_page.request_modal.already_requested_for_mission') }}
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
                        :disabled="form.processing || !form.worker_profile_id || selectedWorkerAlreadyRequested"
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
