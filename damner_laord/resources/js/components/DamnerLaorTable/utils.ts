import type {
    RowKey,
    SortState,
    TableColumn,
    TableFilters,
    TableQueryState,
} from './types';

export const PER_PAGE_OPTIONS = [10, 20, 50] as const;
export const DEFAULT_PER_PAGE = 20;

/** "-created_at" -> { key: "created_at", direction: "desc" } */
export function parseSort(sort: string | null | undefined): SortState {
    if (!sort) {
        return null;
    }

    return sort.startsWith('-')
        ? { key: sort.slice(1), direction: 'desc' }
        : { key: sort, direction: 'asc' };
}

export function formatSort(state: SortState): string | null {
    if (!state) {
        return null;
    }

    return state.direction === 'desc' ? `-${state.key}` : state.key;
}

/** Click cycle on a header: none -> asc -> desc -> none. */
export function nextSort(current: SortState, key: string): SortState {
    if (!current || current.key !== key) {
        return { key, direction: 'asc' };
    }

    return current.direction === 'asc' ? { key, direction: 'desc' } : null;
}

export function sortKeyOf<T>(column: TableColumn<T>): string | null {
    if (!column.sortable) {
        return null;
    }

    return column.sortable === true ? column.key : column.sortable;
}

export function cellValue<T>(row: T, column: TableColumn<T>): unknown {
    return column.value
        ? column.value(row)
        : (row as Record<string, unknown>)[column.key];
}

/**
 * Default cell text: empty values show as an em dash. Objects should get a
 * `#cell-<key>` slot; until then they print as JSON, not "[object Object]".
 */
export function displayValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (
        typeof value === 'string' ||
        typeof value === 'number' ||
        typeof value === 'boolean'
    ) {
        return String(value);
    }

    return JSON.stringify(value);
}

export function rowKeyOf<T>(row: T, rowKey: RowKey<T>): string {
    return typeof rowKey === 'function'
        ? rowKey(row)
        : String((row as Record<string, unknown>)[rowKey]);
}

/**
 * Page buttons with gaps, always showing first and last:
 * (5, 20) -> [1, '…', 4, 5, 6, '…', 20]
 */
export function pageWindow(
    current: number,
    last: number,
    siblings = 1,
): Array<number | '…'> {
    const pages = new Set<number>([1, last]);

    for (let page = current - siblings; page <= current + siblings; page++) {
        if (page >= 1 && page <= last) {
            pages.add(page);
        }
    }

    const sorted = [...pages].sort((a, b) => a - b);
    const result: Array<number | '…'> = [];

    sorted.forEach((page, index) => {
        const previous = sorted[index - 1];

        if (previous !== undefined && page - previous > 1) {
            result.push('…');
        }

        result.push(page);
    });

    return result;
}

/**
 * Turn table state into URL query params, leaving out defaults so URLs stay
 * short: { search: 'pp', page: 1, perPage: 20 } -> { search: 'pp' }
 */
export function toQueryParams<F extends TableFilters>(
    state: TableQueryState<F>,
    defaultPerPage = DEFAULT_PER_PAGE,
): Record<string, string | number> {
    const params: Record<string, string | number> = {};

    if (state.search.trim()) {
        params.search = state.search.trim();
    }

    if (state.status) {
        params.status = state.status;
    }

    if (state.sort) {
        params.sort = state.sort;
    }

    if (state.page > 1) {
        params.page = state.page;
    }

    if (state.perPage !== defaultPerPage) {
        params.per_page = state.perPage;
    }

    for (const [key, value] of Object.entries(state.filters)) {
        if (value !== null && value !== '') {
            params[key] = value;
        }
    }

    return params;
}
