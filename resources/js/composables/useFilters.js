import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useFilters(route, initialFilters = {}) {

    // STATE
    const search = ref(initialFilters.search || '');
    const job = ref(initialFilters.job || '');
    const location = ref(initialFilters.location || '');

    // APPLY FILTERS
    function apply(extra = {}) {
        router.get(route, {
            search: search.value,
            job: job.value,
            location: location.value,
            ...extra
        }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }

    // RESET FILTERS (optional but useful)
    function reset() {
        search.value = '';
        job.value = '';
        location.value = '';

        apply();
    }

    return {
        search,
        job,
        location,
        apply,
        reset
    };
}