<script setup>
const props = defineProps({
    columns: Array,
    sortable: Boolean,
    sort: String,
    direction: String,
    rows: {
        type: Array,
        default: () => []
    },
    emptyText: {
        type: String,
        default: 'No records found.'
    }
});

const emit = defineEmits(['sort']);

function handleSort(field) {
    emit('sort', field);
}
</script>

<template>
    <div class="table-container">
        <div class="table-scroll">
            <table class="data-table">
                <!-- HEADER -->
                <thead>
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            @click="col.sortable ? handleSort(col.key) : null"
                            :class="{ sortable: col.sortable }"
                        >
                            {{ col.label }}

                            <!-- SORT ICON -->
                            <span v-if="sortable && sort === col.key">
                                {{ direction === 'asc' ? '↑' : '↓' }}
                            </span>
                        </th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody>
                    <template v-if="rows.length">
                        <slot />
                    </template>

                    <tr v-else>
                        <td
                            :colspan="columns.length"
                            class="table-empty-state"
                        >
                            {{ emptyText }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION SLOT -->
        <slot name="pagination" />
    </div>
</template>

<style>
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    max-width: 100%;
    /* height: 75vh; */
    display: flex;
    flex-direction: column;
}

.table-scroll {
    flex: 1;
    overflow-y: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.05em;
    color: #6b7280;
}

.data-table th.sortable {
    cursor: pointer;
}

.data-table th,
.data-table td {
    text-align: left;
    padding: 8px 12px;
    border-bottom: 1px solid #e5e7eb;
}

.table-empty-state {
    text-align: center !important;
    padding: 48px 16px !important;
    color: #6b7280;
    font-size: 15px;
}

@media (max-width: 768px) {
    /* .data-table th:nth-child(4),
    .data-table td:nth-child(4),
    .data-table th:nth-child(5),
    .data-table td:nth-child(5) {
        display: none;
    } */
}
</style>