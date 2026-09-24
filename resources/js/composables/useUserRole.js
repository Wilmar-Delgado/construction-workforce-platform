import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useUserRole() {
    const user = usePage().props.auth.user;

    const isSelfEmployed = computed(() => user.role?.name === 'self_employed');
    const isAdministrator = computed(() => user.role?.name === 'administrator');
    const isCompany = computed(() => user.company_id !== null
        && ['company_owner', 'planning_manager'].includes(user.role?.name));

    return { isSelfEmployed, isAdministrator, isCompany };
}
