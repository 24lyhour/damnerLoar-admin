<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { brand } from '@/config/brand';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<!-- Two-column auth layout based on the shadcn-vue login-02 block. -->
<template>
    <div class="grid min-h-svh lg:grid-cols-2">
        <div class="flex flex-col gap-4 p-6 md:p-10">
            <div class="flex justify-center gap-2 md:justify-start">
                <Link
                    :href="home()"
                    class="flex items-center gap-2 font-medium"
                >
                    <span
                        class="flex size-6 items-center justify-center rounded-md bg-primary text-primary-foreground"
                    >
                        <AppLogoIcon class="size-4 fill-current" />
                    </span>
                    {{ brand.name }}
                </Link>
            </div>

            <div class="flex flex-1 items-center justify-center">
                <div class="flex w-full max-w-xs flex-col gap-6">
                    <div
                        v-if="title || description"
                        class="flex flex-col items-center gap-1 text-center"
                    >
                        <h1 v-if="title" class="text-2xl font-bold">
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="text-sm text-balance text-muted-foreground"
                        >
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>

        <aside
            class="relative hidden flex-col justify-between bg-primary p-10 text-primary-foreground lg:flex"
        >
            <div class="flex flex-col gap-4">
                <AppLogoIcon class="size-12 fill-current" />
                <div>
                    <p class="text-2xl font-semibold">{{ brand.nameKhmer }}</p>
                    <p class="text-3xl font-bold">{{ brand.product }}</p>
                </div>
                <p class="max-w-sm text-sm opacity-80">{{ brand.tagline }}</p>
            </div>

            <p class="flex items-center gap-2 text-xs opacity-70">
                <ShieldCheck class="size-4" />
                {{ brand.notice }}
            </p>
        </aside>
    </div>
</template>
