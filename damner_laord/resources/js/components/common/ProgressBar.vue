<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        value: number;
        max: number;
        /** Show "value/max" next to the bar */
        showLabel?: boolean;
    }>(),
    {
        showLabel: true,
    },
);

const percent = computed(() =>
    props.max > 0 ? Math.min(100, (props.value / props.max) * 100) : 0,
);
</script>

<template>
    <div class="flex items-center gap-2">
        <span
            class="h-1.5 w-full min-w-16 overflow-hidden rounded-full bg-accent"
            role="progressbar"
            :aria-valuenow="value"
            :aria-valuemax="max"
        >
            <span
                class="block h-full rounded-full bg-primary"
                :style="{ width: `${percent}%` }"
            />
        </span>
        <span
            v-if="showLabel"
            class="text-xs whitespace-nowrap text-muted-foreground tabular-nums"
        >
            {{ value }}/{{ max }}
        </span>
    </div>
</template>
