<script setup>
import { watch, onMounted, onBeforeUnmount } from 'vue';

let activeScrollLocks = 0;
let lockedScrollContainers = [];

function getScrollContainers() {
    const contentAreas = Array.from(document.querySelectorAll('.content-area'));

    return contentAreas.length > 0 ? contentAreas : [document.scrollingElement];
}

function getScrollbarWidth(element) {
    if (element === document.scrollingElement) {
        return window.innerWidth - document.documentElement.clientWidth;
    }

    return element.offsetWidth - element.clientWidth;
}

function lockBackgroundScroll() {
    if (activeScrollLocks === 0) {
        lockedScrollContainers = getScrollContainers().map((element) => {
            const scrollbarWidth = getScrollbarWidth(element);
            const supportsScrollbarGutter = CSS.supports('scrollbar-gutter: stable');
            const state = {
                element,
                overflowY: element.style.overflowY,
                scrollbarGutter: element.style.scrollbarGutter,
                paddingInlineEnd: element.style.paddingInlineEnd,
                scrollTop: element.scrollTop
            };

            if (scrollbarWidth > 0) {
                if (supportsScrollbarGutter) {
                    element.style.scrollbarGutter = 'stable';
                } else {
                    const currentPadding = parseFloat(getComputedStyle(element).paddingInlineEnd) || 0;
                    element.style.paddingInlineEnd = `${currentPadding + scrollbarWidth}px`;
                }
            }

            element.style.overflowY = 'hidden';

            return state;
        });
    }

    activeScrollLocks += 1;
}

function unlockBackgroundScroll() {
    if (activeScrollLocks === 0) return;

    activeScrollLocks -= 1;

    if (activeScrollLocks > 0) return;

    lockedScrollContainers.forEach(({ element, overflowY, scrollbarGutter, paddingInlineEnd, scrollTop }) => {
        element.style.overflowY = overflowY;
        element.style.scrollbarGutter = scrollbarGutter;
        element.style.paddingInlineEnd = paddingInlineEnd;
        element.scrollTop = scrollTop;
    });

    lockedScrollContainers = [];
}

const props = defineProps({
    modelValue: Boolean,
    title: String,
    maxWidth: {
        type: String,
        default: '500px'
    }
});

const emit = defineEmits(['update:modelValue', 'close']);

let hasScrollLock = false;

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

    if (props.modelValue && !hasScrollLock) {
        lockBackgroundScroll();
        hasScrollLock = true;
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleEsc);

    if (hasScrollLock) {
        unlockBackgroundScroll();
        hasScrollLock = false;
    }
});

watch(() => props.modelValue, (val) => {
    if (val && !hasScrollLock) {
        lockBackgroundScroll();
        hasScrollLock = true;
    }

    if (!val && hasScrollLock) {
        unlockBackgroundScroll();
        hasScrollLock = false;
    }
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
