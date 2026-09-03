<script setup>
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Briefcase, DollarSign, Star, Eye, Send } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import BasePagination from '@/Components/base/BasePagination.vue';
import BaseFilters from '@/Components/base/BaseFilters.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import { useTranslate } from '@/composables/useTranslate';
import { useFilters } from '@/composables/useFilters';

/* ============================= */
/* GLOBAL / PROPS */
/* ============================= */
const { t } = useTranslate();
const page = usePage();
const authStore = useAuthStore();

const pagination = computed(() => page.props.workers);

const props = defineProps({
    workers: Object,
    jobs: Array,
    filters: Object,
    missions: Array,
    existingRequests: Array,
    selectedWorker: Object,
});

/* ============================= */
/* STATE */
/* ============================= */
const companyName = authStore.user.company?.name ?? '';

const { search, job, apply } = useFilters('/find-workers', props.filters);

let timeout;

watch(search, () => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        apply();
    }, 300);
});

watch(job, () => {
    apply();
});

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true }
);

const selectedWorker = ref(null);
const showProfile = ref(false);
const showRequest = ref(false);

watch(
    () => props.selectedWorker,
    (worker) => {
        if (worker) {
            selectedWorker.value = worker;
            showProfile.value = true;
        }
    },
    { immediate: true }
);

const requestForm = useForm({
    mission_id: '',
    message: '',
});

function ratingDisplay(worker) {
    if (worker.rating === null || worker.rating === undefined) {
        return t('common.not_available');
    }

    return `${Number(worker.rating).toFixed(1)} (${worker.ratings_count})`;
}

const alreadyRequested = (workerId, missionId) => {
    return props.existingRequests.some(r =>
        r.worker_profile_id === workerId &&
        r.mission_id === missionId
    );
};

/* ============================= */
/* ACTIONS */
/* ============================= */
function openProfile(worker) {
    selectedWorker.value = worker;
    showProfile.value = true;
}

function openRequest(worker) {
    selectedWorker.value = worker;

    requestForm.reset();
    requestForm.company = companyName;

    showRequest.value = true;
}

function submitRequest() {
    requestForm.post(route('request-worker.store', selectedWorker.value.id), {
        onSuccess: () => {
            showRequest.value = false;
            requestForm.reset();
            // show toast here
        }
    });
}
</script>

