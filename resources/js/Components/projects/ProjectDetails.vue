<script setup>
import { Building2, CalendarDays, DollarSign, MapPin } from 'lucide-vue-next';
import { useDateTime } from '@/composables/useDateTime';
import { useTranslate } from '@/composables/useTranslate';

defineProps({
    project: {
        type: Object,
        required: true,
    },
});

const { t } = useTranslate();
const { formatDateOnly } = useDateTime();

function formatDate(date) {
    return formatDateOnly(date, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <div class="project-details">
        <div class="project-details-summary">
            <h3>
                <strong>{{ project.title }}</strong>
            </h3>

            <div class="project-details-meta">
                <Building2 class="project-details-icon" />
                <span>{{ project.hiring_company?.name }}</span>
            </div>

            <div class="project-details-meta">
                <MapPin class="project-details-icon" />
                <span>{{ project.city }}, {{ project.province }}</span>
            </div>

            <div class="project-details-meta">
                <CalendarDays class="project-details-icon" />
                <span>
                    {{ formatDate(project.start_date) }} -
                    {{ formatDate(project.end_date) }}
                </span>
            </div>

            <div class="project-details-meta">
                <DollarSign class="project-details-icon" />
                <span>
                    {{ project.hourly_rate ?? '--' }}
                    {{ t('common.per_hour') }}
                </span>
            </div>
        </div>

        <div class="form-group">
            <label>{{ t('find_projects_page.details_modal.trade') }}</label>
            <p>{{ t(`profiles_page.jobs.${project.job_type}`) }}</p>
        </div>

        <div class="form-group">
            <label>{{ t('find_projects_page.details_modal.description') }}</label>
            <p>{{ project.description }}</p>
        </div>

        <div v-if="project.requirements?.length" class="form-group">
            <label>{{ t('find_projects_page.details_modal.requirements') }}</label>
            <div class="project-details-requirements">
                <span
                    v-for="requirement in project.requirements"
                    :key="requirement.id"
                    class="project-details-requirement"
                >
                    {{ requirement.name }}
                </span>
            </div>
        </div>

        <div v-if="project.operational_details" class="form-group">
            <label>{{ t('find_projects_page.details_modal.operational_details') }}</label>

            <p v-if="project.operational_details.site_name">
                <strong>{{ t('find_projects_page.details_modal.site_name') }}:</strong>
                {{ project.operational_details.site_name }}
            </p>

            <p v-if="project.operational_details.address_line_1">
                <strong>{{ t('find_projects_page.details_modal.address') }}:</strong>
                {{ project.operational_details.address_line_1 }}
                <template v-if="project.operational_details.address_line_2">
                    , {{ project.operational_details.address_line_2 }}
                </template>
                <template v-if="project.operational_details.postal_code">
                    , {{ project.operational_details.postal_code }}
                </template>
            </p>

            <p v-if="project.operational_details.directions">
                <strong>{{ t('find_projects_page.details_modal.directions') }}:</strong>
                {{ project.operational_details.directions }}
            </p>

            <p
                v-if="
                    project.operational_details.contact_name ||
                    project.operational_details.contact_phone
                "
            >
                <strong>{{ t('find_projects_page.details_modal.contact') }}:</strong>
                {{ project.operational_details.contact_name }}
                <template v-if="project.operational_details.contact_phone">
                    — {{ project.operational_details.contact_phone }}
                </template>
            </p>
        </div>
    </div>
</template>

<style scoped src="../../../css/components/projects/project-details.css"></style>
