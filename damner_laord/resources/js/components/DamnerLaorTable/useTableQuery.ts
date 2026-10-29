import { router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { proxyRefs, reactive, readonly, ref } from 'vue';
import type { DeepReadonly, Ref } from 'vue';
import type { TableFilters, TableQueryState } from './types';
import { DEFAULT_PER_PAGE, toQueryParams } from './utils';

type Options<F extends TableFilters> = {
    /** Current query as echoed back by the controller (`filters` prop) */
    initial?: Partial<Omit<TableQueryState<F>, 'filters'>> & {
        filters?: Partial<F>;
    };
    /** Filter keys this page supports, with their "all" value */
    filters?: F;
    /** Page props to reload; omit to reload the whole page */
    only?: string[];
    defaultPerPage?: number;
    /** Wait this long after typing before searching (ms) */
    searchDelay?: number;
};

export type TableQuery<F extends TableFilters> = {
    state: DeepReadonly<TableQueryState<F>>;
    /** Bind with v-model; requests are debounced */
    search: string;
    readonly isLoading: boolean;
    setStatus: (status: string | null) => void;
    setSort: (sort: string | null) => void;
    setPage: (page: number) => void;
    setPerPage: (perPage: number) => void;
    setFilter: <K extends keyof F>(key: K, value: F[K]) => void;
    reset: () => void;
};

/** TableQuery with its two live values still as refs (before proxyRefs). */
type WithRefs<Q extends { search: string; isLoading: boolean }> = Omit<
    Q,
    'search' | 'isLoading'
> & {
    search: Ref<string>;
    isLoading: Readonly<Ref<boolean>>;
};

/**
 * Keeps a list page's search, status tab, filters, sort and paging in the
 * URL and reloads the Inertia page when they change. Any change other than
 * the page number jumps back to page 1.
 */
export function useTableQuery<F extends TableFilters = TableFilters>(
    options: Options<F> = {},
): TableQuery<F> {
    const defaultPerPage = options.defaultPerPage ?? DEFAULT_PER_PAGE;
    const emptyFilters = { ...(options.filters ?? ({} as F)) };
    const initial = options.initial ?? {};

    const state = reactive({
        search: initial.search ?? '',
        status: initial.status ?? null,
        sort: initial.sort ?? null,
        page: initial.page ?? 1,
        perPage: initial.perPage ?? defaultPerPage,
        filters: { ...emptyFilters, ...initial.filters },
    }) as TableQueryState<F>;

    const search = ref(state.search);
    const isLoading = ref(false);
    const page = usePage();

    function visit(): void {
        const path = page.url.split('?')[0];

        router.get(path, toQueryParams(state, defaultPerPage), {
            only: options.only,
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => (isLoading.value = true),
            onFinish: () => (isLoading.value = false),
        });
    }

    /** Apply a change, reset to page 1, reload. */
    function update(change: () => void): void {
        change();
        state.page = 1;
        visit();
    }

    watchDebounced(
        search,
        (value) => {
            // Skip when reset() already applied this value
            if (value !== state.search) {
                update(() => (state.search = value));
            }
        },
        { debounce: options.searchDelay ?? 350 },
    );

    const api: WithRefs<TableQuery<F>> = {
        state: readonly(state) as DeepReadonly<TableQueryState<F>>,
        search,
        isLoading: readonly(isLoading),
        setStatus: (status) => update(() => (state.status = status)),
        setSort: (sort) => update(() => (state.sort = sort)),
        setPerPage: (perPage) => update(() => (state.perPage = perPage)),
        setFilter: (key, value) =>
            update(() => {
                state.filters[key] = value;
            }),
        setPage: (target) => {
            state.page = target;
            visit();
        },
        reset: () =>
            update(() => {
                search.value = '';
                state.search = '';
                state.status = null;
                state.sort = null;
                state.perPage = defaultPerPage;
                state.filters = { ...emptyFilters };
            }),
    };

    // proxyRefs unwraps the refs, so templates use `table.search`, not `.value`
    return proxyRefs(api);
}
