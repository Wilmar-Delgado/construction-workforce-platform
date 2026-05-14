<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: 'success'
    }
});

const visible = ref(false);
let timer = null;

function showToast() {
    // reset previous timer
    clearTimeout(timer);

    // hide first so transition can restart
    visible.value = false;

    // small delay so Vue re-renders hidden state
    setTimeout(() => {
        visible.value = true;

        timer = setTimeout(() => {
            visible.value = false;
        }, 3000);
    }, 10);
}

watch(
    () => props.message,
    (val) => {
        if (val) {
            showToast();
        }
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    clearTimeout(timer);
});

const classes = computed(() => ({
    success: props.type === 'success',
    error: props.type === 'error'
}));
</script>

<template>
    <transition name="toast">
        <div v-if="visible && message" class="toast" :class="classes">
            {{ message }}
        </div>
    </transition>
</template>

<style scoped>
.toast {
    position: fixed;
    top: 20px;
    right: 35px;
    z-index: 9999;
    padding: 12px 16px;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.toast.success {
    background: #16a34a;
}

.toast.error {
    background: #dc2626;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>