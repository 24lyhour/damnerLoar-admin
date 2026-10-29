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

<!--
    Two-column auth layout (shadcn-vue login-02, spec 01.1).
    Both columns sit as framed cards on a soft brand-grey page so the
    bright cover photo never meets the page edge head-on.
-->
<template>
    <div
        class="grid min-h-svh gap-6 bg-muted p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] dark:bg-background"
    >
        <aside
            class="relative hidden overflow-hidden rounded-2xl bg-primary shadow-xl ring-1 ring-black/5 lg:block dark:ring-white/10"
        >
            <img
                :src="brand.authCover"
                :alt="`${brand.name}: travel, transport and hotel booking across Cambodia`"
                class="absolute inset-0 size-full object-cover"
            />
            <!-- Soft inner edge so the photo reads as framed, not cut off -->
            <div
                class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgb(255_255_255/0.08)]"
            />
        </aside>

        <main class="flex flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center justify-center gap-2 font-medium lg:justify-start"
            >
                <AppLogoIcon class="size-8 shadow-sm" />
                {{ brand.name }}
            </Link>

            <div class="flex flex-1 items-center justify-center">
                <div
                    class="flex w-full max-w-sm flex-col gap-6 rounded-2xl border bg-card p-6 text-card-foreground shadow-xl sm:p-8 dark:bg-muted/40 dark:shadow-black/40"
                >
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

            <p
                class="flex items-center justify-center gap-1.5 text-xs text-muted-foreground lg:justify-start"
            >
                <ShieldCheck class="size-3.5" />
                {{ brand.notice }}
            </p>
        </main>
    </div>
</template>
