<script setup lang="ts">
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { SelectOption } from './types';

const props = withDefaults(
    defineProps<{
        label: string;
        options: SelectOption[];
        allLabel?: string;
    }>(),
    {
        allLabel: 'All',
    },
);

/** null = no filter ("All") */
const selected = defineModel<string | null>({ default: null });

// The select can't hold an empty value, so "All" uses a sentinel.
const ALL = '__all__';

const current = computed({
    get: () => selected.value ?? ALL,
    set: (value: string) => (selected.value = value === ALL ? null : value),
});

const currentLabel = computed(
    () =>
        props.options.find((option) => option.value === selected.value)
            ?.label ?? props.allLabel,
);
</script>

<!-- Filter chip, e.g. "City: All ▾" (spec list pages). -->
<template>
    <Select v-model="current">
        <SelectTrigger
            size="sm"
            :class="cn('gap-1', selected && 'border-primary text-primary')"
            :aria-label="`Filter by ${label}`"
        >
            <SelectValue>
                <span class="text-muted-foreground">{{ label }}:</span>
                {{ currentLabel }}
            </SelectValue>
        </SelectTrigger>
        <SelectContent>
            <SelectItem :value="ALL">{{ allLabel }}</SelectItem>
            <SelectItem
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
