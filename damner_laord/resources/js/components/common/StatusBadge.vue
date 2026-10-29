<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { StatusTone } from '@/types';

const props = withDefaults(
    defineProps<{
        label: string;
        tone?: StatusTone;
    }>(),
    {
        tone: 'neutral',
    },
);

const toneClasses: Record<StatusTone, string> = {
    success:
        'border-transparent bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    error: 'border-transparent bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-300',
    neutral: 'border-border bg-background text-foreground',
    brand: 'border-transparent bg-primary text-primary-foreground',
};
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex w-fit items-center rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap',
                toneClasses[props.tone],
            )
        "
    >
        {{ label }}
    </span>
</template>
