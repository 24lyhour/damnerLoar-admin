<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SectionCard from '@/components/common/SectionCard.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { formatDate, formatFileSize } from '@/lib/format';
import { verify } from '@/routes/merchant/merchants/documents';
import type { MerchantChecklistItem } from '../../../../../types';

const props = defineProps<{
    merchantUuid: string;
    items: MerchantChecklistItem[];
    /** Admin may tick items (policy "review") */
    editable: boolean;
}>();

const busy = ref<string | null>(null);

function details(item: MerchantChecklistItem): string {
    const parts = [
        item.fileName,
        item.fileSize ? formatFileSize(item.fileSize) : null,
        item.note,
        item.status.value === 'expired' && item.expiresAt
            ? `Expired ${formatDate(item.expiresAt)}`
            : null,
    ];

    return parts.filter(Boolean).join(' · ') || 'Not uploaded yet';
}

function canToggle(item: MerchantChecklistItem): boolean {
    if (!props.editable || item.uuid === null) {
        return false;
    }

    // Verified items can always be unticked; others only when uploaded and valid.
    return item.status.value === 'verified' || item.canVerify;
}

function toggle(item: MerchantChecklistItem, verified: boolean): void {
    if (!item.uuid || !canToggle(item)) {
        return;
    }

    busy.value = item.uuid;
    router.patch(
        verify({ merchant: props.merchantUuid, document: item.uuid }),
        { verified },
        {
            preserveScroll: true,
            onFinish: () => (busy.value = null),
        },
    );
}
</script>

<!-- Spec 03.2 "Approval checklist": one row per required document. -->
<template>
    <SectionCard title="Approval checklist" content-class="px-0">
        <ul class="divide-y">
            <li v-for="item in items" :key="item.type.value" class="flex items-center gap-3 px-6 py-3">
                <Checkbox :id="`doc-${item.type.value}`" :model-value="item.status.value === 'verified'"
                    :disabled="!canToggle(item) || busy === item.uuid"
                    :aria-label="`Mark ${item.type.label} as verified`" @update:model-value="
                        (checked) => toggle(item, checked === true)
                    " />
                <label :for="`doc-${item.type.value}`" class="min-w-0 flex-1">
                    <span class="block text-sm font-medium">{{
                        item.type.label
                        }}</span>
                    <span class="block truncate text-xs text-muted-foreground">
                        {{ details(item) }}
                    </span>
                </label>
                <StatusBadge :label="item.status.label" :tone="item.status.tone" />
            </li>
        </ul>
    </SectionCard>
</template>
