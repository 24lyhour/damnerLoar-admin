<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatNumber } from '@/lib/format';
import type { PaginationMeta } from './types';
import { pageWindow, PER_PAGE_OPTIONS } from './utils';

const props = withDefaults(
    defineProps<{
        meta: PaginationMeta;
        perPageOptions?: readonly number[];
        disabled?: boolean;
    }>(),
    {
        perPageOptions: () => PER_PAGE_OPTIONS,
        disabled: false,
    },
);

const emit = defineEmits<{
    page: [page: number];
    perPage: [perPage: number];
}>();

const pages = computed(() =>
    pageWindow(props.meta.current_page, props.meta.last_page),
);

const isFirst = computed(() => props.meta.current_page <= 1);
const isLast = computed(() => props.meta.current_page >= props.meta.last_page);

function goTo(page: number): void {
    if (page !== props.meta.current_page) {
        emit('page', page);
    }
}

function onPerPage(value: unknown): void {
    const perPage = Number(value);

    if (Number.isInteger(perPage) && perPage !== props.meta.per_page) {
        emit('perPage', perPage);
    }
}
</script>

<!-- "Showing 1–8 of 42" · page buttons · "20 / page" (spec list pages). -->
<template>
    <nav
        class="flex flex-wrap items-center justify-between gap-3 text-sm"
        aria-label="Pagination"
    >
        <p class="text-muted-foreground tabular-nums">
            Showing {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} of
            {{ formatNumber(meta.total) }}
        </p>

        <div class="flex items-center gap-1">
            <Button
                variant="outline"
                size="icon-sm"
                :disabled="disabled || isFirst"
                aria-label="Previous page"
                @click="goTo(meta.current_page - 1)"
            >
                <ChevronLeft />
            </Button>

            <template v-for="(page, index) in pages" :key="`${page}-${index}`">
                <span
                    v-if="page === '…'"
                    class="px-1.5 text-muted-foreground"
                    aria-hidden="true"
                >
                    …
                </span>
                <Button
                    v-else
                    :variant="
                        page === meta.current_page ? 'default' : 'outline'
                    "
                    size="icon-sm"
                    class="tabular-nums"
                    :disabled="disabled"
                    :aria-current="
                        page === meta.current_page ? 'page' : undefined
                    "
                    :aria-label="`Page ${page}`"
                    @click="goTo(page)"
                >
                    {{ page }}
                </Button>
            </template>

            <Button
                variant="outline"
                size="icon-sm"
                :disabled="disabled || isLast"
                aria-label="Next page"
                @click="goTo(meta.current_page + 1)"
            >
                <ChevronRight />
            </Button>
        </div>

        <Select
            :model-value="String(meta.per_page)"
            :disabled="disabled"
            @update:model-value="onPerPage"
        >
            <SelectTrigger size="sm" class="w-28" aria-label="Rows per page">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in perPageOptions"
                    :key="option"
                    :value="String(option)"
                >
                    {{ option }} / page
                </SelectItem>
            </SelectContent>
        </Select>
    </nav>
</template>
