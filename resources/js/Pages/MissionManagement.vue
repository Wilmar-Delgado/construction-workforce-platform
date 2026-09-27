<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { useTranslate } from '@/composables/useTranslate';
import { useDateTime } from '@/composables/useDateTime';
import BasePagination from '@/Components/base/BasePagination.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import ConfirmModal from '@/Components/base/ConfirmModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import MissionDetails from '@/Components/missions/MissionDetails.vue';
import WorkerProfileDetails from '@/Components/workers/WorkerProfileDetails.vue';
import {
    CalendarDays,
    CheckCircle2,
    ChevronDown,
    ChevronUp,
    Eye,
    Info,
    Mail,
    Star,
    User,
    Users,
    XCircle,
} from 'lucide-vue-next';

const { t } = useTranslate();
const { formatDateOnly, formatTimestamp } = useDateTime();
const page = usePage();
const activeTab = ref('requests');
const expandedLists = ref({});
const showRequestModal = ref(false);
const selectedRequest = ref(null);
const requestAction = ref(null);
const acceptanceMessage = ref('');
const rejectionMessage = ref('');
const showRequestHistoryModal = ref(false);
const selectedRequestHistory = ref(null);
const showCompleteModal = ref(false);
const selectedAssignment = ref(null);
const assignmentResolutionAction = ref('complete');
const missionRating = ref(0);
const missionComment = ref('');
const showStopRecruitingModal = ref(false);
const showStartMissionModal = ref(false);
const selectedLifecycleMission = ref(null);
const showMissionDetailsModal = ref(false);
const selectedMissionDetails = ref(null);
const detailsLoading = ref(false);
const showWorkerDetailsModal = ref(false);
const selectedWorkerDetails = ref(null);
const workerDetailsLoading = ref(false);
const detailsError = ref('');
const toastKey = ref(0);

const isSelfEmployed = computed(
    () =>
        page.props.auth?.user?.role?.name === 'self_employed' &&
        page.props.auth?.user?.company_id === null,
);
const missionData = computed(
    () => page.props.missionData ?? { tabs: {}, counts: {} },
);
const activePaginator = computed(
    () =>
        missionData.value.tabs?.[activeTab.value] ?? {
            data: [],
            links: [],
            total: 0,
        },
);
const activeMissions = computed(() => activePaginator.value.data ?? []);
const tabs = computed(() => [
    ['requests', t('mission_management_page.tabs.requests')],
    [
        'staffing',
        isSelfEmployed.value
            ? t('mission_management_page.tabs.assignments')
            : t('mission_management_page.tabs.staffing'),
    ],
    ['in_progress', t('mission_management_page.tabs.in_progress')],
    ['completed', t('mission_management_page.tabs.completed')],
]);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true },
);

const statusLabel = (status) => t(`common.statuses.${status}`);
const dateFormatOptions = { year: 'numeric', month: '2-digit', day: '2-digit' };
const formatDate = (date) => formatDateOnly(date, dateFormatOptions);
const formatTimestampDate = (timestamp) =>
    formatTimestamp(timestamp, dateFormatOptions);
const workerContext = (worker) =>
    worker?.company?.name ?? t('common.self_employed');
const listKey = (mission) => `${activeTab.value}:${mission.id}`;
const isListExpanded = (mission) =>
    expandedLists.value[listKey(mission)] ?? true;
