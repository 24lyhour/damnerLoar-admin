<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import KpiCard from '@/components/common/KpiCard.vue';
import type { Kpi } from '@/types';

withDefaults(
    defineProps<{
        kpis: Kpi[];
        /** Icon per KPI key */
        icons?: Record<string, LucideIcon>;
    }>(),
    {
        icons: () => ({}),
    },
);
</script>

<template>
    <section
        :class="[
            'grid gap-4 sm:grid-cols-2',
            kpis.length > 4
                ? 'lg:grid-cols-3 xl:grid-cols-5'
                : 'xl:grid-cols-4',
        ]"
    >
        <KpiCard
            v-for="kpi in kpis"
            :key="kpi.key"
            :label="kpi.label"
            :value="kpi.value"
            :format="kpi.format"
            :change="kpi.change"
            :change-unit="kpi.changeUnit"
            :change-label="kpi.changeLabel"
            :hint="kpi.hint"
            :icon="icons[kpi.key]"
        />
    </section>
</template>
