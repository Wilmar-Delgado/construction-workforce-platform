import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const user = usePage().props.auth.user;

    // TEMP: derive permissions from role
    const permissions = computed(() => {
        const role = user.role?.name;
        const hasCompanyContext = user.company_id !== null
            && ['company_owner', 'planning_manager'].includes(role);

        if (role === 'self_employed' && user.company_id === null) {
            return [
                'view_projects',
                'create_profile',
                'edit_own_profile',
                'view_project_management',
                'apply_to_projects',
            ];
        }

        if (hasCompanyContext) {
            return [
                'view_workers',
                'manage_workers',
                'create_projects',
                'manage_availability',
                'view_project_management',
                'invite_workers',
                'apply_to_projects',
            ];
        }

        if (role === 'administrator') {
            return [
                'view_workers',
                'view_project_management',
            ];
        }

        return [];
    });

    function can(permission) {
        return permissions.value.includes(permission);
    }

    return {
        can
    };
}
