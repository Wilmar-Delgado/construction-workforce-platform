import { usePage } from '@inertiajs/vue3';

export function useTranslate() {
    const translations = usePage().props.translations;

    const t = (key, params = {}) => {
        let value = key.split('.').reduce((o, i) => o?.[i], translations) ?? key;

        Object.keys(params).forEach(p => {
            value = value.replace(`:${p}`, params[p]);
        });

        return value;
    };

    return { t };
}