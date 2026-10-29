<script setup lang="ts">
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { StatusTab } from './types';

defineProps<{
    tabs: StatusTab[];
}>();

/** null = "All" */
const status = defineModel<string | null>({ default: null });
</script>

<!-- Segmented status tabs with counts: All 42 · Pending approval 3 · … -->
<template>
    <div
        class="inline-flex max-w-full overflow-x-auto rounded-lg bg-muted p-1"
        role="tablist"
        aria-label="Filter by status"
    >
        <button
            v-for="tab in tabs"
            :key="tab.value ?? 'all'"
            type="button"
            role="tab"
            :aria-selected="status === tab.value"
            :class="
                cn(
                    'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
                    status === tab.value
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground',
                )
            "
            @click="status = tab.value"
        >
            {{ tab.label }}
            <span
                v-if="tab.count !== undefined"
                :class="
                    cn(
                        'rounded-full px-1.5 text-xs tabular-nums',
                        status === tab.value
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-background/70',
                    )
                "
            >
                {{ formatNumber(tab.count) }}
            </span>
        </button>
    </div>
</template>
