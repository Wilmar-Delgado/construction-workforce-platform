<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

const form = useForm({
    name: '',
    phone: '',
    address: ''
})

function submit() {
    form.post(route('company.store'))
}
</script>

<template>
<GuestLayout>

    <Head :title="t('onboarding_page.title')" />

    <h2 class="register-title">
        {{ t('onboarding.company.title') }}
    </h2>

    <p class="setup-description">
        {{ t('onboarding.company.description') }}
    </p>

    <form @submit.prevent="submit" class="register-form">

        <!-- Company Name -->
        <div class="form-group">
            <label for="name">
                {{ t('onboarding.company.name') }}
            </label>

            <input
                id="name"
                type="text"
                v-model="form.name"
                required
                autofocus
            />

            <InputError :message="form.errors.name" />
        </div>

        <!-- Phone -->
        <div class="form-group">
            <label for="phone">
                {{ t('onboarding.company.phone') }}
            </label>

            <input
                id="phone"
                type="text"
                v-model="form.phone"
            />

            <InputError :message="form.errors.phone" />
        </div>

        <!-- Address -->
        <div class="form-group">
            <label for="address">
                {{ t('onboarding.company.address') }}
            </label>

            <input
                id="address"
                type="text"
                v-model="form.address"
            />

            <InputError :message="form.errors.address" />
        </div>

        <!-- Actions -->
        <div class="actions">

            <button
                type="submit"
                class="register-btn"
                :disabled="form.processing"
            >
                {{ t('onboarding.company.create') }}
            </button>

        </div>

    </form>

</GuestLayout>
</template>

<style scoped>

.register-title {
    text-align: center;
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 1rem;
    color: #4f46e5;
}

.setup-description {
    text-align: center;
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.register-form {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}

.actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
}

.register-btn {
    background: #111827;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease;
}

.register-btn:hover {
    background: #1f2937;
}

.register-btn:active {
    transform: scale(0.98);
}

.register-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 480px) {

    .actions {
        justify-content: stretch;
    }

    .register-btn {
        width: 100%;
    }

}

</style>
