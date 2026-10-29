<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { settlementStatusMeta } from '@/config/finance';
import { formatCurrency } from '@/lib/format';
import type { DataTableColumn, Settlement } from '@/types';

defineProps<{
    settlements: Settlement[];
}>();

const columns: DataTableColumn<Settlement>[] = [
    { key: 'date', label: 'Date', class: 'whitespace-nowrap' },
    { key: 'flow', label: 'Flow', class: 'whitespace-nowrap' },
    { key: 'amount', label: 'Amount', align: 'right' },
    { key: 'status', label: 'Status' },
];

const rowKey = (row: Settlement): string => `${row.date}-${row.flow}`;
</script>

<template>
    <SectionCard title="Settlement tracker">
        <DataTable :columns="columns" :rows="settlements" :row-key="rowKey">
            <template #cell-amount="{ row }">
                {{ formatCurrency(row.amount) }}
            </template>
            <template #cell-status="{ row }">
                <StatusBadge
                    :label="settlementStatusMeta[row.status].label"
                    :tone="settlementStatusMeta[row.status].tone"
                />
            </template>
        </DataTable>
    </SectionCard>
</template>
