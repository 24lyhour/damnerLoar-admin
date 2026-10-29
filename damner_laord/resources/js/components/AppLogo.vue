<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { brand } from '@/config/brand';
</script>

<template>
    <AppLogoIcon class="size-8" />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold">
            {{ brand.name }}
        </span>
        <span class="truncate text-xs text-muted-foreground">Admin</span>
    </div>
</template>
