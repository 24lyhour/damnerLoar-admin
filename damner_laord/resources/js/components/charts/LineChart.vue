<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import Chart from 'primevue/chart';
import { computed } from 'vue';
import { useChartTheme, withAlpha } from '@/composables/useChartTheme';
import type { ChartSeries } from '@/types';

const props = withDefaults(
    defineProps<{
        labels: string[];
        series: ChartSeries[];
        /** Fill the area under the first series only. */
        fillFirst?: boolean;
        height?: string;
    }>(),
    {
        fillFirst: true,
        height: '16rem',
    },
);

const theme = useChartTheme();

const data = computed<ChartData<'line'>>(() => ({
    labels: props.labels,
    datasets: props.series.map((series, index) => {
        const color = theme.value?.series[index] ?? '#174a8b';

        return {
            label: series.label,
            data: series.data,
            borderColor: color,
            backgroundColor: withAlpha(color, 0.1),
            fill: props.fillFirst && index === 0,
            tension: 0.35,
            pointRadius: 0,
            borderWidth: 2,
        };
    }),
}));

const options = computed<ChartOptions<'line'>>(() => ({
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: {
            position: 'top',
            align: 'start',
            labels: {
                color: theme.value?.text,
                boxWidth: 12,
                boxHeight: 2,
            },
        },
    },
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
        type="line"
        :data="data"
        :options="options"
        :style="{ height: props.height }"
    />
</template>
