<script setup>
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, defineAsyncComponent } from 'vue';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import BasePagination from '@/Components/base/BasePagination.vue';
import ConfirmModal from '@/Components/base/ConfirmModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import { useAuthStore } from '@/stores/auth';
import { jobOptions } from '@/constants/jobs';
import { useTranslate } from '@/composables/useTranslate';
import { useUserRole } from '@/composables/useUserRole';
import { usePermissions } from '@/composables/usePermissions';
import { useDataTable } from '@/composables/useDataTable';
import { Edit2, Trash2, Plus } from 'lucide-vue-next';

const { t } = useTranslate();

// =========================
// LAZY LOADED COMPONENTS
// =========================
const BaseModal = defineAsyncComponent(() =>
    import('@/Components/base/BaseModal.vue')
);

const DataTable = defineAsyncComponent({
    loader: () => import('@/Components/tables/DataTable.vue'),
    loadingComponent: {
        template: `<div>${t('common.loading')}</div>`
    },
    delay: 200,
});

// =========================
// PROPS & STATE
// =========================
const { isSelfEmployed } = useUserRole();
const { can } = usePermissions();

const page = usePage();
const authStore = useAuthStore();

const companyName = authStore.user.company?.name ?? '';
const userName = authStore.userName;

const columns = [
    { key: 'name', label: t('profiles_page.table.name'), sortable: true },
    { key: 'job', label: t('profiles_page.table.job'), sortable: true },
    { key: 'years_experience', label: t('profiles_page.table.experience'), sortable: true },
    { key: 'hourly_rate', label: t('profiles_page.table.rate'), sortable: true },
    { key: 'rating', label: t('profiles_page.table.rating') },
    { key: 'skills', label: t('profiles_page.table.skills') },
    { key: 'actions', label: t('profiles_page.table.actions') }
];

const workers = computed(() => page.props.workerProfiles.data || []);
const pagination = computed(() => page.props.workerProfiles);
const filters = computed(() => page.props.filters);
const hasProfile = page.props.hasWorkerProfile;

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true }
);

const showModal = ref(false);
const action = ref('Create');
const editingWorkerId = ref(null);

const newCertificate = ref('');
const newSkill = ref('');

const form = useForm({
    name: isSelfEmployed.value ? userName : '',
    job: '',
    experience: '',
    rate: '',
    company: companyName,
    certifications: [],
    skills: []
});

const { handleSort } = useDataTable('worker-profiles.index', filters);

const showDeleteModal = ref(false);
const deletingWorker = ref(null);

// =========================
// FORM HELPERS
// =========================
function addCertificate() {
    if (newCertificate.value.trim()) {
        form.certifications.push(newCertificate.value.trim());
        newCertificate.value = '';
    }
}

