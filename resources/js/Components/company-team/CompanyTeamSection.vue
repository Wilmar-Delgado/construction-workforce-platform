<script setup>
import BaseModal from '@/Components/base/BaseModal.vue';
import ConfirmModal from '@/Components/base/ConfirmModal.vue';
import InputError from '@/Components/InputError.vue';
import { useTranslate } from '@/composables/useTranslate';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    team: {
        type: Object,
        required: true,
    },
});

const { t } = useTranslate();

const showInviteModal = ref(false);
const invitationToCancel = ref(null);
const memberToRemove = ref(null);

const invitationForm = useForm({
    name: '',
    email: '',
});

const cancellationForm = useForm({});
const removalForm = useForm({});

const canManage = computed(() => props.team.can_manage);

function openInviteModal() {
    invitationForm.reset();
    invitationForm.clearErrors();
    showInviteModal.value = true;
}

function closeInviteModal() {
    showInviteModal.value = false;
    invitationForm.clearErrors();
}

function submitInvitation() {
    invitationForm.post(route('company-team.invitations.store'), {
        preserveScroll: true,
        onSuccess: closeInviteModal,
    });
}

function confirmCancellation(invitation) {
    invitationToCancel.value = invitation;
}

function cancelInvitation() {
    if (!invitationToCancel.value) {
        return;
    }

    cancellationForm.post(
        route('company-team.invitations.cancel', invitationToCancel.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                invitationToCancel.value = null;
            },
        },
    );
}

function confirmRemoval(member) {
    memberToRemove.value = member;
}

function removeMember() {
    if (!memberToRemove.value) {
        return;
    }

    removalForm.delete(route('company-team.members.destroy', memberToRemove.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            memberToRemove.value = null;
        },
    });
}

function roleLabel(role) {
    return t(`company_team.roles.${role}`);
}
</script>

