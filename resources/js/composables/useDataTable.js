import { router } from '@inertiajs/vue3';

export function useDataTable(routeName, filters) {
    function handleSort(field) {
        let direction = 'asc';

        if (filters.value.sort === field && filters.value.direction === 'asc') {
            direction = 'desc';
        }

        router.get(route(routeName), {
            sort: field,
            direction
        }, {
            preserveState: true,
            replace: true
        });
    }

    return { handleSort };
}