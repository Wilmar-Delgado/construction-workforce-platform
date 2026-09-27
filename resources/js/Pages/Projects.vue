<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { jobOptions } from '@/constants/jobs';
import { useTranslate } from '@/composables/useTranslate';
import { useDateTime } from '@/composables/useDateTime';
import { mergeMultiValueInput } from '@/composables/useMultiValueInput';
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
    Eye,
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
    Archive,
    DollarSign
} from 'lucide-vue-next';

// =========================
// PROPS & STATE
// =========================
const { t } = useTranslate();
const { formatDateOnly } = useDateTime();
const page = usePage();
const projects = computed(() => page.props.projects.data || []);
const pagination = computed(() => page.props.projects);
const search = ref(page.props.filters?.search || '');
const activeTab = ref('all');

const modalMode = ref('create');
const isReadOnly = computed(() => modalMode.value === 'view');

const showDeleteModal = ref(false);
const deletingProjectId = ref(null);
const deletingProjectTitle = ref('');

const showArchiveModal = ref(false);
const archivingProjectId = ref(null);
const archivingProjectTitle = ref('');

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
const editingProjectId = ref(null);
function createProjectDefaults() {
    return {
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
    };
}

const form = useForm(createProjectDefaults());

const userId = page.props.auth?.user?.id;
const storageKey = `projectsViewMode_${userId}`;

const viewMode = ref(localStorage.getItem(storageKey) || 'grid');

watch(viewMode, (value) => {
    localStorage.setItem(storageKey, value);
});

const hasAnyProjects = computed(() => counts.value.all > 0);
const hasTabResults = computed(() => projects.value.length > 0);

let timeout;

