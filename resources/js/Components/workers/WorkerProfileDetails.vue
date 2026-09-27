<script setup>
import { Briefcase, DollarSign, Star } from 'lucide-vue-next';
import { useTranslate } from '@/composables/useTranslate';

defineProps({
    worker: {
        type: Object,
        required: true,
    },
});

const { t } = useTranslate();

function ratingDisplay(worker) {
    if (worker.rating === null || worker.rating === undefined) {
        return t('common.not_available');
    }

    return `${Number(worker.rating).toFixed(1)} (${worker.ratings_count})`;
}
</script>

<template>
    <div class="worker-profile-details">
        <div class="worker-profile-details-top">
            <div class="worker-profile-details-avatar">
                {{ worker.name.charAt(0) }}
            </div>

            <div class="worker-profile-details-summary">
                <h2>{{ worker.name }}</h2>
                <p class="worker-profile-details-job">
                    {{ t(`profiles_page.jobs.${worker.job}`) }}
                </p>
                <span
                    class="worker-profile-details-company"
                    :class="{ 'is-self-employed': !worker.company }"
                >
                    {{ worker.company?.name || t('common.self_employed') }}
                </span>
            </div>

            <div class="worker-profile-details-rating">
                <Star class="worker-profile-details-icon" />
                {{ ratingDisplay(worker) }}
            </div>
        </div>

        <div class="worker-profile-details-meta">
            <div class="worker-profile-details-meta-box">
                <Briefcase class="worker-profile-details-icon" />
                <div>
                    <p class="worker-profile-details-label">
                        {{ t('find_workers_page.profile_modal.experience') }}
                    </p>
                    <p>
                        {{
                            t('find_workers_page.experience_years', {
                                count: worker.years_experience,
                            })
                        }}
                    </p>
                </div>
            </div>

            <div class="worker-profile-details-meta-box">
                <DollarSign class="worker-profile-details-icon" />
                <div>
                    <p class="worker-profile-details-label">
                        {{ t('find_workers_page.profile_modal.rate') }}
                    </p>
                    <p>{{ worker.hourly_rate }} {{ t('common.per_hour') }}</p>
                </div>
            </div>
        </div>

        <div class="worker-profile-details-section">
            <p class="worker-profile-details-section-label">
                {{ t('find_workers_page.profile_modal.certifications') }}
            </p>
            <div class="worker-profile-details-tags">
                <span
                    v-for="certification in worker.certifications"
                    :key="certification.id"
                    class="worker-profile-details-tag is-certification"
                >
                    {{ certification.name }}
                </span>
            </div>
        </div>

        <div class="worker-profile-details-section">
            <p class="worker-profile-details-section-label">
                {{ t('find_workers_page.profile_modal.skills') }}
            </p>
            <div class="worker-profile-details-tags">
                <span
                    v-for="skill in worker.skills"
                    :key="skill.id"
                    class="worker-profile-details-tag"
                >
                    {{ skill.name }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped src="../../../css/components/workers/worker-profile-details.css"></style>
