<script setup lang="ts">
import LineChart from '@/components/charts/LineChart.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import type { ChartSeries } from '@/types';

withDefaults(
    defineProps<{
        title: string;
        labels: string[];
        series: ChartSeries[];
        /** Fill the area under the first line */
        fillFirst?: boolean;
    }>(),
    {
        fillFirst: true,
    },
);
</script>

<template>
    <SectionCard :title="title">
        <LineChart :labels="labels" :series="series" :fill-first="fillFirst" />
    </SectionCard>
</template>
