import { describe, expect, it } from 'vitest';
import type { TableColumn, TableQueryState } from './types';
import {
    cellValue,
    displayValue,
    formatSort,
    nextSort,
    pageWindow,
    parseSort,
    rowKeyOf,
    sortKeyOf,
    toQueryParams,
} from './utils';

type Booking = {
    reference: string;
    amount: number;
    customer: { name: string };
};

const booking: Booking = {
    reference: 'DL-PK-2611',
    amount: 20900,
    customer: { name: 'Sokha Chan' },
};

describe('sort helpers', () => {
    it('parses and formats the server sort string', () => {
        expect(parseSort('-created_at')).toEqual({
            key: 'created_at',
            direction: 'desc',
        });
        expect(parseSort('amount')).toEqual({
            key: 'amount',
            direction: 'asc',
        });
        expect(parseSort(null)).toBeNull();
        // Regression: an empty PHP array arrived as [] and filters.sort was Array.prototype.sort
        expect(parseSort(Array.prototype.sort)).toBeNull();
        expect(formatSort({ key: 'amount', direction: 'desc' })).toBe(
            '-amount',
        );
        expect(formatSort(null)).toBeNull();
    });

    it('cycles none -> asc -> desc -> none on the same column', () => {
        const asc = nextSort(null, 'amount');
        const desc = nextSort(asc, 'amount');

        expect(asc).toEqual({ key: 'amount', direction: 'asc' });
        expect(desc).toEqual({ key: 'amount', direction: 'desc' });
        expect(nextSort(desc, 'amount')).toBeNull();
    });

    it('starts ascending when switching to another column', () => {
        expect(nextSort({ key: 'amount', direction: 'desc' }, 'date')).toEqual({
            key: 'date',
            direction: 'asc',
        });
    });

    it('uses the column key, a custom sort key, or none', () => {
        const byKey: TableColumn<Booking> = {
            key: 'amount',
            label: 'Amount',
            sortable: true,
        };
        const custom: TableColumn<Booking> = {
            key: 'customer',
            label: 'Customer',
            sortable: 'customer_name',
        };
        const off: TableColumn<Booking> = {
            key: 'reference',
            label: 'Booking',
        };

        expect(sortKeyOf(byKey)).toBe('amount');
        expect(sortKeyOf(custom)).toBe('customer_name');
        expect(sortKeyOf(off)).toBeNull();
    });
});

describe('cell helpers', () => {
    it('reads row[key] or a custom value function', () => {
        expect(cellValue(booking, { key: 'amount', label: 'Amount' })).toBe(
            20900,
        );
        expect(
            cellValue(booking, {
                key: 'customer',
                label: 'Customer',
                value: (row) => row.customer.name,
            }),
        ).toBe('Sokha Chan');
    });

    it('shows an em dash for empty values', () => {
        expect(displayValue(null)).toBe('—');
        expect(displayValue(undefined)).toBe('—');
        expect(displayValue('')).toBe('—');
        expect(displayValue(0)).toBe('0');
        expect(displayValue(false)).toBe('false');
        expect(displayValue({ city: 'Kampot' })).toBe('{"city":"Kampot"}');
    });

    it('builds row keys from a field or a function', () => {
        expect(rowKeyOf(booking, 'reference')).toBe('DL-PK-2611');
        expect(rowKeyOf(booking, (row) => `${row.reference}-x`)).toBe(
            'DL-PK-2611-x',
        );
    });
});

describe('pageWindow', () => {
    it('lists every page when there are few', () => {
        expect(pageWindow(1, 3)).toEqual([1, 2, 3]);
        expect(pageWindow(1, 1)).toEqual([1]);
    });

    it('adds gaps around the current page', () => {
        expect(pageWindow(5, 20)).toEqual([1, '…', 4, 5, 6, '…', 20]);
        expect(pageWindow(1, 20)).toEqual([1, 2, '…', 20]);
        expect(pageWindow(20, 20)).toEqual([1, '…', 19, 20]);
    });

    it('does not add a gap between neighbouring pages', () => {
        expect(pageWindow(3, 5)).toEqual([1, 2, 3, 4, 5]);
    });
});

describe('toQueryParams', () => {
    const base: TableQueryState<{ city: string | null; type: string | null }> =
        {
            search: '',
            status: null,
            sort: null,
            page: 1,
            perPage: 20,
            filters: { city: null, type: null },
        };

    it('leaves out defaults and empty values', () => {
        expect(toQueryParams(base)).toEqual({});
    });

    it('maps state to Laravel query names', () => {
        expect(
            toQueryParams({
                ...base,
                search: '  Kampot ',
                status: 'active',
                sort: '-created_at',
                page: 3,
                perPage: 50,
                filters: { city: 'phnom-penh', type: null },
            }),
        ).toEqual({
            search: 'Kampot',
            status: 'active',
            sort: '-created_at',
            page: 3,
            per_page: 50,
            city: 'phnom-penh',
        });
    });
});
