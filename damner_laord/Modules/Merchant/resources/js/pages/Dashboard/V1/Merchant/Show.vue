<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CircleAlert } from '@lucide/vue';
import PageHeader from '@/components/common/PageHeader.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { formatDate } from '@/lib/format';
import { index, show } from '@/routes/merchant/merchants';
import MerchantChecklistCard from '../../../../components/Dashboards/v1/MerchantDashboardComponent/MerchantChecklistCard.vue';
import MerchantCompanyCard from '../../../../components/Dashboards/v1/MerchantDashboardComponent/MerchantCompanyCard.vue';
import MerchantNotesCard from '../../../../components/Dashboards/v1/MerchantDashboardComponent/MerchantNotesCard.vue';
import MerchantReviewActions from '../../../../components/Dashboards/v1/MerchantDashboardComponent/MerchantReviewActions.vue';
import type { MerchantDetail } from '../../../../types';

const props = defineProps<{
    merchant: MerchantDetail;
}>();

defineOptions({
    layout: (props: { merchant: MerchantDetail }) => ({
        breadcrumbs: [
            { title: 'Partners', href: index() },
            { title: 'Merchants', href: index() },
            {
                title: props.merchant.name,
                href: show({ merchant: props.merchant.uuid }),
            },
        ],
    }),
});
</script>

<template>
    <Head :title="merchant.name" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <PageHeader :title="merchant.name">
            <template #actions>
                <MerchantReviewActions :merchant="merchant" />
            </template>
        </PageHeader>

        <p
            class="-mt-2 flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
        >
            {{ merchant.type.label }}
            <template v-if="merchant.appliedAt">
                · applied {{ formatDate(merchant.appliedAt) }}
            </template>
            <template v-if="merchant.approvedAt">
                · approved {{ formatDate(merchant.approvedAt) }}
            </template>
            ·
            <StatusBadge
                :label="merchant.status.label"
                :tone="merchant.status.tone"
            />
        </p>

        <Alert
            v-if="merchant.status.value === 'suspended'"
            variant="destructive"
        >
            <CircleAlert />
            <AlertTitle>Suspended</AlertTitle>
            <AlertDescription>
                {{ merchant.suspendedReason ?? 'No reason recorded.' }}
                Trips and rooms aren't selling and payouts are on hold.
            </AlertDescription>
        </Alert>

        <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <MerchantChecklistCard
                :merchant-uuid="merchant.uuid"
                :items="merchant.checklist"
                :editable="merchant.can.review"
            />

            <div class="flex flex-col gap-4">
                <MerchantCompanyCard :merchant="merchant" />
                <MerchantNotesCard
                    :merchant-uuid="merchant.uuid"
                    :notes="merchant.notes"
                    :editable="merchant.can.review"
                />
            </div>
        </div>
    </div>
</template>