function toggleList(mission) {
    expandedLists.value[listKey(mission)] = !isListExpanded(mission);
}
function relationshipLabel(mission) {
    return mission.management_context?.is_own_mission
        ? t('mission_management_page.tabs.your_mission')
        : t('mission_management_page.tabs.external_assignment');
}
function relationshipClass(mission) {
    return mission.management_context?.is_own_mission
        ? 'your-mission'
        : 'external-assignment';
}
function staffingProgress(mission) {
    return t('mission_management_page.labels.capacity', {
        committed: mission.committed_worker_count ?? 0,
        required: mission.workers ?? 0,
    });
}
function remainingCapacity(mission) {
    return t('mission_management_page.labels.remaining', {
        count: mission.remaining_capacity ?? 0,
    });
}
function recruitingState(mission) {
    return mission.management_context?.recruiting_state === 'staffing_closed'
        ? t('mission_management_page.labels.staffing_closed')
        : t('mission_management_page.labels.recruiting');
}
function listLabel() {
    return {
        requests: t('mission_management_page.labels.requests'),
        staffing: t('mission_management_page.labels.assigned_workers'),
        in_progress: t('mission_management_page.labels.assignments'),
        completed: isSelfEmployed.value
            ? t('mission_management_page.sections.completed_assignments')
            : t('mission_management_page.labels.worker_outcomes'),
    }[activeTab.value];
}
function emptyTitle() {
    return {
        requests: t('mission_management_page.empty_states.no_requests'),
        staffing: isSelfEmployed.value
            ? t('mission_management_page.empty_states.no_assignments')
            : t('mission_management_page.empty_states.no_staffing_missions'),
        in_progress: t(
            'mission_management_page.empty_states.no_in_progress_missions',
        ),
        completed: t(
            'mission_management_page.empty_states.no_completed_missions',
        ),
    }[activeTab.value];
}
function emptyDescription() {
    return {
        requests: isSelfEmployed.value
            ? t(
                  'mission_management_page.empty_states.self_employed_requests_description',
              )
            : t('mission_management_page.empty_states.requests_description'),
        staffing: isSelfEmployed.value
            ? t('mission_management_page.empty_states.assignments_description')
            : t('mission_management_page.empty_states.staffing_description'),
        in_progress: t(
            'mission_management_page.empty_states.in_progress_description',
        ),
        completed: t(
            'mission_management_page.empty_states.completed_description',
        ),
    }[activeTab.value];
}
const requestTypeLabel = (request) =>
    request.type === 'invite'
        ? t('mission_management_page.labels.invitation')
        : t('mission_management_page.labels.application');
const directionLabel = (request) =>
    request.management_context?.direction === 'incoming'
        ? t('mission_management_page.labels.incoming')
        : t('mission_management_page.labels.outgoing');
const requestResponseDateLabel = (request) =>
    request.status === 'rejected'
        ? t('mission_management_page.labels.rejected_on')
        : t('mission_management_page.labels.cancelled_on');
const outcomeDate = (request) =>
    request.status === 'ended_early' ? request.ended_at : request.completed_at;
const outcomeDateLabel = (request) =>
    request.status === 'ended_early'
        ? t('mission_management_page.labels.ended_on')
        : t('mission_management_page.labels.completed_on');
const reviewerRoleLabel = (reviewer) =>
    reviewer?.role?.name
        ? t(`mission_management_page.roles.${reviewer.role.name}`)
        : '';

function requestCreatorName(request) {
    return (
        request.requester?.name ??
        request.worker?.user?.name ??
        request.worker?.name ??
        t('mission_management_page.response_modal.company_contact_fallback')
    );
}
function requestCreatorRoleLabel(request) {
    return request.requester?.role?.name
        ? t(`mission_management_page.roles.${request.requester.role.name}`)
        : t('common.self_employed');
}
function requestIsSelfEmployed(request) {
    return request.requester?.role?.name === 'self_employed';
}
function withMission(mission, request) {
    return { ...request, mission };
}

