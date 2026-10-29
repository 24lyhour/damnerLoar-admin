<script setup lang="ts">
import { ArrowDownRight, ArrowUpRight } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import { Card } from '@/components/ui/card';
import {
    formatCurrency,
    formatNumber,
    formatPercent,
    formatPoints,
} from '@/lib/format';
import type { Kpi, KpiFormat } from '@/types';

const props = withDefaults(
    defineProps<{
        label: string;
        value: number;
        format?: KpiFormat;
        change?: number;
        changeUnit?: Kpi['changeUnit'];
        changeLabel?: string;
        hint?: string;
        icon?: LucideIcon;
    }>(),
    {
        format: 'number',
        changeUnit: 'percent',
        changeLabel: 'vs yesterday',
    },
);

const valueFormatters: Record<KpiFormat, (value: number) => string> = {
    number: formatNumber,
    currency: formatCurrency,
    percent: (value) => formatPercent(value),
};

const displayValue = computed(() => valueFormatters[props.format](props.value));

const displayChange = computed(() =>
    props.changeUnit === 'points'
        ? formatPoints(props.change ?? 0)
        : formatPercent(props.change ?? 0),
);

const isUp = computed(() => (props.change ?? 0) >= 0);
</script>

<template>
    <Card class="gap-3 p-4">
        <div class="flex items-start justify-between gap-2">
            <p class="text-sm text-muted-foreground">{{ label }}</p>
            <span
                v-if="icon"
                class="flex size-8 shrink-0 items-center justify-center rounded-md bg-accent text-primary"
            >
                <component :is="icon" class="size-4" />
            </span>
        </div>

        <p class="text-2xl font-bold tracking-tight tabular-nums">
            {{ displayValue }}
        </p>

        <p
            v-if="change !== undefined"
            class="flex items-center gap-1 text-xs text-muted-foreground"
        >
            <span
                :class="[
                    'flex items-center font-semibold',
                    isUp ? 'text-emerald-600' : 'text-red-500',
                ]"
            >
                <component
                    :is="isUp ? ArrowUpRight : ArrowDownRight"
                    class="size-3.5"
                />
                {{ displayChange }}
            </span>
            {{ changeLabel }}
        </p>
        <p v-else-if="hint" class="text-xs text-muted-foreground">
            {{ hint }}
        </p>
    </Card>
</template>
