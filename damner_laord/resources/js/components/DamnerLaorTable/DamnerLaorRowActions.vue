<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { visitModal } from '@inertiaui/modal-vue';
import { MoreHorizontal } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { toUrl } from '@/lib/utils';
import type { RowAction } from './types';

const props = withDefaults(
    defineProps<{
        actions: RowAction[];
        /** Screen-reader label, e.g. "Actions for Capital Express" */
        label?: string;
    }>(),
    {
        label: 'Row actions',
    },
);

const visible = computed(() =>
    props.actions.filter((action) => !action.hidden),
);
const regular = computed(() =>
    visible.value.filter((action) => !action.destructive),
);
const destructive = computed(() =>
    visible.value.filter((action) => action.destructive),
);

/** Plain links navigate; modal actions open their route in an InertiaUI modal. */
function isPageLink(action: RowAction): boolean {
    return Boolean(action.href) && !action.modal;
}

function select(action: RowAction): void {
    if (action.modal && action.href) {
        void visitModal(toUrl(action.href));

        return;
    }

    action.onSelect?.();
}
</script>

<!--
    "⋯" menu for a table row. Use it in DamnerLaorTable's #row-actions slot:

    <template #row-actions="{ row }">
        <DamnerLaorRowActions :actions="actionsFor(row)" :label="`Actions for ${row.name}`" />
    </template>

    Destructive items come last, after a separator, in red. Nothing renders
    when every action is hidden.
-->
<template>
    <DropdownMenu v-if="visible.length">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon-sm" :aria-label="label">
                <MoreHorizontal />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="min-w-44">
            <template v-for="action in regular" :key="action.key">
                <DropdownMenuItem
                    v-if="isPageLink(action) && action.href"
                    as-child
                    :disabled="action.disabled"
                >
                    <Link :href="action.href" class="flex items-center gap-2">
                        <component :is="action.icon" v-if="action.icon" />
                        {{ action.label }}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem
                    v-else
                    :disabled="action.disabled"
                    @select="select(action)"
                >
                    <component :is="action.icon" v-if="action.icon" />
                    {{ action.label }}
                </DropdownMenuItem>
            </template>

            <template v-if="destructive.length">
                <DropdownMenuSeparator v-if="regular.length" />
                <DropdownMenuItem
                    v-for="action in destructive"
                    :key="action.key"
                    variant="destructive"
                    :disabled="action.disabled"
                    @select="select(action)"
                >
                    <component :is="action.icon" v-if="action.icon" />
                    {{ action.label }}
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
