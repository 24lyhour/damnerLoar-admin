<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import ProgressBar from '@/components/common/ProgressBar.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { tripStatusMeta } from '@/config/transport';
import type { DataTableColumn, Departure } from '@/types';

defineProps<{
    departures: Departure[];
}>();

const columns: DataTableColumn<Departure>[] = [
    { key: 'time', label: 'Departs', class: 'font-semibold tabular-nums' },
    { key: 'route', label: 'Route', class: 'whitespace-nowrap' },
    { key: 'operator', label: 'Operator', class: 'whitespace-nowrap' },
    { key: 'seatsSold', label: 'Seats', class: 'w-40' },
    { key: 'boarded', label: 'Boarded', align: 'right' },
    {
        key: 'roundTripPassengers',
        label: 'Round-trip passengers',
        align: 'right',
    },
    { key: 'status', label: 'Status' },
];

const rowKey = (row: Departure): string => `${row.time}-${row.operator}`;

function statusLabel(trip: Departure): string {
    const label = tripStatusMeta[trip.status].label;

    return trip.status === 'delayed' && trip.delayMinutes
        ? `${label} ${trip.delayMinutes} min`
        : label;
}
</script>

<template>
    <SectionCard title="Next departures">
        <DataTable
            :columns="columns"
            :rows="departures"
            :row-key="rowKey"
            empty-text="No more departures today."
        >
            <template #cell-seatsSold="{ row }">
                <ProgressBar :value="row.seatsSold" :max="row.seatsTotal" />
            </template>
            <template #cell-status="{ row }">
                <StatusBadge
                    :label="statusLabel(row)"
                    :tone="tripStatusMeta[row.status].tone"
                />
            </template>
        </DataTable>
    </SectionCard>
</template>
