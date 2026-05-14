<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTranslate } from '@/composables/useTranslate';

const { t } = useTranslate();

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <h2 class="login-title">{{ t('auth.login.title') }}</h2>

        <div v-if="status" class="status-message">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="login-form">

            <div class="form-group">
                <label for="email">{{ t('auth.login.email') }}</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="form-group">
                <label for="password">{{ t('auth.login.password') }}</label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="remember-row">
                <input type="checkbox" v-model="form.remember" />
                <span>{{ t('auth.login.remember') }}</span>
            </div>

            <div class="actions">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="forgot-link"
                >
                    {{ t('auth.login.forgot') }}
                </Link>

                <button
                    type="submit"
                    class="login-btn"
                    :disabled="form.processing"
                >
                    {{ t('auth.login.submit') }}
                </button>
            </div>

        </form>
    </GuestLayout>
</template>

<style scoped>
.login-title {
    text-align: center;
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 1.5rem;
    color: #4f46e5;
}

.status-message {
    background: #eef2ff;
    color: #4f46e5;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 1rem;
    font-size: 14px;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 500;
}

.form-group input {
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
    transition: border 0.2s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #4f46e5;
}

.remember-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}

.forgot-link {
    font-size: 13px;
    color: #6b7280;
    text-decoration: none;
}

.forgot-link:hover {
    color: #4f46e5;
}

.login-btn {
    background: #111827;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease;
}

.login-btn:hover {
    background: #1f2937;
}

.login-btn:active {
    transform: scale(0.98);
}

.login-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Mobile */
@media (max-width: 480px) {
    .actions {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }

    .login-btn {
        width: 100%;
    }
}
</style>