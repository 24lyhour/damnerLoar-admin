import type { SettlementStatus, StatusTone } from '@/types';

export const settlementStatusMeta: Record<
    SettlementStatus,
    { label: string; tone: StatusTone }
> = {
    expected: { label: 'Expected', tone: 'neutral' },
    ready: { label: 'Ready', tone: 'brand' },
    settled: { label: 'Settled', tone: 'success' },
    paid: { label: 'Paid', tone: 'success' },
};