function addSkill() {
    if (newSkill.value.trim()) {
        form.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
}

function removeCertificate(index) {
    form.certifications.splice(index, 1);
}

function removeSkill(index) {
    form.skills.splice(index, 1);
}

// =========================
// CRUD ACTIONS
// =========================
function submitWorker() {
    if (action.value === 'Create') {
        form.post(route('worker-profiles.store'), {
            onSuccess: resetModal
        });
    } else {
        form.put(route('worker-profiles.update', editingWorkerId.value), {
            onSuccess: resetModal
        });
    }
}

function editWorker(worker) {
    action.value = 'Edit';
    editingWorkerId.value = worker.id;

    form.reset();

    form.name = worker.name ?? '';
    form.job = worker.job ?? '';
    form.experience = worker.years_experience ?? '';
    form.rate = worker.hourly_rate ?? '';
    form.company = worker.company?.name ?? companyName;
    form.certifications = worker.certifications?.map(c => c.name) ?? [];
    form.skills = worker.skills?.map(s => s.name) ?? [];

    showModal.value = true;
}

function deleteWorker(worker) {
    deletingWorker.value = worker;
    showDeleteModal.value = true;
}

function confirmDeleteWorker() {
    form.delete(route('worker-profiles.destroy', deletingWorker.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
}

function resetModal() {
    showModal.value = false;

    form.reset();

    form.name = isSelfEmployed.value ? userName : '';
    form.company = companyName;

    action.value = 'Create';
    editingWorkerId.value = null;
}
</script>

<template>
<Head :title="isSelfEmployed ? t('profiles_page.self_title') : t('profiles_page.company_title')" />

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
        {{ isSelfEmployed ? t('profiles_page.self_title') : t('profiles_page.company_title') }}
    </template>

    <div class="page-container">
        <!-- HEADER -->
        <div class="page-header">
            <h2>
                {{ isSelfEmployed 
                    ? t('profiles_page.self_subtitle') 
                    : t('profiles_page.company_subtitle') 
                }}
            </h2>

            <!-- COMPANY -->
            <button 
                v-if="!isSelfEmployed"
                @click="resetModal(); showModal = true" 
                class="btn-primary"
            >
                <Plus class="icon" /> {{ t('profiles_page.add_worker') }}
            </button>

            <!-- SELF-EMPLOYED -->
            <template v-else>
                <button 
                    v-if="!hasProfile"
                    @click="resetModal(); showModal = true" 
                    class="btn-primary"
                >
                    <Plus class="icon" /> {{ t('profiles_page.create_profile') }}
                </button>

                <button 
                    v-else
                    @click="editWorker(workers[0])"
                    class="btn-primary"
                >
                    <Edit2 class="icon" /> {{ t('profiles_page.edit_profile') }}
                </button>
            </template>
        </div>

        <!-- SELF-EMPLOYED VIEW -->
        <div v-if="can('edit_own_profile')">
            <div v-if="workers.length" class="profile-card">
                <div class="profile-top">
                    <div class="avatar">
                        {{ workers[0].name.charAt(0) }}
                    </div>

                    <div class="profile-info">
                        <h3>{{ workers[0].name }}</h3>
                        <p class="job">
                            {{ t(`profiles_page.jobs.${workers[0].job}`) }}
                        </p>
                    </div>

                    <div class="rating">
                        ⭐ {{ workers[0].rating ?? t('common.not_available') }}
                    </div>
                </div>

                <div class="profile-meta">
                    <div class="meta-box">
                        <p class="label">{{ t('profiles_page.table.experience') }}</p>
                        <p>{{ t('profiles_page.experience_years_short', { count: workers[0].years_experience }) }}</p>
                    </div>

                    <div class="meta-box">
                        <p class="label">{{ t('profiles_page.table.rate') }}</p>
                        <p>${{ workers[0].hourly_rate }} {{ t('common.per_hour') }}</p>
                    </div>
                </div>

                <div class="section">
                    <p class="section-label">{{ t('profiles_page.labels.certifications') }}</p>
                    <div class="tag-row">
                        <span v-for="cert in workers[0].certifications" :key="cert.id" class="tag cert">
                            {{ cert.name }}
                        </span>
                    </div>
                </div>

                <div class="section">
                    <p class="section-label">{{ t('profiles_page.labels.skills') }}</p>
                    <div class="tag-row">
                        <span v-for="skill in workers[0].skills" :key="skill.id" class="tag">
                            {{ skill.name }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <h3>{{ t('profiles_page.empty_title') }}</h3>
                <p>{{ t('profiles_page.empty_desc') }}</p>
            </div>
        </div>

        <!-- TABLE VIEW -->
        <div v-else>
            <DataTable
                v-if="can('manage_workers')"
                :columns="columns"
                :rows="workers"
                :emptyText="t('profiles_page.empty_table', { action: t('profiles_page.add_worker') })"
                sortable
                :sort="filters.sort"
                :direction="filters.direction"
                @sort="handleSort"
            >
                <tr v-for="worker in workers" :key="worker.id">
                    <td>{{ worker.name }}</td>
                    <td>{{ t(`profiles_page.jobs.${worker.job}`) }}</td>
                    <td>{{ t('profiles_page.experience_years_short', { count: worker.years_experience }) }}</td>
                    <td>${{ worker.hourly_rate }} {{ t('common.per_hour') }}</td>
                    <td>⭐{{ worker.rating ?? t('common.not_available') }}</td>
                    <td class="skills">
                        <span v-for="skill in worker.skills.slice(0,2)" :key="skill.id" class="skill-tag">
                            {{ skill.name }}
                        </span>
                        <span v-if="worker.skills?.length > 2" class="more-skills">
                            +{{ worker.skills.length - 2 }}
                        </span>
                    </td>
                    <td class="actions" style="text-align: right;">
                        <button @click="editWorker(worker)" class="table-icon-btn blue">
                            <Edit2 class="table-icon" />
                        </button>
                        <button @click="deleteWorker(worker)" class="table-icon-btn danger">
                            <Trash2 class="table-icon" />
                        </button>
                    </td>
                </tr>

                <template #pagination>
                    <BasePagination :links="pagination.links" />
                </template>
            </DataTable>
        </div>

        <!-- DELETE MODAL -->
        <ConfirmModal
            v-model="showDeleteModal"
            :title="t('profiles_page.delete_modal.title')"
            :message="t('profiles_page.delete_modal.message')"
            :item-name="deletingWorker?.name"
            :confirm-text="t('profiles_page.delete_modal.confirm')"
            :cancel-text="t('profiles_page.delete_modal.cancel')"
            danger
            :loading="form.processing"
            @confirm="confirmDeleteWorker"
        />

        <!-- EDIT/CREATE MODAL -->
        <BaseModal
            v-if="showModal"
            v-model="showModal"
            max-width="900px"
            :title="action === 'Create'
                ? t('profiles_page.add_modal.title')
                : t('profiles_page.edit_modal.title', { name: form.name })"
        >
            <form id="worker-form" @submit.prevent="submitWorker">
                <!-- ROW 1 -->
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ t('profiles_page.add_modal.name') }}</label>
                        <input 
                            v-model="form.name" 
                            type="text"
                            :disabled="form.processing || isSelfEmployed" 
                        />
                        <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
                    </div>

                    <div class="form-group">
                        <label>{{ t('profiles_page.add_modal.job') }}</label>
                        <select v-model="form.job" :disabled="form.processing">
                            <option disabled value="">{{ t('profiles_page.add_modal.job_select') }}</option>
                            <option v-for="job in jobOptions" :key="job" :value="job">
                                {{ t(`profiles_page.jobs.${job}`) }}
                            </option>
                        </select>
                        <p v-if="form.errors.job" class="error">{{ form.errors.job }}</p>
                    </div>
                </div>

                <!-- ROW 2 -->
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ t('profiles_page.add_modal.experience') }}</label>
                        <input v-model.number="form.experience" type="number" :disabled="form.processing" />
                        <p v-if="form.errors.experience" class="error">{{ form.errors.experience }}</p>
                    </div>

                    <div class="form-group">
                        <label>{{ t('profiles_page.add_modal.rate') }}</label>
                        <input v-model.number="form.rate" type="number" step="0.01" :disabled="form.processing" />
                        <p v-if="form.errors.rate" class="error">{{ form.errors.rate }}</p>
                    </div>
                </div>

                <!-- CERTIFICATIONS -->
                <div class="form-group">
                    <label>{{ t('profiles_page.add_modal.certifications') }}</label>

                    <div class="input-with-button">
                        <input v-model="newCertificate" @keyup.enter="addCertificate" :disabled="form.processing" />
                        <button type="button" @click="addCertificate">+</button>
                    </div>

                    <div class="tag-list">
                        <span v-for="(certificate, index) in form.certifications" :key="index" class="tag">
                            {{ certificate }}
                            <button type="button" @click="removeCertificate(index)" class="tag-remove">×</button>
                        </span>
                    </div>
                </div>

                <!-- SKILLS -->
                <div class="form-group">
                    <label>{{ t('profiles_page.add_modal.skills') }}</label>

                    <div class="input-with-button">
                        <input v-model="newSkill" @keyup.enter="addSkill" :disabled="form.processing" />
                        <button type="button" @click="addSkill">+</button>
                    </div>

                    <div class="tag-list">
                        <span v-for="(skill, index) in form.skills" :key="index" class="tag">
                            {{ skill }}
                            <button type="button" @click="removeSkill(index)" class="tag-remove">×</button>
                        </span>
                    </div>
                </div>
            </form>

            <template #footer>
                <button
                    type="submit" 
                    form="worker-form"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing 
                        ? action === 'Create'
                            ? t('profiles_page.add_modal.saving')
                            : t('profiles_page.edit_modal.updating')
                        : (action === 'Create'
                            ? t('profiles_page.add_modal.save')
                            : t('profiles_page.edit_modal.update')) 
                    }}
                </button>

                <button type="button" class="btn-thirdary" @click="showModal=false">
                    {{ t('profiles_page.add_modal.cancel') }}
                </button>
            </template>
        </BaseModal>
    </div>
</SidebarLayout>
</template>
