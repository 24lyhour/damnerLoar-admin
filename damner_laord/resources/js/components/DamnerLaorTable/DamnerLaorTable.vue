<script setup lang="ts" generic="T">
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef, RowSelectionState } from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ChevronsUpDown, Inbox } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';
import DamnerLaorTablePagination from './DamnerLaorTablePagination.vue';
import type { ColumnAlign, PaginationMeta, RowKey, TableColumn } from './types';
import {
    cellValue,
    displayValue,
    formatSort,
    nextSort,
    parseSort,
    PER_PAGE_OPTIONS,
    rowKeyOf,
    sortKeyOf,
} from './utils';

const props = withDefaults(
    defineProps<{
        columns: TableColumn<T>[];
        rows: T[];
        rowKey: RowKey<T>;
        /** Laravel paginator meta; shows the paginator when given */
        meta?: PaginationMeta;
        /** Current server sort, e.g. "-created_at" */
        sort?: string | null;
        /** Adds a checkbox column; use the #bulk-actions slot */
        selectable?: boolean;
        loading?: boolean;
        /** Emit `row-click` and show a pointer on rows */
        clickableRows?: boolean;
        emptyTitle?: string;
        emptyDescription?: string;
        perPageOptions?: readonly number[];
    }>(),
    {
        meta: undefined,
        sort: null,
        selectable: false,
        loading: false,
        clickableRows: false,
        emptyTitle: 'Nothing found',
        emptyDescription: 'Try another search or clear the filters.',
        perPageOptions: () => PER_PAGE_OPTIONS,
    },
);

const emit = defineEmits<{
    sort: [sort: string | null];
    page: [page: number];
    perPage: [perPage: number];
    rowClick: [row: T];
}>();

const slots = defineSlots<
    {
        [K in `cell-${string}`]?: (props: {
            row: T;
            value: unknown;
        }) => unknown;
    } & {
        'bulk-actions'?: (props: { rows: T[]; clear: () => void }) => unknown;
        'row-actions'?: (props: { row: T }) => unknown;
        empty?: () => unknown;
    }
>();

// ---------------------------------------------------------------- columns

const columnById = computed(
    () => new Map(props.columns.map((column) => [column.key, column])),
);

function columnOf(id: string): TableColumn<T> {
    return columnById.value.get(id)!;
}

const columnDefs = computed<ColumnDef<T, unknown>[]>(() =>
    props.columns.map((column) => ({
        id: column.key,
        accessorFn: (row: T) => cellValue(row, column),
    })),
);

const hasRowActions = computed(() => Boolean(slots['row-actions']));

const totalColumns = computed(
    () =>
        props.columns.length +
        (props.selectable ? 1 : 0) +
        (hasRowActions.value ? 1 : 0),
);

const alignClass: Record<ColumnAlign, string> = {
    left: 'text-left',
    center: 'text-center',
    right: 'text-right',
};

function cellClass(column: TableColumn<T>): string {
    return cn(
        alignClass[column.align ?? 'left'],
        column.align === 'right' && 'tabular-nums',
        column.class,
    );
}

// ---------------------------------------------------------------- table + selection

const rowSelection = ref<RowSelectionState>({});

const table = useVueTable<T>({
    get data() {
        return props.rows;
    },
    get columns() {
        return columnDefs.value;
    },
    getRowId: (row) => rowKeyOf(row, props.rowKey),
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    manualSorting: true,
    enableRowSelection: () => props.selectable,
    state: {
        get rowSelection() {
            return rowSelection.value;
        },
    },
    onRowSelectionChange: (updater) => {
        rowSelection.value =
            typeof updater === 'function'
                ? updater(rowSelection.value)
                : updater;
    },
});

// A new page or filter means new rows: forget the old selection.
watch(
    () => props.rows,
    () => (rowSelection.value = {}),
);

const selectedRows = computed(() =>
    table.getSelectedRowModel().rows.map((row) => row.original),
);

function clearSelection(): void {
    rowSelection.value = {};
}

const headerCheckState = computed<boolean | 'indeterminate'>(() => {
    if (table.getIsAllPageRowsSelected()) {
        return true;
    }

    return table.getIsSomePageRowsSelected() ? 'indeterminate' : false;
});

// ---------------------------------------------------------------- sorting

const sortState = computed(() => parseSort(props.sort));

function sortDirectionOf(column: TableColumn<T>) {
    const key = sortKeyOf(column);

    return key && sortState.value?.key === key
        ? sortState.value.direction
        : null;
}

function toggleSort(column: TableColumn<T>): void {
    const key = sortKeyOf(column);

    if (key) {
        emit('sort', formatSort(nextSort(sortState.value, key)));
    }
}

function ariaSort(column: TableColumn<T>) {
    const direction = sortDirectionOf(column);

    if (!sortKeyOf(column)) {
        return undefined;
    }

    if (!direction) {
        return 'none';
    }

    return direction === 'asc' ? 'ascending' : 'descending';
}

const skeletonRows = 5;
const showSkeleton = computed(() => props.loading && props.rows.length === 0);
</script>

