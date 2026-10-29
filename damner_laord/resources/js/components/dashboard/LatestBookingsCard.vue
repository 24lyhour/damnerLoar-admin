<script setup lang="ts">
import StatusBadge from '@/components/common/StatusBadge.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { bookingStatusMeta, serviceMeta } from '@/config/bookings';
import { formatCurrency } from '@/lib/format';
import type { LatestBooking } from '@/types';

defineProps<{
    bookings: LatestBooking[];
}>();
</script>

<template>
    <Card class="gap-2">
        <CardHeader>
            <CardTitle class="text-sm font-semibold">Latest bookings</CardTitle>
        </CardHeader>
        <CardContent class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr
                        class="border-b text-left text-xs text-muted-foreground"
                    >
                        <th class="py-2 pr-3 font-medium">Booking</th>
                        <th class="py-2 pr-3 font-medium">Customer</th>
                        <th class="py-2 pr-3 font-medium">Service</th>
                        <th class="py-2 pr-3 text-right font-medium">Amount</th>
                        <th class="py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="booking in bookings" :key="booking.reference">
                        <td class="py-2.5 pr-3 font-mono text-xs">
                            {{ booking.reference }}
                        </td>
                        <td class="py-2.5 pr-3 whitespace-nowrap">
                            {{ booking.customer }}
                        </td>
                        <td class="py-2.5 pr-3">
                            <span class="flex items-center gap-1.5">
                                <component
                                    :is="serviceMeta[booking.service].icon"
                                    class="size-4 text-primary"
                                />
                                {{ serviceMeta[booking.service].label }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-3 text-right tabular-nums">
                            {{ formatCurrency(booking.amount) }}
                        </td>
                        <td class="py-2.5">
                            <StatusBadge
                                :label="bookingStatusMeta[booking.status].label"
                                :tone="bookingStatusMeta[booking.status].tone"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </CardContent>
    </Card>
</template>
