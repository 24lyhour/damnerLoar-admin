<script setup lang="ts" generic="T extends Record<string, unknown>">
import { cn } from '@/lib/utils';
import type { DataTableColumn } from '@/types';

const props = defineProps<{
    columns: DataTableColumn<T>[];
    rows: T[];
    /** Field name, or a function for rows without a single unique field */
    rowKey: (keyof T & string) | ((row: T) => string);
    emptyText?: string;
}>();

function keyOf(row: T): string {
    return typeof props.rowKey === 'function'
        ? props.rowKey(row)
        : String(row[props.rowKey]);
}

defineSlots<
    {
        [K in `cell-${keyof T & string}`]?: (props: { row: T }) => unknown;
    } & {
        footer?: () => unknown;
    }
>();

const alignClass = {
    left: 'text-left',
    right: 'text-right',
    center: 'text-center',
} as const;
</script>

<!--
    Small read-only table for cards. Override any cell with a
    `#cell-<key>="{ row }"` slot; other cells print the raw value.
-->
<template>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b text-xs text-muted-foreground">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :class="
                            cn(
                                'py-2 pr-3 font-medium whitespace-nowrap last:pr-0',
                                alignClass[column.align ?? 'left'],
                            )
                        "
                    >
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="row in rows" :key="keyOf(row)">
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        :class="
                            cn(
                                'py-2.5 pr-3 last:pr-0',
                                alignClass[column.align ?? 'left'],
                                column.align === 'right' && 'tabular-nums',
                                column.class,
                            )
                        "
                    >
                        <slot :name="`cell-${column.key}`" :row="row">
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
                <tr v-if="!rows.length">
                    <td
                        :colspan="columns.length"
                        class="py-6 text-center text-muted-foreground"
                    >
                        {{ emptyText ?? 'Nothing to show.' }}
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="$slots.footer" class="border-t font-semibold">
                <slot name="footer" />
            </tfoot>
        </table>
    </div>
</template>