function openRequestModal(mission, request, action) {
    selectedRequest.value = withMission(mission, request);
    requestAction.value = action;
    const contact = requestCreatorName(selectedRequest.value);

    if (action === 'reject') {
        rejectionMessage.value = requestIsSelfEmployed(selectedRequest.value)
            ? t(
                  'mission_management_page.response_modal.rejection_message_self_employed',
                  { contact, mission: mission.title },
              )
            : t(
                  'mission_management_page.response_modal.rejection_message_company',
                  {
                      contact,
                      worker: request.worker.name,
                      mission: mission.title,
                  },
              );
    } else {
        acceptanceMessage.value = requestIsSelfEmployed(selectedRequest.value)
            ? t(
                  'mission_management_page.response_modal.acceptance_message_self_employed',
                  { contact, mission: mission.title },
              )
            : t(
                  'mission_management_page.response_modal.acceptance_message_company',
                  {
                      contact,
                      worker: request.worker.name,
                      mission: mission.title,
                  },
              );
    }
    showRequestModal.value = true;
}
function confirmRequestAction() {
    router.post(
        route('mission-management.respond', selectedRequest.value.id),
        {
            action: requestAction.value,
            message:
                requestAction.value === 'reject'
                    ? rejectionMessage.value
                    : acceptanceMessage.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showRequestModal.value = false;
                selectedRequest.value = null;
            },
        },
    );
}
function openRequestHistoryModal(mission, request) {
    selectedRequestHistory.value = withMission(mission, request);
    showRequestHistoryModal.value = true;
}
function openAssignmentModal(mission, request, action) {
    selectedAssignment.value = withMission(mission, request);
    assignmentResolutionAction.value = action;
    missionRating.value = 0;
    missionComment.value = '';
    showCompleteModal.value = true;
}
function confirmAssignmentAction() {
    router.post(
        route(
            assignmentResolutionAction.value === 'end_early'
                ? 'mission-management.end-early'
                : 'mission-management.complete',
            selectedAssignment.value.id,
        ),
        {
            rating: missionRating.value,
            comment: missionComment.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCompleteModal.value = false;
                selectedAssignment.value = null;
            },
        },
    );
}
function openStopRecruitingModal(mission) {
    selectedLifecycleMission.value = mission;
    showStopRecruitingModal.value = true;
}
function openStartMissionModal(mission) {
    selectedLifecycleMission.value = mission;
    showStartMissionModal.value = true;
}
function stopRecruiting() {
    router.post(
        route(
            'mission-management.close-recruiting',
            selectedLifecycleMission.value.id,
        ),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showStopRecruitingModal.value = false;
                selectedLifecycleMission.value = null;
            },
        },
    );
}
function startMission() {
    router.post(
        route('mission-management.start', selectedLifecycleMission.value.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showStartMissionModal.value = false;
                selectedLifecycleMission.value = null;
            },
        },
    );
}
function viewMission(mission) {
    const isOwnMission = mission.management_context?.is_own_mission;
    const request = mission.management_requests?.[0];

    if (!isOwnMission && !request) return;

    detailsLoading.value = true;
    detailsError.value = '';

    axios
        .get(route('mission-management.missions.details', mission.id), {
            params: isOwnMission ? {} : { request: request.id },
        })
        .then(({ data }) => {
            selectedMissionDetails.value = data.mission;
            showMissionDetailsModal.value = true;
        })
        .catch(() => {
            detailsError.value = t(
                'mission_management_page.details_modal.load_error',
            );
        })
        .finally(() => {
            detailsLoading.value = false;
        });
}
function closeMissionDetailsModal() {
    showMissionDetailsModal.value = false;
    selectedMissionDetails.value = null;
}
function viewWorker(request) {
    workerDetailsLoading.value = true;
    detailsError.value = '';

    axios
        .get(
            route(
                'mission-management.workers.details',
                request.worker.id,
            ),
            {
                params: { request: request.id },
            },
        )
        .then(({ data }) => {
            selectedWorkerDetails.value = data.worker;
            showWorkerDetailsModal.value = true;
        })
        .catch(() => {
            detailsError.value = t(
                'mission_management_page.worker_details_modal.load_error',
            );
        })
        .finally(() => {
            workerDetailsLoading.value = false;
        });
}
function closeWorkerDetailsModal() {
    showWorkerDetailsModal.value = false;
    selectedWorkerDetails.value = null;
}
</script>

