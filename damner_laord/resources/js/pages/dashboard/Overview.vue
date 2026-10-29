<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarDays, Download } from '@lucide/vue';
import KpiGrid from '@/components/common/KpiGrid.vue';
import TrendChartCard from '@/components/dashboard/TrendChartCard.vue';
import BusTripTypeCard from '@/components/dashboard/BusTripTypeCard.vue';
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import LatestBookingsCard from '@/components/dashboard/LatestBookingsCard.vue';
import NeedsAttentionCard from '@/components/dashboard/NeedsAttentionCard.vue';
import { Button } from '@/components/ui/button';
import { kpiIcons } from '@/config/dashboard';
import { formatLongDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { DashboardOverview } from '@/types';

defineProps<{
    overview: DashboardOverview;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Overview', href: dashboard() },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <DashboardShell
        :description="`${formatLongDate(overview.generatedAt)} · live data from the last 24 hours`"
    >
        <template #actions>
            <Button variant="outline" size="sm" disabled>
                <CalendarDays />
                Last 30 days
            </Button>
            <Button variant="outline" size="sm" disabled>
                <Download />
                Export
            </Button>
        </template>

        <KpiGrid :kpis="overview.kpis" :icons="kpiIcons" />

        <section class="grid gap-4 xl:grid-cols-3">
            <TrendChartCard
                class="xl:col-span-2"
                :title="`Bookings · last ${overview.bookingsTrend.labels.length} days`"
                :labels="overview.bookingsTrend.labels"
                :series="overview.bookingsTrend.series"
            />
            <NeedsAttentionCard :items="overview.attention" />
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <LatestBookingsCard
                class="xl:col-span-2"
                :bookings="overview.latestBookings"
            />
            <BusTripTypeCard
                title="Bus trip type · today"
                :total="overview.busTripType.total"
                :slices="overview.busTripType.slices"
                :cancelled-return-rate="
                    overview.busTripType.cancelledReturnRate
                "
            />
        </section>
    </DashboardShell>
</template>
