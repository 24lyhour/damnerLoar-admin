<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue';
import {
    Download,
    Eye,
    PauseCircle,
    Pencil,
    PlayCircle,
    Plus,
} from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/common/PageHeader.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import {
    DamnerLaorRowActions,
    DamnerLaorStatusTabs,
    DamnerLaorTable,
    DamnerLaorTableSelectFilter,
    DamnerLaorTableToolbar,
    useTableQuery,
} from '@/components/DamnerLaorTable';
import type {
    RowAction,
    StatusTab,
    TableColumn,
} from '@/components/DamnerLaorTable';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { formatDate } from '@/lib/format';
import {
    confirmReactivate,
    confirmSuspend,
    create,
    edit,
    index,
    show,
} from '@/routes/merchant/merchants';
import type { MerchantIndexProps, MerchantListItem } from '../../../../types';

const props = defineProps<MerchantIndexProps>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Partners', href: index() },
            { title: 'Merchants', href: index() },
        ],
    },
});

type MerchantTableFilters = { type: string | null; city: string | null };

const table = useTableQuery<MerchantTableFilters>({
    initial: {
        search: props.filters.search ?? '',
        status: props.filters.status,
        sort: props.filters.sort,
        perPage: props.filters.per_page,
        filters: {
            type: props.filters.type,
            city: props.filters.city,
        },
    },
    filters: { type: null, city: null },
    only: ['merchants', 'filters', 'counts'],
});

// Spec tabs; "Changes requested" / "Rejected" only appear when they have rows.
const tabs = computed<StatusTab[]>(() => {
    const { counts } = props;
    const extra: StatusTab[] = [
        {
            value: 'changes_requested',
            label: 'Changes requested',
            count: counts.changes_requested,
        },
        { value: 'rejected', label: 'Rejected', count: counts.rejected },
    ];

    return [
        { value: null, label: 'All', count: counts.all },
        { value: 'pending', label: 'Pending approval', count: counts.pending },
        { value: 'active', label: 'Active', count: counts.active },
        { value: 'suspended', label: 'Suspended', count: counts.suspended },
        ...extra.filter((tab) => (tab.count ?? 0) > 0),
    ];
});

const typeOptions = computed(() =>
    props.types.map((type) => ({ value: type.value, label: type.label })),
);

const cityOptions = computed(() =>
    props.cities.map((city) => ({ value: city, label: city })),
);

const columns: TableColumn<MerchantListItem>[] = [
    { key: 'name', label: 'Merchant', sortable: true },
    { key: 'type', label: 'Type' },
    { key: 'city', label: 'City' },
    {
        key: 'commissionPercent',
        label: 'Commission',
        align: 'right',
        sortable: 'commission_rate',
    },
    { key: 'status', label: 'Status' },
    { key: 'joinedAt', label: 'Joined', sortable: 'created_at' },
];

const hasActiveFilters = computed(() =>
    Boolean(
        table.state.search ||
        table.state.filters.type ||
        table.state.filters.city,
    ),
);

function open(merchant: MerchantListItem): void {
    router.visit(show({ merchant: merchant.uuid }));
}

/**
 * Row "⋯" menu: view, edit and suspend / reactivate open the same server
 * modals as the merchant page. Approve, reject and request changes stay on
 * the detail page, next to the document checklist.
 */
function rowActions(merchant: MerchantListItem): RowAction[] {
    const args = { merchant: merchant.uuid };

    return [
        {
            key: 'view',
            label: 'View details',
            icon: Eye,
            href: show(args),
        },
        {
            key: 'edit',
            label: 'Edit details',
            icon: Pencil,
            href: edit(args),
            modal: true,
            hidden: !merchant.can.update,
        },
        {
            key: 'reactivate',
            label: 'Reactivate',
            icon: PlayCircle,
            href: confirmReactivate(args),
            modal: true,
            hidden: !merchant.can.reactivate,
        },
        {
            key: 'suspend',
            label: 'Suspend',
            icon: PauseCircle,
            href: confirmSuspend(args),
            modal: true,
            destructive: true,
            hidden: !merchant.can.suspend,
        },
    ];
}
</script>

<template>
    <Head title="Merchants" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <PageHeader
            title="Merchants"
            description="Bus operators and hotels selling on DamnerLaor"
        >
            <template #actions>
                <Button
                    variant="outline"
                    size="sm"
                    disabled
                    title="Coming soon"
                >
                    <Download />
                    Export
                </Button>
                <ModalLink
                    :href="create().url"
                    :class="buttonVariants({ size: 'sm' })"
                >
                    <Plus />
                    Invite merchant
                </ModalLink>
            </template>
        </PageHeader>

        <DamnerLaorStatusTabs
            :tabs="tabs"
            :model-value="table.state.status"
            @update:model-value="table.setStatus"
        />

        <Card class="py-4">
            <CardContent class="flex flex-col gap-4 px-4">
                <DamnerLaorTableToolbar
                    v-model:search="table.search"
                    search-placeholder="Search name, email, phone…"
                    :can-reset="hasActiveFilters"
                    @reset="table.reset"
                >
                    <template #filters>
                        <DamnerLaorTableSelectFilter
                            label="Type"
                            :options="typeOptions"
                            :model-value="table.state.filters.type"
                            @update:model-value="
                                (value) => table.setFilter('type', value)
                            "
                        />
                        <DamnerLaorTableSelectFilter
                            label="City"
                            :options="cityOptions"
                            :model-value="table.state.filters.city"
                            @update:model-value="
                                (value) => table.setFilter('city', value)
                            "
                        />
                    </template>
                </DamnerLaorTableToolbar>

                <DamnerLaorTable
                    :columns="columns"
                    :rows="merchants.data"
                    row-key="uuid"
                    :meta="merchants.meta"
                    :sort="table.state.sort"
                    :loading="table.isLoading"
                    clickable-rows
                    empty-title="No merchants found"
                    @sort="table.setSort"
                    @page="table.setPage"
                    @per-page="table.setPerPage"
                    @row-click="open"
                >
                    <template #cell-name="{ row }">
                        <div class="flex items-center gap-3">
                            <Avatar class="size-8">
                                <AvatarFallback
                                    class="bg-accent text-xs font-semibold text-primary"
                                >
                                    {{ row.initials }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ row.name }}
                                </p>
                                <p
                                    v-if="row.subtitle"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ row.subtitle }}
                                </p>
                            </div>
                        </div>
                    </template>
                    <template #cell-type="{ row }">{{
                        row.type.label
                    }}</template>
                    <template #cell-commissionPercent="{ row }">
                        {{ row.commissionPercent }} %
                    </template>
                    <template #cell-status="{ row }">
                        <StatusBadge
                            :label="row.status.label"
                            :tone="row.status.tone"
                        />
                    </template>
                    <template #cell-joinedAt="{ row }">
                        {{ row.joinedAt ? formatDate(row.joinedAt) : '—' }}
                    </template>
                    <template #row-actions="{ row }">
                        <DamnerLaorRowActions
                            :actions="rowActions(row)"
                            :label="`Actions for ${row.name}`"
                        />
                    </template>
                </DamnerLaorTable>
            </CardContent>
        </Card>
    </div>
</template>
