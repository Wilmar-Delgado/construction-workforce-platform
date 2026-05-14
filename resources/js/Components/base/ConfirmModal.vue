<script setup>
import BaseModal from '@/Components/base/BaseModal.vue';

const props = defineProps({
    modelValue: Boolean,
    title: String,
    message: String,
    subtitle: String,
    itemName: String,
    confirmText: {
        type: String,
        default: 'Confirm'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    loading: Boolean,
    danger: Boolean
});

const emit = defineEmits(['update:modelValue', 'confirm']);

function close() {
    emit('update:modelValue', false);
}
</script>

<template>
    <BaseModal
        :model-value="modelValue"
        :title="title"
        maxWidth="500px"
        @update:modelValue="emit('update:modelValue', $event)"
    >
        <div class="confirm-body">
            <p class="message">{{ message }}</p>
            <p v-if="subtitle" class="message">{{ subtitle }}</p>

            <p v-if="itemName" class="item-name">
                {{ itemName }}
            </p>
        </div>

        <template #footer>
            <button
                class="btn-confirm"
                :class="{ danger }"
                :disabled="loading"
                @click="emit('confirm')"
            >
                {{ confirmText }}
            </button>

            <button
                class="btn-cancel"
                @click="close"
            >
                {{ cancelText }}
            </button>
        </template>
    </BaseModal>
</template>

<style scoped>
.confirm-body {
    text-align: center;
    padding: 8px 0;
}

.message {
    color: #4b5563;
    margin-bottom: 8px;
}

.item-name {
    font-weight: 700;
    font-size: 18px;
}

.btn-confirm {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-confirm.danger {
    background: #dc2626;
}

.btn-cancel {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
}
</style>