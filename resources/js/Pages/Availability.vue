<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import BaseModal from '@/Components/base/BaseModal.vue';
import ConfirmModal from '@/Components/base/ConfirmModal.vue';
import BaseToast from '@/Components/base/BaseToast.vue';
import AvailabilityCalendar from '@/Components/availability/AvailabilityCalendar.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useTranslate } from '@/composables/useTranslate';
import { useDateTime } from '@/composables/useDateTime';
import { useUserRole } from '@/composables/useUserRole';
import { Plus } from 'lucide-vue-next';

const { t } = useTranslate();
const { formatDateOnly } = useDateTime();
const { isSelfEmployed } = useUserRole();
const page = usePage();
const workers = computed(() => page.props.workerProfiles || []);
const hasSingleWorker = computed(() => workers.value.length === 1);
const showModal = ref(false);
const availabilityCalendar = ref(null);

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
            onSuccess: () => {
                resetModal();
                availabilityCalendar.value?.refresh();
            }
        });
    } else {
        form.put(route('availability.update', editingAvailabilityId.value), {
            data: payload,
            onSuccess: () => {
                resetModal();
                availabilityCalendar.value?.refresh();
            }
        });
    }
}

function openCreateAvailability(prefill = {}) {
    resetModal();

    form.worker_profile_id = prefill.worker_profile_id ?? form.worker_profile_id;
    form.date = prefill.date ?? '';
    form.start_time = prefill.start_time ?? '';
    form.end_time = prefill.end_time ?? '';

    showModal.value = true;
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
    deletingAvailability.value = {
        ...availability,
        worker_name: availability.worker_name ?? workers.value.find(
            (worker) => String(worker.id) === String(availability.worker_profile_id)
        )?.name,
    };
    showModal.value = false;
    showDeleteModal.value = true;
}

function confirmDeleteAvailability() {
    form.delete(route('availability.destroy', deletingAvailability.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingAvailability.value = null;
            resetModal();
            availabilityCalendar.value?.refresh();
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

function formatDate(date) {
    return formatDateOnly(date, { year: 'numeric', month: '2-digit', day: '2-digit' });
}

</script>

<template>
<Head :title="t('availability_page.title')" />

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
            <h2>{{ isSelfEmployed ? t('availability_page.subtitle_self') : t('availability_page.subtitle_company') }}</h2>
            <button @click="openCreateAvailability" class="btn-primary">
                <Plus class="icon" /> {{ t('availability_page.add_availability') }}
            </button>
        </div>

        <AvailabilityCalendar
            ref="availabilityCalendar"
            @edit-availability="editAvailability"
            @create-availability="openCreateAvailability"
        />

        <!-- DELETE MODAL -->
        <ConfirmModal
            v-model="showDeleteModal"
            :title="t('availability_page.delete_modal.title')"
            :message="t('availability_page.delete_modal.message')"
            :item-name="t('availability_page.delete_modal.item_name', {
                worker: deletingAvailability?.worker_name ?? t('common.not_available'),
                date: formatDate(deletingAvailability?.date)
            })"
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
                    v-if="action === 'Update'"
                    type="button"
                    class="btn-danger"
                    :disabled="form.processing"
                    @click="deleteAvailability({
                        id: editingAvailabilityId,
                        worker_profile_id: form.worker_profile_id,
                        date: form.date,
                    })"
                >
                    {{ t('common.delete') }}
                </button>
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

<style scoped src="../../css/pages/availability.css"></style>
