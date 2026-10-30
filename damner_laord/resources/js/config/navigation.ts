import {
    Building2,
    Bus,
    ClipboardList,
    Headset,
    LayoutDashboard,
    Megaphone,
    Settings,
    Store,
    Users,
    Wallet,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import { index as merchants } from '@/routes/merchant/merchants';
import type { NavGroup } from '@/types';

/**
 * Admin sidebar (spec: 10 items in 5 sections).
 * Items without an href are not built yet and show as "Soon".
 */
export const adminNavigation: NavGroup[] = [
    {
        items: [
            { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
        ],
    },
    {
        label: 'Partners',
        items: [
            {
                title: 'Merchants',
                href: merchants(),
                icon: Store,
                badgeKey: 'merchants',
            },
            { title: 'Transport', icon: Bus },
            { title: 'Hotels', icon: Building2 },
        ],
    },
    {
        label: 'Operations',
        items: [
            { title: 'Bookings', icon: ClipboardList },
            { title: 'Customers', icon: Users },
            { title: 'Support', icon: Headset },
        ],
    },
    {
        label: 'Money',
        items: [{ title: 'Finance', icon: Wallet }],
    },
    {
        label: 'Growth',
        items: [{ title: 'Marketing', icon: Megaphone }],
    },
    {
        label: 'System',
        items: [{ title: 'Settings', icon: Settings }],
    },
];
