<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

defineProps<{
    title: string;
    description?: string;
    contentClass?: HTMLAttributes['class'];
}>();
</script>

<!-- Card with a small title row; put buttons/links in #action. -->
<template>
    <Card class="gap-4">
        <CardHeader>
            <CardTitle class="text-sm font-semibold">{{ title }}</CardTitle>
            <CardDescription v-if="description" class="text-xs">
                {{ description }}
            </CardDescription>
            <CardAction v-if="$slots.action">
                <slot name="action" />
            </CardAction>
        </CardHeader>
        <CardContent :class="cn('flex-1', contentClass)">
            <slot />
        </CardContent>
    </Card>
</template>
