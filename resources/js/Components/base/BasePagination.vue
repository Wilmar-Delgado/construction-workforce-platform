<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true
    },
    align: {
        type: String,
        default: 'right' // left | center | right
    }
});

function goTo(link) {
    if (!link.url) return;

    router.visit(link.url, {
        preserveScroll: false,
        preserveState: true,
        onSuccess: () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });
}
</script>

<template>
    <div :class="['pagination', alignClass]">
        <button
            v-for="link in links"
            :key="link.label"
            v-html="link.label"
            :disabled="!link.url"
            @click="goTo(link)"
            :class="['page-btn', { active: link.active }]"
        />
    </div>
</template>

<script>
export default {
    computed: {
        alignClass() {
            return {
                left: 'justify-left',
                center: 'justify-center',
                right: 'justify-right'
            }[this.align];
        }
    }
};
</script>

<style scoped>
.pagination {
    display: flex;
    gap: 6px;
    margin: 12px 0;
}

/* Alignment */
.justify-left {
    justify-content: flex-start;
}

.justify-center {
    justify-content: center;
}

.justify-right {
    justify-content: flex-end;
}

/* Buttons */
.page-btn {
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}

.page-btn.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>