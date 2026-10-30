<script setup lang="ts">
import { ModalLink } from '@inertiaui/modal-vue';
import { Check, Mail, PauseCircle, Pencil, PlayCircle, X } from '@lucide/vue';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    confirmApprove,
    confirmReactivate,
    confirmReject,
    confirmRequestChanges,
    confirmSuspend,
    edit,
} from '@/routes/merchant/merchants';
import type { MerchantDetail } from '../../../../../types';

const props = defineProps<{
    merchant: MerchantDetail;
}>();

const args = computed(() => ({ merchant: props.merchant.uuid }));

const approveLabel = computed(() => {
    const left = props.merchant.pendingDocumentCount;

    return left > 0
        ? `Approve · ${left} ${left === 1 ? 'item' : 'items'} left`
        : 'Approve';
});

const primary = buttonVariants({ size: 'sm' });
const outline = buttonVariants({ size: 'sm', variant: 'outline' });
const destructiveOutline = cn(
    outline,
    'border-destructive/40 text-destructive hover:bg-destructive/10 hover:text-destructive',
);
</script>

<!--
    Buttons on the merchant page (spec 03.2 + suspend / reactivate). Each
    opens a server modal (InertiaUI ModalLink), the same modals the list
    row menu opens. Which buttons show comes from `merchant.can`.
-->
<template>
    <div class="flex flex-wrap items-center gap-2">
        <ModalLink v-if="merchant.can.update" :href="edit(args).url" :class="outline">
            <Pencil />
            Edit details
        </ModalLink>
        <ModalLink v-if="merchant.can.requestChanges" :href="confirmRequestChanges(args).url" :class="primary">
            <Mail />
            Request changes
        </ModalLink>
        <ModalLink v-if="merchant.can.reject" :href="confirmReject(args).url" :class="destructiveOutline">
            <X />
            Reject
        </ModalLink>
        <ModalLink v-if="merchant.can.approve && merchant.pendingDocumentCount === 0" :href="confirmApprove(args).url"
            :class="outline">
            <Check />
            {{ approveLabel }}
        </ModalLink>
        <span v-else-if="merchant.can.approve" :class="cn(outline, 'pointer-events-none opacity-50')"
            title="Verify every checklist item first" aria-disabled="true">
            <Check />
            {{ approveLabel }}
        </span>
        <ModalLink v-if="merchant.can.suspend" :href="confirmSuspend(args).url" :class="destructiveOutline">
            <PauseCircle />
            Suspend
        </ModalLink>
        <ModalLink v-if="merchant.can.reactivate" :href="confirmReactivate(args).url" :class="primary">
            <PlayCircle />
            Reactivate
        </ModalLink>
    </div>
</template>
