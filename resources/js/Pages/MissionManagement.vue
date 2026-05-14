<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useTranslate } from '@/composables/useTranslate';
import {
    User,
    CalendarDays,
    DollarSign,
    Mail,
    Info,
    Eye,
    CheckCircle2,
    XCircle
} from 'lucide-vue-next';

const data = usePage().props.data;

const activeTab = ref('pending');

const { t } = useTranslate();

/* =========================
   COUNTS
========================= */
const counts = computed(() => ({

    pending:
        data.pending.sent.length +
        data.pending.received.length +
        data.pending.join.length,

    ongoing:
        data.ongoing.created.length +
        data.ongoing.joined.length,

    completed:
        data.completed.created.length +
        data.completed.joined.length,

    all:
        data.pending.sent.length +
        data.pending.received.length +
        data.pending.join.length +

        data.ongoing.created.length +
        data.ongoing.joined.length +

        data.completed.created.length +
        data.completed.joined.length
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

/* =========================
   HELPERS
========================= */
const isEmpty = (list) => !list?.length;

function formatDate(date) {

    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString('en-CA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}
</script>

<template>
    <Head title="Mission Management" />

    <SidebarLayout>
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
                    v-for="tab in ['pending', 'ongoing', 'completed', 'all']"
                    :key="tab"
                    :class="{ active: activeTab === tab }"
                    @click="activeTab = tab"
                >
                    {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
                    ({{ counts[tab] }})
                </button>
            </div>

            <!-- ======================== -->
            <!-- PENDING -->
            <!-- ======================== -->
            <div v-if="activeTab === 'pending'">
                <!-- REQUESTS SENT -->
                <div class="mission-section">
                    <h3 class="mission-section-title">
                        {{ t('mission_management_page.tabs.requests_sent') }}
                        ({{ data.pending.sent.length }})
                    </h3>

                    <div
                        v-if="!data.pending.sent.length"
                        class="empty-state"
                    >
                        <h3>No sent requests</h3>
                        <p>
                            Mission invitations you send to workers
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">
                        <div
                            v-for="req in data.pending.sent"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">
                                <h4 class="mission-title">
                                    {{ req.mission.title }}
                                </h4>
                                <div class="mission-top-actions">
                                    <span class="status-badge pending">
                                        {{
                                            req.status.charAt(0).toUpperCase() +
                                            req.status.slice(1)
                                        }}
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
                                        <small>Requested Worker</small>
                                        <p>
                                            {{ req.worker.name }}
                                            <span>
                                                •
                                                {{
                                                    req.worker?.company?.name ??
                                                    'Self-employed'
                                                }}
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <CalendarDays class="mini-icon" />
                                    <div>
                                        <small>Requested Dates</small>
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
                                        <small>Worker's Rate</small>
                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- MESSAGE -->
                            <div class="message-box">
                                <div class="message-header">
                                    <Info class="mini-icon" />
                                    <small>Message Sent</small>
                                </div>
                                <p>
                                    {{
                                        req.message ||
                                        'No message provided.'
                                    }}
                                </p>
                                <div class="message-date">
                                    <Mail class="mini-icon" />

                                    Requested on
                                    {{ formatDate(req.created_at) }}
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="waiting-box">
                                ⏳ {{ t('mission_management_page.tabs.waiting_response') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REQUESTS RECEIVED -->
                <div class="mission-section">
                    <h3 class="mission-section-title">
                        {{ t('mission_management_page.tabs.requests_received') }}
                        ({{ data.pending.received.length }})
                    </h3>
                    <div
                        v-if="isEmpty(data.pending.received)"
                        class="empty-state"
                    >
                        <h3>No received requests</h3>

                        <p>
                            Worker applications and invitations
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">
                        <div
                            v-for="req in data.pending.received"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">

                                <h4 class="mission-title">

                                    {{
                                        req.type === 'apply'
                                            ? req.worker.name
                                            : req.mission.title
                                    }}

                                </h4>

                                <div class="mission-top-actions">
                                    <span class="status-badge pending">
                                        Pending
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

                                {{
                                    req.type === 'apply'
                                        ? req.worker.job
                                        : req.mission.description
                                }}

                            </p>

                            <!-- DETAILS -->
                            <div class="request-details">
                                <div class="detail-item">
                                    <User class="mini-icon" />
                                    <div>
                                        <small>
                                            {{
                                                req.type === 'apply'
                                                    ? 'Worker'
                                                    : 'Requested Worker'
                                            }}
                                        </small>
                                        <p>
                                            {{ req.worker.name }}

                                            <span>
                                                •
                                                {{
                                                    req.worker?.company?.name ??
                                                    'Self-employed'
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
                                                    ? 'Mission Dates'
                                                    : 'Requested Dates'
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
                                        <small>Worker's Rate</small>
                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- MESSAGE -->
                            <div class="message-box">
                                <div class="message-header">
                                    <Info class="mini-icon" />
                                    <small>Message Received</small>
                                </div>

                                <p>
                                    {{
                                        req.message ||
                                        'No message provided.'
                                    }}
                                </p>

                                <div class="message-date">
                                    <Mail class="mini-icon" />
                                    Requested on
                                    {{ formatDate(req.created_at) }}
                                </div>
                            </div>

                            <!-- ACTIONS -->
                            <div class="pending-actions">
                                <button class="btn-secondary action-btn">
                                    <CheckCircle2 class="btn-icon" />
                                    {{ t('mission_management_page.tabs.accept') }}
                                </button>

                                <button class="btn-thirdary action-btn">
                                    <XCircle class="btn-icon" />
                                    {{ t('mission_management_page.tabs.reject') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REQUESTS TO JOIN -->
                <div class="mission-section">
                    <h3 class="mission-section-title">
                        {{ t('mission_management_page.tabs.requests_join') }}
                        ({{ data.pending.join.length }})
                    </h3>

                    <div
                        v-if="isEmpty(data.pending.join)"
                        class="empty-state"
                    >
                        <h3>No join requests</h3>

                        <p>
                            Mission applications submitted by your company
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">
                        <div
                            v-for="req in data.pending.join"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">

                                <h4 class="mission-title">
                                    {{ req.mission.title }}
                                </h4>

                                <div class="mission-top-actions">

                                    <span class="status-badge pending">
                                        Pending
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

                                    <User class="mini-icon" />

                                    <div>

                                        <small>Requested Worker</small>

                                        <p>
                                            {{ req.worker.name }}

                                            <span>
                                                •
                                                {{
                                                    req.worker?.company?.name ??
                                                    'Self-employed'
                                                }}
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <div class="detail-item">

                                    <CalendarDays class="mini-icon" />

                                    <div>

                                        <small>Mission Dates</small>

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

                                        <small>Worker's Rate</small>

                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- MESSAGE -->
                            <div class="message-box">

                                <div class="message-header">
                                    <Info class="mini-icon" />
                                    <small>Message Sent</small>
                                </div>

                                <p>
                                    {{
                                        req.message ||
                                        'No message provided.'
                                    }}
                                </p>

                                <div class="message-date">

                                    <Mail class="mini-icon" />

                                    Requested on
                                    {{ formatDate(req.created_at) }}
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="waiting-box">
                                ⏳ Waiting for response...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================== -->
            <!-- ONGOING -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'ongoing'">
                <!-- CREATED -->
                <div class="mission-section">
                    <h3 class="mission-section-title">
                        Missions You Created
                        ({{ data.ongoing.created.length }})
                    </h3>

                    <div
                        v-if="!data.ongoing.created.length"
                        class="empty-state"
                    >
                        <h3>No active missions</h3>
                        <p>
                            Accepted workers for your missions
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">
                        <div
                            v-for="req in data.ongoing.created"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">

                                <div>
                                    <h4 class="mission-title">
                                        {{ req.mission.title }}
                                    </h4>

                                    <p class="mission-description">
                                        {{ req.worker.job }}
                                    </p>
                                </div>

                                <div class="mission-top-actions">

                                    <span
                                        class="status-badge"
                                        :class="req.status"
                                    >
                                        {{
                                            req.status.charAt(0).toUpperCase() +
                                            req.status.slice(1)
                                        }}
                                    </span>

                                    <button
                                        class="view-btn"
                                        @click="$inertia.visit('/missions')"
                                    >
                                        <Eye class="mini-icon" />
                                    </button>

                                </div>
                            </div>

                            <!-- DETAILS -->
                            <div class="request-details">

                                <div class="detail-item">
                                    <CalendarDays class="mini-icon" />

                                    <div>
                                        <small>Mission Dates</small>

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
                                        <small>Worker</small>

                                        <p>
                                            {{ req.worker.name }}

                                            <span>
                                                •
                                                {{
                                                    req.worker?.company?.name ??
                                                    'Self-employed'
                                                }}
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <DollarSign class="mini-icon" />

                                    <div>
                                        <small>Rate</small>

                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- MESSAGE -->
                            <div class="message-box">
                                <p>
                                    {{
                                        req.message ||
                                        'No message provided.'
                                    }}
                                </p>
                            </div>

                            <!-- CONTACT -->
                            <div class="accepted-box">
                                ✓ Mission accepted.
                                Contact:
                                {{ req.worker.company_id ? req.worker.company?.owner?.phone : req.worker.user?.phone }}
                            </div>

                            <!-- ACTION -->
                            <button class="complete-btn">
                                Complete & Rate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- JOINED -->
                <div class="mission-section">
                    <h3 class="mission-section-title">
                        Missions You Joined
                        ({{ data.ongoing.joined.length }})
                    </h3>

                    <div
                        v-if="!data.ongoing.joined.length"
                        class="empty-state"
                    >
                        <h3>No active missions</h3>

                        <p>
                            External missions your workers joined
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">
                        <div
                            v-for="req in data.ongoing.joined"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">

                                <div>
                                    <h4 class="mission-title">
                                        {{ req.worker.name }}
                                    </h4>

                                    <p class="mission-description">
                                        {{ req.worker.job }}
                                    </p>
                                </div>

                                <div class="mission-top-actions">

                                    <span
                                        class="status-badge"
                                        :class="req.status"
                                    >
                                        {{
                                            req.status.charAt(0).toUpperCase() +
                                            req.status.slice(1)
                                        }}
                                    </span>

                                    <button
                                        class="view-btn"
                                        @click="$inertia.visit('/find-missions')"
                                    >
                                        <Eye class="mini-icon" />
                                    </button>

                                </div>
                            </div>

                            <!-- DETAILS -->
                            <div class="request-details">

                                <div class="detail-item">
                                    <CalendarDays class="mini-icon" />

                                    <div>
                                        <small>Mission Dates</small>

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
                                        <small>Company</small>

                                        <p>
                                            {{
                                                req.mission?.hiring_company?.name ??
                                                'External Company'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <DollarSign class="mini-icon" />

                                    <div>
                                        <small>Rate</small>

                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- MESSAGE -->
                            <div class="message-box">
                                <p>
                                    {{
                                        req.message ||
                                        'No message provided.'
                                    }}
                                </p>
                            </div>

                            <!-- CONTACT -->
                            <div class="accepted-box">
                                ✓ Mission accepted.
                                Contact: {{ req.mission?.hiringCompany?.owner?.phone }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================== -->
            <!-- COMPLETED -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'completed'">

                <!-- CREATED -->
                <div class="mission-section">

                    <h3 class="mission-section-title">
                        Completed Missions You Created
                        ({{ data.completed.created.length }})
                    </h3>

                    <div
                        v-if="!data.completed.created.length"
                        class="empty-state"
                    >
                        <h3>No completed missions</h3>

                        <p>
                            Completed missions for your organization
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">

                        <div
                            v-for="req in data.completed.created"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">

                                <div>

                                    <h4 class="mission-title">
                                        {{ req.mission.title }}
                                    </h4>

                                    <p class="mission-description">
                                        {{ req.worker.job }}
                                    </p>

                                </div>

                                <div class="mission-top-actions">

                                    <span class="status-badge completed">
                                        Completed
                                    </span>

                                    <button
                                        class="view-btn"
                                        @click="$inertia.visit('/missions')"
                                    >
                                        <Eye class="mini-icon" />
                                    </button>

                                </div>
                            </div>

                            <!-- DETAILS -->
                            <div class="request-details">

                                <div class="detail-item">

                                    <CalendarDays class="mini-icon" />

                                    <div>

                                        <small>Mission Dates</small>

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

                                        <small>Worker</small>

                                        <p>
                                            {{ req.worker.name }}

                                            <span>
                                                •
                                                {{
                                                    req.worker?.company?.name ??
                                                    'Self-employed'
                                                }}
                                            </span>
                                        </p>

                                    </div>
                                </div>

                                <div class="detail-item">

                                    <DollarSign class="mini-icon" />

                                    <div>

                                        <small>Final Rate</small>

                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>

                                    </div>
                                </div>
                            </div>

                            <!-- SUMMARY -->
                            <div class="message-box">

                                <div class="message-header">
                                    <Info class="mini-icon" />
                                    <small>Mission Summary</small>
                                </div>

                                <p>
                                    {{
                                        req.message ||
                                        'Mission successfully completed.'
                                    }}
                                </p>

                            </div>

                            <!-- FOOTER -->
                            <div class="accepted-box">
                                ✅ Mission completed successfully.
                            </div>

                        </div>

                    </div>
                </div>

                <!-- JOINED -->
                <div class="mission-section">

                    <h3 class="mission-section-title">
                        Completed Missions You've Joined
                        ({{ data.completed.joined.length }})
                    </h3>

                    <div
                        v-if="!data.completed.joined.length"
                        class="empty-state"
                    >
                        <h3>No completed missions</h3>

                        <p>
                            External missions completed by your workers
                            will appear here.
                        </p>
                    </div>

                    <div v-else class="missions-grid">

                        <div
                            v-for="req in data.completed.joined"
                            :key="req.id"
                            class="mission-card"
                        >

                            <!-- HEADER -->
                            <div class="mission-top">

                                <div>

                                    <h4 class="mission-title">
                                        {{ req.worker.name }}
                                    </h4>

                                    <p class="mission-description">
                                        {{ req.worker.job }}
                                    </p>

                                </div>

                                <div class="mission-top-actions">

                                    <span class="status-badge completed">
                                        Completed
                                    </span>

                                    <button
                                        class="view-btn"
                                        @click="$inertia.visit('/find-missions')"
                                    >
                                        <Eye class="mini-icon" />
                                    </button>

                                </div>
                            </div>

                            <!-- DETAILS -->
                            <div class="request-details">

                                <div class="detail-item">

                                    <CalendarDays class="mini-icon" />

                                    <div>

                                        <small>Mission Dates</small>

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

                                        <small>Company</small>

                                        <p>
                                            {{
                                                req.mission?.hiringCompany?.name ??
                                                'External Company'
                                            }}
                                        </p>

                                    </div>
                                </div>

                                <div class="detail-item">

                                    <DollarSign class="mini-icon" />

                                    <div>

                                        <small>Final Rate</small>

                                        <p>
                                            ${{ req.worker.hourly_rate ?? '--' }}/hour
                                        </p>

                                    </div>
                                </div>
                            </div>

                            <!-- SUMMARY -->
                            <div class="message-box">

                                <div class="message-header">
                                    <Info class="mini-icon" />
                                    <small>Mission Summary</small>
                                </div>

                                <p>
                                    {{
                                        req.message ||
                                        'Mission successfully completed.'
                                    }}
                                </p>

                            </div>

                            <!-- FOOTER -->
                            <div class="accepted-box">
                                ✅ Mission completed successfully.
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <!-- ======================== -->
            <!-- ALL -->
            <!-- ======================== -->
            <div v-else-if="activeTab === 'all'">
                <div v-if="!hasAllData" class="empty-state">
                    <h3>No mission activity yet</h3>

                    <p>
                        Requests, ongoing missions, and completed
                        work will appear here.
                    </p>
                </div>

                <div v-else class="missions-list">
                    <!-- PENDING -->
                    <div class="mission-section">
                        <h3 class="mission-section-title">
                            Pending Activity
                            ({{ counts.pending }})
                        </h3>

                        <div class="missions-grid">
                            <div
                                v-for="req in [
                                    ...data.pending.sent,
                                    ...data.pending.received,
                                    ...data.pending.join
                                ]"
                                :key="`pending-${req.id}`"
                                class="mission-card"
                            >

                                <div class="mission-top">

                                    <h4 class="mission-title">
                                        {{ req.mission?.title ?? req.worker?.name }}
                                    </h4>

                                    <span class="status-badge pending">
                                        Pending
                                    </span>

                                </div>

                                <p class="mission-description">
                                    {{
                                        req.message ||
                                        'Pending mission activity.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ONGOING -->
                    <div class="mission-section">
                        <h3 class="mission-section-title">
                            Ongoing Activity
                            ({{ counts.ongoing }})
                        </h3>

                        <div class="missions-grid">
                            <div
                                v-for="req in [
                                    ...data.ongoing.created,
                                    ...data.ongoing.joined
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
                                        {{
                                            req.status.charAt(0).toUpperCase() +
                                            req.status.slice(1)
                                        }}
                                    </span>
                                </div>

                                <p class="mission-description">
                                    {{
                                        req.message ||
                                        'Mission currently active.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- COMPLETED -->
                    <div class="mission-section">
                        <h3 class="mission-section-title">
                            Completed Activity
                            ({{ counts.completed }})
                        </h3>

                        <div class="missions-grid">
                            <div
                                v-for="req in [
                                    ...data.completed.created,
                                    ...data.completed.joined
                                ]"
                                :key="`completed-${req.id}`"
                                class="mission-card"
                            >

                                <div class="mission-top">

                                    <h4 class="mission-title">
                                        {{ req.mission?.title ?? req.worker?.name }}
                                    </h4>

                                    <span class="status-badge completed">
                                        Completed
                                    </span>

                                </div>

                                <p class="mission-description">
                                    {{
                                        req.message ||
                                        'Mission completed successfully.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
.mission-section .mission-section-title {
    margin: 16px 0;
    font-size: 16px;
    font-weight: 600;
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

    height: 100%;
}

/* ========================= */
/* HEADER */
/* ========================= */
.mission-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;

    margin-bottom: 14px;
}

.mission-title {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;

    color: #111827;
}

.mission-top-actions {
    display: flex;
    align-items: center;
    gap: 8px;

    flex-shrink: 0;
}

/* ========================= */
/* ACTION BUTTONS */
/* ========================= */
.pending-actions {
    display: flex;
    gap: 10px;

    margin-top: 16px;
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
    margin-top: 16px;

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
    color: #535964;
    font-size: 14px;
    line-height: 1.6;

    margin-bottom: 16px;

    min-height: 48px;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;

    overflow: hidden;
    text-overflow: ellipsis;
}

/* ========================= */
/* DETAILS */
/* ========================= */
.request-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

/* ========================= */
/* DETAIL ITEM */
/* ========================= */
.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;

    background: #f9fafb;
    border-radius: 10px;

    padding: 10px;
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
}

.detail-item span {
    color: #6b7280;
    font-size: 12px;
}

/* ========================= */
/* MESSAGE */
/* ========================= */
.message-box {
    margin-top: 14px;

    background: #f9fafb;
    border-left: 3px solid #6366f1;

    padding: 12px;
    border-radius: 10px;

    min-height: 120px;

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
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;

    overflow: hidden;
    text-overflow: ellipsis;
}

.message-date {
    margin-top: auto;

    display: flex;
    align-items: center;
    gap: 6px;

    font-size: 12px;
    color: #6b7280;
}

/* ========================= */
/* WAITING BOX */
/* ========================= */
.waiting-box {
    /* margin-top: auto; */
    margin-top: 16px;

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

/* ========================= */
/* ICONS */
/* ========================= */
.mini-icon {
    width: 14px;
    height: 14px;

    color: #6b7280;
    flex-shrink: 0;
}

/* ========================= */
/* MOBILE */
/* ========================= */
@media (max-width: 768px) {

    .missions-grid {
        grid-template-columns: 1fr;
    }

    .request-details {
        grid-template-columns: 1fr;
    }

}
</style>