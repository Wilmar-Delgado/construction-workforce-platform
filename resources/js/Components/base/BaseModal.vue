<script setup>
import { watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    title: String,
    maxWidth: {
        type: String,
        default: '500px'
    }
});

const emit = defineEmits(['update:modelValue', 'close']);

function close() {
    emit('update:modelValue', false);
    emit('close');
}

// ESC key support
function handleEsc(e) {
    if (e.key === 'Escape') close();
}

onMounted(() => {
    window.addEventListener('keydown', handleEsc);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleEsc);
});

// Prevent body scroll when modal open
watch(() => props.modelValue, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});
</script>

<template>
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal" :style="{ maxWidth }">
            
            <!-- HEADER -->
            <div class="modal-header">
                <h3 v-if="title">{{ title }}</h3>

                <button class="close-btn tag-remove" @click="close">✕</button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <slot />
            </div>

            <!-- FOOTER (optional) -->
            <div v-if="$slots.footer" class="modal-footer">
                <slot name="footer" />
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Overlay */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 1000;
}

/* Modal box */
.modal {
    background: white;
    width: 100%;
    border-radius: 14px;
    padding: 20px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

/* Header */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-weight: 900;
}

.close-btn {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    font-weight: 900;
}

/* Body */
.modal-body {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Footer */
.modal-footer {
    margin-top: 16px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Mobile fix (important for your issue) */
@media (max-width: 768px) {
    /* .modal {
        width: 95%;
        max-width: none;
        padding: 16px;
        border-radius: 12px;
    } */
    .modal {
        width: 95%;
        max-width: none;
        max-width: 100%;
        border-radius: 14px;
        padding: 18px;
        padding: 20px;
    }
}
</style>