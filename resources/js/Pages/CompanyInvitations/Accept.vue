<script setup>
import InputError from '@/Components/InputError.vue';
import { useTranslate } from '@/composables/useTranslate';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const { t } = useTranslate();

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    invitation: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('company-invitations.accept.store', props.token), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head :title="t('company_team.accept.title')" />

        <h2 class="invite-title">
            {{ t('company_team.accept.title') }}
        </h2>
        <p class="invite-subtitle">
            {{ t('company_team.accept.subtitle', { company: invitation.company_name }) }}
        </p>
        <InputError :message="form.errors.invitation" />

        <dl class="invitation-details">
            <div>
                <dt>{{ t('company_team.accept.name') }}</dt>
                <dd>{{ invitation.name }}</dd>
            </div>
            <div>
                <dt>{{ t('company_team.accept.email') }}</dt>
                <dd>{{ invitation.email }}</dd>
            </div>
        </dl>

        <form
            class="invite-form"
            @submit.prevent="submit"
        >
            <div class="form-group">
                <label for="password">
                    {{ t('company_team.accept.password') }}
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    {{ t('company_team.accept.password_confirmation') }}
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <button
                class="btn-primary"
                type="submit"
                :disabled="form.processing"
            >
                {{ t('company_team.accept.submit') }}
            </button>
        </form>
    </GuestLayout>
</template>

<style scoped>
.invite-title {
    margin: 0;
    color: #111827;
    font-size: 24px;
}

.invite-subtitle {
    margin: 8px 0 20px;
    color: #6b7280;
}

.invitation-details {
    display: grid;
    gap: 10px;
    margin: 0 0 22px;
    padding: 16px;
    border-radius: 10px;
    background: #f9fafb;
}

.invitation-details div {
    display: flex;
    justify-content: space-between;
    gap: 16px;
}

dt {
    color: #6b7280;
}

dd {
    margin: 0;
    color: #111827;
    font-weight: 600;
    text-align: right;
}

.invite-form {
    display: grid;
    gap: 16px;
}
</style>
