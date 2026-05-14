<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTranslate } from '@/composables/useTranslate';

const { t } = useTranslate();

const form = useForm({
    name: '',
    email: '',
    role_id: '',
    password: '',
    password_confirmation: '',
});

const props = defineProps({
    roles: Array,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.register.submit')" />

        <h2 class="register-title">
            {{ t('auth.register.title') }}
        </h2>

        <form @submit.prevent="submit" class="register-form">

            <!-- Name -->
            <div class="form-group">
                <label for="name">
                    {{ t('auth.register.name') }}
                </label>

                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">
                    {{ t('auth.register.email') }}
                </label>

                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <!-- Role -->
            <div class="form-group">
                <label for="role_id">
                    {{ t('auth.register.role') }}
                </label>

                <select
                    id="role_id"
                    v-model="form.role_id"
                    required
                >
                    <option value="" disabled>
                        {{ t('auth.register.role_placeholder') }}
                    </option>

                    <option
                        v-for="role in props.roles"
                        :key="role.id"
                        :value="role.id"
                    >
                        {{ role.name }}
                    </option>
                </select>

                <InputError :message="form.errors.role_id" />
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">
                    {{ t('auth.register.password') }}
                </label>

                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">
                    {{ t('auth.register.confirm_password') }}
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" />
            </div>

            <!-- Actions -->
            <div class="actions">
                <Link
                    :href="route('login')"
                    class="login-link"
                >
                    {{ t('auth.register.already_registered') }}
                </Link>

                <button
                    type="submit"
                    class="register-btn"
                    :disabled="form.processing"
                >
                    {{ t('auth.register.submit') }}
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
    margin-bottom: 1.5rem;
    color: #4f46e5;
}

.register-form {
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

.form-group input,
.form-group select {
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
    transition: border 0.2s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #4f46e5;
}

.actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}

.login-link {
    font-size: 13px;
    color: #6b7280;
    text-decoration: none;
}

.login-link:hover {
    color: #4f46e5;
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

/* Mobile */
@media (max-width: 480px) {
    .actions {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }

    .register-btn {
        width: 100%;
    }
}

</style>