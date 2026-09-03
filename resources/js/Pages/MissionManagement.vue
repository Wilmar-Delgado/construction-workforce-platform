<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useTranslate } from '@/composables/useTranslate';
import BasePagination from '@/Components/base/BasePagination.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import MissionSection from '@/Components/mission-management/MissionSection.vue';
import {
    User,
    CalendarDays,
    DollarSign,
    Mail,
    Info,
    Eye,
    CheckCircle2,
    XCircle,
    Star
} from 'lucide-vue-next';

/* ============================= */
/* GLOBAL / PROPS */
/* ============================= */
const { t } = useTranslate();
const page = usePage();

/* =========================
   PAGINATED DATA
========================= */
const pendingSent = computed(() => page.props.data.pending.sent.data || []);
const pendingReceived = computed(() => page.props.data.pending.received.data || []);
const pendingJoin = computed(() => page.props.data.pending.join.data || []);

const ongoingCreated = computed(() => page.props.data.ongoing.created.data || []);
const ongoingJoined = computed(() => page.props.data.ongoing.joined.data || []);

const completedCreated = computed(() => page.props.data.completed.created.data || []);
const completedJoined = computed(() => page.props.data.completed.joined.data || []);

const activeTab = ref('pending');

/* =========================
   SECTION COLLAPSE
========================= */
const sectionState = ref({
    pending_sent: true,
    pending_received: true,
    pending_join: true,

    ongoing_created: true,
    ongoing_joined: true,

    completed_created: true,
    completed_joined: true
});

const showRequestModal = ref(false);
const selectedRequest = ref(null);
const requestAction = ref(null); // accept | reject
const acceptanceMessage = ref('');
const rejectionReason = ref('');

const showCompleteModal = ref(false);
const selectedMission = ref(null);

const missionRating = ref(0);
const missionComment = ref('');

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true }
);

/* =========================
   COUNTS
========================= */
const counts = computed(() => ({

    pending:
        page.props.data.pending.sent.total +
        page.props.data.pending.received.total +
        page.props.data.pending.join.total,

    ongoing:
        page.props.data.ongoing.created.total +
        page.props.data.ongoing.joined.total,

    completed:
        page.props.data.completed.created.total +
        page.props.data.completed.joined.total
}));

const isEmpty = (list) => !list?.total;

/* =========================
   ACTIONS
========================= */
function toggleSection(section) {
    sectionState.value[section] = !sectionState.value[section];
}

function acceptRequest(req) {
    selectedRequest.value = req;
    requestAction.value = 'accept';

    const isSelfEmployed = !req.worker.company_id;

    if (isSelfEmployed) {
        acceptanceMessage.value = t(
            'mission_management_page.response_modal.acceptance_message_self_employed',
            { worker: req.worker.name, mission: req.mission.title }
        );
    } else {
        const contactName = req.worker.company?.owner?.name ?? t('mission_management_page.response_modal.company_contact_fallback');
        acceptanceMessage.value = t(
            'mission_management_page.response_modal.acceptance_message_company',
            { contact: contactName, worker: req.worker.name, mission: req.mission.title }
        );
    }
    showRequestModal.value = true;
}

function rejectRequest(req) {
    selectedRequest.value = req;
    requestAction.value = 'reject';

    rejectionReason.value = '';

    showRequestModal.value = true;
}

function confirmRequestAction() {
    router.post(`/mission-management/requests/${selectedRequest.value.id}/respond`,
        {
            action: requestAction.value,
            message: acceptanceMessage.value,
            reason: rejectionReason.value
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showRequestModal.value = false;
            }
        }
    );
}

function completeMission(req) {
    selectedMission.value = req;

    missionRating.value = 0;
    missionComment.value = '';

    showCompleteModal.value = true;
}

function completeMissionRequest() {
    router.post(
        `/mission-management/requests/${selectedMission.value.id}/complete`,
        {
            rating: missionRating.value,
            comment: missionComment.value
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showCompleteModal.value = false;
            }
        }
    );
}

