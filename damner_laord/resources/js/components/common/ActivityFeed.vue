<script setup lang="ts">
import type { ActivityItem, ActivityTone } from '@/types';

defineProps<{
    items: ActivityItem[];
    emptyText?: string;
}>();

const dotClass: Record<ActivityTone, string> = {
    success: 'bg-emerald-500',
    error: 'bg-red-500',
    neutral: 'bg-muted-foreground/50',
};
</script>

<!-- Time-stamped event list (live activity, alerts). -->
<template>
    <ul v-if="items.length" class="flex flex-col gap-3">
        <li
            v-for="item in items"
            :key="item.key"
            class="grid grid-cols-[auto_auto_1fr] items-start gap-2 text-sm"
        >
            <span
                :class="['mt-1.5 size-2 rounded-full', dotClass[item.tone]]"
            />
            <span class="text-xs text-muted-foreground tabular-nums">
                {{ item.time }}
            </span>
            <div class="min-w-0">
                <p class="font-semibold">{{ item.title }}</p>
                <p class="truncate text-xs text-muted-foreground">
                    {{ item.description }}
                </p>
            </div>
        </li>
    </ul>
    <p v-else class="py-6 text-center text-sm text-muted-foreground">
        {{ emptyText ?? 'No activity yet.' }}
    </p>
</template>
