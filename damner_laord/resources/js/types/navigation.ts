import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};

/** Sidebar entry. Without an href it renders as a disabled "Soon" item. */
export type SidebarNavItem = Omit<NavItem, 'href'> & {
    href?: NavItem['href'];
    /** Key in the shared `navBadges` prop, e.g. "merchants" */
    badgeKey?: string;
};

export type NavGroup = {
    label?: string;
    items: SidebarNavItem[];
};

export type PageTab = {
    title: string;
    href?: NavItem['href'];
    icon?: LucideIcon;
};
