<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useTranslate } from '@/composables/useTranslate';
import BasePagination from '@/Components/base/BasePagination.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import {
    User,
    CalendarDays,
    DollarSign,
    Mail,
    Info,
    Eye,
    CheckCircle2,
    XCircle,
    ChevronDown,
    ChevronUp,
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
    completed_joined: true,

    all_pending: true,
    all_ongoing: true,
    all_completed: true
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
        page.props.data.completed.joined.total,

    all:
        page.props.data.pending.sent.total +
        page.props.data.pending.received.total +
        page.props.data.pending.join.total +

        page.props.data.ongoing.created.total +
        page.props.data.ongoing.joined.total +

        page.props.data.completed.created.total +
        page.props.data.completed.joined.total
}));

/* =========================
   GLOBAL EMPTY STATE
========================= */
const hasAllData = computed(() => {
    return (
        counts.value.pending ||
        counts.value.ongoing ||
        counts.value.completed
    );
});

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
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('pending_sent')">
                        <h3>
                            {{ t('mission_management_page.tabs.awaiting_response_invitations') }}
                            ({{ page.props.data.pending.sent.total }})
                        </h3>

                        <component
                            :is="sectionState.pending_sent ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.pending_sent">
                            <div v-if="isEmpty(page.props.data.pending.sent)" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_sent_requests') }}</h3>

                                <p>
                                    {{ t('mission_management_page.empty_states.sent_requests_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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
                                                @click="$inertia.visit('/missions')"
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.pending.sent.links"
                                :links="page.props.data.pending.sent.links"
                            />
                        </div>
                    </Transition>
                </div>

                <!-- REQUESTS RECEIVED -->
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('pending_received')">
                        <h3>
                            {{ t('mission_management_page.tabs.needs_your_response') }}
                            ({{ page.props.data.pending.received.total }})
                        </h3>

                        <component
                            :is="sectionState.pending_received ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.pending_received">
                            <div v-if="isEmpty(page.props.data.pending.received)" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_received_requests') }}</h3>

                                <p>
                                    {{ t('mission_management_page.empty_states.received_requests_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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
                                                @click="
                                                    $inertia.visit(
                                                        req.type === 'apply'
                                                            ? '/find-workers'
                                                            : '/find-missions'
                                                    )
                                                "
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.pending.received.links"
                                :links="page.props.data.pending.received.links"
                            />
                        </div>
                    </Transition>
                </div>

                <!-- REQUESTS TO JOIN -->
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('pending_join')">
                        <h3>
                            {{ t('mission_management_page.tabs.awaiting_response_applications') }}
                            ({{ page.props.data.pending.join.total }})
                        </h3>

                        <component
                            :is="sectionState.pending_join ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.pending_join">
                            <div v-if="isEmpty(page.props.data.pending.join)" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_join_requests') }}</h3>

                                <p>
                                    {{ t('mission_management_page.empty_states.join_requests_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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
                                                @click="$inertia.visit('/find-missions')"
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.pending.join.links"
                                :links="page.props.data.pending.join.links"
                            />
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- ======================== -->
            <!-- ONGOING -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'ongoing'">
                <!-- CREATED -->
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('ongoing_created')">
                        <h3>
                            {{ t('mission_management_page.tabs.your_active_missions') }}
                            ({{ page.props.data.ongoing.created.total }})
                        </h3>

                        <component
                            :is="sectionState.ongoing_created ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.ongoing_created">
                            <div v-if="!page.props.data.ongoing.created.total" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_active_missions') }}</h3>
                                <p>
                                    {{ t('mission_management_page.empty_states.active_created_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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
                                                @click="$inertia.visit('/missions')"
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.ongoing.created.links"
                                :links="page.props.data.ongoing.created.links"
                            />
                        </div>
                    </Transition>
                </div>

                <!-- JOINED -->
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('ongoing_joined')">
                        <h3>
                            {{ t('mission_management_page.tabs.external_assignments') }}
                            ({{ page.props.data.ongoing.joined.total }})
                        </h3>

                        <component
                            :is="sectionState.ongoing_joined ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.ongoing_joined">
                            <div v-if="!page.props.data.ongoing.joined.total" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_active_missions') }}</h3>

                                <p>
                                    {{ t('mission_management_page.empty_states.active_joined_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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
                                                @click="$inertia.visit('/find-missions')"
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.ongoing.joined.links"
                                :links="page.props.data.ongoing.joined.links"
                            />
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- ======================== -->
            <!-- COMPLETED -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'completed'">

                <!-- CREATED -->
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('completed_created')">
                        <h3>
                            {{ t('mission_management_page.sections.completed_created') }}
                            ({{ page.props.data.completed.created.total }})
                        </h3>

                        <component
                            :is="sectionState.completed_created ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.completed_created">
                            <div v-if="!page.props.data.completed.created.total" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_completed_missions') }}</h3>

                                <p>
                                    {{ t('mission_management_page.empty_states.completed_created_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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

                                            <button class="view-btn" @click="$inertia.visit('/missions')">
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.completed.created.links"
                                :links="page.props.data.completed.created.links"
                            />
                        </div>
                    </Transition>
                </div>

                <!-- JOINED -->
                <div class="mission-section">
                    <div class="mission-section-title" @click="toggleSection('completed_joined')">
                        <h3>
                            {{ t('mission_management_page.sections.completed_joined') }}
                            ({{ page.props.data.completed.joined.total }})
                        </h3>

                        <component
                            :is="sectionState.completed_joined ? ChevronUp : ChevronDown"
                            class="section-arrow"
                        />
                    </div>

                    <Transition name="section-collapse">
                        <div v-if="sectionState.completed_joined">
                            <div v-if="!page.props.data.completed.joined.total" class="empty-state">
                                <h3>{{ t('mission_management_page.empty_states.no_completed_missions') }}</h3>

                                <p>
                                    {{ t('mission_management_page.empty_states.completed_joined_description') }}
                                </p>
                            </div>

                            <div v-else class="missions-grid">
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
                                                @click="$inertia.visit('/find-missions')"
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

                            <!-- PAGINATION -->
                            <BasePagination
                                v-if="page.props.data.completed.joined.links"
                                :links="page.props.data.completed.joined.links"
                            />
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- ======================== -->
            <!-- ALL -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'all'">
                <div v-if="!hasAllData" class="empty-state">
                    <h3>{{ t('mission_management_page.empty_states.no_activity') }}</h3>

                    <p>
                        {{ t('mission_management_page.empty_states.activity_description') }}
                    </p>
                </div>

                <div v-else class="missions-list">
                    <!-- PENDING -->
                    <div class="mission-section">
                        <div class="mission-section-title" @click="toggleSection('all_pending')">
                            <h3>
                                {{ t('mission_management_page.sections.pending_activity') }}
                                ({{ counts.pending }})
                            </h3>

                            <component
                                :is="sectionState.all_pending ? ChevronUp : ChevronDown"
                                class="section-arrow"
                            />
                        </div>

                        <Transition name="section-collapse">
                            <div v-if="sectionState.all_pending">
                                <div v-if="!counts.pending" class="empty-state">
                                    <h3>{{ t('mission_management_page.empty_states.no_pending_activity') }}</h3>

                                    <p>
                                        {{ t('mission_management_page.empty_states.pending_activity_description') }}
                                    </p>
                                </div>

                                <div v-else class="missions-grid">
                                    <div
                                        v-for="req in [
                                            ...pendingSent,
                                            ...pendingReceived,
                                            ...pendingJoin
                                        ]"
                                        :key="`pending-${req.id}`"
                                        class="mission-card"
                                    >

                                        <div class="mission-top">

                                            <h4 class="mission-title">
                                                {{ req.mission?.title ?? req.worker?.name }}
                                            </h4>

                                            <span class="status-badge pending">
                                                {{ statusLabel('pending') }}
                                            </span>

                                        </div>

                                        <p class="mission-description">
                                            {{
                                                req.message ||
                                                t('mission_management_page.fallbacks.pending_activity')
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- PAGINATION -->
                                <BasePagination
                                    v-if="page.props.data.pending.links"
                                    :links="page.props.data.pending.links"
                                />
                            </div>
                        </Transition>
                    </div>

                    <!-- ONGOING -->
                    <div class="mission-section">
                        <div class="mission-section-title" @click="toggleSection('all_ongoing')">
                            <h3>
                                {{ t('mission_management_page.sections.ongoing_activity') }}
                                ({{ counts.ongoing }})
                            </h3>

                            <component
                                :is="sectionState.all_ongoing ? ChevronUp : ChevronDown"
                                class="section-arrow"
                            />
                        </div>

                        <Transition name="section-collapse">
                            <div v-if="sectionState.all_ongoing">
                                <div v-if="!counts.ongoing" class="empty-state">
                                    <h3>{{ t('mission_management_page.empty_states.no_ongoing_activity') }}</h3>

                                    <p>
                                        {{ t('mission_management_page.empty_states.ongoing_activity_description') }}
                                    </p>
                                </div>

                                <div v-else class="missions-grid">
                                    <div
                                        v-for="req in [
                                            ...ongoingCreated,
                                            ...ongoingJoined
                                        ]"
                                        :key="`ongoing-${req.id}`"
                                        class="mission-card"
                                    >

                                        <div class="mission-top">
                                            <h4 class="mission-title">
                                                {{ req.mission?.title ?? req.worker?.name }}
                                            </h4>

                                            <span
                                                class="status-badge"
                                                :class="req.status"
                                            >
                                                {{ statusLabel(req.status) }}
                                            </span>
                                        </div>

                                        <p class="mission-description">
                                            {{
                                                req.message ||
                                                t('mission_management_page.fallbacks.active_mission')
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- PAGINATION -->
                                <BasePagination
                                    v-if="page.props.data.ongoing.links"
                                    :links="page.props.data.ongoing.links"
                                />
                            </div>
                        </Transition>
                    </div>

                    <!-- COMPLETED -->
                    <div class="mission-section">
                        <div class="mission-section-title" @click="toggleSection('all_completed')">
                            <h3>
                                {{ t('mission_management_page.sections.completed_activity') }}
                                ({{ counts.completed }})
                            </h3>

                            <component
                                :is="sectionState.all_completed ? ChevronUp : ChevronDown"
                                class="section-arrow"
                            />
                        </div>

                        <Transition name="section-collapse">
                            <div v-if="sectionState.all_completed">
                                <div v-if="!counts.completed" class="empty-state">
                                    <h3>{{ t('mission_management_page.empty_states.no_completed_activity') }}</h3>

                                    <p>
                                        {{ t('mission_management_page.empty_states.completed_activity_description') }}
                                    </p>
                                </div>

                                <div v-else class="missions-grid">
                                    <div
                                        v-for="req in [
                                            ...completedCreated,
                                            ...completedJoined
                                        ]"
                                        :key="`completed-${req.id}`"
                                        class="mission-card"
                                    >

                                        <div class="mission-top">

                                            <h4 class="mission-title">
                                                {{ req.mission?.title ?? req.worker?.name }}
                                            </h4>

                                            <span class="status-badge completed">
                                                {{ statusLabel('completed') }}
                                            </span>

                                        </div>

                                        <p class="mission-description">
                                            {{
                                                req.message ||
                                                t('mission_management_page.states.mission_completed')
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- PAGINATION -->
                                <BasePagination
                                    v-if="page.props.data.completed.links"
                                    :links="page.props.data.completed.links"
                                />
                            </div>
                        </Transition>
                    </div>
                </div>
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

<style scoped>
/* ========================= */
/* TABS */
/* ========================= */
.tabs {
    display: flex;
    background: #babac4;
    border-radius: 12px;
    padding: 4px;
    font-weight: 400;
    color: black;
}

.tabs button {
    flex: 1;
    padding: 10px;
    border: none;
    background: transparent;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
}

.tabs button.active {
    background: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

/* ========================= */
/* SECTION */
/* ========================= */
.mission-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 12px;

    margin: 16px 0;

    cursor: pointer;
    user-select: none;
}

.mission-section-title h3 {
    font-size: 18px;
    font-weight: 600;

    margin: 0;
}

.section-arrow {
    width: 18px;
    height: 18px;

    color: #6b7280;

    transition: transform 0.2s ease;
}

/* ========================= */
/* TRANSITION/ANIMATION EFFECT*/
/* ========================= */
.section-collapse-enter-active,
.section-collapse-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.section-collapse-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.section-collapse-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* ========================= */
/* PAGINATION */
/* ========================= */
.section-pagination {
    margin-top: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.section-pagination button {
    min-width: 36px;
    height: 36px;

    border: 1px solid #e5e7eb;
    border-radius: 8px;

    background: white;

    font-size: 14px;
    font-weight: 500;

    cursor: pointer;

    transition: all 0.2s ease;
}

.section-pagination button:hover {
    background: #f9fafb;
}

.section-pagination button.active {
    background: #111827;
    color: white;
    border-color: #111827;
}

/* ========================= */
/* GRID */
/* ========================= */
.missions-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;

    align-items: stretch;
}

/* ========================= */
/* CARD */
/* ========================= */
.mission-card {
    background: white;
    border-radius: 16px;
    padding: 20px;

    border: 1px solid #e5e7eb;

    display: flex;
    flex-direction: column;
    min-width: 0;
}

/* ========================= */
/* HEADER */
/* ========================= */
.mission-top {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: flex-start;
    gap: 12px;

    margin-bottom: 14px;
}

.mission-title {
    min-width: 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;

    color: #111827;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-block-size: calc(2 * 1.35em);
}

.mission-top-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;

    flex-shrink: 0;
    justify-self: end;
}

.relationship-badge {
    display: inline-flex;
    align-items: center;
    min-height: 24px;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.relationship-badge.your-mission {
    background: #eff6ff;
    color: #1d4ed8;
}

.relationship-badge.external-assignment {
    background: #f0fdf4;
    color: #15803d;
}

/* ========================= */
/* ACTION BUTTONS */
/* ========================= */
.pending-actions {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}

.action-btn {
    flex: 1;

    height: 44px;

    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    border-radius: 10px;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition: all 0.2s ease;
}

/* BUTTON ICON */
.btn-icon {
    width: 16px;
    height: 16px;

    flex-shrink: 0;
}

/* ========================= */
/* ACCEPTED */
/* ========================= */
.accepted-box {
    margin-top: 12px;
    background: #eefbf3;
    border: 1px solid #86efac;
    border-radius: 10px;
    padding: 12px;
    color: #15803d;
    font-size: 13px;
    font-weight: 500;
}

/* ========================= */
/* COMPLETE BUTTON */
/* ========================= */
.complete-btn {
    margin-top: 12px;
    height: 44px;
    border: none;
    border-radius: 10px;
    background: #0f172a;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.complete-btn:hover {
    opacity: 0.9;
}

/* ========================= */
/* STATUS */
/* ========================= */
.status-badge {
    padding: 4px 10px;
    border-radius: 999px;

    font-size: 11px;
    font-weight: 600;
}

.status-badge.pending {
    background: #fef9c3;
    color: #ca8a04;
    border: 1px solid #fde68a;
}

.status-badge.accepted {
    background: #dbeafe;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.status-badge.ongoing {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
}

.status-badge.completed {
    background: #ede9fe;
    color: #7c3aed;
    border: 1px solid #c4b5fd;
}

/* ========================= */
/* VIEW BUTTON */
/* ========================= */
.view-btn {
    width: 32px;
    height: 32px;

    border-radius: 8px;
    border: 1px solid #e5e7eb;

    background: white;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;

    transition: all 0.2s ease;
}

.view-btn:hover {
    background: #f9fafb;
}

/* ========================= */
/* DESCRIPTION */
/* ========================= */
.mission-description {
    margin-bottom: 12px;
    color: #535964;
    font-size: 14px;
    line-height: 1.6;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;

    overflow: hidden;
    text-overflow: ellipsis;
    min-block-size: calc(2 * 1.6em);
}

.mission-company {
    margin-top: -10px;
    margin-bottom: 14px;
    font-size: 13px;
    color: #6b7280;
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}

/* ========================= */
/* DETAILS */
/* ========================= */
.request-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    align-items: stretch;
}

/* ========================= */
/* DETAIL ITEM */
/* ========================= */
.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;

    background: #f3f4f6;
    border-radius: 10px;

    min-width: 0;
    min-height: 72px;
    padding: 10px;
}

.detail-item > div {
    min-width: 0;
}

.detail-item small {
    display: block;

    margin-bottom: 3px;

    font-size: 11px;
    font-weight: 600;

    color: #6b7280;
    text-transform: uppercase;
}

.detail-item p {
    margin: 0;

    font-size: 14px;
    color: #111827;
    line-height: 1.4;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.detail-item span {
    color: #6b7280;
    font-size: 12px;
}

/* ========================= */
/* MESSAGE */
/* ========================= */
.message-box {
    margin-top: 12px;
    background: #f9fafb;
    border-left: 3px solid #6366f1;
    padding: 12px;
    border-radius: 10px;
    min-block-size: 116px;
    display: flex;
    flex-direction: column;
}

.message-header {
    display: flex;
    align-items: center;
    gap: 6px;

    margin-bottom: 8px;
}

.message-header small {
    font-size: 11px;
    font-weight: 600;

    color: #6b7280;
    text-transform: uppercase;
}

.message-box p {
    margin: 0;

    font-size: 14px;
    color: #374151;
    line-height: 1.5;

    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;

    overflow: hidden;
    text-overflow: ellipsis;
}

.status-date {
    margin-top: auto;
    padding-top: 12px;
    font-size: 12px;
    color: #6b7280;
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}

.review-comment {
    margin-bottom: 14px;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 4px;

    margin-top: auto;
}

.review-star {
    width: 18px;
    height: 18px;
}

.review-score {
    margin-left: 8px;

    font-size: 13px;
    font-weight: 600;

    color: #374151;
}

/* ========================= */
/* WAITING BOX */
/* ========================= */
.waiting-box {
    margin-top: 12px;
    background: #eef6ff;
    border: 1px solid #c7ddff;
    border-radius: 10px;
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: #2563eb;
    font-size: 13px;
    font-weight: 500;
}

/* Keep the final state or action in a consistent footer region without fixing card height. */
.mission-card > .waiting-box,
.mission-card > .pending-actions,
.mission-card > .complete-btn,
.mission-card > .accepted-box {
    flex-shrink: 0;
}

/* ========================= */
/* ICONS */
/* ========================= */
.mini-icon {
    width: 14px;
    height: 14px;

    color: #6b7280;
    flex-shrink: 0;
}

.modal-note {
    display: block;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.form-textarea {
    width: 100%;
}

.rating-stars {
    display: flex;
    justify-content: center;
    gap: 10px;

    margin: 18px 0 24px;
}

.star-btn {
    border: none;
    background: transparent;
    cursor: pointer;

    padding: 0;

    transition: transform .15s;
}

.star-btn:hover {
    transform: scale(1.15);
}

.star-btn svg {
    width: 34px;
    height: 34px;
}

/* Tablet and below: the sidebar leaves insufficient space for two cards. */
@media (max-width: 992px) {
    .missions-grid {
        grid-template-columns: 1fr;
    }
}

/* ========================= */
/* MOBILE */
/* ========================= */
@media (max-width: 768px) {

    .request-details {
        grid-template-columns: 1fr;
    }

    .mission-title,
    .mission-description {
        min-block-size: 0;
    }

    .mission-top-actions {
        gap: 6px;
    }

    .relationship-badge {
        display: none;
    }

    .detail-item {
        min-height: 0;
    }

}
</style>
