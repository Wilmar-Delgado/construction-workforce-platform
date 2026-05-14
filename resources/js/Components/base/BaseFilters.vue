<script setup>
const props = defineProps({
    search: String,
    job: String,
    location: String,

    jobs: {
        type: Array,
        default: () => []
    },

    locations: {
        type: Array,
        default: () => []
    },

    showLocation: {
        type: Boolean,
        default: false
    },

    t: Function
});

const emit = defineEmits([
    'update:search',
    'update:job',
    'update:location',
    'change'
]);

function onSearchInput(e) {
    emit('update:search', e.target.value);
}

function onJobChange(e) {
    emit('update:job', e.target.value);
    emit('change');
}

function onLocationChange(e) {
    emit('update:location', e.target.value);
    emit('change');
}

function onEnter() {
    emit('change');
}
</script>

<template>
    <div class="filters">
        <!-- SEARCH -->
        <div class="search-wrapper">
            <input
                :value="search"
                @input="onSearchInput"
                @keyup.enter="onEnter"
                :placeholder="t('find_missions_page.filters.search')"
            />
        </div>

        <!-- JOB -->
        <select :value="job" @change="onJobChange">
            <option value="">
                {{ t('find_missions_page.filters.job') }}
            </option>
            <option v-for="j in jobs" :key="j" :value="j">
                {{ t(`profiles_page.jobs.${j}`) }}
            </option>
        </select>

        <!-- LOCATION (optional) -->
        <select
            v-if="showLocation"
            :value="location"
            @change="onLocationChange"
        >
            <option value="">
                {{ t('find_missions_page.filters.location') }}
            </option>
            <option v-for="l in locations" :key="l" :value="l">
                {{ l }}
            </option>
        </select>
    </div>
</template>

<style scoped>
.filters {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin: 16px 0;
    width: 100%;
}

.search-wrapper input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

select {
    width: 100%;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

/* Tablet */
@media (min-width: 992px) {
    .filters {
        flex-direction: row;
        align-items: center;
    }

    .search-wrapper {
        flex: 1;
    }

    select {
        width: auto;
        min-width: 180px;
    }
}
</style>