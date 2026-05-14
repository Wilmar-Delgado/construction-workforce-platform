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

    return new Date(date).toLocaleDateString('en-CA', {
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

    return `${diff} day${diff !== 1 ? 's' : ''}`;
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
    <Head title="Find Missions" />

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
                                {{ mission.job_type.charAt(0).toUpperCase() + mission.job_type.slice(1).replace('_', ' ') }}
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

                            <strong>${{ mission.hourly_rate ?? '--' }} {{ t('find_missions_page.mission_card.per_hour') }}</strong>
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
                            <span>{{ selectedMission?.hourly_rate ?? '--' }}/hr</span>
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
                                {{ worker.name }} ({{ worker.job.charAt(0).toUpperCase() + worker.job.slice(1).replace('_', ' ') }})
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