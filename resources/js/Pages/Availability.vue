<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import BasePagination from '@/Components/base/BasePagination.vue';
import DataTable from '@/Components/tables/DataTable.vue';
import ConfirmModal from '@/Components/base/ConfirmModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useTranslate } from '@/composables/useTranslate';
import { useDataTable } from '@/composables/useDataTable';
import { Edit2, Trash2, Plus } from 'lucide-vue-next';

const { t } = useTranslate();
const columns = [
    { key: 'worker_name', label: t('availability_page.table.worker'), sortable: true },
    { key: 'job', label: t('availability_page.table.job'), sortable: true },
    { key: 'date', label: t('availability_page.table.date'), sortable: true },
    { key: 'time', label: t('availability_page.table.time') },
    { key: 'status', label: t('availability_page.table.status'), sortable: true },
    { key: 'actions', label: t('availability_page.table.actions') }
];
const page = usePage();
const workers = computed(() => page.props.workerProfiles || []);
const hasSingleWorker = computed(() => workers.value.length === 1);
const availability = computed(() => page.props.availability.data || []);
const pagination = computed(() => page.props.availability);
const filters = computed(() => page.props.filters);
const showModal = ref(false);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const toastKey = ref(0);

watch(
    () => page.props.flash,
    () => toastKey.value++,
    { deep: true }
);

const form = useForm({
    worker_profile_id: '',
    date: '',
    start_time: '',
    end_time: '',
    status: 'available',
});

const action = ref('Create');
const editingAvailabilityId = ref(null);

const { handleSort } = useDataTable('availability.index', filters);

const showDeleteModal = ref(false);
const deletingAvailability = ref(null);

function submitAvailability() {
    const payload = {
        worker_profile_id: form.worker_profile_id,
        date: form.date,
        start_time: form.start_time,
        end_time: form.end_time,
        status: form.status
    };

    if (action.value === 'Create') {
        form.post(route('availability.store'), {
            data: payload,
            onSuccess: resetModal
        });
    } else {
        form.put(route('availability.update', editingAvailabilityId.value), {
            data: payload,
            onSuccess: resetModal
        });
    }
}

function editAvailability(avai) {
    action.value = 'Update';
    editingAvailabilityId.value = avai.id;

    form.reset();

    form.worker_profile_id = avai.worker_profile_id;
    form.date = avai.date;
    form.start_time = avai.start_time;
    form.end_time = avai.end_time;
    form.status = avai.status;

    showModal.value = true;
}

function deleteAvailability(availability) {
    deletingAvailability.value = availability;
    showDeleteModal.value = true;
}