<template>
    <section class="card company-team-section">
        <div class="company-team-heading">
            <div>
                <h3 class="card-title">
                    {{ t('company_team.title') }}
                </h3>
                <p class="card-subtitle">
                    {{ t('company_team.subtitle', { company: team.company_name }) }}
                </p>
            </div>

            <button
                v-if="canManage"
                class="btn-primary"
                type="button"
                @click="openInviteModal"
            >
                {{ t('company_team.add_planning_manager') }}
            </button>
        </div>

        <div class="team-list">
            <div
                v-for="member in team.members"
                :key="member.id"
                class="team-row"
            >
                <div>
                    <p class="team-member-name">
                        {{ member.name }}
                    </p>
                    <p class="team-member-email">
                        {{ member.email }}
                    </p>
                </div>

                <div class="team-row-actions">
                    <span class="team-role">
                        {{ roleLabel(member.role) }}
                    </span>
                    <button
                        v-if="canManage && !member.is_owner"
                        class="text-danger-action"
                        type="button"
                        @click="confirmRemoval(member)"
                    >
                        {{ t('company_team.remove') }}
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="team.invitations.length"
            class="pending-invitations"
        >
            <h4>
                {{ t('company_team.pending_invitations') }}
            </h4>

            <div
                v-for="invitation in team.invitations"
                :key="invitation.id"
                class="team-row"
            >
                <div>
                    <p class="team-member-name">
                        {{ invitation.name }}
                    </p>
                    <p class="team-member-email">
                        {{ invitation.email }}
                    </p>
                    <span class="invitation-status">
                        {{ t('company_team.pending') }}
                    </span>
                    <p class="invitation-expiry">
                        {{ t('company_team.expires_at', { date: invitation.expires_at }) }}
                    </p>
                </div>

                <button
                    v-if="canManage"
                    class="text-danger-action"
                    type="button"
                    @click="confirmCancellation(invitation)"
                >
                    {{ t('company_team.cancel_invitation') }}
                </button>
            </div>
        </div>
    </section>

    <BaseModal
        v-model="showInviteModal"
        :title="t('company_team.invite_modal.title')"
        max-width="520px"
        @update:model-value="!$event && closeInviteModal()"
    >
        <form
            class="company-team-form"
            @submit.prevent="submitInvitation"
        >
            <div class="form-group">
                <label for="planning-manager-name">
                    {{ t('company_team.invite_modal.name') }}
                </label>
                <input
                    id="planning-manager-name"
                    v-model="invitationForm.name"
                    type="text"
                    autocomplete="name"
                />
                <InputError :message="invitationForm.errors.name" />
            </div>

            <div class="form-group">
                <label for="planning-manager-email">
                    {{ t('company_team.invite_modal.email') }}
                </label>
                <input
                    id="planning-manager-email"
                    v-model="invitationForm.email"
                    type="email"
                    autocomplete="email"
                />
                <InputError :message="invitationForm.errors.email" />
            </div>

            <div class="modal-actions">
                <button
                    class="btn-secondary"
                    type="button"
                    @click="closeInviteModal"
                >
                    {{ t('company_team.cancel') }}
                </button>
                <button
                    class="btn-primary"
                    type="submit"
                    :disabled="invitationForm.processing"
                >
                    {{ t('company_team.invite_modal.send') }}
                </button>
            </div>
        </form>
    </BaseModal>

    <ConfirmModal
        :model-value="Boolean(invitationToCancel)"
        :title="t('company_team.cancel_invitation_modal.title')"
        :message="t('company_team.cancel_invitation_modal.message')"
        :item-name="invitationToCancel?.name"
        :confirm-text="t('company_team.cancel_invitation')"
        :cancel-text="t('company_team.cancel')"
        :loading="cancellationForm.processing"
        danger
        @update:model-value="!$event && (invitationToCancel = null)"
        @confirm="cancelInvitation"
    />

    <ConfirmModal
        :model-value="Boolean(memberToRemove)"
        :title="t('company_team.remove_member_modal.title')"
        :message="t('company_team.remove_member_modal.message')"
        :item-name="memberToRemove?.name"
        :confirm-text="t('company_team.remove')"
        :cancel-text="t('company_team.cancel')"
        :loading="removalForm.processing"
        danger
        @update:model-value="!$event && (memberToRemove = null)"
        @confirm="removeMember"
    />
</template>

<style scoped>
.card-title {
    font-weight: 600;
    font-size: 1.25rem;
}

.card-subtitle {
    color: #6b7280;
    margin-bottom: 1rem;
}

.company-team-section {
    margin-top: 24px;
    padding: 1.5rem;
    border-radius: 12px;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.company-team-heading,
.team-row,
.team-row-actions,
.modal-actions {
    display: flex;
    align-items: center;
}

.company-team-heading,
.team-row {
    justify-content: space-between;
    gap: 16px;
}

.team-list,
.pending-invitations {
    margin-top: 18px;
}

.team-row {
    padding: 12px 0;
    border-top: 1px solid #e5e7eb;
}

.team-member-name {
    margin: 0;
    font-weight: 600;
    color: #111827;
}

.team-member-email,
.invitation-expiry {
    margin: 3px 0 0;
    color: #6b7280;
    font-size: 13px;
}

.team-row-actions,
.modal-actions {
    justify-content: flex-end;
    gap: 12px;
}

.team-role {
    color: #4b5563;
    font-size: 13px;
}

.invitation-status {
    display: inline-block;
    margin-top: 6px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    font-size: 12px;
    font-weight: 600;
}

.text-danger-action {
    border: 0;
    background: transparent;
    color: #dc2626;
    cursor: pointer;
    font: inherit;
}

.pending-invitations h4 {
    margin: 0;
    color: #374151;
    font-size: 14px;
}

.company-team-form {
    display: grid;
    gap: 16px;
}

.modal-actions {
    margin-top: 8px;
}

@media (max-width: 640px) {
    .company-team-heading,
    .team-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .team-row-actions {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
