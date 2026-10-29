import {
    BarChart3,
    CreditCard,
    Home,
    Map,
    RotateCcw,
    Store,
    Ticket,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { dashboard } from '@/routes';
import type { PageTab } from '@/types';

/** Spec 02: one sidebar item, 4 tabs. Tabs without an href are not built yet. */
export const dashboardTabs: PageTab[] = [
    { title: 'Overview', href: dashboard(), icon: Home },
    { title: 'Bookings', icon: Ticket },
    { title: 'Finance', icon: BarChart3 },
    { title: 'Trips', icon: Map },
];

/** Icon per KPI key sent by the backend. */
export const kpiIcons: Record<string, LucideIcon> = {
    bookings_today: Ticket,
    gross_sales_today: CreditCard,
    refunds_waiting: RotateCcw,
    merchants_to_approve: Store,
};
