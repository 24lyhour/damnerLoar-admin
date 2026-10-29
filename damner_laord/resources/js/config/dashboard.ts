import {
    AlertTriangle,
    ArrowRightLeft,
    BarChart3,
    Bus,
    CheckCircle2,
    Clock,
    CreditCard,
    Home,
    Landmark,
    Map,
    Percent,
    Repeat,
    RotateCcw,
    Search,
    Store,
    Ticket,
    Wallet,
    X,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { dashboard } from '@/routes';
import { bookings, finance, trips } from '@/routes/dashboard';
import type { PageTab } from '@/types';

/** Spec 02: one sidebar item, 4 tabs. */
export const dashboardTabs: PageTab[] = [
    { title: 'Overview', href: dashboard(), icon: Home },
    { title: 'Bookings', href: bookings(), icon: Ticket },
    { title: 'Finance', href: finance(), icon: BarChart3 },
    { title: 'Trips', href: trips(), icon: Map },
];

/** Icon per KPI key sent by the backend (all dashboard tabs). */
export const kpiIcons: Record<string, LucideIcon> = {
    // Overview
    bookings_today: Ticket,
    gross_sales_today: CreditCard,
    refunds_waiting: RotateCcw,
    merchants_to_approve: Store,
    // Bookings tracker
    searches: Search,
    active_holds: Clock,
    paid_bookings: CheckCircle2,
    conversion: Percent,
    round_trip_share: Repeat,
    // Finance tracker
    gross_sales: CreditCard,
    platform_revenue: Wallet,
    refunds: RotateCcw,
    payway_unsettled: Landmark,
    owed_to_merchants: Store,
    // Trips tracker
    trips_today: Bus,
    on_the_road: ArrowRightLeft,
    on_time: Clock,
    delayed: AlertTriangle,
    cancelled: X,
};

/** How often the live trackers refresh (spec: "updates every 30 s"). */
export const LIVE_REFRESH_MS = 30_000;
