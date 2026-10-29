<script setup lang="ts">
import { Head, usePoll } from '@inertiajs/vue3';
import BarChart from '@/components/charts/BarChart.vue';
import ActivityFeed from '@/components/common/ActivityFeed.vue';
import BarList from '@/components/common/BarList.vue';
import KpiGrid from '@/components/common/KpiGrid.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import ActiveHoldsCard from '@/components/dashboard/ActiveHoldsCard.vue';
import BusTripTypeCard from '@/components/dashboard/BusTripTypeCard.vue';
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { kpiIcons, LIVE_REFRESH_MS } from '@/config/dashboard';
import { formatLongDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { bookings } from '@/routes/dashboard';
import type { BookingsTracker } from '@/types';

defineProps<{
    tracker: BookingsTracker;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Bookings', href: bookings() },
        ],
    },
});

usePoll(LIVE_REFRESH_MS, { only: ['tracker'] });
</script>

<template>
    <Head title="Bookings tracker" />

    <DashboardShell
        :description="`Live · ${formatLongDate(tracker.generatedAt)} · updates every 30 s`"
    >
        <KpiGrid :kpis="tracker.kpis" :icons="kpiIcons" />

        <section class="grid gap-4 xl:grid-cols-3">
            <SectionCard
                class="xl:col-span-2"
                title="Booking funnel · today"
                content-class="flex flex-col gap-4"
            >
                <BarList :items="tracker.funnel.steps" />
                <p class="text-xs text-muted-foreground">
                    {{ tracker.funnel.insight }}
                </p>
            </SectionCard>

            <SectionCard title="Live activity">
                <ActivityFeed :items="tracker.activity" />
            </SectionCard>
        </section>

        <section class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            <SectionCard title="By service · today">
                <BarChart :slices="tracker.byService" />
            </SectionCard>

            <BusTripTypeCard
                title="Bus trip type"
                :total="tracker.busTripType.total"
                :slices="tracker.busTripType.slices"
            />

            <ActiveHoldsCard
                class="lg:col-span-2 xl:col-span-1"
                :holds="tracker.activeHolds"
            />
        </section>
    </DashboardShell>
</template>
