<script setup lang="ts">
import { useIntervalFn } from '@vueuse/core';
import { ref, watch } from 'vue';
import DataTable from '@/components/common/DataTable.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import { formatCountdown } from '@/lib/format';
import type { ActiveHold, DataTableColumn } from '@/types';

const props = defineProps<{
    holds: ActiveHold[];
}>();

const columns: DataTableColumn<ActiveHold>[] = [
    { key: 'reference', label: 'Booking', class: 'font-mono text-xs' },
    { key: 'item', label: 'Item' },
    { key: 'secondsLeft', label: 'Left', align: 'right' },
];

// Tick locally between server refreshes; reset when fresh data arrives.
const elapsed = ref(0);
useIntervalFn(() => elapsed.value++, 1000);
watch(
    () => props.holds,
    () => (elapsed.value = 0),
);
</script>

<template>
    <SectionCard title="Active holds · expiring soon">
        <DataTable
            :columns="columns"
            :rows="holds"
            row-key="reference"
            empty-text="No seats or rooms on hold."
        >
            <template #cell-secondsLeft="{ row }">
                <span class="font-semibold">
                    {{ formatCountdown(row.secondsLeft - elapsed) }}
                </span>
            </template>
        </DataTable>
    </SectionCard>
</template>
