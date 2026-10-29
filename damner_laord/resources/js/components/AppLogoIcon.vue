<script setup lang="ts">
import { brand } from '@/config/brand';

// Callers pass size classes (size-6, size-8 …) through $attrs.
</script>

<!-- DamnerLaor pin mark. The PNG has a white background, so it always sits on a white tile. -->
<template>
    <img
        :src="brand.logoMark"
        :alt="brand.name"
        class="shrink-0 rounded-md bg-white object-contain ring-1 ring-black/5"
    />
</template>
