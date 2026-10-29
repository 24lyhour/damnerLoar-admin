<script setup lang="ts">
import { computed } from 'vue';
import { formatNumber } from '@/lib/format';
import type { BarListItem } from '@/types';

const props = defineProps<{
    items: BarListItem[];
}>();

const max = computed(() =>
    Math.max(1, ...props.items.map((item) => item.value)),
);
</script>

<!-- Horizontal bars scaled to the largest value (funnels, top lists). -->
<template>
    <ul class="flex flex-col gap-2.5 text-sm">
        <li
            v-for="item in items"
            :key="item.label"
            class="grid grid-cols-[minmax(7rem,9rem)_1fr_auto] items-center gap-3 sm:grid-cols-[minmax(7rem,9rem)_1fr_4rem_7rem]"
        >
            <span class="truncate">{{ item.label }}</span>
            <span class="h-3 overflow-hidden rounded-full bg-accent">
                <span
                    class="block h-full rounded-full bg-primary"
                    :style="{ width: `${(item.value / max) * 100}%` }"
                />
            </span>
            <span class="text-right font-semibold tabular-nums">
                {{ formatNumber(item.value) }}
            </span>
            <span class="hidden text-xs text-muted-foreground sm:block">
                {{ item.note }}
            </span>
        </li>
    </ul>
</template>
