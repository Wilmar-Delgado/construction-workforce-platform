<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { jobOptions } from '@/constants/jobs';
import { useTranslate } from '@/composables/useTranslate';
import DataTable from '@/Components/tables/DataTable.vue';
import BasePagination from '@/Components/base/BasePagination.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import ConfirmModal from '@/Components/base/ConfirmModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import {
    Plus,
    FileText,
    Pencil,
    FolderOpen,
    Clock,
    CheckCircle,
    LayoutGrid,
    List,
    Search,
    MapPin,
    CalendarDays,
    Users,
    Copy,
    Trash2,
    DollarSign
} from 'lucide-vue-next';

// =========================
// PROPS & STATE
// =========================
const { t } = useTranslate();
const page = usePage();
const missions = computed(() => page.props.missions.data || []);
const pagination = computed(() => page.props.missions);
const search = ref(page.props.filters?.search || '');
const activeTab = ref('all');
const action = ref('Create');

const showDeleteModal = ref(false);
const deletingMissionId = ref(null);
const deletingMissionTitle = ref('');
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true }
);
const showModal = ref(false);
const newRequirement = ref('');
const editingMissionId = ref(null);
const form = useForm({
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    city: '',
    province: 'AB',
    country: 'Canada',
    address_line_1: '',
    address_line_2: '',
    postal_code: '',
    site_name: '',
    directions: '',
    job_type: '',
    workers: '',
    hourly_rate: '',
    status: 'draft',
    requirements: [],
});

const userId = page.props.auth?.user?.id;
const storageKey = `missionsViewMode_${userId}`;

const viewMode = ref(localStorage.getItem(storageKey) || 'grid');

watch(viewMode, (value) => {
    localStorage.setItem(storageKey, value);
});

const hasAnyMissions = computed(() => counts.value.all > 0);
const hasTabResults = computed(() => missions.value.length > 0);

let timeout;

watch(search, (value) => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        router.get(route('missions.index'), {
            search: value,
            status: activeTab.value
        }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, 300);
});

