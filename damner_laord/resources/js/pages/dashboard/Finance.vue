<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarDays, Download } from '@lucide/vue';
import KpiGrid from '@/components/common/KpiGrid.vue';
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import MoneyFlowCard from '@/components/dashboard/MoneyFlowCard.vue';
import RevenueByServiceCard from '@/components/dashboard/RevenueByServiceCard.vue';
import SettlementsCard from '@/components/dashboard/SettlementsCard.vue';
import TrendChartCard from '@/components/dashboard/TrendChartCard.vue';
import { Button } from '@/components/ui/button';
import { kpiIcons } from '@/config/dashboard';
import { dashboard } from '@/routes';
import { finance } from '@/routes/dashboard';
import type { FinanceTracker } from '@/types';

defineProps<{
    tracker: FinanceTracker;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Finance', href: finance() },
        ],
    },
});
</script>

<template>
    <Head title="Finance tracker" />

    <DashboardShell
        :description="`Month to date · ${tracker.period.from} – ${tracker.period.to} · USD`"
    >
        <template #actions>
            <Button variant="outline" size="sm" disabled>
                <CalendarDays />
                {{ tracker.period.label }}
            </Button>
            <Button variant="outline" size="sm" disabled>
                <Download />
                Export
            </Button>
        </template>

        <KpiGrid :kpis="tracker.kpis" :icons="kpiIcons" />

        <section class="grid gap-4 xl:grid-cols-3">
            <TrendChartCard
                class="xl:col-span-2"
                title="Cash flow · daily"
                :labels="tracker.cashFlow.labels"
                :series="tracker.cashFlow.series"
            />
            <MoneyFlowCard
                :lines="tracker.moneyFlow.lines"
                :net="tracker.moneyFlow.net"
                :note="tracker.moneyFlow.note"
            />
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <RevenueByServiceCard
                class="xl:col-span-2"
                :rows="tracker.revenueByService"
            />
            <SettlementsCard :settlements="tracker.settlements" />
        </section>
    </DashboardShell>
</template>