<template>
    <Head :title="t('mission_management_page.title')" />
    <SidebarLayout>
        <BaseToast
            :key="`success-${toastKey}`"
            :message="page.props.flash?.success"
            type="success"
        />
        <BaseToast
            :key="`error-${toastKey}`"
            :message="detailsError || page.props.flash?.error"
            type="error"
        />
        <template #title>
            {{ t('mission_management_page.title') }}
        </template>

        <div class="mission-management-page-container">
            <div class="page-header">
                <h2>{{ t('mission_management_page.subtitle') }}</h2>
            </div>
            <div class="tabs" role="tablist">
                <button
                    v-for="[key, label] in tabs"
                    :key="key"
                    type="button"
                    :class="{ active: activeTab === key }"
                    :aria-selected="activeTab === key"
                    @click="activeTab = key"
                >
                    {{ label }} ({{ missionData.counts?.[key] ?? 0 }})
                </button>
            </div>

            <div v-if="activeMissions.length" class="missions-grid">
                <article
                    v-for="mission in activeMissions"
                    :key="mission.id"
                    class="mission-card"
                >
                    <header class="mission-top">
                        <div>
                            <h3 class="mission-title">{{ mission.title }}</h3>
                            <p
                                v-if="mission.description"
                                class="mission-description"
                            >
                                {{ mission.description }}
                            </p>
                        </div>
                        <div class="mission-top-actions">
                            <span
                                class="relationship-badge"
                                :class="relationshipClass(mission)"
                                >{{ relationshipLabel(mission) }}</span
                            >
                            <button
                                v-if="
                                    activeTab !== 'completed' &&
                                    mission.management_context?.can_view_mission
                                "
                                type="button"
                                class="view-btn"
                                :title="
                                    t(
                                        'mission_management_page.actions.view_mission',
                                    )
                                "
                                :aria-label="
                                    t(
                                        'mission_management_page.actions.view_mission',
                                    )
                                "
                                :disabled="detailsLoading"
                                @click="viewMission(mission)"
                            >
                                <Eye class="mini-icon" />
                            </button>
                        </div>
                    </header>

                    <p
                        v-if="mission.hiring_company?.name"
                        class="mission-company"
                    >
                        <strong
                            >{{
                                t('mission_management_page.labels.company')
                            }}:</strong
                        >
                        {{ mission.hiring_company.name }}
                    </p>
                    <div class="mission-meta-grid">
                        <div class="detail-item">
                            <CalendarDays class="mini-icon" />
                            <div>
                                <small>{{
                                    t(
                                        'mission_management_page.labels.mission_dates',
                                    )
                                }}</small>
                                <p>
                                    {{ formatDate(mission.start_date) }} —
                                    {{ formatDate(mission.end_date) }}
                                </p>
                            </div>
                        </div>
                        <div class="detail-item">
                            <Users class="mini-icon" />
                            <div>
                                <small>{{
                                    t(
                                        'mission_management_page.labels.staffing_progress',
                                    )
                                }}</small>
                                <p>{{ staffingProgress(mission) }}</p>
                                <span>{{ remainingCapacity(mission) }}</span>
                            </div>
                        </div>
                        <div
                            v-if="activeTab === 'staffing'"
                            class="detail-item"
                        >
                            <Info class="mini-icon" />
                            <div>
                                <small>{{
                                    t(
                                        'mission_management_page.labels.recruiting',
                                    )
                                }}</small>
                                <p>{{ recruitingState(mission) }}</p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="
                            activeTab === 'staffing' &&
                            (mission.management_context?.can_stop_recruiting ||
                                mission.management_context?.can_start_mission)
                        "
                        class="mission-level-actions"
                    >
                        <button
                            v-if="
                                mission.management_context?.can_stop_recruiting
                            "
                            type="button"
                            class="btn-thirdary action-btn"
                            @click="openStopRecruitingModal(mission)"
                        >
                            {{
                                t(
                                    'mission_management_page.actions.stop_recruiting',
                                )
                            }}
                        </button>
                        <button
                            v-if="mission.management_context?.can_start_mission"
                            type="button"
                            class="btn-secondary action-btn"
                            @click="openStartMissionModal(mission)"
                        >
                            {{
                                t(
                                    'mission_management_page.actions.start_mission',
                                )
                            }}
                        </button>
                    </div>

                    <section class="nested-list">
                        <button
                            type="button"
                            class="nested-list-header"
                            :aria-expanded="isListExpanded(mission)"
                            @click="toggleList(mission)"
                        >
                            <span
                                >{{ listLabel() }} ({{
                                    mission.management_requests?.length ?? 0
                                }})</span
                            >
                            <ChevronUp
                                v-if="isListExpanded(mission)"
                                class="mini-icon"
                            /><ChevronDown v-else class="mini-icon" />
                        </button>
                        <div
                            v-show="isListExpanded(mission)"
                            class="nested-list-content"
                        >
                            <article
                                v-for="request in mission.management_requests"
                                :key="request.id"
                                class="worker-row"
                            >
                                <div class="worker-row-main">
                                    <div class="worker-name">
                                        <User class="mini-icon" />
                                        <div>
                                            <strong>{{
                                                request.worker.name
                                            }}</strong
                                            ><span>{{
                                                workerContext(request.worker)
                                            }}</span>
                                        </div>
                                    </div>
                                    <div class="worker-row-state">
                                        <span
                                            class="status-badge"
                                            :class="request.status"
                                            >{{
                                                statusLabel(request.status)
                                            }}</span
                                        ><span
                                            v-if="activeTab === 'requests'"
                                            class="request-type"
                                            >{{ requestTypeLabel(request) }} ·
                                            {{ directionLabel(request) }}</span
                                        >
                                    </div>
                                    <div class="worker-row-meta">
                                        <template
                                            v-if="activeTab === 'requests'"
                                        >
                                            <template
                                                v-if="
                                                    request.status ===
                                                    'rejected'
                                                "
                                            >
                                                <span
                                                    v-if="request.responded_at"
                                                    >{{
                                                        t(
                                                            'mission_management_page.labels.rejected_on',
                                                        )
                                                    }}
                                                    {{
                                                        formatTimestampDate(
                                                            request.responded_at,
                                                        )
                                                    }}</span
                                                >
                                                <p>
                                                    {{
                                                        request.rejection_message ||
                                                        t(
                                                            'common.no_message_provided',
                                                        )
                                                    }}
                                                </p>
                                            </template>
                                            <template v-else>
                                                <span
                                                    >{{
                                                        t(
                                                            'mission_management_page.labels.requested_on',
                                                        )
                                                    }}
                                                    {{
                                                        formatTimestampDate(
                                                            request.created_at,
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    v-if="
                                                        request.status ===
                                                            'cancelled' &&
                                                        request.responded_at
                                                    "
                                                    >{{
                                                        requestResponseDateLabel(
                                                            request,
                                                        )
                                                    }}
                                                    {{
                                                        formatTimestampDate(
                                                            request.responded_at,
                                                        )
                                                    }}</span
                                                >
                                                <p>
                                                    {{
                                                        request.message ||
                                                        t(
                                                            'common.no_message_provided',
                                                        )
                                                    }}
                                                </p>
                                            </template>
                                        </template>
                                        <template v-else
                                            ><span
                                                >{{
                                                    t(
                                                        'mission_management_page.labels.rate',
                                                    )
                                                }}: ${{
                                                    request.worker
                                                        .hourly_rate ??
                                                    t('common.not_available')
                                                }}
                                                {{ t('common.per_hour') }}</span
                                            ><span
                                                v-if="
                                                    activeTab === 'completed' ||
                                                    request.status ===
                                                        'completed' ||
                                                    request.status ===
                                                        'ended_early'
                                                "
                                                >{{
                                                    outcomeDateLabel(request)
                                                }}
                                                {{
                                                    formatTimestampDate(
                                                        outcomeDate(request),
                                                    )
                                                }}</span
                                            ></template
                                        >
                                    </div>
                                </div>
                                <div
                                    v-if="request.rating"
                                    class="worker-review"
                                >
                                    <div class="review-rating">
                                        <Star
                                            v-for="star in 5"
                                            :key="star"
                                            class="review-star"
                                            :fill="
                                                star <= request.rating.score
                                                    ? '#facc15'
                                                    : 'none'
                                            "
                                            :color="
                                                star <= request.rating.score
                                                    ? '#facc15'
                                                    : '#d1d5db'
                                            "
                                        />
                                    </div>
                                    <p>
                                        {{
                                            request.rating.feedback ||
                                            t(
                                                'mission_management_page.fallbacks.no_feedback',
                                            )
                                        }}
                                    </p>
                                    <span v-if="request.rating.reviewer"
                                        >{{
                                            t(
                                                'mission_management_page.labels.reviewed_by',
                                            )
                                        }}: {{ request.rating.reviewer.name
                                        }}<template
                                            v-if="
                                                reviewerRoleLabel(
                                                    request.rating.reviewer,
                                                )
                                            "
                                        >
                                            —
                                            {{
                                                reviewerRoleLabel(
                                                    request.rating.reviewer,
                                                )
                                            }}</template
                                        ></span
                                    >
                                </div>
                                <div class="worker-row-actions">
                                    <button
                                        type="button"
                                        class="view-btn"
                                        :title="
                                            t(
                                                'mission_management_page.actions.view_worker_profile',
                                            )
                                        "
                                        :aria-label="
                                            t(
                                                'mission_management_page.actions.view_worker_profile',
                                            )
                                        "
                                        :disabled="workerDetailsLoading"
                                        @click="viewWorker(request)"
                                    >
                                        <Eye class="mini-icon" />
                                    </button>
                                    <button
                                        v-if="
                                            activeTab === 'requests' &&
                                            request.status === 'rejected'
                                        "
                                        type="button"
                                        class="view-btn"
                                        :title="
                                            t(
                                                'mission_management_page.actions.view_request_history',
                                            )
                                        "
                                        :aria-label="
                                            t(
                                                'mission_management_page.actions.view_request_history',
                                            )
                                        "
                                        @click="
                                            openRequestHistoryModal(
                                                mission,
                                                request,
                                            )
                                        "
                                    >
                                        <Info class="mini-icon" />
                                    </button>
                                    <template
                                        v-if="
                                            activeTab === 'requests' &&
                                            request.management_context
                                                ?.can_respond
                                        "
                                    >
                                        <button
                                            type="button"
                                            class="btn-secondary action-btn"
                                            @click="
                                                openRequestModal(
                                                    mission,
                                                    request,
                                                    'accept',
                                                )
                                            "
                                        >
                                            <CheckCircle2 class="btn-icon" />{{
                                                t(
                                                    'mission_management_page.tabs.accept',
                                                )
                                            }}
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-thirdary action-btn"
                                            @click="
                                                openRequestModal(
                                                    mission,
                                                    request,
                                                    'reject',
                                                )
                                            "
                                        >
                                            <XCircle class="btn-icon" />{{
                                                t(
                                                    'mission_management_page.tabs.reject',
                                                )
                                            }}
                                        </button>
                                    </template>
                                    <span
                                        v-else-if="
                                            activeTab === 'requests' &&
                                            request.management_context
                                                ?.waiting_for_response
                                        "
                                        class="waiting-state"
                                        ><Mail class="mini-icon" />{{
                                            t(
                                                'mission_management_page.tabs.waiting_response',
                                            )
                                        }}</span
                                    >
                                    <template
                                        v-if="activeTab === 'in_progress'"
                                    >
                                        <button
                                            v-if="
                                                request.management_context
                                                    ?.can_complete
                                            "
                                            type="button"
                                            class="btn-secondary action-btn"
                                            @click="
                                                openAssignmentModal(
                                                    mission,
                                                    request,
                                                    'complete',
                                                )
                                            "
                                        >
                                            {{
                                                t(
                                                    'mission_management_page.actions.complete_and_rate',
                                                )
                                            }}
                                        </button>
                                        <button
                                            v-else-if="
                                                request.management_context
                                                    ?.can_end_early
                                            "
                                            type="button"
                                            class="btn-thirdary action-btn"
                                            @click="
                                                openAssignmentModal(
                                                    mission,
                                                    request,
                                                    'end_early',
                                                )
                                            "
                                        >
                                            {{
                                                t(
                                                    'mission_management_page.actions.end_assignment_and_rate',
                                                )
                                            }}
                                        </button>
                                    </template>
                                </div>
                            </article>
                        </div>
                    </section>
                </article>
            </div>
            <div v-else class="empty-state">
                <h3>{{ emptyTitle() }}</h3>
                <p>{{ emptyDescription() }}</p>
            </div>
            <BasePagination
                v-if="activePaginator.links?.length"
                :links="activePaginator.links"
            />

        <BaseModal
            v-model="showMissionDetailsModal"
                :title="t('find_missions_page.details_modal.title')"
                @close="closeMissionDetailsModal"
            >
                <MissionDetails
                    v-if="selectedMissionDetails"
                    :mission="selectedMissionDetails"
                />

                <template #footer>
                    <button
                        type="button"
                        class="btn-thirdary"
                        @click="closeMissionDetailsModal"
                    >
                        {{ t('common.close') }}
                    </button>
                </template>
        </BaseModal>

        <BaseModal
            v-model="showWorkerDetailsModal"
            :title="t('find_workers_page.profile_modal.title')"
            @close="closeWorkerDetailsModal"
        >
            <WorkerProfileDetails
                v-if="selectedWorkerDetails"
                :worker="selectedWorkerDetails"
            />
        </BaseModal>

            <BaseModal
                v-model="showRequestModal"
                :title="
                    requestAction === 'accept'
                        ? t(
                              'mission_management_page.response_modal.accept_title',
                          )
                        : t(
                              'mission_management_page.response_modal.reject_title',
                          )
                "
            >
                <div v-if="selectedRequest">
                    <p>
                        <strong
                            >{{
                                t('mission_management_page.labels.mission')
                            }}:</strong
                        >
                        {{ selectedRequest.mission.title }}
                    </p>
                    <template v-if="!requestIsSelfEmployed(selectedRequest)"
                        ><p>
                            <strong
                                >{{
                                    t('mission_management_page.labels.company')
                                }}:</strong
                            >
                            {{ selectedRequest.company?.name }}
                        </p>
                        <p>
                            <strong
                                >{{
                                    requestCreatorRoleLabel(selectedRequest)
                                }}:</strong
                            >
                            {{ requestCreatorName(selectedRequest) }}
                        </p>
                        <p>
                            <strong
                                >{{
                                    t(
                                        'mission_management_page.labels.worker_offered',
                                    )
                                }}:</strong
                            >
                            {{ selectedRequest.worker.name }}
                        </p></template
                    >
                    <p v-else>
                        <strong
                            >{{
                                t('mission_management_page.labels.worker')
                            }}:</strong
                        >
                        {{ selectedRequest.worker.name }} —
                        {{ t('common.self_employed') }}
                    </p>
                    <template v-if="requestAction === 'accept'"
                        ><span class="modal-note">{{
                            t(
                                'mission_management_page.response_modal.accept_note',
                                {
                                    contact:
                                        requestCreatorName(selectedRequest),
                                },
                            )
                        }}</span
                        ><label class="form-label">{{
                            t('mission_management_page.labels.message')
                        }}</label
                        ><textarea
                            v-model="acceptanceMessage"
                            rows="5"
                            class="form-textarea"
                            :placeholder="
                                t(
                                    'mission_management_page.response_modal.acceptance_placeholder',
                                )
                            "
                        />
                    </template>
                    <template v-else
                        ><span class="modal-note">{{
                            t(
                                'mission_management_page.response_modal.reject_note',
                                {
                                    contact:
                                        requestCreatorName(selectedRequest),
                                },
                            )
                        }}</span
                        ><label class="form-label">{{
                            t('mission_management_page.labels.message')
                        }}</label
                        ><textarea
                            v-model="rejectionMessage"
                            rows="5"
                            class="form-textarea"
                            :placeholder="
                                t(
                                    'mission_management_page.response_modal.rejection_placeholder',
                                )
                            "
                        />
                    </template>
                </div>
                <template #footer
                    ><button
                        type="button"
                        class="btn-thirdary"
                        @click="showRequestModal = false"
                    >
                        {{ t('common.cancel') }}</button
                    ><button
                        v-if="requestAction === 'accept'"
                        type="button"
                        class="btn-secondary"
                        @click="confirmRequestAction"
                    >
                        {{
                            t(
                                'mission_management_page.response_modal.confirm_accept',
                            )
                        }}</button
                    ><button
                        v-else
                        type="button"
                        class="btn-danger"
                        @click="confirmRequestAction"
                    >
                        {{
                            t(
                                'mission_management_page.response_modal.confirm_reject',
                            )
                        }}
                    </button></template
                >
            </BaseModal>

            <BaseModal
                v-model="showRequestHistoryModal"
                :title="
                    t('mission_management_page.request_history_modal.title')
                "
            >
                <div v-if="selectedRequestHistory">
                    <p>
                        <strong>{{
                            t('mission_management_page.labels.original_request')
                        }}</strong>
                    </p>
                    <p>
                        {{ t('mission_management_page.labels.requested_on') }}
                        {{
                            formatTimestampDate(
                                selectedRequestHistory.created_at,
                            )
                        }}
                    </p>
                    <p>
                        {{
                            selectedRequestHistory.message ||
                            t('common.no_message_provided')
                        }}
                    </p>
                    <p>
                        <strong>{{
                            t('mission_management_page.labels.rejection')
                        }}</strong>
                    </p>
                    <p v-if="selectedRequestHistory.responded_at">
                        {{ t('mission_management_page.labels.rejected_on') }}
                        {{
                            formatTimestampDate(
                                selectedRequestHistory.responded_at,
                            )
                        }}
                    </p>
                    <p>
                        {{
                            selectedRequestHistory.rejection_message ||
                            t('common.no_message_provided')
                        }}
                    </p>
                </div>
                <template #footer
                    ><button
                        type="button"
                        class="btn-thirdary"
                        @click="showRequestHistoryModal = false"
                    >
                        {{ t('common.close') }}
                    </button></template
                >
            </BaseModal>

            <BaseModal
                v-model="showCompleteModal"
                :title="
                    assignmentResolutionAction === 'end_early'
                        ? t(
                              'mission_management_page.completion_modal.end_early_title',
                          )
                        : t('mission_management_page.completion_modal.title')
                "
            >
                <div v-if="selectedAssignment">
                    <p>
                        <strong
                            >{{
                                t('mission_management_page.labels.mission')
                            }}:</strong
                        >
                        {{ selectedAssignment.mission.title }}
                    </p>
                    <p>
                        <strong
                            >{{
                                t('mission_management_page.labels.worker')
                            }}:</strong
                        >
                        {{ selectedAssignment.worker.name }}
                    </p>
                    <p class="modal-note">
                        {{ workerContext(selectedAssignment.worker) }}
                    </p>
                    <label class="form-label">{{
                        t(
                            'mission_management_page.completion_modal.rating_label',
                        )
                    }}</label>
                    <div class="rating-stars">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            class="star-btn"
                            @click="missionRating = star"
                        >
                            <Star
                                :fill="
                                    star <= missionRating ? '#facc15' : 'none'
                                "
                                :color="
                                    star <= missionRating
                                        ? '#facc15'
                                        : '#d1d5db'
                                "
                            />
                        </button>
                    </div>
                    <label class="form-label">{{
                        t(
                            'mission_management_page.completion_modal.comments_label',
                        )
                    }}</label
                    ><textarea
                        v-model="missionComment"
                        rows="4"
                        class="form-textarea"
                        :placeholder="
                            t(
                                'mission_management_page.completion_modal.comments_placeholder',
                            )
                        "
                    />
                </div>
                <template #footer
                    ><button
                        type="button"
                        class="btn-thirdary"
                        @click="showCompleteModal = false"
                    >
                        {{ t('common.cancel') }}</button
                    ><button
                        type="button"
                        class="btn-secondary"
                        @click="confirmAssignmentAction"
                    >
                        {{
                            assignmentResolutionAction === 'end_early'
                                ? t(
                                      'mission_management_page.actions.end_assignment',
                                  )
                                : t(
                                      'mission_management_page.actions.complete_mission',
                                  )
                        }}
                    </button></template
                >
            </BaseModal>

            <ConfirmModal
                v-model="showStopRecruitingModal"
                :title="t('mission_management_page.recruiting_modal.title')"
                :message="t('mission_management_page.recruiting_modal.message')"
                :subtitle="
                    t('mission_management_page.recruiting_modal.subtitle')
                "
                :item-name="selectedLifecycleMission?.title"
                :confirm-text="
                    t('mission_management_page.recruiting_modal.confirm')
                "
                :cancel-text="t('common.cancel')"
                @confirm="stopRecruiting"
            />
            <ConfirmModal
                v-model="showStartMissionModal"
                :title="t('mission_management_page.start_modal.title')"
                :message="t('mission_management_page.start_modal.message')"
                :subtitle="t('mission_management_page.start_modal.subtitle')"
                :item-name="selectedLifecycleMission?.title"
                :confirm-text="t('mission_management_page.start_modal.confirm')"
                :cancel-text="t('common.cancel')"
                @confirm="startMission"
            />
        </div>
    </SidebarLayout>
</template>

<style scoped src="../../css/pages/mission-management.css"></style>
