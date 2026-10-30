<script setup lang="ts">
import { computed } from 'vue';
import SectionCard from '@/components/common/SectionCard.vue';
import type { MerchantDetail } from '../../../../../types';

const props = defineProps<{
    merchant: MerchantDetail;
}>();

const rows = computed(() =>
    [
        { label: 'Legal name', value: props.merchant.legalName },
        { label: 'Contact', value: props.merchant.contactName },
        { label: 'Phone', value: props.merchant.phone },
        { label: 'Email', value: props.merchant.email },
        { label: 'Website', value: props.merchant.website },
        { label: 'Address', value: props.merchant.address },
        { label: 'Province', value: props.merchant.province },
        { label: 'Commission', value: `${props.merchant.commissionPercent} %` },
        { label: 'Approved by', value: props.merchant.approvedBy },
    ].filter((row) => row.value),
);
</script>

<!-- Spec 03.2 "Company" card. -->
<template>
    <SectionCard title="Company">
        <dl class="flex flex-col divide-y text-sm">
            <div v-for="row in rows" :key="row.label" class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
                <dt class="text-muted-foreground">{{ row.label }}</dt>
                <dd class="text-right font-medium break-all">
                    {{ row.value }}
                </dd>
            </div>
        </dl>
    </SectionCard>
</template>
