<script setup lang="ts">
import { computed } from 'vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import { formatNumber, formatPercent } from '@/lib/format';
import type { ChartSlice } from '@/types';

const props = defineProps<{
    title: string;
    total: number;
    slices: ChartSlice[];
    /** Optional footer row, shown on the Overview tab */
    cancelledReturnRate?: number;
}>();

// Matches the --chart-N order used by DoughnutChart
const swatches = ['bg-chart-1', 'bg-chart-2', 'bg-chart-3', 'bg-chart-4'];

const sliceTotal = computed(() =>
    props.slices.reduce((sum, slice) => sum + slice.value, 0),
);

function share(value: number): string {
    return sliceTotal.value
        ? formatPercent((value / sliceTotal.value) * 100, 0)
        : '0%';
}
</script>

<template>
    <SectionCard :title="title" content-class="flex flex-col gap-4">
        <div class="flex items-center gap-6">
            <DoughnutChart :slices="slices">
                <template #center>
                    <span class="text-xl font-bold tabular-nums">
                        {{ formatNumber(total) }}
                    </span>
                    <span class="text-[10px] text-muted-foreground">
                        bus bookings
                    </span>
                </template>
            </DoughnutChart>

            <ul class="flex flex-1 flex-col gap-2 text-sm">
                <li
                    v-for="(slice, index) in slices"
                    :key="slice.label"
                    class="flex items-center gap-2"
                >
                    <span :class="['size-2.5 rounded-sm', swatches[index]]" />
                    <span class="flex-1">{{ slice.label }}</span>
                    <span class="font-semibold tabular-nums">
                        {{ share(slice.value) }}
                    </span>
                </li>
            </ul>
        </div>

        <div
            v-if="cancelledReturnRate !== undefined"
            class="flex items-center justify-between border-t pt-3 text-sm"
        >
            <span class="text-muted-foreground">Return legs cancelled</span>
            <span class="font-semibold tabular-nums">
                {{ formatPercent(cancelledReturnRate) }}
            </span>
        </div>
    </SectionCard>
</template>