<!--
    List-page table (spec p.2: status tabs, search + filters, DataTable, paginator).
    Server-driven: sorting and paging are emitted, the page reloads with new rows.
    Pair it with useTableQuery(), DamnerLaorTableToolbar and DamnerLaorStatusTabs.
-->
<template>
    <div class="flex flex-col gap-3">
        <div
            v-if="selectable && slots['bulk-actions'] && selectedRows.length"
            class="flex flex-wrap items-center gap-2 rounded-lg border bg-accent px-3 py-2 text-sm"
            role="region"
            aria-label="Bulk actions"
        >
            <span class="font-medium">{{ selectedRows.length }} selected</span>
            <slot
                name="bulk-actions"
                :rows="selectedRows"
                :clear="clearSelection"
            />
            <Button
                variant="ghost"
                size="sm"
                class="ml-auto"
                @click="clearSelection"
            >
                Clear
            </Button>
        </div>

        <div class="overflow-hidden rounded-lg border">
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow class="hover:bg-transparent">
                        <TableHead v-if="selectable" class="w-10">
                            <Checkbox
                                :model-value="headerCheckState"
                                aria-label="Select all rows on this page"
                                :disabled="!rows.length"
                                @update:model-value="
                                    (checked) =>
                                        table.toggleAllPageRowsSelected(
                                            checked === true,
                                        )
                                "
                            />
                        </TableHead>

                        <TableHead
                            v-for="column in columns"
                            :key="column.key"
                            :aria-sort="ariaSort(column)"
                            :style="
                                column.width
                                    ? { width: column.width }
                                    : undefined
                            "
                            :class="
                                cn(
                                    'text-xs whitespace-nowrap text-muted-foreground',
                                    alignClass[column.align ?? 'left'],
                                    column.headerClass,
                                )
                            "
                        >
                            <button
                                v-if="sortKeyOf(column)"
                                type="button"
                                :class="
                                    cn(
                                        '-mx-1 inline-flex items-center gap-1 rounded px-1 py-0.5 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
                                        sortDirectionOf(column) &&
                                            'text-foreground',
                                    )
                                "
                                @click="toggleSort(column)"
                            >
                                {{ column.label }}
                                <ArrowUp
                                    v-if="sortDirectionOf(column) === 'asc'"
                                    class="size-3.5"
                                />
                                <ArrowDown
                                    v-else-if="
                                        sortDirectionOf(column) === 'desc'
                                    "
                                    class="size-3.5"
                                />
                                <ChevronsUpDown
                                    v-else
                                    class="size-3.5 opacity-50"
                                />
                            </button>
                            <template v-else>{{ column.label }}</template>
                        </TableHead>

                        <TableHead v-if="hasRowActions" class="w-12">
                            <span class="sr-only">Actions</span>
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody
                    :aria-busy="loading"
                    :class="
                        cn(
                            'transition-opacity',
                            loading && !showSkeleton && 'opacity-60',
                        )
                    "
                >
                    <template v-if="showSkeleton">
                        <TableRow v-for="index in skeletonRows" :key="index">
                            <TableCell v-for="col in totalColumns" :key="col">
                                <Skeleton class="h-4 w-full max-w-32" />
                            </TableCell>
                        </TableRow>
                    </template>

                    <template v-else-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="
                                row.getIsSelected() ? 'selected' : undefined
                            "
                            :class="cn(clickableRows && 'cursor-pointer')"
                            @click="
                                clickableRows && emit('rowClick', row.original)
                            "
                        >
                            <TableCell
                                v-if="selectable"
                                class="w-10"
                                @click.stop
                            >
                                <Checkbox
                                    :model-value="row.getIsSelected()"
                                    aria-label="Select row"
                                    @update:model-value="
                                        (checked) =>
                                            row.toggleSelected(checked === true)
                                    "
                                />
                            </TableCell>

                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                :class="cellClass(columnOf(cell.column.id))"
                            >
                                <slot
                                    :name="`cell-${cell.column.id}`"
                                    :row="row.original"
                                    :value="cell.getValue()"
                                >
                                    {{ displayValue(cell.getValue()) }}
                                </slot>
                            </TableCell>

                            <TableCell
                                v-if="hasRowActions"
                                class="w-12 text-right"
                                @click.stop
                            >
                                <slot name="row-actions" :row="row.original" />
                            </TableCell>
                        </TableRow>
                    </template>

                    <TableEmpty v-else :colspan="totalColumns">
                        <slot name="empty">
                            <div
                                class="flex flex-col items-center gap-1 text-center whitespace-normal"
                            >
                                <Inbox
                                    class="mb-1 size-6 text-muted-foreground"
                                />
                                <p class="font-medium">{{ emptyTitle }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ emptyDescription }}
                                </p>
                            </div>
                        </slot>
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <DamnerLaorTablePagination
            v-if="meta && meta.total > 0"
            :meta="meta"
            :per-page-options="perPageOptions"
            :disabled="loading"
            @page="emit('page', $event)"
            @per-page="emit('perPage', $event)"
        />
    </div>
</template>
