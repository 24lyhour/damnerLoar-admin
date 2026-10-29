<script setup lang="ts">
import PageHeader from '@/components/common/PageHeader.vue';
import PageTabs from '@/components/common/PageTabs.vue';
import { dashboardTabs } from '@/config/dashboard';

defineProps<{
    description: string;
}>();
</script>

<!-- Shared frame for every Dashboard tab: title, actions, tab bar. -->
<template>
    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <PageHeader title="Dashboard" :description="description">
            <template v-if="$slots.actions" #actions>
                <slot name="actions" />
            </template>
        </PageHeader>

        <PageTabs :tabs="dashboardTabs" />

        <slot />
    </div>
</template>
