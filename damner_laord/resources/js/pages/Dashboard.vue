<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarDays, Download } from '@lucide/vue';
import KpiCard from '@/components/common/KpiCard.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import PageTabs from '@/components/common/PageTabs.vue';
import BookingsTrendCard from '@/components/dashboard/BookingsTrendCard.vue';
import BusTripTypeCard from '@/components/dashboard/BusTripTypeCard.vue';
import LatestBookingsCard from '@/components/dashboard/LatestBookingsCard.vue';
import NeedsAttentionCard from '@/components/dashboard/NeedsAttentionCard.vue';
import { Button } from '@/components/ui/button';
import { dashboardTabs, kpiIcons } from '@/config/dashboard';
import { formatLongDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { DashboardOverview } from '@/types';

defineProps<{
    overview: DashboardOverview;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Overview',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <PageHeader
            title="Dashboard"
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
        </PageHeader>

        <PageTabs :tabs="dashboardTabs" />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <KpiCard
                v-for="kpi in overview.kpis"
                :key="kpi.key"
                :label="kpi.label"
                :value="kpi.value"
                :format="kpi.format"
                :change="kpi.change"
                :hint="kpi.hint"
                :icon="kpiIcons[kpi.key]"
            />
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <BookingsTrendCard
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
                :total="overview.busTripType.total"
                :slices="overview.busTripType.slices"
                :cancelled-return-rate="
                    overview.busTripType.cancelledReturnRate
                "
            />
        </section>
    </div>
</template>
