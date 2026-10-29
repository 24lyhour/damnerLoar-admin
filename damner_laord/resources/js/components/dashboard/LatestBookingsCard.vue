<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { bookingStatusMeta, serviceMeta } from '@/config/bookings';
import { formatCurrency } from '@/lib/format';
import type { DataTableColumn, LatestBooking } from '@/types';

defineProps<{
    bookings: LatestBooking[];
}>();

const columns: DataTableColumn<LatestBooking>[] = [
    { key: 'reference', label: 'Booking', class: 'font-mono text-xs' },
    { key: 'customer', label: 'Customer', class: 'whitespace-nowrap' },
    { key: 'service', label: 'Service' },
    { key: 'amount', label: 'Amount', align: 'right' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <SectionCard title="Latest bookings">
        <DataTable :columns="columns" :rows="bookings" row-key="reference">
            <template #cell-service="{ row }">
                <span class="flex items-center gap-1.5">
                    <component
                        :is="serviceMeta[row.service].icon"
                        class="size-4 text-primary"
                    />
                    {{ serviceMeta[row.service].label }}
                </span>
            </template>
            <template #cell-amount="{ row }">
                {{ formatCurrency(row.amount) }}
            </template>
            <template #cell-status="{ row }">
                <StatusBadge
                    :label="bookingStatusMeta[row.status].label"
                    :tone="bookingStatusMeta[row.status].tone"
                />
            </template>
        </DataTable>
    </SectionCard>
</template>
