import { usePage } from '@inertiajs/vue3';

const localeFor = (locale) => locale === 'fr' ? 'fr-CA' : 'en-CA';

function parseDateOnly(value) {
    const match = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (!match) {
        return null;
    }

    const [, year, month, day] = match.map(Number);

    return new Date(year, month - 1, day);
}

function dateOnlyDayNumber(value) {
    const match = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (!match) {
        return null;
    }

    const [, year, month, day] = match.map(Number);

    return Date.UTC(year, month - 1, day) / 86_400_000;
}

function supportedTimezone(timezone, timezoneOptions) {
    return typeof timezone === 'string' && Object.hasOwn(timezoneOptions ?? {}, timezone)
        ? timezone
        : 'UTC';
}

export function useDateTime() {
    const page = usePage();

    const displayLocale = () => localeFor(page.props.locale);
    const displayTimezone = () => supportedTimezone(
        page.props.auth?.user?.timezone,
        page.props.timezoneOptions,
    );

    const formatDateOnly = (value, options = {}) => {
        const date = parseDateOnly(value);

        return date
            ? new Intl.DateTimeFormat(displayLocale(), options).format(date)
            : '';
    };

    const formatTimestamp = (value, options = {}) => {
        if (!value) {
            return '';
        }

        const timestamp = new Date(value);

        if (Number.isNaN(timestamp.getTime())) {
            return '';
        }

        return new Intl.DateTimeFormat(displayLocale(), {
            ...options,
            timeZone: displayTimezone(),
        }).format(timestamp);
    };

    const calendarDayDifference = (start, end) => {
        const startDay = dateOnlyDayNumber(start);
        const endDay = dateOnlyDayNumber(end);

        return startDay === null || endDay === null ? null : endDay - startDay;
    };

    return {
        formatDateOnly,
        formatTimestamp,
        calendarDayDifference,
    };
}
