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
                'view_missions',
                'create_profile',
                'edit_own_profile',
                'view_mission_management',
                'apply_to_missions',
            ];
        }

        if (hasCompanyContext) {
            return [
                'view_workers',
                'manage_workers',
                'create_missions',
                'manage_availability',
                'view_mission_management',
                'invite_workers',
                'apply_to_missions',
            ];
        }

        if (role === 'administrator') {
            return [
                'view_workers',
                'view_mission_management',
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