function viewOwnMission(req) {
    router.get(route('missions.index'), {
        mission: req.mission.id,
    });
}

function viewExternalMission(req) {
    router.get(route('find-missions.index'), {
        mission: req.mission.id,
        request: req.id,
    });
}

function viewWorkerProfile(req) {
    router.get(route('find-workers.index'), {
        worker: req.worker.id,
        request: req.id,
    });
}

function formatDate(date) {

    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString(page.props.locale === 'fr' ? 'fr-CA' : 'en-CA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function statusLabel(status) {
    return t(`common.statuses.${status}`);
}

const formatPhone = (phone) => {
    if (!phone) return '';

    const digits = phone.replace(/\D/g, '');

    if (digits.length === 10) {
        return `+1 ${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6)}`;
    }

    return `+1 ${digits}`;
};
</script>

<template>
    <Head :title="t('mission_management_page.title')" />

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
            {{ t('mission_management_page.title') }}
        </template>

        <div class="mission-management-page-container">
            <!-- Header -->
            <div class="page-header">
                <h2>{{ t('mission_management_page.subtitle') }}</h2>
            </div>

            <!-- TABS -->
            <div class="tabs">
                <button
                    v-for="tab in [
                        { key: 'pending', label: t('mission_management_page.tabs.requests') },
                        { key: 'ongoing', label: t('mission_management_page.tabs.active') },
                        { key: 'completed', label: t('mission_management_page.tabs.completed') }
                    ]"
                    :key="tab.key"
                    :class="{ active: activeTab === tab.key }"
                    @click="activeTab = tab.key"
                >
                    {{ tab.label }}
                    ({{ counts[tab.key] }})
                </button>
            </div>

            <!-- ======================== -->
            <!-- PENDING -->
            <!-- ======================== -->
            <div v-if="activeTab === 'pending'">
                <!-- REQUESTS SENT -->
                <MissionSection
                    :title="t('mission_management_page.tabs.awaiting_response_invitations')"
                    :count="page.props.data.pending.sent.total"
                    :expanded="sectionState.pending_sent"
                    :empty="isEmpty(page.props.data.pending.sent)"
                    :empty-title="t('mission_management_page.empty_states.no_sent_requests')"
                    :empty-description="t('mission_management_page.empty_states.sent_requests_description')"
                    @toggle="toggleSection('pending_sent')"
                >
                            <div class="missions-grid">
                                <div v-for="req in pendingSent" :key="req.id" class="mission-card">
                                    <!-- HEADER -->
                                    <div class="mission-top">

                                        <h4 class="mission-title">
                                            {{ req.mission.title }}
                                        </h4>

                                        <div class="mission-top-actions">

                                            <span class="status-badge" :class="req.status">
                                                {{ statusLabel(req.status) }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="t('mission_management_page.actions.view_worker_profile')"
                                                :aria-label="t('mission_management_page.actions.view_worker_profile')"
                                                @click="viewWorkerProfile(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <p class="mission-description">
                                        {{ req.mission.description }}
                                    </p>

                                    <!-- DETAILS -->
                                    <div class="request-details">

                                        <div class="detail-item">
                                            <User class="mini-icon" />

                                            <div>
                                                <small>{{ t('mission_management_page.labels.requested_worker') }}</small>

                                                <p>
                                                    {{ req.worker.name }}
                                                    <!-- <span>
                                                        - {{ req.worker.job.replace('_', ' ').charAt(0).toUpperCase() + req.worker.job.replace('_', ' ').slice(1) }}
                                                    </span> -->
                                                    <span>
                                                        • {{ req.worker?.company?.name ?? t('common.self_employed') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />

                                            <div>
                                                <small>{{ t('mission_management_page.labels.requested_dates') }}</small>

                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />

                                            <div>
                                                <small>{{ t('mission_management_page.labels.worker_rate') }}</small>

                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- MESSAGE -->
                                    <div class="message-box">
                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>{{ t('mission_management_page.labels.message_sent') }}</small>
                                        </div>

                                        <p>
                                            {{
                                                req.message ||
                                                t('common.no_message_provided')
                                            }}
                                        </p>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.requested_on') }}
                                        {{ formatDate(req.created_at) }}
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="waiting-box">
                                        ⏳ {{ t('mission_management_page.tabs.waiting_response') }}
                                    </div>

                                </div>

                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.pending.sent.links"
                            :links="page.props.data.pending.sent.links"
                        />
                    </template>
                </MissionSection>

                <!-- REQUESTS RECEIVED -->
                <MissionSection
                    :title="t('mission_management_page.tabs.needs_your_response')"
                    :count="page.props.data.pending.received.total"
                    :expanded="sectionState.pending_received"
                    :empty="isEmpty(page.props.data.pending.received)"
                    :empty-title="t('mission_management_page.empty_states.no_received_requests')"
                    :empty-description="t('mission_management_page.empty_states.received_requests_description')"
                    @toggle="toggleSection('pending_received')"
                >
                            <div class="missions-grid">
                                <div v-for="req in pendingReceived" :key="req.id" class="mission-card">
                                    <!-- HEADER -->
                                    <div class="mission-top">

                                        <h4 class="mission-title">
                                            {{ req.mission.title }}
                                        </h4>

                                        <div class="mission-top-actions">

                                            <span class="status-badge" :class="req.status">
                                                {{ statusLabel(req.status) }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="req.type === 'apply'
                                                    ? t('mission_management_page.actions.view_worker_profile')
                                                    : t('mission_management_page.actions.view_mission')"
                                                :aria-label="req.type === 'apply'
                                                    ? t('mission_management_page.actions.view_worker_profile')
                                                    : t('mission_management_page.actions.view_mission')"
                                                @click="req.type === 'apply'
                                                    ? viewWorkerProfile(req)
                                                    : viewExternalMission(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <p class="mission-description">
                                        {{ req.mission.description }}
                                    </p>

                                    <!-- DETAILS -->
                                    <div class="request-details">

                                        <div class="detail-item">
                                            <User class="mini-icon" />
                                            <div>
                                                <small>
                                                    {{
                                                        req.type === 'apply'
                                                            ? t('mission_management_page.labels.worker')
                                                            : t('mission_management_page.labels.requested_worker')
                                                    }}
                                                </small>
                                                <p>
                                                    {{ req.worker.name }}
                                                    <!-- <span>
                                                        - {{ req.worker.job.replace('_', ' ').charAt(0).toUpperCase() + req.worker.job.replace('_', ' ').slice(1) }}
                                                    </span> -->
                                                    <span>
                                                        •
                                                        {{
                                                            req.type === 'apply'
                                                                ? (
                                                                    req.worker?.company?.name
                                                                        ? req.worker.company.name
                                                                        : t('common.self_employed')
                                                                )
                                                                : (
                                                                    req.company?.name
                                                                        ? req.company.name
                                                                        : t('common.unknown_company')
                                                                )
                                                        }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />
                                            <div>
                                                <small>
                                                    {{
                                                        req.type === 'apply'
                                                            ? t('mission_management_page.labels.mission_dates')
                                                            : t('mission_management_page.labels.requested_dates')
                                                    }}
                                                </small>
                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.worker_rate') }}</small>
                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MESSAGE -->
                                    <div class="message-box">

                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>{{ t('mission_management_page.labels.message_received') }}</small>
                                        </div>

                                        <p>
                                            {{
                                                req.message ||
                                                t('common.no_message_provided')
                                            }}
                                        </p>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.requested_on') }}
                                        {{ formatDate(req.created_at) }}
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="pending-actions">
                                        <button @click="acceptRequest(req)" class="btn-secondary action-btn">
                                            <CheckCircle2 class="btn-icon" />
                                            {{ t('mission_management_page.tabs.accept') }}
                                        </button>

                                        <button @click="rejectRequest(req)" class="btn-thirdary action-btn">
                                            <XCircle class="btn-icon" />
                                            {{ t('mission_management_page.tabs.reject') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.pending.received.links"
                            :links="page.props.data.pending.received.links"
                        />
                    </template>
                </MissionSection>

                <!-- REQUESTS TO JOIN -->
                <MissionSection
                    :title="t('mission_management_page.tabs.awaiting_response_applications')"
                    :count="page.props.data.pending.join.total"
                    :expanded="sectionState.pending_join"
                    :empty="isEmpty(page.props.data.pending.join)"
                    :empty-title="t('mission_management_page.empty_states.no_join_requests')"
                    :empty-description="t('mission_management_page.empty_states.join_requests_description')"
                    @toggle="toggleSection('pending_join')"
                >
                            <div class="missions-grid">
                                <div v-for="req in pendingJoin" :key="req.id" class="mission-card">
                                    <!-- HEADER -->
                                    <div class="mission-top">
                                        <h4 class="mission-title">
                                            {{ req.mission.title }}
                                        </h4>

                                        <div class="mission-top-actions">

                                            <span class="status-badge" :class="req.status">
                                                {{ statusLabel(req.status) }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="t('mission_management_page.actions.view_mission')"
                                                :aria-label="t('mission_management_page.actions.view_mission')"
                                                @click="viewExternalMission(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <div>
                                        <p class="mission-description">
                                            {{ req.mission.description }}
                                        </p>

                                        <div class="mission-company">
                                            <strong>{{ t('mission_management_page.labels.company_name') }}:</strong> {{ req.mission?.hiring_company?.name }} |
                                            <strong>{{ t('mission_management_page.labels.company_owner') }}:</strong> {{ req.mission?.hiring_company?.owner?.name }}
                                        </div>
                                    </div>

                                    <!-- DETAILS -->
                                    <div class="request-details">
                                        <div class="detail-item">
                                            <User class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.proposed_worker') }}</small>

                                                <p>
                                                    {{ req.worker.name }}
                                                    <!-- <span>
                                                        - {{ req.worker.job.replace('_', ' ').charAt(0).toUpperCase() + req.worker.job.replace('_', ' ').slice(1) }}
                                                    </span> -->
                                                    <span> 
                                                        • {{ req.worker?.company?.name ?? t('common.self_employed') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.mission_dates') }}</small>
                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.worker_rate') }}</small>
                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MESSAGE -->
                                    <div class="message-box">
                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>{{ t('mission_management_page.labels.message_sent') }}</small>
                                        </div>

                                        <p>
                                            {{
                                                req.message ||
                                                t('common.no_message_provided')
                                            }}
                                        </p>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.requested_on') }}
                                        {{ formatDate(req.created_at) }}
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="waiting-box">
                                        ⏳ {{ t('mission_management_page.tabs.waiting_response') }}
                                    </div>
                                </div>
                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.pending.join.links"
                            :links="page.props.data.pending.join.links"
                        />
                    </template>
                </MissionSection>
            </div>

            <!-- ======================== -->
            <!-- ONGOING -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'ongoing'">
                <!-- CREATED -->
                <MissionSection
                    :title="t('mission_management_page.tabs.your_active_missions')"
                    :count="page.props.data.ongoing.created.total"
                    :expanded="sectionState.ongoing_created"
                    :empty="!page.props.data.ongoing.created.total"
                    :empty-title="t('mission_management_page.empty_states.no_active_missions')"
                    :empty-description="t('mission_management_page.empty_states.active_created_description')"
                    @toggle="toggleSection('ongoing_created')"
                >
                            <div class="missions-grid">
                                <div v-for="req in ongoingCreated" :key="req.id" class="mission-card">

                                    <!-- HEADER -->
                                    <div class="mission-top">
                                        <h4 class="mission-title">
                                            {{ req.mission.title }}
                                        </h4>

                                        <div class="mission-top-actions">

                                            <span class="relationship-badge your-mission">
                                                {{ t('mission_management_page.tabs.your_mission') }}
                                            </span>

                                            <span class="status-badge" :class="req.status">
                                                {{ statusLabel(req.status) }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="t('mission_management_page.actions.view_mission')"
                                                :aria-label="t('mission_management_page.actions.view_mission')"
                                                @click="viewOwnMission(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <p class="mission-description">
                                        {{ req.mission.description }}
                                    </p>

                                    <!-- DETAILS -->
                                    <div class="request-details">
                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.mission_dates') }}</small>

                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <User class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.assigned_worker') }}</small>
                                                <p>
                                                    {{ req.worker.name }}

                                                    <!-- <span>
                                                        - {{ req.worker.job.replace('_', ' ').charAt(0).toUpperCase() + req.worker.job.replace('_', ' ').slice(1) }}
                                                    </span> -->

                                                    <span>
                                                        • {{ req.worker?.company?.name ?? t('common.self_employed') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />

                                            <div>
                                                <small>{{ t('mission_management_page.labels.rate') }}</small>

                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MESSAGE -->
                                    <div class="message-box">
                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>{{ t('mission_management_page.labels.message_received') }}</small>
                                        </div>

                                        <p>
                                            {{
                                                req.message ||
                                                t('common.no_message_provided')
                                            }}
                                        </p>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.accepted_on') }}
                                        {{ formatDate(req.responded_at) }}
                                    </div>

                                    <!-- ACTION -->
                                    <button @click="completeMission(req)" class="complete-btn">
                                        {{ t('mission_management_page.actions.complete_and_rate') }}
                                    </button>
                                </div>
                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.ongoing.created.links"
                            :links="page.props.data.ongoing.created.links"
                        />
                    </template>
                </MissionSection>

                <!-- JOINED -->
                <MissionSection
                    :title="t('mission_management_page.tabs.external_assignments')"
                    :count="page.props.data.ongoing.joined.total"
                    :expanded="sectionState.ongoing_joined"
                    :empty="!page.props.data.ongoing.joined.total"
                    :empty-title="t('mission_management_page.empty_states.no_active_missions')"
                    :empty-description="t('mission_management_page.empty_states.active_joined_description')"
                    @toggle="toggleSection('ongoing_joined')"
                >
                            <div class="missions-grid">
                                <div v-for="req in ongoingJoined" :key="req.id" class="mission-card">

                                    <!-- HEADER -->
                                    <div class="mission-top">
                                        <h4 class="mission-title">
                                            {{ req.mission.title }}
                                        </h4>

                                        <div class="mission-top-actions">

                                            <span class="relationship-badge external-assignment">
                                                {{ t('mission_management_page.tabs.external_assignment') }}
                                            </span>

                                            <span class="status-badge" :class="req.status">
                                                {{ statusLabel(req.status) }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="t('mission_management_page.actions.view_mission')"
                                                :aria-label="t('mission_management_page.actions.view_mission')"
                                                @click="viewExternalMission(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <div>
                                        <p class="mission-description">
                                            {{ req.mission.description }}
                                        </p>

                                        <div class="mission-company">
                                            <strong>{{ t('mission_management_page.labels.company_name') }}:</strong> {{ req.mission?.hiring_company?.name }} |
                                            <strong>{{ t('mission_management_page.labels.company_owner') }}:</strong> {{ req.mission?.hiring_company?.owner?.name }}
                                        </div>
                                    </div>

                                    <!-- DETAILS -->
                                    <div class="request-details">

                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.mission_dates') }}</small>
                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <User class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.assigned_worker') }}</small>
                                                <p>
                                                    {{ req.worker.name }}

                                                    <!-- <span>
                                                        - {{ req.worker.job.replace('_', ' ').charAt(0).toUpperCase() + req.worker.job.replace('_', ' ').slice(1) }}
                                                    </span> -->

                                                    <span>
                                                        • {{ req.worker?.company?.name ?? t('common.self_employed') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.rate') }}</small>
                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MESSAGE -->
                                    <div class="message-box">
                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>
                                                {{
                                                    req.type === 'apply'
                                                        ? t('mission_management_page.labels.application_message')
                                                        : t('mission_management_page.labels.invitation_message')
                                                }}
                                            </small>
                                        </div>

                                        <p>
                                            {{ req.message || t('common.no_message_provided') }}
                                        </p>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.accepted_on') }}
                                        {{ formatDate(req.responded_at) }}
                                    </div>

                                    <!-- CONTACT -->
                                    <div class="accepted-box">
                                        {{ t('mission_management_page.states.mission_accepted') }}
                                        {{ t('mission_management_page.labels.contact_company_owner') }}:
                                        {{
                                            formatPhone(
                                                req.mission?.hiring_company?.owner?.phone
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.ongoing.joined.links"
                            :links="page.props.data.ongoing.joined.links"
                        />
                    </template>
                </MissionSection>
            </div>

            <!-- ======================== -->
            <!-- COMPLETED -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'completed'">

                <!-- CREATED -->
                <MissionSection
                    :title="t('mission_management_page.sections.completed_created')"
                    :count="page.props.data.completed.created.total"
                    :expanded="sectionState.completed_created"
                    :empty="!page.props.data.completed.created.total"
                    :empty-title="t('mission_management_page.empty_states.no_completed_missions')"
                    :empty-description="t('mission_management_page.empty_states.completed_created_description')"
                    @toggle="toggleSection('completed_created')"
                >
                            <div class="missions-grid">
                                <div
                                    v-for="req in completedCreated"
                                    :key="req.id"
                                    class="mission-card"
                                >

                                    <!-- HEADER -->
                                    <div class="mission-top">
                                        <div>
                                            <h4 class="mission-title">
                                                {{ req.mission.title }}
                                            </h4>
                                            <!-- <p class="job-description">
                                                {{ req.worker.job }}
                                            </p> -->
                                        </div>

                                        <div class="mission-top-actions">
                                            <span class="status-badge" :class="req.status">
                                                {{ statusLabel(req.status) }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="t('mission_management_page.actions.view_mission')"
                                                :aria-label="t('mission_management_page.actions.view_mission')"
                                                @click="viewOwnMission(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <p class="mission-description">
                                        {{ req.mission.description }}
                                    </p>

                                    <!-- DETAILS -->
                                    <div class="request-details">
                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.mission_dates') }}</small>
                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <User class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.worker') }}</small>
                                                <p>
                                                    {{ req.worker.name }}
                                                    <span>
                                                        •
                                                        {{
                                                            req.worker?.company?.name ??
                                                            t('common.self_employed')
                                                        }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.final_rate') }}</small>
                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- WORKER REVIEW -->
                                    <div class="message-box">
                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>{{ t('mission_management_page.labels.worker_review') }}</small>
                                        </div>

                                        <p class="review-comment">
                                            {{
                                                req.rating?.feedback ||
                                                t('mission_management_page.fallbacks.no_feedback')
                                            }}
                                        </p>

                                        <div class="review-rating">
                                            <Star
                                                v-for="star in 5"
                                                :key="star"
                                                class="review-star"
                                                :fill="star <= (req.rating?.score ?? 0) ? '#facc15' : 'none'"
                                                :color="star <= (req.rating?.score ?? 0) ? '#facc15' : '#d1d5db'"
                                            />

                                            <span class="review-score">
                                                {{ t('mission_management_page.rating.score_given', { score: req.rating?.score ?? '--' }) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.completed_on') }}
                                        {{ formatDate(req.completed_at) }}
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="accepted-box">
                                        ✅ {{ t('mission_management_page.states.mission_completed') }}
                                    </div>

                                </div>

                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.completed.created.links"
                            :links="page.props.data.completed.created.links"
                        />
                    </template>
                </MissionSection>

                <!-- JOINED -->
                <MissionSection
                    :title="t('mission_management_page.sections.completed_joined')"
                    :count="page.props.data.completed.joined.total"
                    :expanded="sectionState.completed_joined"
                    :empty="!page.props.data.completed.joined.total"
                    :empty-title="t('mission_management_page.empty_states.no_completed_missions')"
                    :empty-description="t('mission_management_page.empty_states.completed_joined_description')"
                    @toggle="toggleSection('completed_joined')"
                >
                            <div class="missions-grid">
                                <div
                                    v-for="req in completedJoined"
                                    :key="req.id"
                                    class="mission-card"
                                >

                                    <!-- HEADER -->
                                    <div class="mission-top">
                                        <div>
                                            <h4 class="mission-title">
                                                {{ req.mission.title }}
                                            </h4>
                                            <!-- <p class="job-description">
                                                {{ req.worker.job }}
                                            </p> -->
                                        </div>

                                        <div class="mission-top-actions">

                                            <span class="status-badge completed">
                                                {{ statusLabel('completed') }}
                                            </span>

                                            <button
                                                class="view-btn"
                                                :title="t('mission_management_page.actions.view_mission')"
                                                :aria-label="t('mission_management_page.actions.view_mission')"
                                                @click="viewExternalMission(req)"
                                            >
                                                <Eye class="mini-icon" />
                                            </button>

                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <p class="mission-description">
                                        {{ req.mission.description }}
                                    </p>

                                    <!-- DETAILS -->
                                    <div class="request-details">

                                        <div class="detail-item">
                                            <CalendarDays class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.mission_dates') }}</small>
                                                <p>
                                                    {{ formatDate(req.mission.start_date) }}
                                                    —
                                                    {{ formatDate(req.mission.end_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <User class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.worker') }}</small>
                                                <p>
                                                    {{ req.worker.name }}
                                                    <span>
                                                        •
                                                        {{
                                                            req.worker?.company?.name ??
                                                            t('common.self_employed')
                                                        }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="detail-item">
                                            <DollarSign class="mini-icon" />
                                            <div>
                                                <small>{{ t('mission_management_page.labels.final_rate') }}</small>
                                                <p>
                                                    ${{ req.worker.hourly_rate ?? '--' }} {{ t('common.per_hour') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- WORKER REVIEW -->
                                    <div class="message-box">
                                        <div class="message-header">
                                            <Info class="mini-icon" />
                                            <small>{{ t('mission_management_page.labels.worker_review') }}</small>
                                        </div>

                                        <p class="review-comment">
                                            {{
                                                req.rating?.feedback ||
                                                t('mission_management_page.fallbacks.no_feedback')
                                            }}
                                        </p>

                                        <div class="review-rating">
                                            <Star
                                                v-for="star in 5"
                                                :key="star"
                                                class="review-star"
                                                :fill="star <= (req.rating?.score ?? 0) ? '#facc15' : 'none'"
                                                :color="star <= (req.rating?.score ?? 0) ? '#facc15' : '#d1d5db'"
                                            />

                                            <span class="review-score">
                                                {{ t('mission_management_page.rating.score_received', { score: req.rating?.score ?? '--' }) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="status-date">
                                        <Mail class="mini-icon" />
                                        {{ t('mission_management_page.labels.completed_on') }}
                                        {{ formatDate(req.completed_at) }}
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="accepted-box">
                                        ✅ {{ t('mission_management_page.states.mission_completed') }}
                                    </div>

                                </div>
                            </div>

                    <template #pagination>
                        <BasePagination
                            v-if="page.props.data.completed.joined.links"
                            :links="page.props.data.completed.joined.links"
                        />
                    </template>
                </MissionSection>
            </div>

            <BaseModal
                v-model="showRequestModal"
                :title="
                    requestAction === 'accept'
                        ? t('mission_management_page.response_modal.accept_title')
                        : t('mission_management_page.response_modal.reject_title')
                "
                max-width="500px"
            >

                <div v-if="selectedRequest">
                    <p>
                        <strong>{{ t('mission_management_page.labels.mission') }}:</strong>
                        {{ selectedRequest.mission.title }}
                    </p>

                    <p>
                        <strong>{{ t('mission_management_page.labels.worker') }}:</strong>
                        {{ selectedRequest.worker.name }}
                    </p>

                    <div v-if="requestAction === 'accept'">
                        <span class="modal-note">
                            {{ t('mission_management_page.response_modal.accept_note') }}
                        </span>

                        <label class="form-label">
                            {{ t('mission_management_page.labels.message') }}
                        </label>

                        <textarea
                            v-model="acceptanceMessage"
                            rows="5"
                            class="form-textarea"
                            :placeholder="t('mission_management_page.response_modal.acceptance_placeholder')"
                        />
                    </div>

                    <div v-if="requestAction === 'reject'">
                        <span class="modal-note">
                            {{ t('mission_management_page.response_modal.reject_note') }}
                        </span>

                        <label class="form-label">
                            {{ t('mission_management_page.labels.reason_optional') }}
                        </label>

                        <textarea
                            v-model="rejectionReason"
                            rows="5"
                            class="form-textarea"
                            :placeholder="t('mission_management_page.response_modal.rejection_placeholder')"
                        />
                    </div>
                </div>
                <template #footer>
                    <button
                        class="btn-thirdary"
                        @click="showRequestModal = false"
                    >
                        {{ t('common.cancel') }}
                    </button>

                    <button
                        v-if="requestAction === 'accept'"
                        class="btn-secondary"
                        @click="confirmRequestAction"
                    >
                        {{ t('mission_management_page.response_modal.confirm_accept') }}
                    </button>

                    <button
                        v-else
                        class="btn-danger"
                        @click="confirmRequestAction"
                    >
                        {{ t('mission_management_page.response_modal.confirm_reject') }}
                    </button>
                </template>
            </BaseModal>

            <BaseModal
                v-model="showCompleteModal"
                :title="t('mission_management_page.completion_modal.title')"
                max-width="500px"
            >
                <div v-if="selectedMission">
                    <p>
                        <strong>{{ t('mission_management_page.labels.mission') }}:</strong>
                        {{ selectedMission.mission.title }}
                    </p>

                    <p>
                        <strong>{{ t('mission_management_page.labels.worker') }}:</strong>
                        {{ selectedMission.worker.name }}
                    </p>

                    <p class="modal-note">
                        {{
                            selectedMission.worker.job
                                ?.replace('_',' ')
                                .replace(/\b\w/g, c => c.toUpperCase())
                        }}

                        •

                        {{ selectedMission.worker?.company?.name ?? t('common.self_employed') }}
                    </p>

                    <label class="form-label">
                        {{ t('mission_management_page.completion_modal.rating_label') }}
                    </label>

                    <div class="rating-stars">

                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            class="star-btn"
                            @click="missionRating = star"
                        >
                            <Star
                                :fill="star <= missionRating ? '#facc15' : 'none'"
                                :color="star <= missionRating ? '#facc15' : '#d1d5db'"
                            />
                        </button>

                    </div>

                    <label class="form-label">
                        {{ t('mission_management_page.completion_modal.comments_label') }}
                    </label>

                    <textarea
                        v-model="missionComment"
                        rows="4"
                        class="form-textarea"
                        :placeholder="t('mission_management_page.completion_modal.comments_placeholder')"
                    />
                </div>

                <template #footer>
                    <button
                        class="btn-thirdary"
                        @click="showCompleteModal = false"
                    >
                        {{ t('common.cancel') }}
                    </button>

                    <button
                        class="btn-secondary"
                        @click="completeMissionRequest"
                    >
                        {{ t('mission_management_page.actions.complete_mission') }}
                    </button>
                </template>
            </BaseModal>
        </div>
    </SidebarLayout>
</template>

<style scoped src="../../css/pages/mission-management.css"></style>