watch(search, (value) => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        router.get(route('projects.index'), {
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
    router.get(route('projects.index'), {
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
    return formatDateOnly(date, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function statusLabel(status) {
    return t(`common.statuses.${status}`);
}

function staffingProgress(project) {
    return t('projects_page.labels.staffing_progress', {
        committed: project.committed_worker_count ?? 0,
        required: project.workers ?? 0,
    });
}

function staffingSummary(project) {
    return t('projects_page.labels.workers_needed_remaining', {
        workers: project.workers ?? 0,
        remaining: project.remaining_capacity ?? 0,
    });
}

function lifecycleLabel(project) {
    if (project.status === 'open') {
        return project.recruiting_closed_at
            ? t('projects_page.labels.staffing_closed')
            : t('projects_page.labels.recruiting');
    }

    return statusLabel(project.status);
}

function canEditProject(project) {
    return ['draft', 'open'].includes(project.status);
}

function dateRange(startDate, endDate) {
    return t('projects_page.labels.date_range', {
        start: formatDate(startDate),
        end: formatDate(endDate)
    });
}

function toInputDate(date) {
    if (!date) return '';

    return date.slice(0, 10);
}

function addRequirement() {
    form.requirements = mergeMultiValueInput(form.requirements, newRequirement.value);
    newRequirement.value = '';
}

function removeRequirement(index) {
    form.requirements.splice(index, 1);
}

function submitProject() {
    addRequirement();

    if (modalMode.value === 'create') {
        form.post(route('projects.store'), {
            onError: (errors) => {
                console.log(errors);
            },
            onSuccess: closeProjectModal
        });
    } else {
        form.put(route('projects.update', editingProjectId.value), {
            onError: (errors) => {
                console.log(errors);
            },
            onSuccess: closeProjectModal
        });
    }
}

function populateProjectForm(project) {
    editingProjectId.value = project.id;

    form.reset();
    form.clearErrors();

    form.title = project.title;
    form.description = project.description;
    form.start_date = toInputDate(project.start_date);
    form.end_date = toInputDate(project.end_date);
    form.city = project.city;
    form.province = project.province;
    form.country = project.country;
    form.address_line_1 = project.address_line_1;
    form.address_line_2 = project.address_line_2;
    form.postal_code = project.postal_code;
    form.site_name = project.site_name;
    form.directions = project.directions;
    form.job_type = project.job_type;
    form.workers = project.workers;
    form.hourly_rate = project.hourly_rate;
    form.status = project.status;
    form.requirements = project.requirements?.map(r => r.name) || [];

    newRequirement.value = '';
}

function editProject(project) {
    modalMode.value = 'edit';

    populateProjectForm(project);

    showModal.value = true;
}

function viewProject(project) {
    modalMode.value = 'view';

    populateProjectForm(project);

    showModal.value = true;
}

function openCreateModal() {
    resetProjectForm();

    editingProjectId.value = null;
    modalMode.value = 'create';
    showModal.value = true;
}

function duplicateProject(project) {
    populateProjectForm(project);

    const copyCount = projects.value.filter(m =>
        m.title.startsWith(project.title)
    ).length;

    form.title = t('projects_page.copy_title', { title: project.title, count: copyCount });
    form.status = 'draft';

    editingProjectId.value = null;
    modalMode.value = 'create';

    showModal.value = true;
}

function deleteProject(project) {
    deletingProjectId.value = project.id;
    deletingProjectTitle.value = project.title;
    showDeleteModal.value = true;
}

function confirmDeleteProject() {
    form.delete(route('projects.destroy', deletingProjectId.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingProjectId.value = null;
            deletingProjectTitle.value = '';
        }
    });
}

function archiveProject(project) {
    archivingProjectId.value = project.id;
    archivingProjectTitle.value = project.title;
    showArchiveModal.value = true;
}

function confirmArchiveProject() {
    form.put(route('projects.archive', archivingProjectId.value), {
        onSuccess: () => {
            showArchiveModal.value = false;
            archivingProjectId.value = null;
            archivingProjectTitle.value = '';
        }
    });
}

function resetProjectForm() {
    form.defaults(createProjectDefaults());

    form.reset();
    form.clearErrors();
    newRequirement.value = '';
}

function closeProjectModal() {
    showModal.value = false;
    resetProjectForm();

    editingProjectId.value = null;
    modalMode.value = 'create';
}
</script>

<template>
<Head :title="t('projects_page.title')" />

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
        {{ t('projects_page.title') }}
    </template>

    <div class="project-page-container">
        <!-- Header -->
        <div class="page-header">
            <h2>{{ t('projects_page.subtitle') }}</h2>
            <button @click="openCreateModal" class="btn-primary">
                <Plus class="icon" /> {{ t('projects_page.create_project') }}
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <FileText class="stat-icon green" />
                    <p class="stat-label">{{ t('projects_page.stats.total_projects') }}</p>
                </div>
                <p class="stat-value">{{ counts.all }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <Pencil class="stat-icon gray" />
                    <p class="stat-label gray">{{ t('projects_page.stats.draft') }}</p>
                </div>
                <p class="stat-value">{{ counts.draft }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <FolderOpen class="stat-icon green" />
                    <p class="stat-label">{{ t('projects_page.stats.open') }}</p>
                </div>
                <p class="stat-value">{{ counts.open }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <Clock class="stat-icon green" />
                    <p class="stat-label">{{ t('projects_page.stats.in_progress') }}</p>
                </div>
                <p class="stat-value">{{ counts.in_progress }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <CheckCircle class="stat-icon green" />
                    <p class="stat-label">{{ t('projects_page.stats.completed') }}</p>
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
                    :placeholder="t('projects_page.filters.search')"
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
                    {{ tab === 'all' ? t('projects_page.tabs.all') : statusLabel(tab) }}
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
            <!-- USER HAS NO PROJECTS AT ALL -->
            <template v-if="!hasAnyProjects">
                <h3>{{ t('projects_page.empty_title') }}</h3>
                <p>{{ t('projects_page.empty_desc') }}</p>
            </template>

            <!-- TAB IS EMPTY -->
            <template v-else>
                <h3>
                    {{ t('projects_page.empty_tab_title', {
                        status: activeTab === 'all' ? t('projects_page.tabs.all') : statusLabel(activeTab)
                    }) }}
                </h3>

                <p>
                    {{
                        search
                            ? t('projects_page.empty_search_description')
                            : t('projects_page.empty_tab_description', {
                                status: activeTab === 'all' ? t('projects_page.tabs.all') : statusLabel(activeTab)
                            })
                    }}
                </p>
            </template>
        </div>

        <template v-else>
            <!-- GRID VIEW -->
            <div v-if="viewMode === 'grid'">
                <div class="projects-grid">
                    <div
                        v-for="project in projects"
                        :key="project.id"
                        class="project-card"
                    >
                        <!-- Header -->
                        <div class="project-top">
                            <h3 class="project-title">{{ project.title }}</h3>

                            <span class="status-badge" :class="project.status">
                                {{ statusLabel(project.status) }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="project-desc">
                            {{ project.description || t('projects_page.fallbacks.no_description') }}
                        </p>

                        <!-- Meta -->
                        <div class="project-meta">
                            <div class="meta-row">
                                <MapPin class="meta-icon" />
                                {{ project.city }}, {{ project.province }}
                            </div>

                            <div class="meta-row">
                                <CalendarDays class="meta-icon" />
                                {{ dateRange(project.start_date, project.end_date) }}
                            </div>

                            <div class="meta-row">
                                <Users class="meta-icon" />
                                {{ staffingSummary(project) }}
                            </div>

                            <div class="meta-row">
                                <Clock class="meta-icon" />
                                {{ lifecycleLabel(project) }}
                            </div>

                            <div class="meta-row green">
                                <DollarSign class="meta-icon green" />
                                <span v-if="project.hourly_rate > 0">
                                    {{ project.hourly_rate }} {{ t('common.per_hour') }}
                                </span>
                                <span v-else>
                                    --
                                </span>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="project-actions">
                            <button v-if="canEditProject(project)" class="btn-card-edit" @click="editProject(project)">
                                {{ t('projects_page.actions.edit') }}
                            </button>

                            <button v-else class="btn-card-edit" @click="viewProject(project)">
                                {{ t('projects_page.actions.view') }}
                            </button>

                            <button class="icon-btn blue" @click="duplicateProject(project)">
                                <Copy class="icon" />
                            </button>

                            <button v-if="project.can_delete" class="icon-btn danger" @click="deleteProject(project)">
                                <Trash2 class="icon" />
                            </button>

                            <button v-if="project.can_archive" class="icon-btn danger" @click="archiveProject(project)">
                                <Archive class="icon" />
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
                    { key: 'title', label: t('projects_page.table.title'), sortable: true },
                    { key: 'status', label: t('projects_page.table.status'), sortable: true },
                    { key: 'start_date', label: t('projects_page.table.start_date') },
                    { key: 'end_date', label: t('projects_page.table.end_date') },
                    { key: 'city', label: t('projects_page.table.city') },
                    { key: 'staffing', label: t('projects_page.table.staffing') },
                    { key: 'actions', label: t('projects_page.table.actions') }
                ]"
                :rows="projects"
                min-width="960px"
                sortable
                :sort="'title'"
                :direction="'asc'"
            >
                <tr v-for="project in projects" :key="project.id">
                    <td class="project-title-cell">{{ project.title }}</td>
                    <td class="table-cell-nowrap">{{ lifecycleLabel(project) }}</td>
                    <td class="table-cell-nowrap">{{ formatDate(project.start_date) }}</td>
                    <td class="table-cell-nowrap">{{ formatDate(project.end_date) }}</td>
                    <td class="table-cell-nowrap">{{ project.city }}, {{ project.province }}</td>
                    <td class="table-cell-nowrap">{{ staffingProgress(project) }}</td>
                    <td class="actions" style="text-align: right;">
                        <!-- <button class="table-icon-btn blue" @click="editProject(project)">
                            <Pencil class="table-icon" />
                        </button> -->
                        <button class="table-icon-btn blue" @click="canEditProject(project) ? editProject(project) : viewProject(project)">
                            <component
                                :is="canEditProject(project) ? Pencil : Eye"
                                class="table-icon"
                            />
                        </button>
                        <button class="table-icon-btn blue" @click="duplicateProject(project)">
                            <Copy class="table-icon" />
                        </button>
                        <button v-if="project.can_delete" class="table-icon-btn danger" @click="deleteProject(project)">
                            <Trash2 class="table-icon" />
                        </button>
                        <button v-if="project.can_archive" class="table-icon-btn danger" @click="archiveProject(project)">
                            <Archive class="table-icon" />
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
            :title="t('projects_page.delete_modal.title', { title: deletingProjectTitle })"
            :message="t('projects_page.delete_modal.message')"
            :subtitle="t('projects_page.delete_modal.subtitle')"
            :confirm-text="t('projects_page.delete_modal.confirm')"
            :cancel-text="t('common.cancel')"
            danger
            :loading="form.processing"
            @confirm="confirmDeleteProject"
        />

        <!-- ARCHIVE MODAL -->
        <ConfirmModal
            v-model="showArchiveModal"
            :title="t('projects_page.archive_modal.title', { title: archivingProjectTitle })"
            :message="t('projects_page.archive_modal.message')"
            :subtitle="t('projects_page.archive_modal.subtitle')"
            :confirm-text="t('projects_page.archive_modal.confirm')"
            :cancel-text="t('common.cancel')"
            danger
            :loading="form.processing"
            @confirm="confirmArchiveProject"
        />

        <!-- EDIT/CREATE MODAL -->
        <BaseModal
            v-model="showModal"
            @close="closeProjectModal"
            :title="modalMode === 'create'
                ? t('projects_page.add_modal.title')
                : modalMode === 'edit'
                    ? t('projects_page.edit_modal.title', { title: form.title })
                    : t('projects_page.view_modal.title', { title: form.title })"
        >
            <form id="project-form" @submit.prevent="submitProject">
                <!-- BODY -->
                <div class="form-grid">
                    <!-- TITLE -->
                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.project_title') }}</label>
                        <input v-model="form.title" type="text" :disabled="isReadOnly" />
                        <span v-if="form.errors.title" class="error">{{ form.errors.title }}</span>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.description') }}</label>
                        <textarea v-model="form.description" :disabled="isReadOnly"></textarea>
                        <span v-if="form.errors.description" class="error">{{ form.errors.description }}</span>
                    </div>

                    <!-- DATES -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.start_date') }}</label>
                        <input v-model="form.start_date" type="date" :disabled="isReadOnly" />
                        <span v-if="form.errors.start_date" class="error">{{ form.errors.start_date }}</span>
                    </div>

                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.end_date') }}</label>
                        <input v-model="form.end_date" type="date" :disabled="isReadOnly" />
                        <span v-if="form.errors.end_date" class="error">{{ form.errors.end_date }}</span>
                    </div>

                    <!-- SITE NAME -->
                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.site_name') }}</label>
                        <input v-model="form.site_name" type="text" :disabled="isReadOnly" />
                    </div>

                    <!-- ADDRESS -->
                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.address_line_1') }}</label>
                        <input v-model="form.address_line_1" type="text" :disabled="isReadOnly" />
                        <span v-if="form.errors.address_line_1" class="error">{{ form.errors.address_line_1 }}</span>
                    </div>

                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.address_line_2') }}</label>
                        <input v-model="form.address_line_2" type="text" :disabled="isReadOnly" />
                    </div>

                    <!-- CITY / PROVINCE -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.city') }}</label>
                        <input v-model="form.city" type="text" :disabled="isReadOnly" />
                        <span v-if="form.errors.city" class="error">{{ form.errors.city }}</span>
                    </div>

                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.province') }}</label>
                        <input v-model="form.province" type="text" :disabled="isReadOnly" />
                        <span v-if="form.errors.province" class="error">{{ form.errors.province }}</span>
                    </div>

                    <!-- POSTAL -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.postal_code') }}</label>
                        <input v-model="form.postal_code" type="text" :disabled="isReadOnly" />
                    </div>

                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.country') }}</label>
                        <input v-model="form.country" type="text" :disabled="isReadOnly" />
                        <span v-if="form.errors.country" class="error">{{ form.errors.country }}</span>
                    </div>

                    <!-- DIRECTIONS -->
                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.directions') }}</label>
                        <textarea v-model="form.directions" :disabled="isReadOnly"></textarea>
                    </div>

                    <!-- JOB TYPE -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.job_type') }}</label>
                        <select v-model="form.job_type" :disabled="isReadOnly">
                            <option disabled value="">{{ t('profiles_page.add_modal.job_select') }}</option>
                            <option v-for="job in jobOptions" :key="job" :value="job">
                                {{ t(`profiles_page.jobs.${job}`) }}
                            </option>
                        </select>
                        <span v-if="form.errors.job_type" class="error">{{ form.errors.job_type }}</span>
                    </div>

                    <!-- WORKERS -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.number_of_workers') }}</label>
                        <input v-model="form.workers" type="number" :disabled="isReadOnly" />
                        <span v-if="form.errors.workers" class="error">{{ form.errors.workers }}</span>
                    </div>

                    <!-- RATE -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.hourly_rate') }}</label>
                        <input v-model="form.hourly_rate" type="number" step="0.01" min="0" :disabled="isReadOnly" />
                        <span v-if="form.errors.hourly_rate" class="error">{{ form.errors.hourly_rate }}</span>
                    </div>

                    <!-- STATUS -->
                    <div class="form-group">
                        <label>{{ t('projects_page.add_modal.status') }}</label>
                        <select v-model="form.status" :disabled="isReadOnly">
                            <option value="draft">{{ t('projects_page.stats.draft') }}</option>
                            <option value="open">{{ t('projects_page.stats.open') }}</option>
                            <option
                                v-if="isReadOnly && !['draft', 'open'].includes(form.status)"
                                :value="form.status"
                            >
                                {{ statusLabel(form.status) }}
                            </option>
                        </select>
                        <span v-if="form.errors.status" class="error">{{ form.errors.status }}</span>
                    </div>

                    <!-- REQUIREMENTS -->
                    <div class="form-group full">
                        <label>{{ t('projects_page.add_modal.requirements') }}</label>

                        <div class="input-with-button">
                            <input
                                v-model="newRequirement"
                                type="text"
                                :placeholder="t('projects_page.add_modal.requirements_placeholder')"
                                @keydown.enter.prevent="addRequirement"
                                :disabled="isReadOnly"
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
                                    v-if="!isReadOnly"
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
                    v-if="modalMode !== 'view'"
                    type="submit" 
                    form="project-form"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing 
                        ? t('projects_page.add_modal.saving')
                        : (modalMode === 'create'
                            ? t('projects_page.add_modal.save')
                            : t('projects_page.edit_modal.save')) 
                    }}
                </button>
                <button type="button" class="btn-thirdary" @click="closeProjectModal">
                    {{ modalMode === 'view' ? t('common.close') : t('common.cancel') }}
                </button>
            </template>
        </BaseModal>
    </div>
</SidebarLayout>
</template>

<style scoped src="../../css/pages/projects.css"></style>
