<script setup>
import { ChevronDown, ChevronUp } from 'lucide-vue-next';

defineProps({
    title: {
        type: String,
        required: true,
    },
    count: {
        type: Number,
        required: true,
    },
    expanded: {
        type: Boolean,
        required: true,
    },
    empty: {
        type: Boolean,
        required: true,
    },
    emptyTitle: {
        type: String,
        required: true,
    },
    emptyDescription: {
        type: String,
        required: true,
    },
});

defineEmits(['toggle']);
</script>

<template>
    <div class="mission-section">
        <div class="mission-section-title" @click="$emit('toggle')">
            <h3>
                {{ title }}
                ({{ count }})
            </h3>

            <component
                :is="expanded ? ChevronUp : ChevronDown"
                class="section-arrow"
            />
        </div>

        <Transition name="section-collapse">
            <div v-if="expanded">
                <div v-if="empty" class="empty-state">
                    <h3>{{ emptyTitle }}</h3>
                    <p>{{ emptyDescription }}</p>
                </div>

                <slot v-else />
                <slot name="pagination" />
            </div>
        </Transition>
    </div>
</template>

<style scoped src="../../../css/components/mission-management/mission-section.css"></style>
