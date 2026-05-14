import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useUserRole() {
    const user = usePage().props.auth.user;

    const isSelfEmployed = computed(() => user.role?.name === 'self_employed');
    const isCompany = computed(() => !isSelfEmployed.value);

    return { isSelfEmployed, isCompany };
}