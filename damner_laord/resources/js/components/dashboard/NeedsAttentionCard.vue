<script setup lang="ts">
import { CircleAlert, Info } from '@lucide/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import type { AttentionItem } from '@/types';

defineProps<{
    items: AttentionItem[];
}>();
</script>

<template>
    <Card class="gap-2">
        <CardHeader>
            <CardTitle class="text-sm font-semibold">Needs attention</CardTitle>
        </CardHeader>
        <CardContent class="px-3">
            <ul v-if="items.length" class="divide-y">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-start gap-3 px-3 py-3"
                >
                    <span
                        :class="
                            cn(
                                'flex size-8 shrink-0 items-center justify-center rounded-md',
                                item.severity === 'error'
                                    ? 'bg-red-50 text-red-500 dark:bg-red-950'
                                    : 'bg-accent text-primary',
                            )
                        "
                    >
                        <component
                            :is="item.severity === 'error' ? CircleAlert : Info"
                            class="size-4"
                        />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold">{{ item.title }}</p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>
                </li>
            </ul>
            <p
                v-else
                class="px-3 py-6 text-center text-sm text-muted-foreground"
            >
                All clear, nothing waiting.
            </p>
        </CardContent>
    </Card>
</template>