watch(activeTab, (value) => {
    router.get(route('missions.index'), {
        search: search.value,
        status: value
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});

const counts = computed(() => ({
    all: page.props.counts.all,
    draft: page.props.counts.draft,
    open: page.props.counts.open,
    in_progress: page.props.counts.in_progress,
    completed: page.props.counts.completed,
}));

// =========================
// ACTIONS
// =========================
function formatDate(date) {
    if (!date) return '';

    return new Date(date).toLocaleDateString('en-CA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function toInputDate(date) {
    if (!date) return '';

    return date.slice(0, 10);
}

function addRequirement() {
    if (newRequirement.value.trim()) {
        form.requirements.push(newRequirement.value.trim());
        newRequirement.value = '';
    }
}

function removeRequirement(index) {
    form.requirements.splice(index, 1);
}

function submitMission() {
    if (action.value === 'Create') {
        form.post(route('missions.store'), {
            onError: (errors) => {
                console.log(errors);
            },
            onSuccess: resetModal
        });
    } else {
        form.put(route('missions.update', editingMissionId.value), {
            onError: (errors) => {
                console.log(errors);
            },
            onSuccess: resetModal
        });
    }
}

function editMission(mission) {
    action.value = 'Edit';
    editingMissionId.value = mission.id;

    form.reset();
    form.title = mission.title;
    form.description = mission.description;
    form.start_date = toInputDate(mission.start_date);
    form.end_date = toInputDate(mission.end_date);
    form.city = mission.city;
    form.province = mission.province;
    form.country = mission.country;
    form.address_line_1 = mission.address_line_1;
    form.address_line_2 = mission.address_line_2;
    form.postal_code = mission.postal_code;
    form.site_name = mission.site_name;
    form.directions = mission.directions;
    form.job_type = mission.job_type;
    form.workers = mission.workers;
    form.hourly_rate = mission.hourly_rate;
    form.status = mission.status;
    form.requirements = mission.requirements?.map(r => r.name) || [];
    newRequirement.value = '';

    showModal.value = true;
}

function duplicateMission(mission) {
    form.reset();
    
    const copyCount = missions.value.filter(m =>
        m.title.startsWith(mission.title)
    ).length;

    form.title = `${mission.title} (Copy ${copyCount})`;
    
    form.description = mission.description;
    form.start_date = toInputDate(mission.start_date);
    form.end_date = toInputDate(mission.end_date);
    form.city = mission.city;
    form.province = mission.province;
    form.country = mission.country;
    form.address_line_1 = mission.address_line_1;
    form.address_line_2 = mission.address_line_2;
    form.postal_code = mission.postal_code;
    form.site_name = mission.site_name;
    form.directions = mission.directions;
    form.job_type = mission.job_type;
    form.workers = mission.workers;
    form.hourly_rate = mission.hourly_rate;
    form.status = 'draft';
    form.requirements = mission.requirements?.map(r => r.name) || [];
    newRequirement.value = '';

    action.value = 'Create';
    editingMissionId.value = null;

    showModal.value = true;
}

function deleteMission(mission) {
    deletingMissionId.value = mission.id;
    deletingMissionTitle.value = mission.title;
    showDeleteModal.value = true;
}

function confirmDeleteMission() {
    form.delete(route('missions.destroy', deletingMissionId.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingMissionId.value = null;
            deletingMissionTitle.value = '';
        }
    });
}

function resetModal() {
    showModal.value = false;

    form.defaults({
        title: '',
        description: '',
        start_date: '',
        end_date: '',
        city: '',
        province: 'AB',
        country: 'Canada',
        address_line_1: '',
        address_line_2: '',
        postal_code: '',
        site_name: '',
        directions: '',
        job_type: '',
        workers: '',
        hourly_rate: '',
        status: 'draft',
        requirements: [],
    });

    form.reset();
    form.clearErrors();
    newRequirement.value = '';

    action.value = 'Create';
    editingMissionId.value = null;
}
</script>

<template>
<Head title="Missions" />

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
        {{ t('missions_page.title') }}
    </template>

    <div class="mission-page-container">
        <!-- Header -->
        <div class="page-header">
            <h2>{{ t('missions_page.subtitle') }}</h2>
            <button @click="resetModal(); showModal = true" class="btn-primary">
                <Plus class="icon" /> {{ t('missions_page.create_mission') }}
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <FileText class="stat-icon green" />
                    <p class="stat-label">{{ t('missions_page.stats.total_missions') }}</p>
                </div>
                <p class="stat-value">{{ counts.all }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <Pencil class="stat-icon gray" />
                    <p class="stat-label gray">{{ t('missions_page.stats.draft') }}</p>
                </div>
                <p class="stat-value">{{ counts.draft }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <FolderOpen class="stat-icon green" />
                    <p class="stat-label">{{ t('missions_page.stats.open') }}</p>
                </div>
                <p class="stat-value">{{ counts.open }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <Clock class="stat-icon green" />
                    <p class="stat-label">{{ t('missions_page.stats.in_progress') }}</p>
                </div>
                <p class="stat-value">{{ counts.in_progress }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <CheckCircle class="stat-icon green" />
                    <p class="stat-label">{{ t('missions_page.stats.completed') }}</p>
                </div>
                <p class="stat-value">{{ counts.completed }}</p>
            </div>
        </div>

        <div class="toolbar">
            <!-- SEARCH -->
            <div class="search-box">
                <Search class="search-icon" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search missions..."
                />
            </div>

            <!-- TABS -->
            <div class="tabs">
                <button
                    v-for="tab in ['all','draft','open','in_progress','completed']"
                    :key="tab"
                    :class="{ active: activeTab === tab }"
                    @click="activeTab = tab"
                >
                    {{ tab.replace('_', ' ').charAt(0).toUpperCase() + tab.replace('_', ' ').slice(1) }}
                </button>
            </div>

            <!-- VIEW TOGGLE -->
            <div class="view-toggle">
                <button
                    :class="{ active: viewMode === 'grid' }"
                    @click="viewMode = 'grid'"
                >
                    <LayoutGrid class="icon" />
                </button>

                <button
                    :class="{ active: viewMode === 'table' }"
                    @click="viewMode = 'table'"
                >
                    <List class="icon" />
                </button>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="!hasTabResults" class="empty-state">
            <!-- USER HAS NO MISSIONS AT ALL -->
            <template v-if="!hasAnyMissions">
                <h3>{{ t('missions_page.empty_title') }}</h3>
                <p>{{ t('missions_page.empty_desc') }}</p>
            </template>

            <!-- TAB IS EMPTY -->
            <template v-else>
                <h3>
                    No
                    {{
                        activeTab === 'all'
                            ? 'missions'
                            : activeTab.replace('_', ' ')
                    }}
                    missions found
                </h3>

                <p>
                    {{
                        search
                            ? 'Try adjusting your search or filters.'
                            : `You currently have no ${activeTab.replace('_', ' ')} missions.`
                    }}
                </p>
            </template>
        </div>

        <template v-else>
            <!-- GRID VIEW -->
            <div v-if="viewMode === 'grid'">
                <div class="missions-grid">
                    <div
                        v-for="mission in missions"
                        :key="mission.id"
                        class="mission-card"
                    >
                        <!-- Header -->
                        <div class="mission-top">
                            <h3 class="mission-title">{{ mission.title }}</h3>

                            <span class="status-badge" :class="mission.status">
                                {{ mission.status.replace('_', ' ') }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="mission-desc">
                            {{ mission.description || 'No description provided.' }}
                        </p>

                        <!-- Meta -->
                        <div class="mission-meta">
                            <div class="meta-row">
                                <MapPin class="meta-icon" />
                                {{ mission.city }}, {{ mission.province }}
                            </div>

                            <div class="meta-row">
                                <CalendarDays class="meta-icon" />
                                {{ formatDate(mission.start_date) }} to {{ formatDate(mission.end_date) }}
                            </div>

                            <div class="meta-row">
                                <Users class="meta-icon" />
                                <span v-if="mission.workers > 0">
                                    {{ mission.workers }} worker{{ mission.workers > 1 ? 's' : '' }} needed
                                </span>
                                <span v-else>
                                    N/A
                                </span>
                            </div>

                            <div class="meta-row green">
                                <DollarSign class="meta-icon green" />
                                <span v-if="mission.hourly_rate > 0">
                                    {{ mission.hourly_rate }}/hour
                                </span>
                                <span v-else>
                                    --
                                </span>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mission-actions">
                            <button class="btn-card-edit" @click="editMission(mission)">
                                Edit
                            </button>

                            <button class="icon-btn blue" @click="duplicateMission(mission)">
                                <Copy class="icon" />
                            </button>

                            <button class="icon-btn danger" @click="deleteMission(mission)">
                                <Trash2 class="icon" />
                            </button>
                        </div>
                    </div>
                </div>
                <BasePagination :links="pagination.links" />
            </div>

            <!-- TABLE VIEW -->
            <DataTable
                v-else
                :columns="[
                    { key: 'title', label: 'Title', sortable: true },
                    { key: 'status', label: 'Status', sortable: true },
                    { key: 'start_date', label: 'Start Date' },
                    { key: 'end_date', label: 'End Date' },
                    { key: 'city', label: 'City' },
                    { key: 'actions', label: 'Actions' }
                ]"
                :rows="missions"
                sortable
                :sort="'title'"
                :direction="'asc'"
            >
                <tr v-for="mission in missions" :key="mission.id">
                    <td>{{ mission.title }}</td>
                    <td>{{ mission.status.charAt(0).toUpperCase() + mission.status.slice(1).replace('_', ' ') }}</td>
                    <td>{{ formatDate(mission.start_date) }}</td>
                    <td>{{ formatDate(mission.end_date) }}</td>
                    <td>{{ mission.city }}, {{ mission.province }}</td>
                    <td class="actions" style="text-align: right;">
                        <button class="table-icon-btn blue" @click="editMission(mission)">
                            <Pencil class="table-icon" />
                        </button>
                        <button class="table-icon-btn blue" @click="duplicateMission(mission)">
                            <Copy class="table-icon" />
                        </button>
                        <button class="table-icon-btn danger" @click="deleteMission(mission)">
                            <Trash2 class="table-icon" />
                        </button>
                    </td>
                </tr>
                <template #pagination>
                    <BasePagination :links="pagination.links" />
                </template>
            </DataTable>
        </template>

        <!-- DELETE MODAL -->
        <ConfirmModal
            v-model="showDeleteModal"
            title="Delete Mission"
            message="Are you sure you want to delete this mission?"
            subtitle="This action cannot be undone."
            :item-name="deletingMissionTitle"
            confirm-text="Yes, Delete"
            cancel-text="Cancel"
            danger
            :loading="form.processing"
            @confirm="confirmDeleteMission"
        />

        <!-- EDIT/CREATE MODAL -->
        <BaseModal
            v-model="showModal"
            @close="form.reset()"
            max-width="900px"
            :title="action === 'Create'
                ? t('missions_page.add_modal.title')
                : t('missions_page.edit_modal.title') + ' - ' + form.title"
        >
            <form id="mission-form" @submit.prevent="submitMission">
                <!-- BODY -->
                <div class="form-grid">
                    <!-- TITLE -->
                    <div class="form-group full">
                        <label>{{ t('missions_page.add_modal.mission_title') }}</label>
                        <input v-model="form.title" type="text" />
                        <span v-if="form.errors.title" class="error">{{ form.errors.title }}</span>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="form-group full">
                        <label>{{ t('missions_page.add_modal.description') }}</label>
                        <textarea v-model="form.description"></textarea>
                        <span v-if="form.errors.description" class="error">{{ form.errors.description }}</span>
                    </div>

                    <!-- DATES -->
                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.start_date') }}</label>
                        <input v-model="form.start_date" type="date" />
                        <span v-if="form.errors.start_date" class="error">{{ form.errors.start_date }}</span>
                    </div>

                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.end_date') }}</label>
                        <input v-model="form.end_date" type="date" />
                        <span v-if="form.errors.end_date" class="error">{{ form.errors.end_date }}</span>
                    </div>

                    <!-- SITE NAME -->
                    <div class="form-group full">
                        <label>{{ t('missions_page.add_modal.site_name') }}</label>
                        <input v-model="form.site_name" type="text" />
                    </div>

                    <!-- ADDRESS -->
                    <div class="form-group full">
                        <label>{{ t('missions_page.add_modal.address_line_1') }}</label>
                        <input v-model="form.address_line_1" type="text" />
                        <span v-if="form.errors.address_line_1" class="error">{{ form.errors.address_line_1 }}</span>
                    </div>

                    <div class="form-group full">
                        <label>{{ t('missions_page.add_modal.address_line_2') }}</label>
                        <input v-model="form.address_line_2" type="text" />
                    </div>

                    <!-- CITY / PROVINCE -->
                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.city') }}</label>
                        <input v-model="form.city" type="text" />
                        <span v-if="form.errors.city" class="error">{{ form.errors.city }}</span>
                    </div>

                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.province') }}</label>
                        <input v-model="form.province" type="text" />
                        <span v-if="form.errors.province" class="error">{{ form.errors.province }}</span>
                    </div>

                    <!-- POSTAL -->
                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.postal_code') }}</label>
                        <input v-model="form.postal_code" type="text" />
                    </div>

                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.country') }}</label>
                        <input v-model="form.country" type="text" />
                        <span v-if="form.errors.country" class="error">{{ form.errors.country }}</span>
                    </div>

                    <!-- DIRECTIONS -->
                    <div class="form-group full">
                        <label>{{ t('missions_page.add_modal.directions') }}</label>
                        <textarea v-model="form.directions"></textarea>
                    </div>

                    <!-- JOB TYPE -->
                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.job_type') }}</label>
                        <select v-model="form.job_type">
                            <option disabled value="">{{ t('profiles_page.add_modal.job_select') }}</option>
                            <option v-for="job in jobOptions" :key="job" :value="job">
                                {{ t(`profiles_page.jobs.${job}`) }}
                            </option>
                        </select>
                        <span v-if="form.errors.job_type" class="error">{{ form.errors.job_type }}</span>
                    </div>

                    <!-- WORKERS -->
                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.number_of_workers') }}</label>
                        <input v-model="form.workers" type="number" />
                        <span v-if="form.errors.workers" class="error">{{ form.errors.workers }}</span>
                    </div>

                    <!-- RATE -->
                    <div class="form-group">
                        <label>Hourly Rate ($)</label>
                        <input v-model="form.hourly_rate" type="number" step="0.01" min="0" />
                        <span v-if="form.errors.hourly_rate" class="error">{{ form.errors.hourly_rate }}</span>
                    </div>

                    <!-- STATUS -->
                    <div class="form-group">
                        <label>{{ t('missions_page.add_modal.status') }}</label>
                        <select v-model="form.status">
                            <option value="draft">{{ t('missions_page.stats.draft') }}</option>
                            <option value="open">{{ t('missions_page.stats.open') }}</option>
                            <option value="in_progress">{{ t('missions_page.stats.in_progress') }}</option>
                            <option value="completed">{{ t('missions_page.stats.completed') }}</option>
                        </select>
                        <span v-if="form.errors.status" class="error">{{ form.errors.status }}</span>
                    </div>

                    <!-- REQUIREMENTS -->
                    <div class="form-group full">
                        <label>Requirements</label>

                        <div class="input-with-button">
                            <input
                                v-model="newRequirement"
                                type="text"
                                placeholder="Add requirements (e.g. 'Own tools', '5+ Years of Experience, etc')"
                                @keyup.enter="addRequirement"
                            />

                            <button type="button" @click="addRequirement">+</button>
                        </div>

                        <div class="tag-list">
                            <span
                                v-for="(req, index) in form.requirements"
                                :key="index"
                                class="tag blue-tag"
                            >
                                {{ req }}

                                <button
                                    type="button"
                                    @click="removeRequirement(index)"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </form>
            <template #footer>
                <button
                    type="submit" 
                    form="mission-form"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing 
                        ? t('missions_page.add_modal.saving')
                        : (action === 'Create'
                            ? t('missions_page.add_modal.save')
                            : t('missions_page.edit_modal.save')) 
                    }}
                </button>
                <button type="button" class="btn-thirdary" @click="showModal=false">
                    {{ t('missions_page.add_modal.cancel') }}
                </button>
            </template>
        </BaseModal>
    </div>
</SidebarLayout>
</template>