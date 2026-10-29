<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import Chart from 'primevue/chart';
import { computed } from 'vue';
import { useChartTheme } from '@/composables/useChartTheme';
import type { ChartSlice } from '@/types';

const props = withDefaults(
    defineProps<{
        slices: ChartSlice[];
        size?: string;
    }>(),
    {
        size: '9rem',
    },
);

const theme = useChartTheme();

const data = computed<ChartData<'doughnut'>>(() => ({
    labels: props.slices.map((slice) => slice.label),
    datasets: [
        {
            data: props.slices.map((slice) => slice.value),
            backgroundColor: theme.value?.series ?? [],
            borderColor: theme.value?.surface,
            borderWidth: 2,
        },
    ],
}));

const options: ChartOptions<'doughnut'> = {
    cutout: '68%',
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
};
</script>

<template>
    <div class="relative" :style="{ width: props.size, height: props.size }">
        <Chart
            v-if="theme"
            type="doughnut"
            :data="data"
            :options="options"
            class="size-full"
        />
        <div
            class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center"
        >
            <slot name="center" />
        </div>
    </div>
</template>
