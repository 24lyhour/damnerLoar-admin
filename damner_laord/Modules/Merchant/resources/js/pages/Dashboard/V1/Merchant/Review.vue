<script setup lang="ts">
import { computed } from 'vue';
import { DamnerFormField, DamnerFormModal } from '@/components/DamnerFormModal';
import { Textarea } from '@/components/ui/textarea';
import {
    approve,
    reactivate,
    reject,
    requestChanges,
    suspend,
} from '@/routes/merchant/merchants';
import type { RouteFormDefinition } from '@/wayfinder';
import type {
    MerchantListItem,
    MerchantReview,
    MerchantReviewProps,
} from '../../../../types';

// Rendered inside an InertiaUI modal (MerchantReviewController@confirm).
const props = defineProps<MerchantReviewProps>();

type ReviewConfig = {
    action: (args: { merchant: string }) => RouteFormDefinition<'post'>;
    title: (merchant: MerchantListItem) => string;
    description: (merchant: MerchantListItem) => string;
    submitLabel: string;
    destructive?: boolean;
    /** Required text field sent with the request */
    field?: { name: 'message' | 'reason'; label: string; placeholder: string };
};

const reviews: Record<MerchantReview, ReviewConfig> = {
    approve: {
        action: (args) => approve.form(args),
        title: () => 'Approve this merchant?',
        description: (m) =>
            `${m.name} goes live in the apps and starts receiving bookings.`,
        submitLabel: 'Approve',
    },
    'request-changes': {
        action: (args) => requestChanges.form(args),
        title: () => 'Request changes',
        description: (m) =>
            `We'll email ${m.email ?? 'the merchant'} this message and keep a copy in the notes.`,
        submitLabel: 'Send request',
        field: {
            name: 'message',
            label: 'What needs to change?',
            placeholder:
                'e.g. Please upload the new operating licence and seat layouts for both sleeper buses.',
        },
    },
    reject: {
        action: (args) => reject.form(args),
        title: () => 'Reject this application?',
        description: () =>
            "The merchant can't sell on DamnerLaor. This can't be undone.",
        submitLabel: 'Reject application',
        destructive: true,
        field: {
            name: 'reason',
            label: 'Reason',
            placeholder: 'Why is the application rejected?',
        },
    },
    suspend: {
        action: (args) => suspend.form(args),
        title: (m) => `Suspend ${m.name}?`,
        description: () =>
            'Their trips and rooms stop selling and payouts go on hold until reactivated.',
        submitLabel: 'Suspend merchant',
        destructive: true,
        field: {
            name: 'reason',
            label: 'Reason',
            placeholder: 'e.g. 3 no-show trips this week',
        },
    },
    reactivate: {
        action: (args) => reactivate.form(args),
        title: (m) => `Reactivate ${m.name}?`,
        description: () =>
            'They start selling again and are included in the next payout.',
        submitLabel: 'Reactivate',
    },
};

const config = computed(() => reviews[props.review]);
</script>

<!-- One confirm page for all five status changes (universe: Delete.vue). -->
<template>
    <DamnerFormModal
        :action="config.action({ merchant: merchant.uuid })"
        :title="config.title(merchant)"
        :description="config.description(merchant)"
        :submit-label="config.submitLabel"
        :destructive="config.destructive"
        :form-error-keys="['status']"
        size="sm"
    >
        <template #default="{ errors }">
            <DamnerFormField
                v-if="config.field"
                v-slot="field"
                :name="config.field.name"
                :label="config.field.label"
                :error="errors[config.field.name]"
                required
            >
                <Textarea
                    v-bind="field"
                    :placeholder="config.field.placeholder"
                    rows="5"
                />
            </DamnerFormField>
            <p
                v-else-if="review === 'approve' && pendingDocumentCount > 0"
                class="text-sm text-destructive"
            >
                {{ pendingDocumentCount }} required document(s) still need
                verifying.
            </p>
        </template>
    </DamnerFormModal>
</template>
