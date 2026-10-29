<script setup lang="ts">
import { computed } from 'vue';
import DataTable from '@/components/common/DataTable.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import { serviceMeta } from '@/config/bookings';
import { formatCurrency, formatNumber } from '@/lib/format';
import type { DataTableColumn, ServiceRevenue } from '@/types';

const props = defineProps<{
    rows: ServiceRevenue[];
}>();

const columns: DataTableColumn<ServiceRevenue>[] = [
    { key: 'service', label: 'Service' },
    { key: 'bookings', label: 'Bookings', align: 'right' },
    { key: 'gross', label: 'Gross', align: 'right' },
    { key: 'commission', label: 'Commission', align: 'right' },
    { key: 'refunds', label: 'Refunds', align: 'right' },
    { key: 'roundTripShare', label: 'Round-trip', align: 'right' },
];

const totals = computed(() => ({
    bookings: props.rows.reduce((sum, row) => sum + row.bookings, 0),
    gross: props.rows.reduce((sum, row) => sum + row.gross, 0),
    commission: props.rows.reduce((sum, row) => sum + row.commission, 0),
    refunds: props.rows.reduce((sum, row) => sum + row.refunds, 0),
}));
</script>

<template>
    <SectionCard title="Revenue by service · MTD">
        <DataTable :columns="columns" :rows="rows" row-key="service">
            <template #cell-service="{ row }">
                <span class="flex items-center gap-1.5 font-medium">
                    <component
                        :is="serviceMeta[row.service].icon"
                        class="size-4 text-primary"
                    />
                    {{ serviceMeta[row.service].label }}
                </span>
            </template>
            <template #cell-bookings="{ row }">
                {{ formatNumber(row.bookings) }}
            </template>
            <template #cell-gross="{ row }">
                {{ formatCurrency(row.gross) }}
            </template>
            <template #cell-commission="{ row }">
                {{ formatCurrency(row.commission) }}
                <span class="text-muted-foreground">
                    ({{ row.commissionRate }}%)
                </span>
            </template>
            <template #cell-refunds="{ row }">
                {{ formatCurrency(row.refunds) }}
            </template>
            <template #cell-roundTripShare="{ row }">
                {{
                    row.roundTripShare === null ? '—' : `${row.roundTripShare}%`
                }}
            </template>

            <template #footer>
                <tr class="text-right tabular-nums">
                    <td class="py-2.5 pr-3 text-left">Total</td>
                    <td class="py-2.5 pr-3">
                        {{ formatNumber(totals.bookings) }}
                    </td>
                    <td class="py-2.5 pr-3">
                        {{ formatCurrency(totals.gross) }}
                    </td>
                    <td class="py-2.5 pr-3">
                        {{ formatCurrency(totals.commission) }}
                    </td>
                    <td class="py-2.5 pr-3">
                        {{ formatCurrency(totals.refunds) }}
                    </td>
                    <td />
                </tr>
            </template>
        </DataTable>
    </SectionCard>
</template>
