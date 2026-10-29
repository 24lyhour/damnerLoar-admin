<script setup lang="ts">
import SectionCard from '@/components/common/SectionCard.vue';
import { formatCurrency } from '@/lib/format';
import type { MoneyLine } from '@/types';

defineProps<{
    lines: MoneyLine[];
    net: MoneyLine;
    note?: string;
}>();

function signed(amount: number): string {
    return amount < 0
        ? `−${formatCurrency(Math.abs(amount))}`
        : formatCurrency(amount);
}
</script>

<!-- "Where the money goes": gross down to platform net revenue. -->
<template>
    <SectionCard title="Where the money goes · MTD">
        <dl class="flex flex-col text-sm">
            <div
                v-for="line in lines"
                :key="line.label"
                class="flex items-center justify-between border-b py-2.5"
            >
                <dt>{{ line.label }}</dt>
                <dd class="font-semibold tabular-nums">
                    {{ signed(line.amount) }}
                </dd>
            </div>
            <div
                class="mt-1 flex items-center justify-between border-t-2 border-foreground pt-3"
            >
                <dt class="font-medium">{{ net.label }}</dt>
                <dd class="text-base font-bold text-emerald-600 tabular-nums">
                    {{ signed(net.amount) }}
                </dd>
            </div>
        </dl>
        <p v-if="note" class="mt-2 text-xs text-muted-foreground">
            {{ note }}
        </p>
    </SectionCard>
</template>