<template>
    <Head :title="t('find_workers_page.title')" />

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
            {{ t('find_workers_page.title') }}
        </template>

        <div class="page-container-lg">
            <!-- Header -->
            <div class="page-header">
                <h2>{{ t('find_workers_page.subtitle') }}</h2>
            </div>
            
            <!-- SEARCH + FILTER -->
            <BaseFilters
                :search="search"
                :job="job"
                :jobs="jobs"
                :t="t"

                @update:search="val => search = val"
                @update:job="val => job = val"
                @change="apply"
            />

            <p class="results-count">
                {{ workers.total }} {{ t('find_workers_page.workers_found') }}
            </p>

            <!-- WORKERS GRID -->
            <div class="workers-grid">
                <div v-for="worker in workers.data" :key="worker.id" class="worker-card">
                    <!-- HEADER -->
                    <div class="card-header">
                        <h3 class="worker-name">{{ worker.name }}</h3>
                        <div class="rating">
                            <Star class="icon" /> {{ ratingDisplay(worker) }}
                        </div>
                    </div>

                    <p class="job">{{ t(`profiles_page.jobs.${worker.job}`) }}</p>

                    <div class="card-body">
                        <!-- EXPERIENCE + RATE -->
                        <div class="card-meta">
                            <div class="meta-item">
                                <Briefcase class="icon" />
                                <span>{{ t('find_workers_page.experience_years_short', { count: worker.years_experience }) }}</span>
                            </div>
                            <div class="meta-item">
                                <DollarSign class="icon" />
                                <span>{{ worker.hourly_rate }} {{ t('common.per_hour') }}</span>
                            </div>
                        </div>

                        <div class="company-wrapper">
                            <span class="company-tag" :class="{ 'self-employed': !worker.company }">
                                {{ worker.company?.name || t('common.self_employed') }}
                            </span>
                        </div>

                        <!-- CERTIFICATIONS -->
                        <div class="fixed-section">
                            <p class="section-label">{{ t('find_workers_page.certifications') }}</p>
                            <div class="tag-row">
                                <template v-if="worker.certifications.length">
                                    <span v-for="cert in worker.certifications.slice(0,2)" :key="cert.id" class="tag cert">
                                        {{ cert.name }}
                                    </span>

                                    <span v-if="worker.certifications.length > 2" class="tag more">
                                        +{{ worker.certifications.length - 2 }}
                                    </span>
                                </template>

                                <span v-else class="empty-tags">
                                    {{ t('find_workers_page.no_certifications') }}
                                </span>
                            </div>
                        </div>

                        <!-- SKILLS -->
                        <div class="fixed-section">
                            <p class="section-label">{{ t('find_workers_page.top_skills') }}</p>
                            <div class="tag-row">
                                <span v-for="skill in worker.skills.slice(0,2)" :key="skill.id" class="tag">
                                    {{ skill.name }}
                                </span>

                                <span v-if="worker.skills.length > 2" class="tag more">
                                    +{{ worker.skills.length - 2 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="card-actions">
                        <button @click="openProfile(worker)" class="btn-thirdary">
                            <Eye class="icon" />{{ t('find_workers_page.view_profile') }}
                        </button>

                        <button @click="openRequest(worker)" class="btn-secondary">
                            <Send class="icon" />{{ t('find_workers_page.request') }}
                        </button>
                    </div>
                </div>
            </div>
            <BasePagination :links="pagination.links" />

            <!-- PROFILE MODAL -->
            <BaseModal v-model="showProfile" :title="t('find_workers_page.profile_modal.title')">
                <!-- TOP SECTION -->
                <div class="profile-top">
                    <div class="avatar">
                        {{ selectedWorker.name.charAt(0) }}
                    </div>

                    <div class="profile-info">
                        <h2>{{ selectedWorker.name }}</h2>
                        <p class="job">{{ t(`profiles_page.jobs.${selectedWorker.job}`) }}</p>

                        <span class="company-tag" :class="{ 'self-employed': !selectedWorker.company }">
                            {{ selectedWorker.company?.name || t('common.self_employed') }}
                        </span>
                    </div>

                    <div class="rating">
                        <Star class="icon" />
                        {{ ratingDisplay(selectedWorker) }}
                    </div>
                </div>

                <!-- META -->
                <div class="profile-meta">
                    <div class="meta-box">
                        <Briefcase class="icon" />
                        <div>
                            <p class="label">{{ t('find_workers_page.profile_modal.experience') }}</p>
                            <p>{{ t('find_workers_page.experience_years', { count: selectedWorker.years_experience }) }}</p>
                        </div>
                    </div>

                    <div class="meta-box">
                        <DollarSign class="icon" />
                        <div>
                            <p class="label">{{ t('find_workers_page.profile_modal.rate') }}</p>
                            <p>{{ selectedWorker.hourly_rate }} {{ t('common.per_hour') }}</p>
                        </div>
                    </div>
                </div>

                <!-- CERTIFICATIONS -->
                <div class="section">
                    <p class="section-label">{{ t('find_workers_page.profile_modal.certifications') }}</p>
                    <div class="tag-row">
                        <span v-for="cert in selectedWorker.certifications" :key="cert.id" class="tag cert">
                            {{ cert.name }}
                        </span>
                    </div>
                </div>

                <!-- SKILLS -->
                <div class="section">
                    <p class="section-label">{{ t('find_workers_page.profile_modal.skills') }}</p>
                    <div class="tag-row">
                        <span v-for="skill in selectedWorker.skills" :key="skill.id" class="tag">
                            {{ skill.name }}
                        </span>
                    </div>
                </div>
            </BaseModal>

            <!-- REQUEST MODAL -->
            <BaseModal
                v-model="showRequest"
                :title="t('find_workers_page.request_modal.title')"
            >
                <form id="request-form" @submit.prevent="submitRequest">
                    <!-- Worker Summary -->
                    <div class="request-worker">
                        <div>
                            <p class="worker-name">{{ selectedWorker.name }}</p>
                            <p class="job">
                                {{ t(`profiles_page.jobs.${selectedWorker.job}`) }}
                            </p>
                        </div>
                        <div class="right">
                            <p class="rate">
                                ${{ selectedWorker.hourly_rate }} {{ t('common.per_hour') }}
                            </p>
                            <p class="rating">
                                ⭐ {{ ratingDisplay(selectedWorker) }}
                            </p>
                        </div>
                    </div>

                    <!-- Mission Select -->
                    <div class="form-group">
                        <label>{{ t('find_workers_page.request_modal.select_mission') }}</label>
                        <select
                            v-model="requestForm.mission_id"
                            :disabled="requestForm.processing"
                        >
                            <option disabled value="">
                                {{ t('find_workers_page.request_modal.choose_mission') }}
                            </option>

                            <option
                                v-for="mission in missions"
                                :key="mission.id"
                                :value="mission.id"
                            >
                                {{ mission.title }}
                            </option>
                        </select>

                        <p v-if="requestForm.errors.mission_id" class="error">{{ requestForm.errors.mission_id }}</p>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label>{{ t('find_workers_page.request_modal.mission_desc') }}</label>

                        <textarea
                            v-model="requestForm.message"
                            rows="3"
                            :disabled="requestForm.processing"
                        ></textarea>
                    </div>
                </form>

                <template #footer>
                    <button
                        type="submit"
                        form="request-form"
                        class="btn-primary btn-full"
                        :disabled="
                            requestForm.processing ||
                            alreadyRequested(selectedWorker.id, requestForm.mission_id)
                        "
                    >
                        {{
                            alreadyRequested(selectedWorker.id, requestForm.mission_id)
                                ? t('find_workers_page.request_modal.already_requested')
                                : (requestForm.processing 
                                    ? t('find_workers_page.request_modal.sending') 
                                    : t('find_workers_page.request_modal.send'))
                        }}
                    </button>
                </template>
            </BaseModal>
        </div>
    </SidebarLayout>
</template>

<style scoped src="../../css/pages/find-workers.css"></style>
