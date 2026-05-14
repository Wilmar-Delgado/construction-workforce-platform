import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const user = usePage().props.auth.user;

    // TEMP: derive permissions from role
    const permissions = computed(() => {
        if (user.role?.name === 'self_employed') {
            return [
                'view_missions',
                'create_profile',
                'edit_own_profile'
            ];
        }

        return [
            'view_workers',
            'manage_workers',
            'create_missions',
            'manage_availability'
        ];
    });

    function can(permission) {
        return permissions.value.includes(permission);
    }

    return {
        can
    };
}