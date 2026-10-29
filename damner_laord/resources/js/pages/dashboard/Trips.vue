<script setup lang="ts">
import { Head, usePoll } from '@inertiajs/vue3';
import ActivityFeed from '@/components/common/ActivityFeed.vue';
import KpiGrid from '@/components/common/KpiGrid.vue';
import SectionCard from '@/components/common/SectionCard.vue';
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import DeparturesCard from '@/components/dashboard/DeparturesCard.vue';
import LiveMapCard from '@/components/dashboard/LiveMapCard.vue';
import { kpiIcons, LIVE_REFRESH_MS } from '@/config/dashboard';
import { formatLongDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { trips } from '@/routes/dashboard';
import type { TripsTracker } from '@/types';

defineProps<{
    tracker: TripsTracker;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Trips', href: trips() },
        ],
    },
});

usePoll(LIVE_REFRESH_MS, { only: ['tracker'] });
</script>

<template>
    <Head title="Trips tracker" />

    <DashboardShell
        :description="`Live departures · ${formatLongDate(tracker.generatedAt)}`"
    >
        <KpiGrid :kpis="tracker.kpis" :icons="kpiIcons" />

        <section class="grid gap-4 xl:grid-cols-3">
            <LiveMapCard class="xl:col-span-2" />
            <SectionCard title="Alerts">
                <ActivityFeed
                    :items="tracker.alerts"
                    empty-text="No delays or cancellations."
                />
            </SectionCard>
        </section>

        <DeparturesCard :departures="tracker.departures" />
    </DashboardShell>
</template>
