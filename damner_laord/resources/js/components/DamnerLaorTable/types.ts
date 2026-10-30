import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

/** Pagination block Laravel sends with `Resource::collection($paginator)`. */
export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    /** First row number on this page, null when empty */
    from: number | null;
    /** Last row number on this page, null when empty */
    to: number | null;
};

/** A paginated Resource collection as it arrives in page props. */
export type Paginated<T> = {
    data: T[];
    meta: PaginationMeta;
};

export type ColumnAlign = 'left' | 'center' | 'right';

/**
 * One table column. Keep it declarative; render custom cells with a
 * `#cell-<key>="{ row, value }"` slot on <DamnerLaorTable>.
 */
export type TableColumn<T> = {
    /** Field name, or any unique id when `value` is given */
    // `string & Record<never, never>` keeps autocomplete for T's fields
    key: (keyof T & string) | (string & Record<never, never>);
    label: string;
    /** Read the cell value; defaults to `row[key]` */
    value?: (row: T) => unknown;
    /** Server-side sort key; true uses `key`. Omit to disable sorting. */
    sortable?: boolean | string;
    align?: ColumnAlign;
    /** Extra classes for every body cell, e.g. "font-mono text-xs" */
    class?: string;
    headerClass?: string;
    /** Fixed width, e.g. "8rem" */
    width?: string;
};

export type RowKey<T> = (keyof T & string) | ((row: T) => string);

/** One entry in a row's "⋯" menu (DamnerLaorRowActions). */
export type RowAction = {
    key: string;
    label: string;
    icon?: LucideIcon;
    /** Navigate (Wayfinder route); otherwise use onSelect */
    href?: InertiaLinkProps['href'];
    /** Open `href` as an InertiaUI modal (Inertia::modal route) */
    modal?: boolean;
    onSelect?: () => void;
    /** Red text, shown last after a separator */
    destructive?: boolean;
    /** Leave it out entirely (e.g. no permission) */
    hidden?: boolean;
    disabled?: boolean;
};

export type StatusTab = {
    /** null = the "All" tab */
    value: string | null;
    label: string;
    count?: number;
};

export type SelectOption = {
    value: string;
    label: string;
};

export type SortDirection = 'asc' | 'desc';

export type SortState = {
    key: string;
    direction: SortDirection;
} | null;

/** Everything a list page keeps in the URL. */
export type TableQueryState<F extends TableFilters = TableFilters> = {
    search: string;
    status: string | null;
    /** "created_at" (asc) or "-created_at" (desc) */
    sort: string | null;
    page: number;
    perPage: number;
    filters: F;
};

export type TableFilters = Record<string, string | null>;
