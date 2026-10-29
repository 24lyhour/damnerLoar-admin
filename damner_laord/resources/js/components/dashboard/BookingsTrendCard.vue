<script setup lang="ts">
import LineChart from '@/components/charts/LineChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { ChartSeries } from '@/types';

defineProps<{
    title: string;
    labels: string[];
    series: ChartSeries[];
}>();
</script>

<template>
    <Card class="gap-4">
        <CardHeader>
            <CardTitle class="text-sm font-semibold">{{ title }}</CardTitle>
        </CardHeader>
        <CardContent>
            <LineChart :labels="labels" :series="series" />
        </CardContent>
    </Card>
</template>
