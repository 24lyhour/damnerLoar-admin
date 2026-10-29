export type ServiceType = 'bus' | 'hotel' | 'package';

export type BookingStatus =
    | 'confirmed'
    | 'awaiting_payment'
    | 'payment_failed'
    | 'cancelled'
    | 'boarding';

export type KpiFormat = 'number' | 'currency' | 'percent';

export type Kpi = {
    key: string;
    label: string;
    value: number;
    format: KpiFormat;
    /** Change vs the previous period, e.g. 8.2 */
    change?: number;
    /** "percent" -> 8.2%, "points" -> 0.3 pt */
    changeUnit?: 'percent' | 'points';
    changeLabel?: string;
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

export type BarListItem = {
    label: string;
    value: number;
    /** Optional text on the right, e.g. "75% of previous" */
    note?: string;
};

export type ActivityTone = 'success' | 'error' | 'neutral';

export type ActivityItem = {
    key: string;
    time: string;
    title: string;
    description: string;
    tone: ActivityTone;
};

export type ActiveHold = {
    reference: string;
    item: string;
    /** Seconds left before the hold expires */
    secondsLeft: number;
};

export type BookingsTracker = {
    generatedAt: string;
    kpis: Kpi[];
    funnel: {
        steps: BarListItem[];
        insight: string;
    };
    activity: ActivityItem[];
    byService: ChartSlice[];
    busTripType: {
        total: number;
        slices: ChartSlice[];
    };
    activeHolds: ActiveHold[];
};

export type SettlementStatus = 'expected' | 'ready' | 'settled' | 'paid';

export type Settlement = {
    date: string;
    flow: string;
    amount: number;
    status: SettlementStatus;
};

export type ServiceRevenue = {
    service: ServiceType;
    bookings: number;
    gross: number;
    commission: number;
    commissionRate: number;
    refunds: number;
    /** Share of bookings that are round-trip, null when not applicable */
    roundTripShare: number | null;
};

export type MoneyLine = {
    label: string;
    amount: number;
};

export type FinanceTracker = {
    period: { label: string; from: string; to: string };
    kpis: Kpi[];
    cashFlow: {
        labels: string[];
        series: ChartSeries[];
    };
    moneyFlow: {
        lines: MoneyLine[];
        net: MoneyLine;
        note: string;
    };
    revenueByService: ServiceRevenue[];
    settlements: Settlement[];
};

export type TripStatus =
    | 'scheduled'
    | 'boarding'
    | 'departed'
    | 'delayed'
    | 'cancelled';

export type Departure = {
    time: string;
    route: string;
    operator: string;
    seatsSold: number;
    seatsTotal: number;
    boarded: number;
    roundTripPassengers: number;
    status: TripStatus;
    /** Minutes late, only for delayed trips */
    delayMinutes?: number;
};

export type TripsTracker = {
    generatedAt: string;
    kpis: Kpi[];
    alerts: ActivityItem[];
    departures: Departure[];
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
