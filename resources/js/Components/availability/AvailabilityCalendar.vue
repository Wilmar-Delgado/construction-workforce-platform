<script setup>
import axios from 'axios';
import { DayPilotMonth, DayPilotScheduler } from '@daypilot/daypilot-lite-vue';
import { computed, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTranslate } from '@/composables/useTranslate';

const { t } = useTranslate();
const page = usePage();
const emit = defineEmits(['edit-availability', 'create-availability']);

const isMobile = typeof window !== 'undefined' && window.matchMedia('(max-width: 768px)').matches;
const initialWorkerOptions = page.props.workerProfiles || [];
const calendarView = ref(isMobile ? 'day' : 'week');
const activeDate = ref(toIsoDate(new Date()));
const workers = ref([]);
const workerOptions = ref(initialWorkerOptions);
const availabilities = ref([]);
const selectedWorkerId = ref(
    isMobile
        ? (initialWorkerOptions.length > 0 ? String(initialWorkerOptions[0].id) : null)
        : ''
);
const isLoading = ref(false);
const hasLoadError = ref(false);
let latestRequestId = 0;

const locale = computed(() => page.props.locale === 'fr' ? 'fr-ca' : 'en-ca');
const weekStarts = computed(() => page.props.locale === 'fr' ? 1 : 0);
const displayLocale = computed(() => page.props.locale === 'fr' ? 'fr-CA' : 'en-CA');
const hasMultipleWorkers = computed(() => workerOptions.value.length > 1);
const visibleRangeLabel = computed(() => {
    const start = parseIsoDate(visibleStartDate.value);

    if (calendarView.value === 'month') {
        return new Intl.DateTimeFormat(displayLocale.value, {
            month: 'long',
            year: 'numeric',
        }).format(start);
    }

    const formatter = new Intl.DateTimeFormat(displayLocale.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    if (calendarView.value === 'day') {
        return formatter.format(start);
    }

    const end = parseIsoDate(visibleEndDate.value);

    return typeof formatter.formatRange === 'function'
        ? formatter.formatRange(start, end)
        : `${formatter.format(start)} – ${formatter.format(end)}`;
});

const visibleStartDate = computed(() => {
    const date = parseIsoDate(activeDate.value);

    if (calendarView.value === 'month') {
        return toIsoDate(new Date(date.getFullYear(), date.getMonth(), 1));
    }

    if (calendarView.value === 'week') {
        const offset = (date.getDay() - weekStarts.value + 7) % 7;
        date.setDate(date.getDate() - offset);
    }

    return toIsoDate(date);
});

const visibleEndDate = computed(() => {
    const date = parseIsoDate(visibleStartDate.value);

    if (calendarView.value === 'month') {
        return toIsoDate(new Date(date.getFullYear(), date.getMonth() + 1, 0));
    }

    date.setDate(date.getDate() + (calendarView.value === 'week' ? 6 : 0));

    return toIsoDate(date);
});

const schedulerConfig = computed(() => ({
    startDate: visibleStartDate.value,
    days: calendarView.value === 'week' ? 7 : 1,
    scale: 'Hour',
    cellWidth: 46,
    rowHeaderWidth: isMobile ? 116 : 230,
    rowMarginTop: 6,
    rowMarginBottom: 6,
    eventHeight: 40,
    eventPadding: 6,
    eventBorderRadius: 6,
    eventTextWrappingEnabled: true,
    eventMoveHandling: 'Disabled',
    eventResizeHandling: 'Disabled',
    eventDeleteHandling: 'Disabled',
    eventClickHandling: 'Enabled',
    timeRangeClickHandling: 'Disabled',
    timeRangeSelectedHandling: 'Enabled',
    locale: locale.value,
    weekStarts: weekStarts.value,
    resources: workers.value.map((worker) => ({
        id: String(worker.id),
        html: resourceLabel(worker),
    })),
    timeHeaders: [
        { groupBy: 'Day' },
        { groupBy: 'Hour' },
    ],
    onEventClick: handleEventClick,
    onTimeRangeSelected: handleSchedulerTimeRangeSelected,
    events: schedulerEvents.value,
}));

const schedulerEvents = computed(() => availabilities.value.map((availability) => ({
    id: availability.id,
    availabilityId: availability.id,
    resource: String(availability.worker_profile_id),
    start: toDateTime(availability.date, availability.start_time),
    end: toDateTime(availability.date, availability.end_time),
    text: `${statusLabel(availability.status)}\n${formatTime(availability.start_time)}–${formatTime(availability.end_time)}`,
    ...statusColors(availability.status),
})));

const monthConfig = computed(() => ({
    startDate: visibleStartDate.value,
    locale: locale.value,
    weekStarts: weekStarts.value,
    eventMoveHandling: 'Disabled',
    eventResizeHandling: 'Disabled',
    eventDeleteHandling: 'Disabled',
    eventClickHandling: 'Enabled',
    timeRangeSelectedHandling: 'Enabled',
    onEventClick: handleEventClick,
    onTimeRangeSelected: handleMonthTimeRangeSelected,
    events: monthEvents.value,
}));

const monthEvents = computed(() => availabilities.value.map((availability) => {
    const worker = workers.value.find((item) => item.id === availability.worker_profile_id);

    return {
        id: availability.id,
        availabilityId: availability.id,
        start: toDateTime(availability.date, availability.start_time),
        end: toDateTime(availability.date, availability.end_time),
        text: `${worker?.name ?? ''} — ${statusLabel(availability.status)} ${formatTime(availability.start_time)}–${formatTime(availability.end_time)}`,
        ...statusColors(availability.status),
    };
}));

function setCalendarView(view) {
    calendarView.value = view;
}

function resourceLabel(worker) {
    return `<div style="font-weight: 600; line-height: 1.25;">${escapeHtml(worker.name)}</div><div style="color: #6b7280; font-size: 12px; line-height: 1.25; margin-top: 2px;">${escapeHtml(t(`profiles_page.jobs.${worker.job}`))}</div>`;
}

function handleEventClick(args) {
    const availability = availabilities.value.find((item) => item.id === args.e.data.availabilityId);

    if (availability) {
        emit('edit-availability', availability);
    }
}

function handleSchedulerTimeRangeSelected(args) {
    emit('create-availability', {
        worker_profile_id: Number(args.resource),
        date: args.start.toString('yyyy-MM-dd'),
        start_time: args.start.toString('HH:mm'),
        end_time: args.end.toString('HH:mm'),
    });

    args.control.clearSelection();
}

function handleMonthTimeRangeSelected(args) {
    emit('create-availability', {
        date: args.start.toString('yyyy-MM-dd'),
    });

    args.control.clearSelection();
}

function goToday() {
    activeDate.value = toIsoDate(new Date());
}

function goPrevious() {
    const date = parseIsoDate(activeDate.value);

    if (calendarView.value === 'month') {
        date.setMonth(date.getMonth() - 1);
    } else {
        date.setDate(date.getDate() - (calendarView.value === 'week' ? 7 : 1));
    }

    activeDate.value = toIsoDate(date);
}

function goNext() {
    const date = parseIsoDate(activeDate.value);

    if (calendarView.value === 'month') {
        date.setMonth(date.getMonth() + 1);
    } else {
        date.setDate(date.getDate() + (calendarView.value === 'week' ? 7 : 1));
    }

    activeDate.value = toIsoDate(date);
}

async function loadCalendarData() {
    const requestId = ++latestRequestId;
    isLoading.value = true;
    hasLoadError.value = false;

    try {
        const { data } = await axios.get(route('availability.calendar'), {
            params: {
                start: visibleStartDate.value,
                end: visibleEndDate.value,
                ...(selectedWorkerId.value ? { worker_profile_id: selectedWorkerId.value } : {}),
            },
        });

        if (requestId !== latestRequestId) return;

        workers.value = data.workers;
        availabilities.value = data.availabilities;

        if (isMobile && selectedWorkerId.value === null && data.workers.length > 0) {
            workerOptions.value = data.workers;
            selectedWorkerId.value = String(data.workers[0].id);
            return;
        }

        if (!isMobile && !selectedWorkerId.value) {
            workerOptions.value = data.workers;
        }
    } catch {
        if (requestId === latestRequestId) {
            workers.value = [];
            availabilities.value = [];
            hasLoadError.value = true;
        }
    } finally {
        if (requestId === latestRequestId) {
            isLoading.value = false;
        }
    }
}

function statusLabel(status) {
    return t(`availability_page.status_options.${status}`);
}

function statusColors(status) {
    return {
        available: {
            backColor: '#dcfce7',
            borderColor: '#16a34a',
            fontColor: '#166534',
        },
        booked: {
            backColor: '#ffedd5',
            borderColor: '#ea580c',
            fontColor: '#9a3412',
        },
        unavailable: {
            backColor: '#fee2e2',
            borderColor: '#dc2626',
            fontColor: '#991b1b',
        },
    }[status];
}

function formatTime(time) {
    return time.slice(0, 5);
}

function toDateTime(date, time) {
    return `${date}T${time}`;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function parseIsoDate(value) {
    const [year, month, day] = value.split('-').map(Number);

    return new Date(year, month - 1, day);
}

function toIsoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

watch([calendarView, activeDate, selectedWorkerId], loadCalendarData);
onMounted(loadCalendarData);

defineExpose({
    refresh: loadCalendarData,
});
</script>

<template>
    <section class="availability-calendar" :aria-busy="isLoading">
        <div class="availability-calendar__toolbar">
            <div class="availability-calendar__navigation">
                <button type="button" class="availability-calendar__button" @click="goToday">
                    {{ t('availability_page.calendar.today') }}
                </button>
                <button type="button" class="availability-calendar__button" @click="goPrevious">
                    {{ t('availability_page.calendar.previous') }}
                </button>
                <button type="button" class="availability-calendar__button" @click="goNext">
                    {{ t('availability_page.calendar.next') }}
                </button>
            </div>

            <div class="availability-calendar__view-switcher" role="group">
                <button
                    v-for="view in ['day', 'week', 'month']"
                    :key="view"
                    type="button"
                    class="availability-calendar__button"
                    :class="{ 'availability-calendar__button--active': calendarView === view }"
                    :aria-pressed="calendarView === view"
                    @click="setCalendarView(view)"
                >
                    {{ t(`availability_page.calendar.${view}`) }}
                </button>
            </div>
        </div>

        <div class="availability-calendar__context">
            <div>
                <p class="availability-calendar__range">{{ visibleRangeLabel }}</p>
                <p v-if="hasLoadError" class="availability-calendar__message availability-calendar__message--error">
                    {{ t('availability_page.calendar.load_error') }}
                </p>

                <div class="availability-calendar__legend" :aria-label="t('availability_page.calendar.status_legend')">
                    <span v-for="status in ['available', 'booked', 'unavailable']" :key="status" class="availability-calendar__legend-item">
                        <span :class="`availability-calendar__legend-swatch availability-calendar__legend-swatch--${status}`"></span>
                        {{ statusLabel(status) }}
                    </span>
                </div>
            </div>

            <label v-if="hasMultipleWorkers" class="availability-calendar__worker-filter">
                <span>{{ t('availability_page.calendar.worker_filter_label') }}</span>
                <select v-model="selectedWorkerId">
                    <option value="">{{ t('availability_page.calendar.all_workers') }}</option>
                    <option v-for="worker in workerOptions" :key="worker.id" :value="String(worker.id)">
                        {{ worker.name }} — {{ t(`profiles_page.jobs.${worker.job}`) }}
                    </option>
                </select>
            </label>
        </div>

        <div class="availability-calendar__surface" :class="{ 'availability-calendar__surface--loading': isLoading }">
            <div v-if="isLoading" class="availability-calendar__loading" aria-live="polite">
                {{ t('availability_page.calendar.loading') }}
            </div>
            <p v-if="!isLoading && workers.length === 0" class="availability-calendar__empty-state">
                {{ t('availability_page.calendar.no_workers') }}
            </p>
            <DayPilotScheduler
                v-else-if="calendarView !== 'month'"
                :config="schedulerConfig"
                :events="schedulerEvents"
            />
            <DayPilotMonth
                v-else-if="workers.length"
                :config="monthConfig"
                :events="monthEvents"
            />
            <p v-if="!isLoading && workers.length && availabilities.length === 0" class="availability-calendar__empty-state availability-calendar__empty-state--overlay">
                {{ t('availability_page.calendar.no_slots') }}
            </p>
        </div>
    </section>
</template>

<style scoped src="../../../css/components/availability/availability-calendar.css"></style>
