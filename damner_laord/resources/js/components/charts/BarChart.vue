<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import Chart from 'primevue/chart';
import { computed } from 'vue';
import { useChartTheme } from '@/composables/useChartTheme';
import type { ChartSlice } from '@/types';

const props = withDefaults(
    defineProps<{
        slices: ChartSlice[];
        height?: string;
    }>(),
    {
        height: '12rem',
    },
);

const theme = useChartTheme();

const data = computed<ChartData<'bar'>>(() => ({
    labels: props.slices.map((slice) => slice.label),
    datasets: [
        {
            data: props.slices.map((slice) => slice.value),
            // One colour per bar, following --chart-1..n
            backgroundColor: theme.value?.series ?? [],
            borderRadius: 4,
            maxBarThickness: 48,
        },
    ],
}));

const options = computed<ChartOptions<'bar'>>(() => ({
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: {
            ticks: { color: theme.value?.text },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: { color: theme.value?.text },
            grid: { color: theme.value?.grid },
            border: { display: false },
        },
    },
}));
</script>

<template>
    <Chart
        v-if="theme"
        type="bar"
        :data="data"
        :options="options"
        :style="{ height: props.height }"
    />
</template>
