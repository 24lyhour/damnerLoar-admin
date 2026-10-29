export type ServiceType = 'bus' | 'hotel' | 'package';

export type BookingStatus =
    | 'confirmed'
    | 'awaiting_payment'
    | 'payment_failed'
    | 'cancelled'
    | 'boarding';

export type KpiFormat = 'number' | 'currency';

export type Kpi = {
    key: string;
    label: string;
    value: number;
    format: KpiFormat;
    /** Percent change vs the previous period, e.g. 8.2 */
    change?: number;
    hint?: string;
};

export type AttentionSeverity = 'error' | 'info';

export type AttentionItem = {
    key: string;
    title: string;
    description: string;
    severity: AttentionSeverity;
};

export type LatestBooking = {
    reference: string;
    customer: string;
    service: ServiceType;
    amount: number;
    status: BookingStatus;
};

export type ChartSeries = {
    label: string;
    data: number[];
};

export type ChartSlice = {
    label: string;
    value: number;
};

export type DashboardOverview = {
    generatedAt: string;
    kpis: Kpi[];
    bookingsTrend: {
        labels: string[];
        series: ChartSeries[];
    };
    attention: AttentionItem[];
    latestBookings: LatestBooking[];
    busTripType: {
        total: number;
        slices: ChartSlice[];
        cancelledReturnRate: number;
    };
};
