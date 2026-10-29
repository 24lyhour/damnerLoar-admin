<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavGroup } from '@/types';

defineProps<{
    groups: NavGroup[];
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup
        v-for="(group, index) in groups"
        :key="group.label ?? index"
        class="px-2 py-1"
    >
        <SidebarGroupLabel v-if="group.label">
            {{ group.label }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in group.items" :key="item.title">
                <SidebarMenuButton
                    v-if="item.href"
                    as-child
                    :is-active="isCurrentOrParentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuButton
                    v-else
                    disabled
                    :tooltip="`${item.title} (coming soon)`"
                >
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                </SidebarMenuButton>

                <SidebarMenuBadge
                    v-if="item.badge"
                    class="rounded-full bg-sidebar-primary text-sidebar-primary-foreground"
                >
                    {{ item.badge }}
                </SidebarMenuBadge>
                <SidebarMenuBadge
                    v-else-if="!item.href"
                    class="text-[10px] font-normal text-muted-foreground"
                >
                    Soon
                </SidebarMenuBadge>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
