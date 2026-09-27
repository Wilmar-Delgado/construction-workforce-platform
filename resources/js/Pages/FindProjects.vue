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
    projects: Object,
    locations: Array,
    filters: Object,
    workers: Array,
    existingRequests: Array,
});

/* ============================= */
/* STATE */
/* ============================= */
const pagination = computed(() => props.projects);

const {
    search,
    job,
    location,
    apply
} = useFilters('/find-projects', props.filters);

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
const selectedProject = ref(null);

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
        selectedProject.value = null;
    }
});

const filteredWorkers = computed(() => {
    if (!selectedProject.value) return [];

    return props.workers.filter(w =>
        w.job === selectedProject.value.job_type
    );
});

function workerAlreadyRequested(workerId, projectId = selectedProject.value?.id) {
    if (!projectId) return false;

    return props.existingRequests.some((request) =>
        String(request.project_id) === String(projectId)
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

function selfEmployedRequestForProject(project) {
    if (!isSelfEmployed.value || !selfEmployedWorker.value) {
        return null;
    }

    return props.existingRequests.find((request) =>
        String(request.project_id) === String(project.id)
        && String(request.worker_profile_id) === String(selfEmployedWorker.value.id)
    ) ?? null;
}

function selfEmployedRequestLabel(project) {
    const request = selfEmployedRequestForProject(project);

    if (!request) {
        return '';
    }

    if (request.status === 'pending') {
        return request.type === 'invite'
            ? t('find_projects_page.project_card.invited')
            : t('find_projects_page.project_card.applied');
    }

    return t(`common.statuses.${request.status}`);
}

function openRequestModal(project) {
    resetRequestForm();
    selectedProject.value = project;

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

function projectDuration(project) {
    if (!project.start_date || !project.end_date) return '-';

    const diff = calendarDayDifference(project.start_date, project.end_date);

    return t(
        diff === 1
            ? 'find_projects_page.project_card.duration_day'
            : 'find_projects_page.project_card.duration_days',
        { count: diff }
    );
}

function staffingProgress(project) {
    return t('find_projects_page.project_card.capacity', {
        committed: project.committed_worker_count ?? 0,
        required: project.workers ?? 0,
    });
}

function submitRequest() {
    form.post(route('request-project.store', selectedProject.value.id), {
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
    <Head :title="t('find_projects_page.title')" />

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
            {{ t('find_projects_page.title') }}
        </template>

        <div class="page-container-lg">
            <!-- Header -->
            <div class="page-header">
                <h2>{{ t('find_projects_page.subtitle') }}</h2>
            </div>

            <!-- SEARCH + FILTER -->
            <BaseFilters
                :search="search"
                :job="job"
                :location="location"
                :jobs="jobOptions"
                :locations="locations"
                :showLocation="true"
                translation-namespace="find_projects_page"
                :t="t"

                @update:search="val => search = val"
                @update:job="val => job = val"
                @update:location="val => location = val"
                @change="apply"
            />

            <p class="results-count">
                {{ projects.total }} {{ t('find_projects_page.projects_found') }}
            </p>

            <!-- PROJECTS LIST -->
            <div class="projects-list">
                <div
                    v-for="project in projects.data"
                    :key="project.id"
                    class="project-card"
                >
                    <!-- TOP -->
                    <div class="project-header">
                        <div>
                            <h3 class="project-title">{{ project.title }}</h3>

                           <div class="project-subtitle">
                                <div class="meta-inline">
                                    <Building2 class="mini-icon" />
                                    <span>{{ project.hiring_company?.name }}</span>
                                </div>

                                <div class="meta-inline">
                                    <MapPin class="mini-icon" />
                                    <span>{{ project.city }}, {{ project.country }}</span>
                                </div>

                                <!-- <div class="meta-inline">
                                    <Briefcase class="mini-icon" />
                                    <span>{{ project.job_type.charAt(0).toUpperCase() + project.job_type.slice(1).replace('_', ' ') }}</span>
                                </div> -->
                            </div>
                        </div>

                        <div class="job-wrapper">
                            <span class="job-tag">
                                {{ t(`profiles_page.jobs.${project.job_type}`) }}
                            </span>
                        </div>
                    </div>

                    <!-- STATS -->
                    <div class="project-stats">
                        <div class="stat-box">
                            <div class="stat-top">
                                <CalendarDays class="mini-icon" />
                                <small>{{ t('find_projects_page.project_card.duration') }}</small>
                            </div>

                            <strong>{{ projectDuration(project) }}</strong>
                        </div>

                        <div class="stat-box">
                            <div class="stat-top">
                                <DollarSign class="mini-icon" />
                                <small>{{ t('find_projects_page.project_card.rate') }}</small>
                            </div>

                            <strong>${{ project.hourly_rate ?? '--' }} {{ t('common.per_hour') }}</strong>
                        </div>

                        <div class="stat-box">
                            <div class="stat-top">
                                <Clock3 class="mini-icon" />
                                <small>{{ t('find_projects_page.project_card.starts') }}</small>
                            </div>

                            <strong>{{ formatDate(project.start_date) }}</strong>
                        </div>

                        <div class="stat-box">
                            <div class="stat-top">
                                <Users class="mini-icon" />
                                <small>{{ t('find_projects_page.project_card.workers') }}</small>
                            </div>

                            <strong>{{ staffingProgress(project) }}</strong>
                            <span class="stat-secondary">
                                {{ t('find_projects_page.project_card.remaining_capacity', { count: project.remaining_capacity }) }}
                            </span>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <p class="project-description">
                        {{ project.description }}
                    </p>

                    <!-- REQUIREMENTS -->
                    <div v-if="project.requirements?.length" class="project-requirements">
                        <p class="requirements-label">
                            {{ t('find_projects_page.project_card.requirements') }}:
                        </p>

                        <div class="requirements-tags">
                            <span
                                v-for="req in project.requirements"
                                :key="req.id"
                                class="requirement-tag"
                            >
                                {{ req.name }}
                            </span>
                        </div>
                    </div>

                        <!-- FOOTER -->
                        <div class="project-footer">
                        <small>
                            {{ t('find_projects_page.project_card.posted_by') }} {{ project.hiring_company?.name }} <span>• {{ formatTimestamp(project.created_at, { year: 'numeric', month: 'short', day: 'numeric' }) }}</span>
                        </small>

                        <span
                            v-if="selfEmployedRequestForProject(project)"
                            class="btn-secondary project-request-status"
                        >
                            {{ selfEmployedRequestLabel(project) }}
                        </span>

                        <span
                            v-else-if="selfEmployedNeedsActiveProfile"
                            class="btn-secondary project-request-status"
                        >
                            {{ t('find_projects_page.project_card.active_profile_required') }}
                        </span>

                        <button
                            v-else-if="can('apply_to_projects')"
                            class="btn-secondary"
                            @click="openRequestModal(project)"
                        >
                            <Send class="btn-icon" />
                            {{ t('find_projects_page.project_card.request_join') }}
                        </button>
                    </div>
                </div>
            </div>

            <BasePagination :links="pagination.links" />

            <BaseModal
                v-model="showRequestModal"
                :title="t('find_projects_page.request_modal.title')"
            >
                <form id="request-form" @submit.prevent="submitRequest">
                    
                    <!-- Project Summary -->
                    <div class="request-project">
                        <h3><strong>{{ selectedProject?.title }}</strong></h3>
                        <!-- <p><Building2 class="mini-icon" />{{ selectedProject?.hiring_company?.name }}</p> -->
                        <div class="meta-inline">
                            <Building2 class="mini-icon" />
                            <span>{{ selectedProject?.hiring_company?.name }}</span>
                        </div>
                        <div class="meta-inline">
                            <CalendarDays class="mini-icon" />
                            <span>
                                {{ formatDate(selectedProject?.start_date) }} -
                                {{ formatDate(selectedProject?.end_date) }}
                            </span>
                        </div>
                        <div class="meta-inline">
                            <DollarSign class="mini-icon" />
                            <span>{{ selectedProject?.hourly_rate ?? '--' }} {{ t('common.per_hour') }}</span>
                        </div>
                    </div>

                    <!-- Worker Selection -->
                    <div class="form-group">
                        <label>{{ t('find_projects_page.request_modal.select_worker') }}</label>

                        <!-- Company -->
                        <select
                            v-if="authStore.user.company"
                            v-model="form.worker_profile_id"
                        >
                            <option disabled value="">
                                {{ t('find_projects_page.request_modal.worker') }}
                            </option>

                            <option
                                v-for="worker in filteredWorkers"
                                :key="worker.id"
                                :value="worker.id"
                                :disabled="workerAlreadyRequested(worker.id)"
                            >
                                {{ worker.name }} ({{ t(`profiles_page.jobs.${worker.job}`) }}){{ workerAlreadyRequested(worker.id) ? ` — ${t('find_projects_page.request_modal.already_requested')}` : '' }}
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
                            {{ t('find_projects_page.request_modal.no_matching_workers') }}
                        </p>

                        <p v-if="form.errors.worker_profile_id" class="error">
                            {{ form.errors.worker_profile_id }}
                        </p>

                        <p v-else-if="selectedWorkerAlreadyRequested" class="error">
                            {{ t('find_projects_page.request_modal.already_requested_for_project') }}
                        </p>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label>{{ t('find_projects_page.request_modal.message') }}</label>
                        <textarea
                            v-model="form.message"
                            rows="3"
                        />
                    </div>

                    <!-- Info box -->
                    <div class="info-box">
                        <Info class="mini-icon" /> 
                        <span>{{ t('find_projects_page.request_modal.info') }}</span>
                    </div>
                </form>

                <template #footer>
                    <button
                        type="button"
                        class="btn-thirdary"
                        @click="showRequestModal = false"
                    >
                        {{ t('find_projects_page.request_modal.cancel') }}
                    </button>

                    <button
                        type="submit"
                        form="request-form"
                        class="btn-primary"
                        :disabled="form.processing || !form.worker_profile_id || selectedWorkerAlreadyRequested"
                    >
                        <Send class="btn-icon" />
                        {{ form.processing ? t('find_projects_page.request_modal.sending') : t('find_projects_page.request_modal.send') }}
                    </button>
                </template>
            </BaseModal>
        </div>
    </SidebarLayout>
</template>

<style scoped src="../../css/pages/find-projects.css"></style>