function confirmDeleteAvailability() {
    form.delete(route('availability.destroy', deletingAvailability.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
}

function resetModal() {
    showModal.value = false;

    form.reset();

    form.worker_profile_id = hasSingleWorker.value && workers.value.length
        ? workers.value[0].id 
        : '';
    form.date = '';
    form.start_time = '';
    form.end_time = '';
    form.status = 'available';

    action.value = 'Create';
    editingAvailabilityId.value = null;
}
</script>

<template>
<Head title="Availability" />

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
        {{ t('availability_page.title') }}
    </template>

    <div class="page-container">

        <!-- Header -->
        <div class="page-header">
            <h2>{{ hasSingleWorker ? t('availability_page.subtitle_self') : t('availability_page.subtitle_company') }}</h2>
            <button @click="resetModal(); showModal = true" class="btn-primary">
                <Plus class="icon" /> {{ t('availability_page.add_availability') }}
            </button>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :rows="availability"
            :emptyText="`No availability slots added yet. Click '${t('availability_page.add_availability')}' to get started.`"
            sortable
            :sort="filters.sort"
            :direction="filters.direction"
            @sort="handleSort"
        >
            <tr v-for="avai in availability" :key="avai.id">
                <td class="worker-name">{{ avai.worker_name }}</td>
                <td>{{ t(`profiles_page.jobs.${avai.job}`) }}</td>
                <td>{{ avai.date }}</td>
                <td>{{ avai.start_time }} - {{ avai.end_time }}</td>
                <td><span :class="`status-tag status-${avai.status}`">{{ avai.status.charAt(0).toUpperCase() + avai.status.slice(1) }}</span></td>
                <td class="actions" style="text-align: right;">
                    <button @click="editAvailability(avai)" class="table-icon-btn blue">
                        <Edit2 class="table-icon" />
                    </button>
                    <button @click="deleteAvailability(avai)" class="table-icon-btn danger">
                        <Trash2 class="table-icon" />
                    </button>
                </td>
            </tr>

            <template #pagination>
                <BasePagination :links="pagination.links" />
            </template>
        </DataTable>

        <!-- DELETE MODAL -->
        <ConfirmModal
            v-model="showDeleteModal"
            :title="t('availability_page.delete_modal.title')"
            :message="t('availability_page.delete_modal.message')"
            :item-name="deletingAvailability?.worker_name + ' on ' + deletingAvailability?.date"
            :confirmText="t('availability_page.delete_modal.confirm')"
            :cancelText="t('availability_page.delete_modal.cancel')"
            danger
            :loading="form.processing"
            @confirm="confirmDeleteAvailability"
        />

        <!-- ADD MODAL -->
        <BaseModal
            v-model="showModal"
            :title="action === 'Create'
                ? t('availability_page.add_modal.title')
                : t('availability_page.edit_modal.title')"
        >
            <form id="availability-form" @submit.prevent="submitAvailability">
                <!-- ROW 1 -->
                <div class="form-group">
                    <label>{{ t('availability_page.add_modal.worker') }}</label>
                    <select v-model="form.worker_profile_id" :disabled="hasSingleWorker" :class="{ 'disabled-field': hasSingleWorker }">
                        <option disabled value="">
                            {{ t('availability_page.add_modal.select_worker') }}
                        </option>
                        <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                            {{ worker.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.worker_profile_id" class="error">{{ form.errors.worker_profile_id }}</p>
                </div>

                <!-- ROW 2 -->
                <div class="form-group">
                    <label>{{ t('availability_page.add_modal.date') }}</label>
                    <input v-model.date="form.date" type="date" />
                    <p v-if="form.errors.date" class="error">{{ form.errors.date }}</p>
                </div>

                <!-- ROW 3 -->
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ t('availability_page.add_modal.start_time') }}</label>
                        <input v-model="form.start_time" type="time" />
                        <p v-if="form.errors.start_time" class="error">{{ form.errors.start_time }}</p>
                    </div>

                    <div class="form-group">
                        <label>{{ t('availability_page.add_modal.end_time') }}</label>
                        <input v-model="form.end_time" type="time" />
                        <p v-if="form.errors.end_time" class="error">{{ form.errors.end_time }}</p>
                    </div>
                </div>

                <!-- ROW 4 -->
                <div class="form-group">
                    <label>{{ t('availability_page.add_modal.status') }}</label>
                    <select v-model="form.status" required>
                        <option value="available">{{ t('availability_page.status_options.available') }}</option>
                        <option value="booked">{{ t('availability_page.status_options.booked') }}</option>
                        <option value="unavailable">{{ t('availability_page.status_options.unavailable') }}</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <button 
                    type="submit"
                    form="availability-form"
                    class="btn-primary btn-full"
                    :disabled="form.processing"
                >
                    {{ form.processing
                        ? action === 'Create'
                            ? t('availability_page.add_modal.saving')
                            : t('availability_page.edit_modal.updating')
                        : action === 'Create'
                            ? t('availability_page.add_modal.save')
                            : t('availability_page.edit_modal.update')
                    }}
                </button> 
            </template>
        </BaseModal>
    </div>
</SidebarLayout>
</template>