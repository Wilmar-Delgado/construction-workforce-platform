<script setup>
import { Building2, CalendarDays, DollarSign, MapPin } from 'lucide-vue-next';
import { useDateTime } from '@/composables/useDateTime';
import { useTranslate } from '@/composables/useTranslate';

defineProps({
    mission: {
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
    <div class="mission-details">
        <div class="mission-details-summary">
            <h3>
                <strong>{{ mission.title }}</strong>
            </h3>

            <div class="mission-details-meta">
                <Building2 class="mission-details-icon" />
                <span>{{ mission.hiring_company?.name }}</span>
            </div>

            <div class="mission-details-meta">
                <MapPin class="mission-details-icon" />
                <span>{{ mission.city }}, {{ mission.province }}</span>
            </div>

            <div class="mission-details-meta">
                <CalendarDays class="mission-details-icon" />
                <span>
                    {{ formatDate(mission.start_date) }} -
                    {{ formatDate(mission.end_date) }}
                </span>
            </div>

            <div class="mission-details-meta">
                <DollarSign class="mission-details-icon" />
                <span>
                    {{ mission.hourly_rate ?? '--' }}
                    {{ t('common.per_hour') }}
                </span>
            </div>
        </div>

        <div class="form-group">
            <label>{{ t('find_missions_page.details_modal.trade') }}</label>
            <p>{{ t(`profiles_page.jobs.${mission.job_type}`) }}</p>
        </div>

        <div class="form-group">
            <label>{{ t('find_missions_page.details_modal.description') }}</label>
            <p>{{ mission.description }}</p>
        </div>

        <div v-if="mission.requirements?.length" class="form-group">
            <label>{{ t('find_missions_page.details_modal.requirements') }}</label>
            <div class="mission-details-requirements">
                <span
                    v-for="requirement in mission.requirements"
                    :key="requirement.id"
                    class="mission-details-requirement"
                >
                    {{ requirement.name }}
                </span>
            </div>
        </div>

        <div v-if="mission.operational_details" class="form-group">
            <label>{{ t('find_missions_page.details_modal.operational_details') }}</label>

            <p v-if="mission.operational_details.site_name">
                <strong>{{ t('find_missions_page.details_modal.site_name') }}:</strong>
                {{ mission.operational_details.site_name }}
            </p>

            <p v-if="mission.operational_details.address_line_1">
                <strong>{{ t('find_missions_page.details_modal.address') }}:</strong>
                {{ mission.operational_details.address_line_1 }}
                <template v-if="mission.operational_details.address_line_2">
                    , {{ mission.operational_details.address_line_2 }}
                </template>
                <template v-if="mission.operational_details.postal_code">
                    , {{ mission.operational_details.postal_code }}
                </template>
            </p>

            <p v-if="mission.operational_details.directions">
                <strong>{{ t('find_missions_page.details_modal.directions') }}:</strong>
                {{ mission.operational_details.directions }}
            </p>

            <p
                v-if="
                    mission.operational_details.contact_name ||
                    mission.operational_details.contact_phone
                "
            >
                <strong>{{ t('find_missions_page.details_modal.contact') }}:</strong>
                {{ mission.operational_details.contact_name }}
                <template v-if="mission.operational_details.contact_phone">
                    — {{ mission.operational_details.contact_phone }}
                </template>
            </p>
        </div>
    </div>
</template>

<style scoped src="../../../css/components/missions/mission-details.css"></style>
