<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { cn } from '@/lib/utils';
import type { PageTab } from '@/types';

defineProps<{
    tabs: PageTab[];
}>();

const { isCurrentUrl } = useCurrentUrl();

const baseClass =
    '-mb-px flex items-center gap-1.5 border-b-2 px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors [&_svg]:size-4';
</script>

<template>
    <nav class="flex overflow-x-auto border-b" aria-label="Page sections">
        <template v-for="tab in tabs" :key="tab.title">
            <Link
                v-if="tab.href"
                :href="tab.href"
                :class="
                    cn(
                        baseClass,
                        isCurrentUrl(tab.href)
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:text-foreground',
                    )
                "
                :aria-current="isCurrentUrl(tab.href) ? 'page' : undefined"
            >
                <component :is="tab.icon" v-if="tab.icon" />
                {{ tab.title }}
            </Link>
            <span
                v-else
                :class="
                    cn(
                        baseClass,
                        'cursor-not-allowed border-transparent text-muted-foreground/60',
                    )
                "
                :title="`${tab.title} (coming soon)`"
            >
                <component :is="tab.icon" v-if="tab.icon" />
                {{ tab.title }}
            </span>
        </template>
    </nav>
</template>
