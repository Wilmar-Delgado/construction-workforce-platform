<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useTranslate } from '@/composables/useTranslate';
import { useUserRole } from '@/composables/useUserRole';
import { usePermissions } from '@/composables/usePermissions';
import { TrendingUp, Calendar, Pencil, Users, Briefcase, UserPlus, Search } from 'lucide-vue-next';

const { t } = useTranslate();
const { isSelfEmployed, isAdministrator } = useUserRole();
const { can } = usePermissions();

const page = usePage();
const authStore = useAuthStore();
const statsData = page.props.stats;
const stats = computed(() => {
    const commonStats = [
        { key: 'ongoing_projects', value: statsData.ongoing_projects, icon: TrendingUp },
        { key: 'pending_requests', value: statsData.pending_requests, icon: Calendar },
    ];

    if (isSelfEmployed.value) {
        return [
            ...commonStats,
            { key: 'completed_projects', value: statsData.completed_projects, icon: Users },
            { key: 'total_applications', value: statsData.total_applications, icon: Briefcase },
        ];
    }

    return [
        ...commonStats,
        { key: 'active_workers', value: statsData.active_workers, icon: Users },
        { key: 'total_projects', value: statsData.total_projects, icon: Briefcase },
    ];
});
const hasProfile = page.props.hasWorkerProfile;

const openFindProjects = () => {
    router.visit('/find-projects');
};

const openProjectManagement = () => {
    router.visit('/project-management');
};

const openProfiles = () => {
    router.visit('/worker-profiles');
};

const openFindworkers = () => {
    router.visit('/find-workers');
};

const openAvailability = () => {
    router.visit('/availability');
};

const openProjects = () => {
    router.visit('/projects');
};

const openSettings = () => {
    router.visit('/settings');
};
</script>

<template>
    <Head :title="t('home_page.title')" />

    <SidebarLayout>
        <template #title>
            {{ t('home_page.title') }}
        </template>

        <div class="page-container">
            <!-- Welcome -->
            <div class="welcome-section">
                <h2>{{ t('home_page.welcome', { name: authStore.userName }) }}</h2>
                <p>{{ t('home_page.welcome_subtitle') }}</p>
            </div>

            <!-- Stats -->
            <div class="home-stats-grid">
                <div v-for="stat in stats" :key="stat.key" class="stat-card">
                    <div class="stat-header">
                        <component
                            :is="stat.icon"
                            class="stat-icon"
                        />
                        <p class="stat-label">
                            {{ t(`home_page.stats.${stat.key}`) }}
                        </p>
                    </div>
                    <p class="stat-value">
                        {{ stat.value }}
                    </p>
                </div>
            </div>

            <!-- Main Actions -->
            <div class="actions-grid">
                <!-- Company: Owner / Planning Manager -->
                <template v-if="can('manage_workers')">
                    <div class="action-card" @click="openAvailability">
                        <div class="action-icon">
                            <UserPlus class="icon" />
                        </div>
                        <h3>{{ t('home_page.actions.make_available') }}</h3>
                        <p>{{ t('home_page.actions.make_available_desc') }}</p>
                    </div>

                    <div class="action-card" @click="openFindworkers">
                        <div class="action-icon">
                            <Search class="icon" />
                        </div>
                        <h3>{{ t('home_page.actions.search_worker') }}</h3>
                        <p>{{ t('home_page.actions.search_worker_desc') }}</p>
                    </div>
                </template>

                <!-- Self-employed -->
                <template v-else-if="isSelfEmployed">
                    <!-- Create / Edit Profile -->
                    <div class="action-card" @click="openProfiles">
                        <div class="action-icon">
                            <component :is="hasProfile ? Pencil : UserPlus" class="icon" />
                        </div>

                        <h3>
                            {{ hasProfile 
                                ? t('home_page.actions.edit_profile') 
                                : t('home_page.actions.create_profile') 
                            }}
                        </h3>

                        <p>
                            {{ hasProfile 
                                ? t('home_page.actions.edit_profile_desc') 
                                : t('home_page.actions.create_profile_desc') 
                            }}
                        </p>
                    </div>

                    <!-- Browse Projects -->
                    <div class="action-card" @click="openFindProjects">
                        <div class="action-icon">
                            <Briefcase class="icon" />
                        </div>
                        <h3>{{ t('home_page.actions.browse_projects') }}</h3>
                        <p>{{ t('home_page.actions.browse_projects_desc') }}</p>
                    </div>

                </template>
            </div>

            <!-- Quick Access -->
            <div class="quick-access">
                <h4>{{ t('home_page.quick_access') }}</h4>
                <div class="quick-buttons">
                    <button
                        v-if="can('view_project_management')"
                        @click="openProjectManagement"
                    >
                        {{ t('home_page.project_hub') }}
                    </button>

                    <button
                        v-if="isSelfEmployed || can('manage_workers')"
                        @click="openProfiles"
                    >
                        {{ isSelfEmployed ? t('home_page.manage_profile') : t('home_page.manage_profiles') }}
                    </button>

                    <button
                        v-if="isSelfEmployed"
                        @click="openFindProjects"
                    >
                        {{ t('find_projects') }}
                    </button>

                    <button
                        v-else-if="can('create_projects')"
                        @click="openProjects"
                    >
                        {{ t('home_page.manage_projects') }}
                    </button>

                    <button
                        v-if="isAdministrator"
                        @click="openFindworkers"
                    >
                        {{ t('find_workers') }}
                    </button>

                    <button @click="openSettings">{{ t('home_page.settings') }}</button>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>

<style scoped src="../../css/pages/home.css"></style>
