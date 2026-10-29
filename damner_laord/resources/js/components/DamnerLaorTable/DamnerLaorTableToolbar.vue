<script setup lang="ts">
import { Search, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

withDefaults(
    defineProps<{
        searchPlaceholder?: string;
        /** Show a "Reset" button (pass true when any filter is active) */
        canReset?: boolean;
    }>(),
    {
        searchPlaceholder: 'Search…',
        canReset: false,
    },
);

const search = defineModel<string>('search', { default: '' });

const emit = defineEmits<{
    reset: [];
}>();
</script>

<!-- Search box + filter chips (#filters) + right-side buttons (#actions). -->
<template>
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative w-full sm:w-72">
            <Search
                class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                type="search"
                :placeholder="searchPlaceholder"
                :aria-label="searchPlaceholder"
                class="pl-8"
            />
        </div>

        <slot name="filters" />

        <Button
            v-if="canReset"
            variant="ghost"
            size="sm"
            @click="emit('reset')"
        >
            <X />
            Reset
        </Button>

        <div v-if="$slots.actions" class="ml-auto flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
