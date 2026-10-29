<script setup lang="ts">
import { MapPinned } from '@lucide/vue';
import SectionCard from '@/components/common/SectionCard.vue';
</script>

<!--
    Spec 02.4: live bus map is a phase 2 feature (GPS from the merchant app
    every 60 s, Leaflet or Google Maps). Placeholder until then.
-->
<template>
    <SectionCard
        title="Live map · buses on the road"
        description="GPS comes from the merchant app (driver phone) every 60 s."
    >
        <div
            class="flex h-48 flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/40 text-center"
        >
            <MapPinned class="size-6 text-muted-foreground" />
            <p class="text-sm font-medium">Live map arrives in phase 2</p>
            <p class="max-w-xs text-xs text-muted-foreground">
                Needs GPS tracking in the merchant app first.
            </p>
        </div>
    </SectionCard>
</template>
